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

	echo "<div id='content'><table border=0 width=95%><TBODY><TR> <TD id='tableheader' colspan=3>News</TD></TR>";

	$sqlresult = mysql_query("SELECT * from openglnews order by date desc"); 
   
	while($row = mysql_fetch_object($sqlresult)) {

		echo "<tr> <td>";
		echo "<p style='color:#000000;'>";
		echo "<br><br>";
		echo "<b>$row->date</b><br>";
		echo "$row->text";
		echo "</p>";
		echo "</td></tr>";
	
	}

	echo "</tbody></table></div>";
	
	dbDisconnect();
?>			
	
</body>
</html>