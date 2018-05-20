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
	
	dbConnect();

	echo "<center>";
	
	// list single reports
	$reportID = (int)mysql_real_escape_string($_GET['id']); 
	
	// Counters for tab captions
	$sqlResult = mysql_query("SELECT count(*) from openglgpuandext where ReportID = $reportID");
	$extCount = mysql_result($sqlResult, 0);
	$sqlResult = mysql_query("SELECT count(*) from compressedTextureFormats where ReportID = $reportID");
	$compressedCount = mysql_result($sqlResult, 0);
	$sqlResult = mysql_query("SELECT count(*) from reportHistory where ReportID = $reportID");
	$historyCount = mysql_result($sqlResult, 0);
	
	$sqlresult = mysql_query("SELECT GL_RENDERER FROM openglcaps WHERE ReportID = $reportID");
	$row = mysql_fetch_array($sqlresult);  
	
	?>	
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
			<li><a data-toggle='tab' href='#tabs-5'>History <span class='badge'><?php echo $historyCount ?></span></a></li>
			<!-- <li><a data-toggle='tab' href='#tabs-6'>n/a</a></li> -->
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
	$sqlresult = mysql_query("SELECT * FROM openglcaps WHERE ReportID = $reportID");
	$colindex  = 0;    
	$index = 0;
	$emptyCaps = array();
	
	while($row = mysql_fetch_row($sqlresult))
	{
		foreach ($row as $data)
		{
			$caption = mysql_field_name($sqlresult, $colindex);		  
			if (!is_null($data)) {
				if ($caption == 'submitter') {
					if ($data != '') {
						$sqlSubRes = mysql_query("SELECT submissiondate from openglcaps WHERE ReportID = $reportID");
						$submissionRow = mysql_fetch_row($sqlSubRes);
						if ($submissionRow[0] != "") {
							$submissionDate = " (".$submissionRow[0].")";
							} else {
							$submissionDate = "";
						}
						
						echo "<tr><td>Submitted by</td>";
						echo "<td><a href='./listreports.php?submitter=$data'>$data</a>$submissionDate</td></tr>";
						
						$sqlHistoryResult = mysql_query("SELECT date,submitter from reportHistory where Reportid = $reportID order by Id desc");						
						$historyCount = mysql_num_rows($sqlHistoryResult);
						$historyRow = mysql_fetch_row($sqlHistoryResult);
						if ($historyCount > 0) {
							$index++;
							echo "<tr><td>Last update</td>";
							echo "<td><a href='./listreports.php?submitter=$historyRow[1]'>$historyRow[1]</a> ($historyRow[0])</td></tr>";
						}
						
						//						$index++;
						} else {
						//$index++;
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
	$str = "SELECT Name FROM openglgpuandext LEFT JOIN openglextensions ON openglextensions.PK = openglgpuandext.ExtensionID WHERE openglgpuandext.ReportID = $reportID ORDER BY FIELD(SUBSTR(openglextensions.Name, 1, 3), 'GL_') DESC, FIELD(SUBSTR(openglextensions.Name, INSTR(openglextensions.Name, '_')+1, 3), 'EXT', 'ARB') DESC, openglextensions.Name ASC";  
	$sqlresult = mysql_query($str);  
	$extarray = array();
	while($row = mysql_fetch_row($sqlresult)) {	
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
					$sqlresult = mysql_query("select text from compressedTextureFormats ctf join enumTranslationTable ett on ctf.formatEnum = enum where reportId = $reportID");  
					$sqlCount = mysql_num_rows($sqlresult);
					$compFormats = array();
					if ($sqlCount > 0) {
						while($row = mysql_fetch_row($sqlresult)) {
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

	<!-- Report history -->
	<div id='tabs-5' class='tab-pane fade in reportdiv'>
		<table id='history' class='table table-striped table-bordered table-hover responsive' style='width:100%;'>
			<thead>
				<tr>
					<td>Date</td>
					<td>Submitter</td>
					<td>Changes</td>
				</tr>
			</thead>
			<tbody>
				<?php
					if ($historyCount > 0) {	
						$sqlResult = mysql_query("SELECT date,submitter,log FROM reportHistory where reportId = $reportID order by id desc") or die(mysql_error());  
						while($row = mysql_fetch_row($sqlResult)) {
							echo "<tr><td valign=top>$row[0]</td>";
							echo "<td valign=top>$row[1]</td>";	
							echo "<td >$row[2]</td></tr>";	
						}		
						} else {
						echo "<tr><td >No updates have been made to this report yet</td>";
						echo "<td valign=top>&nbsp;</td>";	
						echo "<td>&nbsp;</td></tr>";	
					}
				?>
			</tbody>
		</table>
	</div>	

</div>

<?php	
	dbDisconnect();  
	include 'footer.html';
?>
</center>
	
	<script>
    	$(document).ready(function() 
        {
            var tableNames = [ "#caps", "#extensions", "#compressedformats", "#history" ];
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