<?php
	/* 		
		*
		* OpenGL hardware capability database server implementation
		*	
		* Copyright (C) 2011-2018 by Sascha Willems (www.saschawillems.de)
		*	
		* This code is free software, you can redistribute it and/or
		* modify it under the terms of the GNU Affero General Public
		* License version 3 as published by the Free Software Foundation.
		*	
		* Please review the following information to ensure the GNU Lesser
		* General Public License version 3 requirements will be met:
		* http://www.gnu.org/licenses/agpl-3.0.de.html
		*	
		* The code is distributed WITHOUT ANY WARRANTY; without even the
		* implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
		* PURPOSE.  See the GNU AGPL 3.0 for more details.		
		*
	*/
	
	include 'header.html';
	include 'dbconfig.php';
	
	DB::connect();	

    function shorten($string, $length) {
        return (strlen($string) >= $length) ? substr($string, 0, $length-10). " ... " : $string;
	}
	
	function implementation_details($reportids) {
		// TODO: Optimize, single statement
		$headerFields = array("GL_VENDOR", "GL_RENDERER", "GL_VERSION");
		foreach ($headerFields as $headerField) {
			echo "<tr>";		
			echo "<td>$headerField</td>";		 
			foreach ($reportids as $repid) {
				$stmnt = DB::$connection->prepare("SELECT $headerField FROM openglcaps WHERE ReportID = :reportid");
				$stmnt->execute(["reportid" => $repid]);
				$sqlrow = $stmnt->fetch(PDO::FETCH_NUM);
				echo "<td>".shorten($sqlrow[0], 32)."</td>";
			}	
			echo "</tr>";
		}
	}

	echo "<center>";
	
	if (isset($_GET['compare'])) {
		$reportids = array();
		$reportlimit = false;
		
		if (empty($_REQUEST['id'])) {
			die("<div style='padding-top: 15px;'><b>Note : </b>No reports selected to compare.</div>");
		}

		foreach ($_REQUEST['id'] as $k => $v) {
			$reportids[] = (int)$k;	
			if (count($reportids) > 7) {
				$reportlimit = true;	 
				break; 
			}
		}   
			
		if ($reportlimit) {echo "<b>Note : </b>You selected more than 8 reports to compare, only displaying the first 8 selected reports.\n"; }	

		if (empty($reportids)) {
			die("<div style='padding-top: 15px;'><b>Note : </b>No reports selected to compare.</div>");
		}

		$repids = implode(",", $reportids);   

		$spirvExtCount = DB::getCount("SELECT count(*) from spirvextensions where ReportID in ($repids)", []);

		?>
		<div class='header'>
			<h4 style='margin-left:10px;'>Comparing <?php echo count($reportids) ?> devices</h4>
			<label id="toggle-label" class="checkbox-inline" style="display:none;">
				<input id="toggle-event" type="checkbox" data-toggle="toggle" data-size="small" data-onstyle="success"> Display only different values
			</label>
		</div>				
		<?php

		sort($reportids, SORT_NUMERIC);
		
		$colspan = count($reportids) + 1;				

?>
		<!-- Tabs -->
		<div id='tabs'>
			<ul class='nav nav-tabs'>
				<li class='active'><a data-toggle='tab' href='#tab-implementation'>Implementation</a></li>
				<li><a data-toggle='tab' href='#tab-extensions'>Extensions</a></li>
				<?php if ($spirvExtCount > 0) { echo "<li><a data-toggle='tab' href='#tabs-spirv-ext'>SPIR-V Extensions</a></li>"; } ?>
				<li><a data-toggle='tab' href='#tab-compressed'>Compressed formats</a></li>
			</ul>
		</div>

		<div class='tablediv tab-content' style='width:75%;'>

		<!-- Implementation -->
		<div id='tab-implementation' class='tab-pane fade in active reportdiv'>
			<table id='caps' width='100%' class='table table-striped table-bordered table-hover'>
				<thead>
					<tr>
						<th>Capability</th>
						<?php foreach ($reportids as $reportId) { echo "<th>Report $reportId</th>"; } ?>
					</tr>
				</thead>
				<tbody>
					<?php		
						$stmnt = DB::$connection->prepare("SELECT * FROM openglcaps WHERE ReportID IN ($repids)");
						$stmnt->execute();
						$reportindex = 0;
							
						// Gather data into array
						$column    = array();
						$captions  = array();
							
						while($row = $stmnt->fetch(PDO::FETCH_NUM)) {
							$colindex = 0;
							$reportdata = array();									
							foreach ($row as $data) {
								$meta = $stmnt->getColumnMeta($colindex);
								$caption = $meta["name"];  	
								if (strpos($caption, 'GL_') !== false) {
									$reportdata[] = $data;	  
									$captions[]   = $caption;
								}								
								if ($caption == 'submitter') {
									$reportdata[] = $data;	  
									$captions[]   = 'Submitted by';
								}								
								if ($caption == 'os') {
									$reportdata[] = $data;	  
									$captions[]   = 'Operating System';
								}								
								$colindex++;
							} 							
							$column[] = $reportdata; 							
							$reportindex++;
						}   
							
						// Generate table from selected reports
						$index = 1;  
						for ($i = 0, $arrsize = sizeof($column[0]); $i < $arrsize; ++$i) { 	  
							$add = "";

							// Get min and max for this capability
							if (is_numeric($column[0][$i])) {
								
								$minval = $column[0][$i];
								$maxval = $column[0][$i];
								
								for ($j = 0, $subarrsize = sizeof($column); $j < $subarrsize; ++$j) {	 			
									if ($column[$j][$i] < $minval) {
										$minval = $column[$j][$i];
									}
									if ($column[$j][$i] > $maxval) {
										$maxval = $column[$j][$i];
									}
								}
							}								
								
							// Caption
							$fontStyle = ($minval < $maxval) ? "style='color:#FF0000;'" : "";					
							$headerFields = array("GL_VENDOR", "GL_RENDERER", "GL_VERSION", "GL_SHADING_LANGUAGE_VERSION", "Operating System", "Submitted by");
							if (!in_array($captions[$i], $headerFields)) {
								$className = ($minval < $maxval) ? "" : "class='sameCaps'";
							} else {
								$className = "";
							}
							echo "<tr $add $className>\n";
							echo "<td $fontStyle>". $captions[$i] ."</td>\n";									
							
							// Values
							for ($j = 0, $subarrsize = sizeof($column); $j < $subarrsize; ++$j) {	 
								$fontstyle = '';
								if ($captions[$i] == 'GL_RENDERER') {					
									echo "<td class='valuezeroleftblack'>".shorten($column[$j][$i], 32)."</td>";
								} else {
									if (is_numeric($column[$j][$i]) ) {
										
										if ($column[$j][$i] < $maxval) {
											$fontstyle = "style='color:#FF0000;'";
										}
										
										if ($captions[$i] == 'GL_SHADING_LANGUAGE_VERSION') {
											echo "<td>".number_format($column[$j][$i], 2, '.', ',')."</td>";
											} else {
											echo "<td $fontstyle>".number_format($column[$j][$i], 0, '.', ',')."</td>";
										}
										} else {
										echo "<td>".$column[$j][$i]."</td>";
									}
								}
							} 
							echo "</tr>\n";
							$index++;
						}	   
							
						}
						else {	  
							echo "No reports to compare...";
						}
						
					?>	
				</tbody>
			</table>
		</div>
		
		<!-- Extensions -->
		<div id='tab-extensions' class='tab-pane fade reportdiv'>
			<table id='extensions' width='100%' class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>Extension</th>
						<?php foreach ($reportids as $reportId) { echo "<th>Report $reportId</th>"; } ?>
					</tr>
				</thead>
				<tbody>
					<?php	
						// Gather all extensions supported by at least one of the reports
						$str = "";	
						$extcaption = array();						
						$stmnt = DB::$connection->prepare("SELECT DISTINCT Name FROM openglgpuandext LEFT JOIN openglextensions ON openglextensions.PK = openglgpuandext.ExtensionID WHERE openglgpuandext.ReportID IN ($repids)  ORDER BY FIELD(SUBSTR(openglextensions.Name, 1, 3), 'GL_') DESC, FIELD(SUBSTR(openglextensions.Name, INSTR(openglextensions.Name, '_')+1, 3), 'EXT', 'ARB') DESC, openglextensions.Name ASC");
						$stmnt->execute();
						while($row = $stmnt->fetch(PDO::FETCH_NUM)) {	
							foreach ($row as $data) {
								$extcaption[] = $data;	  
							}
						}
						
						// Get extensions for each selected report into an array 
						$extarray = array(); 				
						foreach ($reportids as $repid) {
							$stmnt = DB::$connection->prepare("SELECT Name FROM openglgpuandext LEFT JOIN openglextensions ON openglextensions.PK = openglgpuandext.ExtensionID WHERE openglgpuandext.ReportID = :reportid");
							$stmnt->execute(["reportid" => $repid]);	
							$str = " = $repid";			
							$subarray = array();
							while($row = $stmnt->fetch(PDO::FETCH_NUM)) {	
								foreach ($row as $data) {
									$subarray[] = $data;	  
								}
							}
							$extarray[] = $subarray; 
						}
						
						// Generate table
						$colspan = count($reportids) + 1;	
						
						implementation_details($reportids);
						
						// Extension count 	
						echo "<tr><td>Extension count</td>"; 
						for ($i = 0, $arrsize = sizeof($extarray); $i < $arrsize; ++$i) { 	  
							echo "<td>".count($extarray[$i])."</td>";
						}
						echo "</tr>"; 		
						$rowindex++;
						
						foreach ($extcaption as $extension){
							
							// Check if missing it at least one report
							$missing = false;
							$index = 0;
							foreach ($reportids as $repid) {
								if (!in_array($extension, $extarray[$index])) { 
									$missing = true;
								}
								$index++;
							}  			
							
							$add = '';
							if ($missing) {
								$add = 'color:#FF0000;';
							}
							$className = "same";
							$index = 0;
							foreach ($reportids as $repid) {
								if (!in_array($extension, $extarray[$index])) { 
									$className = "diff";
								}
								$index++;
							}
							echo "<tr style='$add' class='$className'><td>$extension</td>\n";		 
							$index = 0;
							foreach ($reportids as $repid) {
								echo "<td style='margin-left:10px;'><span class='". (in_array($extension, $extarray[$index]) ? "glyphicon glyphicon-ok supported" : "glyphicon glyphicon-remove unsupported")."'></td>";
								$index++;
							}  
							$rowindex++;
							echo "</tr>"; 
						}	 
					?>					
				</tbody>
			</table>
		</div>	

		<!-- SPIR-V Extensions -->
