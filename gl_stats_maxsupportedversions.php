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
		
		<table border="0" id="reports" class="table table-striped table-bordered" cellspacing="0" width="100%">
			<?php
				$params = '';
				if (isset($_GET['searchstring'])) {
					$params = '&searchstring='.$_GET['searchstring'];
				}
				$sortby = $_GET['sortby'];
				
				$sortorder = "ORDER BY name ASC";
				$caption = "";
				
				$colspan = 5;
								
				echo "<caption class='tableheader'>Displaying maximum supported OpenGL versions per device</caption>";
				echo "<thead><tr>";
				echo "	<td class='caption'>Device</td>";				
				echo "	<td class='caption'>Version</td>";				
				echo "	<td class='caption'>Version string</td>";
				echo "	<td align='center'><input type='submit' name='compare' value='compare'></td>\n";
				echo "</tr></thead><tbody>"; 
				
				$str = "select GL_RENDERER as name, max(trim(left(GL_VERSION,3))) as maxversion, max(GL_VERSION) as glversion, max(reportid) as repid from openglcaps group by name $sortorder";	  	   
				
				$sqlresult = mysql_query($str); 
				
				$currentvendor  = ""; 
				
				$index       = 0;
				
				while($row = mysql_fetch_object($sqlresult))
				{
					$name         = trim($row->name);
					$version      = $row->maxversion;
					$glversion    = $row->glversion;
					$reportid     = trim($row->repid);	 
					$renderer	   = trim($row->GL_RENDERER);
					$glslsversion = trim($row->GL_SHADING_LANGUAGE_VERSION); 
					
					
					echo "<tr>";
					$bgcolor = $index % 2 != 0 ? $bgcolordef : $bgcolorodd; 
					
					echo "<td class='firstrow' style='background-color:".$bgcolor.";'><a href='gl_generatereport.php?reportID=$reportid'>$name</a></td>";		 
					echo "<td class='valuezeroleftblack' style='background-color:".$bgcolor.";'>$version</td>\n";
					echo "<td class='valuezeroleft' style='background-color:".$bgcolor.";'>$glversion</td>\n";		 
					echo "<td align='center' style='font-size: 12px; background-color:".$bgcolor."'><input type='checkbox' name='id[$reportid]'></td>\n";				
					
					echo "</tr>\n";
					$index++;
					
				}
				
				dbDisconnect();  
			?>   
		</tbody></table>
		
	</form> 
	
	<script>
		$(document).ready(function() {
			$('#reports').DataTable({
				"pageLength" : 50,
				"searchHighlight": true,
				"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ]			
			});
		} );	
	</script>	
	
	<?php include("./gl_footer.inc");	?>
</div>

</body>
</html>