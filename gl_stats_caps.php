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
			<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="margin-bottom:0px;">
				Filter by capability name (like) : 
				<input type="text" name="searchstring" size=40">      
				<input type="submit" name="submit" value="Filter">
			</form>     
		</tr></td>			
	</table>	
<?php	

	// Filter via input box
	$searchstring = '';
	if(isset($_POST['submit'])) {	 
		$searchstring  = mysql_real_escape_string(strtolower($_POST['searchstring']));
	} 	
	
	$str             = "SELECT * FROM openglcaps WHERE ReportID = 1";	  	   
	$sqlresult       = mysql_query($str) or die(mysql_error());  	
 
  echo "<table border=0>";
	if ($searchstring != '') {
		echo "<tr><td id='tableheader'><b>OpenGL hardware capabilities (Name like '$searchstring')</td></tr>\n";
	} else {
		echo "<tr><td id='tableheader'><b>OpenGL hardware capabilities</td></tr>";
	}	    

  //while($report = mysql_fetch_object($sqlresult)) 
//   {   
   $colindex = 0;
   $glcapnames = array();
   while($row = mysql_fetch_row($sqlresult))
    {
	 foreach ($row as $data)
	  {   
	  $caption = mysql_field_name($sqlresult, $colindex);
	  $colindex++;	 
	   if ($caption === 'GL_VENDOR') {
	    continue; }
	   if ($caption === 'GL_RENDERER') {
	    continue; }
	   if ($caption === 'GL_VERSION') {
	    continue; }
	  if ($searchstring != '') {
		if (stripos($caption, $searchstring) === FALSE) {
			continue;
		}
	  }
	  if (strpos($caption, 'GL_') !== FALSE) {
		 $glcapnames[] = $caption;
	  }
     }
//   break;
  // }
  
   }
   
	sort($glcapnames); 
	$index = 0;
	foreach ($glcapnames as $glcapname) {
		$bgcolor  = $index % 2 != 0 ? $bgcolordef : $bgcolorodd;   
		$index++;
		echo "<tr><td class='firstrow' style='padding-left:10px; font-size: 12px; background-color:".$bgcolor."'><a href='gl_stats_caps_single.php?listreportsbycap=$glcapname'>$glcapname</a></td></tr>";
	}

	echo "</table>";
    
	mysql_close();	?>
  
</p>
<?php include("./gl_footer.inc");	?>
</div>
</body>
</html>