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
	
	$sqlresult = mysql_query("SELECT * FROM openglcaps") or die(mysql_error());  
    
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
    
	echo "<div class='header'>";
		echo "<h4 style='margin-left:10px;'>Displaying values for $glcap</h4>";
        echo "<h5>Lower = $minval / Upper = $maxval / Median = $median</h5>"; 
	echo "</div>";				    
	
?>  

<center>
<div class='reportdiv'>   
	<table id="reports" class="table table-striped table-bordered table-hover reporttable">
		
	<?php  	
	
		/*
		echo "<table border=0 class='glcaps'>";
		echo "<tr><td colspan=2 id='tableheader'><b>Displaying report values for $glcap";  
		echo "<br>Lower = $minval / Upper = $maxval / Median = $median"; 
		echo "</tr>";
		*/
			
		echo "<thead><tr>";  
		echo "<td class='caption'>Renderer</td>";		   
		echo "<td class='caption'>Value</td>";		   
		echo "</tr></thead>";	
		
		$sqlresult = mysql_query("SELECT * FROM openglcaps WHERE ".$glcap." is not null ORDER BY `".$glcap."` DESC") or die(mysql_error());  
		while($row = mysql_fetch_object($sqlresult)) {
			echo "<tr><td class='firstrow'><a href='gl_generatereport.php?reportID=".$row->ReportID."'>$row->GL_RENDERER $row->GL_VERSION</a></td>";
			echo "<td class='firstrow'>".$row->$glcap."</td></tr>";
		}
		
		
	dbDisconnect();	
	?>
	
	</tbody>
	</table>
	
<script>
	$(document).ready(function() {
		$('#reports').DataTable({
			"pageLength" : 50,
			"searchHighlight" : true,		
			"order": [[ 1, "desc" ]],
			"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ]
//            "sDom": '<l<"centered"f><"floatleft"p>>rt'
		});
	} );	
</script>	
	
	<?php include("./gl_footer.inc");	?>
</div>
</center>
</body>
</html>