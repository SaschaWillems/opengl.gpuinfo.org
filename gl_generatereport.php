<head>
	<link rel="stylesheet" href="./libs/jquery-ui/themes/flick/jquery-ui.css">
	<script src="./libs/jquery.min.js"></script>
	<script src="./libs/jquery-ui/jquery-ui.min.js"></script>
	<script>
		$(function() {
			$( "#tabs" ).tabs();
		});
	</script>
</head>
<body>
	<div>
		<?php
			/* 		
				*
				* OpenGL hardware capability database server implementation
				*	
				* Copyright (C) 2011-2015 by Sascha Willems (www.saschawillems.de)
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
			
			include './gl_htmlheader.inc';	
			include './gl_menu.inc';
			include './gl_config.php';
			
			dbConnect();	
			
			// list single reports
			$reportID = $_GET['reportID']; 
			
			// Counters for tab captions
			$sqlResult = mysql_query("select count(*) from openglgpuandext where ReportID = $reportID");
			$extCount = mysql_result($sqlResult, 0);
			$sqlResult = mysql_query("select count(*) from compressedTextureFormats where ReportID = $reportID");
			$compressedCount = mysql_result($sqlResult, 0);
			$sqlResult = mysql_query("select count(*) from reportHistory where ReportID = $reportID");
			$historyCount = mysql_result($sqlResult, 0);
			
			$sqlresult = mysql_query("SELECT GL_RENDERER FROM openglcaps WHERE ReportID = $reportID");
			$row = mysql_fetch_array($sqlresult);  
			
			echo "<table width='80%'><tr><td valign=top>"; 
			
			echo "<div id='tabs' style='font-size:12px;'>";
			echo "<ul>";
			echo "<h2 style='margin-left:10px;'>Report for '".$row['GL_RENDERER']."'</h2>";
			echo "<li><a href='#tabs-1'>Implementation</a></li>";
			echo "<li><a href='#tabs-2'>Extensions ($extCount)</a></li>";
			echo "<li><a href='#tabs-3'>Compressed formats ($compressedCount)</a></li>";
			echo "<li><a href='#tabs-4'>History ($historyCount)</a></li>";
			echo "<li><a href='#tabs-5'>n/a</a></li>";
			echo "</ul>";
			
			// Implementation and capabilities
			echo "<div id='tabs-1'>";
			echo "<table width='95%'>";
			
			$sqlresult = mysql_query("SELECT * FROM openglcaps WHERE ReportID = $reportID");
			$colindex  = 0;    
			$index = 0;
			$emptyCaps = array();
			
			while($row = mysql_fetch_row($sqlresult))
			{
				foreach ($row as $data)
				{
					$caption = mysql_field_name($sqlresult, $colindex);		  
					$bgcolor  = $index % 2 != 0 ? $bgcolordef : $bgcolorodd; 	  
					
					if (!is_null($data)) {
						if ($caption == 'submitter') {
							if ($data != '') {
								$sqlSubRes = mysql_query("select submissiondate from openglcaps WHERE ReportID = $reportID");
								$submissionRow = mysql_fetch_row($sqlSubRes);
								if ($submissionRow[0] != "") {
									$submissionDate = " (".$submissionRow[0].")";
									} else {
									$submissionDate = "";
								}
								
								echo "<tr style='background-color:$bgcolor;'><td class='firstrow'>Submitted by</td>";
								echo "<td class='valuezeroleftdark'><a href='listreports2.php?submitter=$data'>$data</a>$submissionDate</td></tr>";
								
								$sqlHistoryResult = mysql_query("SELECT date,submitter from reportHistory where Reportid = $reportID order by Id desc");						
								$historyCount = mysql_num_rows($sqlHistoryResult);
								$historyRow = mysql_fetch_row($sqlHistoryResult);
								if ($historyCount > 0) {
									$index++;
									$bgcolor  = $index % 2 != 0 ? $bgcolordef : $bgcolorodd; 	  
									echo "<tr style='background-color:$bgcolor;'><td class='firstrow'>Last update</td>";
									echo "<td class='valuezeroleftdark'><a href='listreports2.php?submitter=$historyRow[1]'>$historyRow[1]</a> ($historyRow[0])</td></tr>";
								}
								
								//						$index++;
								} else {
								//$index++;
							}
						}
						
						if ($caption == 'os') {
							echo "<tr style='background-color:$bgcolor;'><td class='firstrow'>Operating system</td>";
							echo "<td class='valuezeroleftdark'>$data</td></tr>";
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
							echo "<tr style='background-color:$bgcolor;'><td class='firstrow'>Context type</td>";
							echo "<td class='valuezeroleftdark'>$contextType</td></tr>";
						}
						
						if (strpos($caption, 'GL_') !== false) {
							echo "<tr style='background-color:$bgcolor;'><td class='firstrow'>$caption</td>";
							
							if ((is_numeric($data) && ($caption!=='GL_SHADING_LANGUAGE_VERSION')) ) {
								echo "<td class='valuezeroleftdark'>".number_format($data)."</td></tr>";
								} else {
								echo "<td class='valuezeroleftdark'>$data</td></tr>";
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
			echo "</table></div>";
			
			
			// Extensions
			echo "<div id='tabs-2'>";
			echo "<table width='95%'>";
			$reportID = $_GET['reportID'];         
			$str = "SELECT Name FROM openglgpuandext LEFT JOIN openglextensions ON openglextensions.PK = openglgpuandext.ExtensionID WHERE openglgpuandext.ReportID = $reportID ORDER BY FIELD(SUBSTR(openglextensions.Name, 1, 3), 'GL_') DESC, FIELD(SUBSTR(openglextensions.Name, INSTR(openglextensions.Name, '_')+1, 3), 'EXT', 'ARB') DESC, openglextensions.Name ASC";  
			$sqlresult = mysql_query($str);  
			$extarray = array();
			while($row = mysql_fetch_row($sqlresult)) {	
				foreach ($row as $data) {
					$extarray[] = $data;
				}
			}	 
			$index = 0;
			foreach ($extarray as $extension) {
				$bgcolor  = $index % 2 != 0 ? $bgcolordef : $bgcolorodd; 	   
				echo "<tr><td class='firstrow' style='background-color:$bgcolor;'><a href='listreports2.php?listreportsbyextension=$extension'>$extension</a></td><tr/>";
				$index++;
			}	
			echo "</table></div>";
			
			// Compressed texture formats
			echo "<div id='tabs-3'><table width='95%'>";
			$reportID = $_GET['reportID'];         
			$sqlresult = mysql_query("select text from compressedTextureFormats ctf join enumTranslationTable ett on ctf.formatEnum = enum where reportId = $reportID");  
			$sqlCount = mysql_num_rows($sqlresult);
			if ($sqlCount > 0) {
				$index = 0;
				while($row = mysql_fetch_row($sqlresult)) {
					foreach ($row as $data) {
						$bgcolor  = $index % 2 != 0 ? $bgcolordef : $bgcolorodd; 	   
						echo "<tr><td class='firstrow' style='background-color:$bgcolor;'>$data</td><tr/>";
						$index++;
					}
				}
				} else {
				echo "<tr><td>No compressed formats available or submitted</td></tr>";
			}	
			echo "</table></div>";
			
			// Report history
			echo "<div id='tabs-4'><table width='95%'>";
			if ($historyCount > 0) {	
				$sqlResult = mysql_query("SELECT date,submitter,log FROM reportHistory where reportId = $reportID order by id desc") or die(mysql_error());  
				$index = 0;
				while($row = mysql_fetch_row($sqlResult)) {
					$bgcolor  = $index % 2 == 0 ? $bgcolordef : $bgcolorodd; 	  
					echo "<tr style='background-color:$bgcolor;'><td class='firstrow' valign=top>$row[0]</td>";
					echo "<td class='firstrow' valign=top>$row[1]</td>";	
					echo "<td class='firstrow' >$row[2]</td></tr>";	
					$index++;
				}		
				} else {
				echo "<tr><td>No updates have been made to this report yet</td></tr>";
			}
			echo "</table></div>";
			
			// List of caps not available for this report
			echo "<div id='tabs-5'><table width='95%'>";
			echo "<tr><td class='firstrow'>";	
			echo "<p>";
			if (sizeof($emptyCaps) > 0) {
				$missingCapsList = implode("<br>", $emptyCaps);  
				echo "<b>Capabilites not available in this report :</b><br>";
				echo $missingCapsList;
			}
			echo "</table></div>";
			
			
			
			dbDisconnect();  
		?>
	</div>
	
	<?php include("./gl_footer.inc");	?>
</body>
</html>