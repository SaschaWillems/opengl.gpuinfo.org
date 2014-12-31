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

	function median() {
		$args = func_get_args();

		switch(func_num_args())
		{

			case 1:
				$args = array_pop($args);
				// fallthrough

			default:
				if(!is_array($args)) {
					trigger_error('need list of numbers for median',E_USER_NOTICE);
					return false;
				}

				sort($args);
			   
				$n = count($args);
				$h = intval($n / 2);

				if($n % 2 == 0) {
					$median = ($args[$h] + $args[$h-1]) / 2;
				} else {
					$median = $args[$h];
				}

				break;
		}
	   
		return $median;
	}   

  $str             = "SELECT * FROM openglcaps";	  	   
  $sqlresult       = mysql_query($str) or die(mysql_error());  
    
  if (mysql_real_escape_string($_GET['listreportsbycap']) == FALSE) {
    exit('Invalid query passed as parameter!');
    mysql_close();   
   }
  else {
   $glcap = mysql_real_escape_string($_GET['listreportsbycap']);
   }

  $minval =  65535;
  $maxval = -65535;
  $avgval =  0; 
  $totalnumreports = 0;
  $index = 0;
  $valueisint = FALSE;
  $values = array();
  
  while($row = mysql_fetch_object($sqlresult)) 
   {   
   if (intval($row->$glcap) !== 0) 
    {
    $tmpint = intval($row->$glcap);
	if ($tmpint < $minval) {
	 $minval = $tmpint; }
	if ($tmpint > $maxval) {
	 $maxval = $tmpint; }
	$avgval = $avgval + $tmpint; 
	$totalnumreports++;
    if (is_int($row->$glcap)) {
 	 $valueisint = TRUE; }
	$values[] = intval($row->$glcap);
    }
   else
    {
    if ($index == 0) {
	 if (is_int($row->$glcap)) {
	 $valueisint = FALSE; }  
	 }
	}
   $index++;	
   }
  
  $avgval = round($avgval / $totalnumreports); 
   
  // Display min / max / avg 
   $median = median($values);
  
?>  

	<div id='content'>
	 <table border=0>
		<tr><td class="firstrow">
			<form method="get" action="" style="margin-bottom:0px;">
				Filter by renderer name (like) : 
				<input type="text" name="searchstring" size="40">      
				<input type="hidden" name="listreportsbycap" value="<?=$_GET['listreportsbycap']?>" />				
				<input type="submit" value="Filter">
			</form>     
		</tr></td>			
	</table>  
<?php  

	// Filter via input box
	$searchstring = '';
	if(isset($_GET['searchstring'])) {	 
		$searchstring  = mysql_real_escape_string(strtolower($_GET['searchstring']));
	} 	
	$search = '';
	if ($searchstring != '') {
		$search = ' where GL_RENDERER like "%'.$searchstring.'%"';
	}

	echo "<table border=0 class='glcaps'>";
	echo "<tr><td colspan=2 id='tableheader'><b>Displaying report values for $glcap";  
	if ($search != '') {
		echo " (Renderer like '$searchstring')\n";
	}
	echo "<br>Lower = $minval / Upper = $maxval / Median = $median"; 
	echo "</tr>";
	
	echo "<tr>";  
	echo "<td class='caption'><b>Renderer</b></td>";		   
	echo "<td class='caption'><b>Value</b></td>";		   
	echo "</tr>";	
    
	$str       = "SELECT * FROM openglcaps $search ORDER BY `".$glcap."` DESC";	  	   
	$sqlresult = mysql_query($str) or die(mysql_error());  
	while($row = mysql_fetch_object($sqlresult)) {
		$bgcolor  = $index % 2 != 0 ? $bgcolordef : $bgcolorodd;   
		$index++;
		echo "<tr><td class='firstrow' style='background-color:".$bgcolor."'><a href='gl_generatereport.php?reportID=".$row->ReportID."'>$row->GL_RENDERER $row->GL_VERSION</a></td>";
		echo "<td class='value' style='background-color:".$bgcolor."'>".$row->$glcap."</td></tr>";
	}

  echo "</table>";
    
mysql_close();	?>
  
<?php include("./gl_footer.inc");	?>
</div>
</body>
</html>