<?php		
		if ($spirvExtCount > 0) {
?>
			<div id='tabs-spirv-ext' class='tab-pane fade reportdiv'>
				<table id='spirv-extensions' width='100%' class='table table-striped table-bordered'>
					<thead>
						<tr>
							<th>SPIR-V Extension</th>
							<?php foreach ($reportids as $reportId) { echo "<th>Report $reportId</th>"; } ?>
						</tr>
					</thead>
					<tbody>
						<?php	
							$stmnt = DB::$connection->prepare("SELECT DISTINCT group_concat(name) FROM spirvextensions where reportid IN ($repids) ORDER by name desc");
							$stmnt->execute();						
							$extcaption = explode(',', $stmnt->fetchColumn());
							$extarray = array(); 				
							foreach ($reportids as $repid) {
								$stmnt = DB::$connection->prepare("SELECT group_concat(name) FROM spirvextensions where reportid = :reportid");
								$stmnt->execute(["reportid" => $repid]);	
								$subarray = array();
								while ($row = $stmnt->fetchColumn()) {	
									$subarray = explode(',', $row);
								}
								$extarray[] = $subarray; 
							}						
							$colspan = count($reportids) + 1;								
							implementation_details($reportids);							
							echo "<tr><td>Extension count</td>"; 
							foreach ($extarray as $ext) {
								echo "<td>".count($ext)."</td>";
							}
							echo "</tr>"; 		
							foreach ($extcaption as $extension) {
								$className = "same";
								$index = 0;
								foreach ($reportids as $repid) {
									if (!in_array($extension, $extarray[$index])) { 
										$className = "diff";
										break;
									}
									$index++;
								}  										
								echo "<tr ".($className == "diff" ? "style='color:#FF0000;'" : "")." class='$className'><td>$extension</td>\n";		 
								$index = 0;
								foreach ($reportids as $repid) {
									echo "<td style='margin-left:10px;'><span class='". (in_array($extension, $extarray[$index]) ? "glyphicon glyphicon-ok supported" : "glyphicon glyphicon-remove unsupported")."'></td>";
									$index++;
								}  
								echo "</tr>"; 
							}	 
						?>					
					</tbody>
				</table>
			</div>	
<?php
		}
