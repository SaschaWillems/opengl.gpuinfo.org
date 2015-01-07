<head>
	<link rel="stylesheet" href="./libs/jquery-ui/themes/flick/jquery-ui.css">
	<link rel="stylesheet" href="./libs/bootstrap.min.css">
	<link rel="stylesheet" href="./libs/dataTables.bootstrap.css">	
	<link rel="stylesheet" href="./libs/dataTables.searchHighlight.css">	
	<script src="./libs/jquery.min.js"></script>
	<script src="./libs/jquery-ui/jquery-ui.min.js"></script>
	<script src="./libs/jquery.highlight.js"></script>
	<script src="./libs/jquery.dataTables.min.js"></script>
	<script src="./libs/dataTables.bootstrap.js"></script>
	<script src="./libs/dataTables.searchHighlight.min.js"></script>
	<script>
		$(function() {
			$( "#tabs" ).tabs();
		});
	</script>	
</head>

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
?>


<div id="content">
	
	<form method="get" action="gl_comparereports.php?compare" style="margin-bottom:0px;">
		
		<?php
			$osNames = array("Windows", "Linux", "MacOS", "Unknown");
			$osLikes = array("windows%", "linux%", "macos%", "");
			
			// Creat tabs for each os
			echo "<div id='tabs' style='font-size:12px;'>";
			echo "<ul>";
			echo "<h2 style='margin-left:10px;'>Reports by operating system</h2>";
			for ($i = 0; $i < count($osNames); $i++) {
				
				$sqlResult = mysql_query("SELECT count(*) from openglcaps WHERE os like '$osLikes[$i]'");	
				$sqlCount = mysql_result($sqlResult, 0);	
				echo "<li><a href='#tabs-$i'>$osNames[$i] ( $sqlCount )</a></li>";
			}
			echo "</ul>";	
			
			for ($i = 0; $i < count($osNames); $i++) {
				// Generate separate table for each os 
				echo "<div id='tabs-$i'>";
				echo "<table border='0' id='reports-$i' class='table table-striped table-bordered' cellspacing='0' width='100%'>";
				
				$groupby = $_GET['groupby'];
				
				$sortorder = "ORDER BY reportid desc";
				$vendorheader = false;
				$negate = false;
				$caption = "";
				
				$colspan = 7;				
								
				// Group reports by renderer, os, etc.
				echo "<thead>";
				echo "<tr>";
				echo "	<td class='caption'>Renderer</td>";
				echo "	<td class='caption'>GL</td>";
				echo "	<td class='caption'>SL</td>";
				echo "	<td class='caption'>OS</td>";
				echo "	<td class='caption'>Date</td>";
				echo "	<td align=center><input type='submit' name='compare' value='compare'></td>";
				echo "</tr>";
				echo "</thead><tbody>"; 
				
				$sqlsubresult = mysql_query("SELECT *, date(submissiondate) as reportdate from openglcaps WHERE os like '$osLikes[$i]'");
				
				$index = 0;
				while($subrow = mysql_fetch_object($sqlsubresult)) {
					echo "<tr>";
				
					$versionreplace = array("Compatibility Profile Context");
					$version = str_replace($versionreplace, "", trim($subrow->GL_VERSION));
					
					echo "<td style='padding-left:15px; padding-right:15px; font-size: 12px;'><a href='gl_generatereport.php?reportID=$subrow->ReportID'>$subrow->GL_RENDERER $version</a></td>";
					
					preg_match("|[0-9]+(?:\.[0-9]*)?|", $subrow->GL_VERSION, $versionint);	 
					echo "<td style='padding-right:15px; font-size: 12px;'>".$versionint[0]."</td>\n";
					
					preg_match("|[0-9]+(?:\.[0-9]*)?|", $subrow->GL_SHADING_LANGUAGE_VERSION, $glslsversionint);	 
					if ($glslsversionint[0] == '') {
						echo "<td style='padding-right:15px; font-size: 12px;'>-</td>\n";				
						} else {
						echo "<td style='padding-right:15px; font-size: 12px;'>".$glslsversionint[0]."</td>\n";				
					}
					
					echo "<td style='font-size: 12px;'>$subrow->os</td>";
					echo "<td style='font-size: 12px;'>$subrow->reportdate</td>";
					echo "<td align='center' style='font-size: 12px;'><input type='checkbox' name='id[$subrow->ReportID]'></td>\n";				
					echo "</tr>";
				}
								
				echo "</tbody></table></div>";
				
				echo "<script>";
				echo "$(document).ready(function() {";
				echo "	$('#reports-$i').DataTable({";
				echo "		'order': [[ 4, 'desc' ]],";
				echo "		'pageLength' : 50,";
				echo "		'searchHighlight': true,";
				echo "		'lengthMenu': [ [10, 25, 50, -1], [10, 25, 50, 'All'] ]";
				echo "	}); } ); </script>";
				
			}
			
		?>
		
	</div>
</form>   

<?php 
	dbDisconnect();  
	include("./gl_footer.inc");	
?>
</div>

</body>
</html>				