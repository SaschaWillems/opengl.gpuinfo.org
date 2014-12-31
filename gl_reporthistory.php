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
	$reportId = $_GET['reportId']; 
	if ($reportId == "") {
		echo "<b><font color=red>No reportId specified!</font></b>";
		die("");
	}
	
	$sqlResult = mysql_query("SELECT description FROM openglcaps where reportId = $reportId") or die(mysql_error());  
	$sqlRow = mysql_fetch_row($sqlResult);
	echo "<div id='content'>";
	echo "<table border=0><TBODY><tr><td id='tableheader' colspan=3><b>Report history for</b> <br><font style='font-size:small;'>$sqlRow[0]<br></font></td></tr>";   

	$sqlResult = mysql_query("SELECT date,submitter,log FROM reportHistory where reportId = $reportId order by id desc") or die(mysql_error());  
	$index = 0;
	while($row = mysql_fetch_row($sqlResult)) {
		$bgcolor  = $index % 2 == 0 ? $bgcolordef : $bgcolorodd; 	  
		echo "<tr style='background-color:$bgcolor;'><td class='firstrow' valign=top>$row[0]</td>";
		echo "<td class='firstrow' valign=top>$row[1]</td>";	
		echo "<td class='firstrow' >$row[2]</td></tr>";	
		$index++;
	}
	
	echo "</tbody></table>";
	
	dbDisconnect();	
?> 
  
</div>
</body>
</html>