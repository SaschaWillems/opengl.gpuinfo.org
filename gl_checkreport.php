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

	// Checks wether an OpenGL report is already present in the database
	// Used by client application
	
	include './gl_config.php';
	
	dbConnect();	
			
	$description = mysql_real_escape_string($_GET['description']);	
	
	$sqlresult = mysql_query("select ReportID from openglcaps where description = '$description'");
	$sqlcount = mysql_num_rows($sqlresult);   
	$sqlrow = mysql_fetch_row($sqlresult);
	
	if ($sqlcount > 0) {
		header('HTTP/ 200 report_present '.$sqlrow[0].'');
		echo "$sqlrow[0]";
	} else {
		header('HTTP/ 200 report_new');
		echo "-1";
	}

	dbDisconnect();	
?>