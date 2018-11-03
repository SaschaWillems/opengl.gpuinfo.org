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
	
	// Updates the given report with new data from xml
	
	include '../dbconfig.php';
	
	// Check for valid file
	$path='./';

	// Reports are pretty small, so limit file size for upload (Note : internal format query makes for some big files)
	$MAX_FILESIZE = 1024 * 1024;
	$file = $_FILES['data']['name'];

	// Check filesize
	if ($_FILES['data']['size'] > $MAX_FILESIZE)  {
		echo "File exceeds size limitation of $MAX_FILESIZE bytes!";    
		exit();  
	}

	// Check file extension 
	$ext = pathinfo($_FILES['data']['name'], PATHINFO_EXTENSION); 
	if ($ext != 'xml') {
		echo "Report '$file' is not a of file type XML!";    
		exit();  
	} 

	move_uploaded_file($_FILES['data']['tmp_name'], $path.'update_'.$_FILES['data']['name']) or die(''); 

	DB::connect();

	// Try to parse XML into database
	$dom = new DomDocument();
	$dom->load($path.'update_'.$_FILES['data']['name']); 		   

	$nodes = $dom->getElementsByTagName('implementationinfo'); 
	$xmlnode   = $nodes->item(0)->getElementsByTagName("description"); 
	$description = $xmlnode->item(0)->textContent; 

	// Find report
	try {	
		$stmnt = DB::$connection->prepare("SELECT * FROM openglcaps WHERE description = :description");
		$stmnt->execute(["description" => $description]);
		$row = $stmnt->fetch(PDO::FETCH_ASSOC);
		$reportId = $row["ReportID"];
	} catch (PDOException $e) {
		die('Error while trying to update report');
	}

	if ($reportId == "") {
		die("No report to update! Wrong Id?");
	}
		
	$submitter = $nodes->item(0)->getElementsByTagName("submitter")->item(0)->textContent;
	if ( (is_null($submitter)) ||($submitter == "")) {
		$submitter = "unknown";
	}
	// Get caps to update
	$xmlnode = $nodes->item(0)->getElementsByTagName('caps'); 
	$capsUpdated = array();
	$capsNewValue = array();
	foreach ($xmlnode->item(0)->childNodes as $capnode) {	
		if ($capnode->nodeName == "#text") {continue;} 	  
		$capid = $capnode->getAttribute("id");
		$capvalue = $capnode->nodeValue;
		if (strpos($capid, 'GL_') !== false) 
		{
			// Only null values
			try {	
				$stmnt = DB::$connection->prepare("SELECT `".$capid."` FROM openglcaps WHERE reportID = :reportid");
				$stmnt->execute(["reportid" => $reportId]);
				$row = $stmnt->fetch(PDO::FETCH_NUM);
				if ((is_null($sqlRow[0])) && (strpos($capvalue, 'n/a') == false)) {
					$capsUpdated[] = $capid;
					$capsNewValue[] = trim($capvalue);
				}	
			} catch (PDOException $e) {
				die('Error while trying to update report');
			}	
		}
	}	
	
	// Updated
	for ($i=0; $i<sizeof($capsUpdated); $i++) {
		$capName = $capsUpdated[$i];
		$capValue = $capsNewValue[$i]; 
		try {	
			$stmnt = DB::$connection->prepare("UPDATE openglcaps SET `$capName` = :capvalue WHERE reportID = :reportid");
			$stmnt->execute(["reportid" => $reportId, "capvalue" => $capValue]);
		} catch (PDOException $e) {
			die('Error while trying to update report');
		}	
	}
	
	// Generate history entry
	if (sizeof($capsUpdated) > 0) {
		$log = "Updated fields :<br>";
		for ($i=0; $i<sizeof($capsUpdated); $i++) {
			$log .= $capsUpdated[$i] . " = " . $capsNewValue[$i] . "<br>";
		}
	}
	
	// Compressed texture format
	$formatsInserted = array();
	foreach ($nodes->item(0)->getElementsByTagName("compressedtextureformat") as $formatNode) {
		$formatEnum = $formatNode->textContent; 
		try {	
			$stmnt = DB::$connection->prepare("INSERT ignore into compressedTextureFormats (reportId, formatEnum) values (:reportid, :formatenum)");
			$stmnt->execute(["reportid" => $reportId, "formatenum" => $formatEnum]);
			if ($stmnt->rowCount() > 0) {
				$formatsInserted[] = $formatNode->textContent;
			}
		} catch (PDOException $e) {
			die('Error while trying to update report');
		}	
	}
	
	// Generate history entry
	if (sizeof($formatsInserted) > 0) {
		$log .= "Inserted compressed texture formats :<br>";
		$log .= implode("<br>", $formatsInserted);
	}
			
	$msg = "http://opengl.gpuinfo.org/gl_generatereport.php?reportID=$reportId\n\nSubmitter : $submitter\n\nLog : $log";
	mail($mailto, 'Report updated', $msg); 
	
	try {	
		$stmnt = DB::$connection->prepare("INSERT INTO reportHistory (reportId, submitter, log) VALUES(:reportid, :submitter, :log);");
		$stmnt->execute(["reportid" => $reportId, "submitter" => $submitter, "log" => $log]);
	} catch (PDOException $e) {
		die('Error while trying to update report');
	}	
	
	// Return message to be displayed in app	
	if (sizeof($capsUpdated) > 0) {
		echo sizeof($capsUpdated), " capabilities have been added.\n\n";
	};
	if (sizeof($formatsInserted) > 0) {
		echo sizeof($formatsInserted), " compressed texture formats have been added to the report.\n\n";
	};	
	
	if ( (sizeof($capsUpdated) > 0) || (sizeof($formatsInserted) > 0) ) {
		echo "See the report history for details.\n\nThank you for your contribution!";
	}
		
	DB::disconnect();	 
?>