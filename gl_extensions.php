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
	include './gl_config.php';
	
	dbConnect();	
	
	$sqlResult = mysql_query("SELECT count(*) FROM openglextensions");
	$sqlCount = mysql_result($sqlResult, 0);
	echo "<div class='header'>";
		echo "<h4 style='margin-left:10px;'>Listing all available extensions ($sqlCount)</h4>";
	echo "</div>";				
?>

<center>
	
	<div class="reportdiv">
	<table id="extensions" class="table table-striped table-bordered table-hover reporttable" >
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
			echo "<td class='caption'>Extension</td>";		   
			echo "<td class='caption'>Coverage</td>";		   
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
			"paging" : false,
			"stateSave": false, 
			"searchHighlight" : true
		});
	} );	
</script>
</div>
</center>
<?php include("./gl_footer.inc");	?></center>	
</body>
</html>