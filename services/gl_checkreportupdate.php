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
	
	include './../dbconfig.php';
	
	DB::connect();	

	// Checks if the send report has fields missing in the online report
	// If that's the case, the report can be updated

	$capsXml = $_GET['caps'];	
	$reportId = $_GET['reportId'];
	$canUpdate = false;
	$capsArray = explode(",", $capsXml);

	try {	
		$stmnt = DB::$connection->prepare("SELECT * from openglcaps WHERE reportId = :reportid");
		$stmnt->execute(["reportid" => $reportId]);			
		$colIndex  = 0;    
		while($row = $stmnt->fetch(PDO::FETCH_NUM)) {
			foreach ($row as $data) {
				$meta = $stmnt->getColumnMeta($colindex);
				$fieldname = $meta["name"];
				$fieldvalue = trim($data);
				if (in_array($fieldname, $capsArray)) {
					if (is_null($data)) {
						$canUpdate = true;
						break;
					}
				}
				$colIndex++;
			}
		}			
		if ($canUpdate) {
			header('HTTP/ 200 update_possible');
			echo "true";
		} else {
			header('HTTP/ 200 report_same');
			echo "false";
		}	
	} catch (PDOException $e) {
		header('HTTP/ 500 server error');
		echo 'Server error: Could not check report!';
	}

	DB::disconnect();	 
?>