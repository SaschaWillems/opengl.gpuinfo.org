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

	$reportID = (int)($_GET['id']); 
	
	$extCount = DB::getCount("SELECT count(*) from openglgpuandext where ReportID  = :reportid", [':reportid' => $reportID]);
	$compressedCount = DB::getCount("SELECT count(*) from compressedTextureFormats where reportid = :reportid", [':reportid' => $reportID]);
		
	$stmnt = DB::$connection->prepare("SELECT GL_RENDERER FROM openglcaps WHERE ReportID = :reportid");
	$stmnt->execute(["reportid" => $reportID]);
	$row = $stmnt->fetch(PDO::FETCH_ASSOC);  	
?>	
<center>
	<!-- Header -->
	<div class='header'>
	<h4 style='margin-left:10px;'>Report for <?php echo $row['GL_RENDERER']?></h4>
	</div>
				
	<!-- Tabs -->
	<div>
		<ul class='nav nav-tabs'>
			<li class='active'><a data-toggle='tab' href='#tabs-1'>Implementation</a></li>
			<li><a data-toggle='tab' href='#tabs-2'>Extensions <span class='badge'><?php echo $extCount ?></span></a></li>
			<li><a data-toggle='tab' href='#tabs-3'>Compressed formats <span class='badge'><?php echo $compressedCount ?></span></a></li>
		</ul>
	</div>

	<div class='tablediv tab-content' style='width:75%;'>

	<!-- Implementation and capabilities -->
	<div id='tabs-1' class='tab-pane fade in active reportdiv'>
		<table id='caps' class='table table-striped table-bordered table-hover responsive' style='width:100%;'>
			<thead>
				<tr>
					<td>Capability</td>
					<td>Value</td>
				</tr>
			</thead>
			<tbody>
				<?php	
					$stmnt = DB::$connection->prepare("SELECT * FROM openglcaps WHERE ReportID = :reportid");
					$stmnt->execute(["reportid" => $reportID]);

					$colindex  = 0;    
					$index = 0;
					$emptyCaps = array();
					
					while($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
						foreach ($row as $data) {
							$meta = $stmnt->getColumnMeta($index);
							$caption = $meta["name"];  	
							if (!is_null($data)) {
								if ($caption == 'submitter') {
									if ($data != '') {
										$stmntSub = DB::$connection->prepare("SELECT submissiondate FROM openglcaps WHERE ReportID = :reportid");
										$stmntSub->execute(["reportid" => $reportID]);				
										$subRow = $stmntSub->fetch(PDO::FETCH_NUM);
										if ($subRow[0] != "") {
											$submissionDate = " (".$subRow[0].")";
											} else {
											$submissionDate = "";
										}
										
										echo "<tr><td>Submitted by</td>";
										echo "<td><a href='./listreports.php?submitter=$data'>$data</a>$submissionDate</td></tr>";
										
										$stmntHistory = DB::$connection->prepare("SELECT date,submitter from reportHistory WHERE ReportID = :reportid");
										$stmntHistory->execute(["reportid" => $reportID]);				
										$historyCount = $stmntHistory->rowCount();
										$historyRow = $stmntHistory->fetch(PDO::FETCH_NUM);
										if ($historyCount > 0) {
											$index++;
											echo "<tr><td>Last update</td>";
											echo "<td><a href='./listreports.php?submitter=$historyRow[1]'>$historyRow[1]</a> ($historyRow[0])</td></tr>";
										}					
									}
								}
								
								if ($caption == 'os') {
									echo "<tr><td>Operating system</td>";
									echo "<td>$data</td></tr>";
								}
								
								if ($caption == 'comment') {
									echo "<tr><td>Comment</td>";
									echo "<td>$data</td></tr>";
								}

								if ($caption == 'contexttype') {
									$contextType = "OpenGL";
									if ($data == "core") {
										$contextType = "OpenGL core";
									}
									if ($data == "es2") {
										$contextType = "OpenGL ES 2.0";
									}
									if ($data == "es3") {
										$contextType = "OpenGL ES 3.0";
									}
									echo "<tr><td>Context type</td>";
									echo "<td>$contextType</td></tr>";
								}
								
								if (strpos($caption, 'GL_') !== false) {
									echo "<tr><td><a href='./displaycapability.php?name=$caption'>$caption</a></td>";
									
									if ((is_numeric($data) && ($caption!=='GL_SHADING_LANGUAGE_VERSION')) ) {
										echo "<td>".number_format($data)."</td></tr>";
										} else {
										echo "<td>$data</td></tr>";
									}
									
								}
							}
							
							if (strpos($caption, 'extensions') !== false) {
								$extstr = $data; 	  
							};
							if ((strpos($caption, 'GL_') !== false) && (is_null($data))) {	
								$emptyCaps[] = $caption;
							}
							$colindex++;
							$index++;
						}
						
					}	
				?>
			</tbody>
		</table>
	</div>

	<!-- Extensions -->
	<div id='tabs-2' class='tab-pane fade in reportdiv'>
	<table id='extensions' class='table table-striped table-bordered table-hover responsive' style='width:100%;'>
		<thead>
			<tr>
				<td>Extension</td>
			</tr>
		</thead>
		<tbody>
			<?php	
				$stmnt = DB::$connection->prepare("SELECT Name FROM openglgpuandext LEFT JOIN openglextensions ON openglextensions.PK = openglgpuandext.ExtensionID WHERE openglgpuandext.ReportID = :reportid ORDER BY FIELD(SUBSTR(openglextensions.Name, 1, 3), 'GL_') DESC, FIELD(SUBSTR(openglextensions.Name, INSTR(openglextensions.Name, '_')+1, 3), 'EXT', 'ARB') DESC, openglextensions.Name ASC");
				$stmnt->execute(["reportid" => $reportID]);
				$extarray = array();
				while($row = $stmnt->fetch(PDO::FETCH_NUM)) {	
					foreach ($row as $data) {
						$extarray[]= $data;
						echo "<tr><td><a href='./listreports.php?listreportsbyextension=$data'>$data</a></td></tr>";
					}	
				}
			?>
		</tbody>
		</table>
	</div>
	
	<!-- Compressed texture formats -->
	<div id='tabs-3' class='tab-pane fade in reportdiv'>
		<table id='compressedformats' class='table table-striped table-bordered table-hover reporttable'>
			<thead>
				<tr>
					<td>Compressed format</td>
				</tr>
			</thead>
			<tbody>
				<?php	
					$stmnt = DB::$connection->prepare("SELECT text from compressedTextureFormats ctf join enumTranslationTable ett on ctf.formatEnum = enum where reportId = :reportid");
					$stmnt->execute(["reportid" => $reportID]);
					if ($stmnt->rowCount() > 0) {
						while($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
							foreach ($row as $data) {
								echo "<tr><td><a href='./listreports.php?compressedtextureformat=$data'>$data</a></td></tr>";
							}
						}
					} else {
						echo "<tr><td>No compressed formats available or submitted</td></tr>";
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
</center>
	
	<script>
    	$(document).ready(function() 
        {
            var tableNames = [ "#caps", "#extensions", "#compressedformats" ];
	        for (var i=0; i < tableNames.length; i++) 
            {           
                $(tableNames[i]).DataTable({
					"pageLength" : -1,
					"paging" : false,
					"order": [], 
					"searchHighlight": true,
					"bAutoWidth": false,
					"sDom": 'flpt',
					"deferRender": true,
					"processing": true,				
                    "searchHighlight": true
                });
            }
		} );	


</script>	 	

</body>
</html>