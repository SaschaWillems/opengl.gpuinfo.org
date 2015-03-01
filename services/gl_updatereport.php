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
	
	// Updates the given report with new data from xml
	
	include './../gl_config.php';
	
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

	dbConnect();	

	// Try to parse XML into database
	$dom = new DomDocument();
	$dom->load($path.'update_'.$_FILES['data']['name']); 		   

	$nodes = $dom->getElementsByTagName('implementationinfo'); 
	$xmlnode   = $nodes->item(0)->getElementsByTagName("description"); 
	$description = $xmlnode->item(0)->textContent; 

	// Find report
	$sqlResult = mysql_query("SELECT ReportID FROM openglcaps WHERE description='$description'");
	$sqlRow = mysql_fetch_row($sqlResult);
	$reportId = $sqlRow[0];	
	
	if ($reportId == "") {
		echo "No report to update! Wrong Id?";
		mysql_close();	 
		die('');
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
		if (strpos($capnode->nodeName, 'GL_') !== false) {
			// Only null values
			$sqlResultCap = mysql_query("SELECT ".$capnode->nodeName." FROM openglcaps WHERE reportID = $reportId");
			$sqlRow = mysql_fetch_row($sqlResultCap);
			if ((is_null($sqlRow[0])) && (strpos($capnode->nodeValue, 'n/a') == false)) {
				$capsUpdated[] = $capnode->nodeName;
				$capsNewValue[] = trim($capnode->nodeValue);
			}
		}
	}	
	
	// Updated
	for ($i=0; $i<sizeof($capsUpdated); $i++) {
		$capName = $capsUpdated[$i];
		$capValue = $capsNewValue[$i]; 
		$sqlStr = "UPDATE openglcaps SET $capName = '$capValue' WHERE reportId = $reportId";
		$sqlResult = mysql_query($sqlStr) or die(mysql_error()); 
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
		mysql_query("insert ignore into compressedTextureFormats (reportId, formatEnum) values ($reportId, $formatEnum)");
		if (mysql_affected_rows() > 0) {
			$formatsInserted[] = $formatNode->textContent;
		}
	}
	
	// Generate history entry
	if (sizeof($formatsInserted) > 0) {
		$log .= "Inserted compressed texture formats :<br>";
		$log .= implode("<br>", $formatsInserted);
	}
	
	// Internal format query information
	/*
	$xml = simplexml_load_file($path.'update_'.$_FILES['data']['name']);

	foreach ($xml->internalformatinformation->target as $target) {
		// Insert texture target if not already present
		$sqlStr = "insert ignore into intFormatTargets (name) values('".$target['name']."')";
		mysql_query($sqlStr) or die(mysql_error());	
		// Get id
		$sqlRes = mysql_query("select id from intFormatTargets where name = '".$target['name']."'");
		$sqlRow = mysql_fetch_row($sqlRes) or die (mysql_error());
		$targetId = $sqlRow[0];
		
		// Insert target format info
		foreach ($target->format as $format) {
			$supported = ($format['supported'] == "true") ? 1 : 0;
			$sqlStr  = "insert ignore into intFormats (reportId, targetId, name, supported) values";
			$sqlStr .= "($reportId, $targetId, '".$format['name']."', '".$supported."')";
			mysql_query($sqlStr) or die(mysql_error());			
			// Get id
			$sqlRes = mysql_query("select formatid from intFormats where reportId = $reportId and targetId = $targetId and name = '".$format['name']."'");
			$sqlRow = mysql_fetch_row($sqlRes) or die (mysql_error());
			$formatId = $sqlRow[0];
			
			// Insert internal format properties
			if ($supported == 1) {
				foreach ($format->value as $value) {
					$sqlStr  = "insert ignore into intFormatProps (intFormatId, name, value) values";
					$sqlStr .= "($formatId, '".$value['name']."', ".$value.")";
					mysql_query($sqlStr) or die(mysql_error());							
				}
			}
			
		}
		
	}
	*/
		

	$msg = "http://delphigl.de/glcapsviewer/gl_generatereport.php?reportID=$reportId\n\nSubmitter : $submitter\n\nLog : $log";
	mail('webmaster@delphigl.de', 'Report updated', $msg); 
	
	$sqlStr = "INSERT INTO reportHistory (reportId, submitter, log) VALUES($reportId, '$submitter', '$log');";
	$sqlResult = mysql_query($sqlStr);	
	
	// Return message to be display in app	
	if (sizeof($capsUpdated) > 0) {
		echo sizeof($capsUpdated), " capabilities have been added.\n\n";
	};
	if (sizeof($formatsInserted) > 0) {
		echo sizeof($formatsInserted), " compressed texture formats have been added to the report.\n\n";
	};	
	
	if ( (sizeof($capsUpdated) > 0) || (sizeof($formatsInserted) > 0) ) {
		echo "See the report history for details.\n\nThank you for your contribution!";
	}
	
	
	dbDisconnect();	 
?>