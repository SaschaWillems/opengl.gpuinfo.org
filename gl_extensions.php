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

	 <table border=0>
		<tr><td class="firstrow">
			<form method="get" action="" style="margin-bottom:0px;">
				Filter by extension name (like) : 
				<input type="text" name="searchstring" size=40">      
				<input type="submit" value="Filter">
			</form>     
		</tr></td>			
	</table>		
   
 <table border="0">
  <TBODY>    
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

	// Caption
	echo "<tr><td id='tableheader' colspan=4><b>";
	$sqlresult = mysql_query("SELECT count(*) FROM openglextensions $search") or die(mysql_error());  	
	$sqlcount  = mysql_result($sqlresult, 0);
	if ($search != '') {
		echo "Listing $sqlcount OpenGL extensions (Name like '$searchstring')\n";
	} else {
		echo "Listing $sqlcount OpenGL extensions\n";
	}
	echo "</b></td></tr>\n";		  
	
   
	// Get total report count for percentage statistics
	$str             = "SELECT count(*) FROM openglcaps";	  	   
	$sqlresult       = mysql_query($str) or die(mysql_error());  
	$totalnumreports = mysql_result($sqlresult, 0);

	$str = "SELECT PK FROM openglextensions $search";
	$sqlresult = mysql_query($str) or die(mysql_error());  

	echo "<tr>";  
	echo "<td class='caption'><b><a href='".$_SERVER['PHP_SELF']."?sortby=extension'>Extension</a></b></td>";		   
	echo "<td class='caption'><b>Count</b></td>";		   
	echo "<td class='caption'><b><a href='".$_SERVER['PHP_SELF']."?sortby=percentage'>Percentage</a></b></td>";		   
	echo "<td class='caption'><b>Spec</b></td>";		   
	echo "</tr>";


	// Gather all extensions 
	$extarray = array();  
	while($row = mysql_fetch_row($sqlresult))
	{	
	foreach ($row as $data)
	{
	$extarray[] = $data;	 
	}
	}   


	//$str = "SELECT DISTINCT Name FROM openglgpuandext LEFT JOIN openglextensions ON openglextensions.PK = openglgpuandext.ExtensionID ORDER BY openglextensions.Name ASC";	
	$str = "SELECT DISTINCT Name FROM openglgpuandext LEFT JOIN openglextensions ON openglextensions.PK = openglgpuandext.ExtensionID $search";
	//  ORDER BY FIELD(SUBSTR(openglextensions.Name, 1, 3), 'GL_') DESC, FIELD(SUBSTR(openglextensions.Name, INSTR(openglextensions.Name, '_')+1, 3), 'EXT', 'ARB') DESC, openglextensions.Name ASC";
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
	foreach ($extarray as $ext)
	{	  

	$substr = "SELECT * FROM openglgpuandext LEFT JOIN openglextensions ON openglextensions.PK = openglgpuandext.ExtensionID where openglgpuandext.ExtensionID = $ext";	
	$substr = "SELECT COUNT(*) FROM openglgpuandext WHERE openglgpuandext.ExtensionID = $ext";
	$subsqlresult = mysql_query($substr) or die(mysql_error());  ; 

	//$count = mysql_num_rows($subsqlresult); 

	while($row = mysql_fetch_array($subsqlresult)){
	$extcount[] = $row['COUNT(*)'];
	}	

	$index++;
	} 

	// Sort
	$sortby = $_GET['sortby'];
	//  Sort by supported percentage
	if ($sortby == "percentage") {
		array_multisort($extcount, SORT_DESC, $extname); }
	//  Sort by extension name
	if ($sortby == "extension") {
		array_multisort($extname, SORT_STRING, $extcount); }

	// Output table
	$index = 0;
	for ($i = 0; $i <= count($extname); $i++) {
		if (!empty($extname[$i])) {
			$bgcolor  = $index % 2 != 0 ? $bgcolordef : $bgcolorodd; 
			echo "<tr style='background-color:$bgcolor;'>";
			echo "<td class='firstrow'><a href='listreports2.php?listreportsbyextension=".$extname[$i]."'>".$extname[$i]."</a> (<a href='listreports2.php?listreportsbyextensionunsupported=".$extname[$i]."'>not</a>)</td>";
			echo "<td class='valuezeroleft' align=center>".$extcount[$i]."</td>";
			echo "<td class='valuezeroleft' align=center>".round(($extcount[$i]/$totalnumreports*100), 2)."%</td>";

			$link = str_replace("GL_", "", $extname[$i]);
			$extparts = explode("_", $link);
			$vendor = $extparts[0];
			$link = str_replace($vendor."_", "", $link);
			echo "<td class='valuezeroleft'><a href='http://www.opengl.org/registry/specs/$vendor/$link.txt' target='_blank'>link</a></td>";

			echo "</tr>";	    
			$index++;
		}
	}  

	mysql_close();	
	?>   
	</tbody>	
	</table>  

	<?php include("./gl_footer.inc");	?></center>	
</div>
</body>
</html>