?>

		<!-- Compressed formats -->
		<div id='tab-compressed' class='tab-pane fade reportdiv'>
			<table id='compressedformats' width='100%' class='table table-striped table-bordered table-hover'>
				<thead>
					<tr>
						<th>Compressed format</th>
						<?php foreach ($reportids as $reportId) { echo "<th>Report $reportId</th>"; } ?>					
					</tr>
				</thead>
				<tbody>
					<?php									
						// Gather all compressed formats supported by at least one of the reports
						$stmnt = DB::$connection->prepare(
							"SELECT DISTINCT text FROM compressedTextureFormats ctf LEFT JOIN enumTranslationTable ett ON ctf.formatEnum = ett.enum WHERE ctf.ReportID IN ($repids) ORDER BY text ASC"
						);
						$stmnt->execute();
						$formatcaptions = array();					
						while($row = $stmnt->fetch(PDO::FETCH_NUM)) {	
							foreach ($row as $data)	{
								$formatcaptions[] = $data;	  
							}
						}			
						// Get compressed formats for each selected report into an array 
						$formatarray = array(); 
						
						foreach ($reportids as $repid) {
							$stmnt = DB::$connection->prepare(
								"SELECT DISTINCT text FROM compressedTextureFormats ctf LEFT JOIN enumTranslationTable ett ON ctf.formatEnum = ett.enum WHERE ctf.ReportID = :reportid"
							);
							$stmnt->execute(["reportid" => $repid]);	
							$subarray = array();
							while($row = $stmnt->fetch(PDO::FETCH_NUM)) {	
								foreach ($row as $data) {
									$subarray[] = $data;	  
								}
							}
							$formatarray[] = $subarray; 
						}
						
						// Generate table
						$colspan = count($reportids) + 1;	
						
						implementation_details($reportids);
						
						// Format count 	
						echo "<tr><td>Format count</td>"; 
						for ($i = 0, $arrsize = sizeof($formatarray); $i < $arrsize; ++$i) { 	  
							echo "<td>".count($formatarray[$i])."</td>";
						}
						echo "</tr>"; 		
						$rowindex++;
						
						foreach ($formatcaptions as $format) {
							// Check if missing it at least one report
							$missing = false;
							$index = 0;
							foreach ($reportids as $repid) {
								if (!in_array($format, $formatarray[$index])) { 
									$missing = true;
								}
								$index++;
							}  			
							
							$add = ($missing) ? 'color:#FF0000;' : '';
							$className = "same";
							$index = 0;
							foreach ($reportids as $repid) {
								if (!in_array($format, $formatarray[$index])) 
								{ 
									$className = "diff";
								}
								$index++;
							}
							echo "<tr style='$add' class='$className'><td>$format</td>\n";		 
							$index = 0;
							foreach ($reportids as $repid) {
								echo "<td style='margin-left:10px;'><span class='". (in_array($format, $formatarray[$index]) ? "glyphicon glyphicon-ok supported" : "glyphicon glyphicon-remove unsupported")."'></td>";
								$index++;
							}  
							$rowindex++;
							echo "</tr>"; 
						}	  
					?>					
				</tbody>
			</table>
		</div>
	
	</div>

<?php	
	DB::disconnect();
	include 'footer.html';
?>
	
<script>
	$(document).ready(function() 
	{
		var tableNames = [ "#caps", "#extensions", "#compressedformats", "#spirv-extensions" ];
		for (var i=0; i < tableNames.length; i++) 
		{           
			$(tableNames[i]).DataTable({
				"pageLength" : -1,
				"paging" : false,
				"order": [], 
				"searchHighlight": true,
				"sDom": 'flpt',
				"deferRender": true							
			});
		}
		$("#toggle-label").show();			
	} );	

	$('#toggle-event').change(function() {
		if ($(this).prop('checked')) {
			$('.same').hide();
			$('.sameCaps').hide();
		} else {
			$('.same').show();				
			$('.sameCaps').show();
		}
	} );
</script>
	
</div>
</body>
</html>