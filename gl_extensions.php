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
	
	<table border="0" id="extensions" class="table table-striped table-bordered" cellspacing="0" width="100%">
		<?php
			
			// Filter via input box
			$searchstring = '';
			if(isset($_GET['searchstring'])) {	 
				$searchstring  = mysql_real_escape_string(strtolower($_GET['searchstring']));
			} 	
			$search = '';
			if ($searchstring != '') {
				$search = ' where name like "%'.$searchstring.'%"';
			}			
			
			// Get total report count for percentage statistics
			$str             = "SELECT count(*) FROM openglcaps";	  	   
			$sqlresult       = mysql_query($str) or die(mysql_error());  
			$totalnumreports = mysql_result($sqlresult, 0);
			
			$str = "SELECT PK FROM openglextensions $search";
			$sqlresult = mysql_query($str) or die(mysql_error());  
			
			echo "<thead><tr>";  
			
			$sortby = $_GET['sortby'];				
			if ($sortby == "extDesc") {
				echo "<td class='caption'><b><a href='".$_SERVER['PHP_SELF']."?sortby=extAsc'>Extension</a></b></td>";		   
				$sortOrder = 'DESC';
				} else {
				echo "<td class='caption'><b><a href='".$_SERVER['PHP_SELF']."?sortby=extDesc'>Extension</a></b></td>";		   
				$sortOrder = 'ASC';
			}
			
			echo "<td class='caption'><b>Count</b></td>";		   
			echo "<td class='caption'><b><a href='".$_SERVER['PHP_SELF']."?sortby=percentage'>Percentage</a></b></td>";		   
			echo "</tr></thead><tbody>";
			
			
			// Gather all extensions 
			$extarray = array();  
			while($row = mysql_fetch_row($sqlresult))
			{	
				foreach ($row as $data)
				{
					$extarray[] = $data;	 
				}
			}   
			
			$str = "SELECT DISTINCT Name FROM openglgpuandext LEFT JOIN openglextensions ON openglextensions.PK = openglgpuandext.ExtensionID $search";
			if ($sortby == "percentage") {
				$str .= "ORDER BY FIELD(SUBSTR(openglextensions.Name, 1, 3), 'GL_') DESC, FIELD(SUBSTR(openglextensions.Name, INSTR(openglextensions.Name, '_')+1, 3), 'EXT', 'ARB') DESC, openglextensions.Name $sortOrder";
			}
			$sqlresult = mysql_query($str) or die(mysql_error());  ; 
			$extname = array();
			$totalextcount = 0;
			while($row = mysql_fetch_row($sqlresult))
			{	
				foreach ($row as $data)
				{
					$extname[] = $data;	 
				}
			}  
			$extcount = array();
			
			$index = 0;	
			foreach ($extarray as $ext) {	  					
				$substr = "SELECT * FROM openglgpuandext LEFT JOIN openglextensions ON openglextensions.PK = openglgpuandext.ExtensionID where openglgpuandext.ExtensionID = $ext";	
				$substr = "SELECT COUNT(*) FROM openglgpuandext WHERE openglgpuandext.ExtensionID = $ext";
				$subsqlresult = mysql_query($substr) or die(mysql_error());  ; 
				
				//$count = mysql_num_rows($subsqlresult); 
				
				while($row = mysql_fetch_array($subsqlresult)){
					$extcount[] = $row['COUNT(*)'];
				}	
				
				$index++;
			} 
			
			
			// Output table
			$index = 0;
			for ($i = 0; $i <= count($extname); $i++) {
				if (!empty($extname[$i])) {
					$link = str_replace("GL_", "", $extname[$i]);
					$extparts = explode("_", $link);
					$vendor = $extparts[0];
					$link = str_replace($vendor."_", "", $link);						
					echo "<tr>";						
					echo "<td class='firstrow'><a href='gl_listreports.php?listreportsbyextension=".$extname[$i]."'>".$extname[$i]."</a> (<a href='gl_listreports.php?listreportsbyextensionunsupported=".$extname[$i]."'>not</a>) [<a href='http://www.opengl.org/registry/specs/$vendor/$link.txt' target='_blank' title='Show specification for this extensions'>?</a>]</td>";
					echo "<td class='firstrow' align=center>".$extcount[$i]."</td>";
					echo "<td class='firstrow' align=center>".round(($extcount[$i]/$totalnumreports*100), 2)."%</td>";
					echo "</tr>";	    
					$index++;
				}
			}  
			
			
			dbDisconnect();	
		?>   
	</tbody>
</table>  

<script>
	$(document).ready(function() {
		$('#extensions').DataTable({
			"pageLength" : -1,
			"stateSave": true, 
			"searchHighlight" : true,		
			"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ]
		});
	} );	
</script>

<?php include("./gl_footer.inc");	?></center>	
</div>
</body>
</html>