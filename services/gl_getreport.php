<?php
	/* 		
	*
	* OpenGL hardware capability database server implementation
	*	
	* Copyright (C) 2011-2018 by Sascha Willems (www.saschawillems.de)
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
	
	include '../dbconfig.php';
	
	DB::connect();
	
	// Fetches report data for the given device
		
	$reportId = (int)$_GET['reportId'];	
	echo "<report>";

	// Implementation details and capabilities
	$skipfields = array("ReportID", "description", "appversion", "fileversion", "submitter", "extensions", "submissiondate", "note", "contexttype", "os");
	$colindex  = 0;    
	$stmnt = DB::$connection->prepare("SELECT * from openglcaps where reportId = :reportid");
	$stmnt->execute(["reportid" => $reportId]);
	echo "<implementation>";	
	while($row = $stmnt->fetch(PDO::FETCH_NUM)) {
		foreach ($row as $data) {
			$meta = $stmnt->getColumnMeta($colindex);
			$fieldname = $meta["name"];  	
			$fieldvalue = trim($data);
			// Skip fields that are not supposed to be shwon in glCapsViewer
			if (!in_array($fieldname, $skipfields)) {
				echo "<cap id=\"$fieldname\">$fieldvalue</cap>";
			}
			$colindex++;
		}
	}	
	echo "</implementation>";

	// Extensions
	$stmnt = DB::$connection->prepare("SELECT Name FROM openglgpuandext LEFT JOIN openglextensions ON openglextensions.PK = openglgpuandext.ExtensionID WHERE openglgpuandext.ReportID = :reportid ORDER BY FIELD(SUBSTR(openglextensions.Name, 1, 3), 'GL_') DESC, FIELD(SUBSTR(openglextensions.Name, INSTR(openglextensions.Name, '_')+1, 3), 'EXT', 'ARB') DESC, openglextensions.Name ASC");
	$stmnt->execute(["reportid" => $reportId]);
	echo "<extensions>";
	while($row = $stmnt->fetch(PDO::FETCH_NUM)) {
		foreach ($row as $data) {
			echo "<extension>$data</extension>";
		}
	}	 	
	echo "</extensions>";
	
	// Compressed texture formats
	$stmnt = DB::$connection->prepare("SELECT formatEnum from compressedTextureFormats where reportId = :reportid");
	$stmnt->execute(["reportid" => $reportId]);
	echo "<compressedtextureformats>";
	while($row = $stmnt->fetch(PDO::FETCH_NUM)) {
		foreach ($row as $data) {
			echo "<format>$data</format>";
		}
	}	 	
	echo "</compressedtextureformats>";
			
	echo "</report>";
		
	DB::disconnect();	 		
?>