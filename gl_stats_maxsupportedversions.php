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
			Filter renderer (like) : 
			<input type="text" name="searchstring" size=40">      
			<input type="hidden" name="orderby" value="<?=$_GET['orderby']?>" />				
			<input type="submit" value="Filter">
		</form>   
		</tr></td>			
	</table>		
	
<form method="get" action="gl_comparereports.php?compare" style="margin-bottom:0px;">	
  
 <table border="0">
  <TBODY>   
  <?php
	$params = '';
	if (isset($_GET['searchstring'])) {
	 $params = '&searchstring='.$_GET['searchstring'];
	}
   $sortby = $_GET['sortby'];
   
   $sortorder = "ORDER BY name ASC";
   $caption = "";
		  
   $colspan = 5;
  
	// Searching via in-page form
	$searchstring = '';
	if(isset($_GET['searchstring'])) {	 
		$searchstring  = mysql_real_escape_string(strtolower($_GET['searchstring']));
	} 
	
	// Order by
	if(isset($_GET['orderby'])) {	 
		if ($_GET['orderby'] == 'version_asc') {
			$sortorder = ' order by glversion asc ';
		}
		if ($_GET['orderby'] == 'version_desc') {
			$sortorder = ' order by glversion desc ';
		}
	} 
	   
	// Submitter
	if($_GET['submitter'] != '') {
		$submitter = mysql_real_escape_string(strtolower($_GET['submitter']));
		echo "<tr><td id='tableheader' colspan=$colspan><b>\n";
		echo "Displaying all reports submitted by <b>$submitter</b>(<a href='listreports2.php?sortby=description_asc'>Show all reports</a>) :";	 
		echo "</b></td>";		  
	}	

	if ($searchstring != '') {
		echo "<tr><td id='tableheader' colspan=$colspan><b>\n";
		if ($negate == false) {
			echo "Displaying maximum supported OpenGL versions per device, filtered by '<b>".strtoupper($searchstring)."' </b>(<a href='./gl_stats_maxsupportedversions.php'>Show all</a>) :";	 
		}
		if ($negate == true) {
			echo "Displaying all reports not supporting <b>".strtoupper($searchstring)." </b>(<a href='listreports2.php?sortby=description_asc'>Show all reports</a>) :";	 
		}
		echo "</b></td></tr>\n";		  
	} else {
		echo "<tr><td id='tableheader' colspan=$colspan><b>";
		echo "Displaying maximum supported OpenGL versions per device\n";
		echo "</b></td></tr>\n";		  
	}
	
	
	echo "<tr>";
	echo "<td class='caption'><b>Device</b></td>";
	
	echo "<td class='caption'><b>Max. supp.<br>OpenGL<br>Version</b>";
		echo "<a href='".$_SERVER['PHP_SELF']."?orderby=version_asc$params'><img src='sort_asc.png' title='Sort ascending' width='12px'></a>";
		echo "<a href='".$_SERVER['PHP_SELF']."?orderby=version_desc$params'><img src='sort_desc.png' title='Sort descending' width='12px'></a>";
	echo "</td>";
	
	echo "<td class='caption'><b>Version string</b></td>";
	echo "<td align='center'><input type='submit' name='compare' value='compare'></td>\n";
	echo "</tr>"; 

	$search = '';
	if ($searchstring != '') {
		$search = ' where GL_RENDERER like "%'.$searchstring.'%"';
	}
		
	   $str = "select GL_RENDERER as name, max(trim(left(GL_VERSION,3))) as maxversion, max(GL_VERSION) as glversion, max(reportid) as repid from openglcaps $search group by name $sortorder";	  	   
	      
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
  </tbody>
</table>

	  
     </form>   


<?php include("./gl_footer.inc");	?>
</div>

</body>
</html>