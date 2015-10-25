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
	
	// Check for valid file
	$path='./';
	
	// Reports are pretty small, so limit file size for upload (512 KByte will be more than enough)
	$MAX_FILESIZE = 512 * 1024;
	
	$file = $_FILES['data']['name'];
	
	// Check filesize
	if ($_FILES['data']['size'] > $MAX_FILESIZE)  {
		echo "File exceeds size limitation of 512 KByte!";    
		exit();  
	}
	
	// Check file extension 
	$ext = pathinfo($_FILES['data']['name'], PATHINFO_EXTENSION); 
	if ($ext != 'xml') 
	{
		echo "Report '$file' is not a of file type XML!";    
		exit();  
	} 
	
	move_uploaded_file($_FILES['data']['tmp_name'], $path.$_FILES['data']['name']) or die(''); 
	
	// Try to parse XML into database
	$dom = new DomDocument();
	$dom->load($path.$_FILES['data']['name']); 		   
	
	$nodes = $dom->getElementsByTagName('implementationinfo'); 
	$xmlnode = $nodes->item(0)->getElementsByTagName("description"); 
	$description = $xmlnode->item(0)->textContent; 

	// Check required file version
	$xmlnode = $nodes->item(0)->getElementsByTagName("fileversion"); 
	$fileversion = str_replace(".", "", $xmlnode->item(0)->textContent);
	
	if ($fileversion < 30) 
	{
		echo "Report version outdated, please use a recent version of the OpenGL hardware capabilitiy viewer!";    
		exit();  
	}
	
	// Connect to DB 
	include './../gl_config.php';
	dbConnect();			
	
	// * Check if report already exists in database first
	$sqlstr = "SELECT * FROM openglcaps WHERE description = '$description'";
	$sqlresult = mysql_query($sqlstr);
	
	if (mysql_num_rows($sqlresult) > 0) {
		echo "res_duplicate";	  
		exit();	  
	}
	
	// * Generate INSERT selection
	//  Value selection
	$selectionstr = "INSERT INTO openglcaps (";
	$selectionstr .= "description, appversion, fileversion, submitter, os, contexttype, "; 
	//  Gather caps
	$xmlnode = $nodes->item(0)->getElementsByTagName('caps'); 
	$caparray = array();
	foreach ($xmlnode->item(0)->childNodes as $capnode) 
	{
		if ($capnode->nodeName == "#text") {continue;} 	  
		$caparray[] = "`".$capnode->getAttribute("id")."`";
	}
	
	$selectionstr .= implode(", ", $caparray) .")"; 
	// Values
	$valuestr  = "VALUES (";
	$xmlnode   = $nodes->item(0)->getElementsByTagName("description"); 
	$description = $xmlnode->item(0)->textContent; 
	$valuestr .= '"'.$xmlnode->item(0)->textContent .'"'.", ";
	
	$xmlnode   = $nodes->item(0)->getElementsByTagName("appversion"); 
	$valuestr .= '"'.$xmlnode->item(0)->textContent .'"'.", ";
	
	$xmlnode   = $nodes->item(0)->getElementsByTagName("fileversion"); 
	$valuestr .= '"'.$xmlnode->item(0)->textContent .'"'.", ";
	
	$xmlnode   = $nodes->item(0)->getElementsByTagName("submitter"); 
	$valuestr .= '"'.$xmlnode->item(0)->textContent .'"'.", ";
	
	$xmlnode   = $nodes->item(0)->getElementsByTagName("os"); 
	$valuestr .= '"'.$xmlnode->item(0)->textContent .'"'.", ";
	
	$xmlnode   = $nodes->item(0)->getElementsByTagName("contexttype"); 
	if ($xmlnode->length==0) { 
		$valuestr .= '"default"'.", ";
		} else {
		$valuestr .= '"'.$xmlnode->item(0)->textContent .'"'.", ";
	}	
	
	// Gather caps
	$xmlnode = $nodes->item(0)->getElementsByTagName("caps"); 
	$caparray = array();
	foreach ($xmlnode->item(0)->childNodes as $capnode) 
	{
		if ($capnode->nodeName == "#text") {continue;} 	  
		if ($capnode->nodeValue == "n/a") 
		{
			$caparray[] = 'NULL';
		} 
		else 
		{
			$caparray[] = '"'.trim($capnode->nodeValue).'"';
		}
	}
	$valuestr .= implode(",", $caparray) .")";  
	$valuestr = str_replace("\"n/a\"", "NULL", $valuestr);
	$sql = $selectionstr ." ". $valuestr;
	
	$sqlresult = mysql_query($sql) or die(mysql_error());
	
	// Extension names into separate DB
	$xmlnode = $nodes->item(0)->getElementsByTagName("extension"); 
	$extarray = array();
	
	foreach ($nodes->item(0)->getElementsByTagName("extension") as $capnode) {
		$extarray[] = '("'.$capnode->textContent.'")';
	}
	
	$sqlstr = "INSERT IGNORE INTO openglextensions (Name) VALUES " .implode(", ", $extarray);
	$sqlresult = mysql_query($sqlstr) or die(mysql_error());
	
	// Put supported extensions into third table
	$sqlstr = "SELECT ReportID FROM openglcaps WHERE description='$description'";   
	$sqlresult = mysql_query($sqlstr) or die(mysql_error());
	
	$sqlrow = mysql_fetch_assoc($sqlresult);
	$reportID = $sqlrow["ReportID"];
	
	$xmlnode = $nodes->item(0)->getElementsByTagName("extension"); 
	$extarray = array();
	
	foreach ($nodes->item(0)->getElementsByTagName("extension") as $capnode) {
		$extarray[] = '"'.$capnode->textContent.'"';
	}
	
	$sqlstr = "INSERT INTO openglgpuandext SELECT $reportID AS ReportID, PK AS ExtensionID FROM openglextensions WHERE Name IN (".implode(", ", $extarray).")"; 
	$sqlresult = mysql_query($sqlstr) or die(mysql_error()); 
	
	// Compressed texture formats
	foreach ($nodes->item(0)->getElementsByTagName("compressedtextureformat") as $formatNode) {
		$formatEnum = $formatNode->textContent; 
		mysql_query("insert ignore into compressedTextureFormats (reportId, formatEnum) values ($reportID, $formatEnum)") or die(mysql_error());
	}	
	
	echo "res_uploaded";	  
	
	$msg = "New hardware report added to the database\n";
	$msg .= "Description : $description\n";
	$msg .= "Link : http://opengl.delphigl.de/gl_generatereport.php?reportID=$reportID";
	mail('webmaster@delphigl.de', 'New OpenGL report uploaded', $msg); 
	
	dbDisconnect();	 
?>