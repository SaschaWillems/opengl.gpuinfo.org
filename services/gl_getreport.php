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
	
	include './../gl_config.php';
	
	dbConnect();	
	
	// Fetches report data for the given device
		
	$reportId = mysql_real_escape_string($_GET['reportId']);	
	$sqlresult = mysql_query("select * from openglcaps where reportId = $reportId");
	echo "<report>";

	echo "<implementation>";	
	$skipfields = array("ReportID", "description", "appversion", "fileversion", "submitter", "extensions", "submissiondate", "note", "contexttype", "os");
	$colindex  = 0;    
	while($row = mysql_fetch_row($sqlresult)) {
		foreach ($row as $data) {
			$fieldname = mysql_field_name($sqlresult, $colindex);		  
			$fieldvalue = trim($data);
			// Skip fields that are not supposed to be shwon in glCapsViewer
			if (!in_array($fieldname, $skipfields)) {
				echo "<$fieldname>$fieldvalue</$fieldname>";
			}
			$colindex++;
		}
	}	
	echo "</implementation>";

	echo "<extensions>";
	$str = "SELECT Name FROM openglgpuandext LEFT JOIN openglextensions ON openglextensions.PK = openglgpuandext.ExtensionID WHERE openglgpuandext.ReportID = $reportId ORDER BY FIELD(SUBSTR(openglextensions.Name, 1, 3), 'GL_') DESC, FIELD(SUBSTR(openglextensions.Name, INSTR(openglextensions.Name, '_')+1, 3), 'EXT', 'ARB') DESC, openglextensions.Name ASC";  
	$sqlresult = mysql_query($str);  
	while($row = mysql_fetch_row($sqlresult)) {	
		foreach ($row as $data) {
			echo "<extension>$data</extension>";
		}
	}	 	
	echo "</extensions>";
	
		
	echo "</report>";
		
	dbDisconnect();	 		
?>