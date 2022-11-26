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
    // include '../functions.php';

    function shorten($string, $length) {
        return (strlen($string) >= $length) ? substr($string, 0, $length-10). " ... " . substr($string, -5) : $string;
    }

    DB::connect();

    $data = array();
    $params = array();    
             
    // Ordering
    $orderByColumn = '';
    $orderByDir = '';
    if (isset($_REQUEST['order'])) {
        $orderByColumn = $_REQUEST['order'][0]['column'];
        $orderByDir = $_REQUEST['order'][0]['dir'];
    }

    // Paging
    $paging = '';
    if (isset($_REQUEST['start'] ) && $_REQUEST['length'] != '-1') {
        $paging = "LIMIT ".$_REQUEST["length"]. " OFFSET ".$_REQUEST["start"];
    }  

    // Filtering
    $searchColumns = array('id');

    array_push($searchColumns, 'renderer', 'version', 'glversion', 'glslversion', 'ctxtype', 'os');

    // Per-column, filtering
    $filters = array();
    for ($i = 0; $i < count($_REQUEST['columns']); $i++) {
        $column = $_REQUEST['columns'][$i];
        if (($column['searchable'] == 'true') && ($column['search']['value'] != '')) {
            $filters[] = $searchColumns[$i].' like :filter_'.$i;
            $params['filter_'.$i] = '%'.$column['search']['value'].'%';
        }
    }
    if (sizeof($filters) > 0) {
        $searchClause = 'having '.implode(' and ', $filters);
    }        

    $whereClause = '';
    $selectAddColumns = '';
    $negate = false;
	if (isset($_REQUEST['filter']['option'])) {
		if ($_REQUEST['filter']['option'] == 'not') {
			$negate = true;
		}
    }        
	// Filters
    // Extension
	if (isset($_REQUEST['filter']['extension'])) {
	    $extension = $_REQUEST['filter']['extension'];
        if ($extension != '') {
            $whereClause = "where reportid ".($negate ? "not" : "")." in (select distinct(reportid) from openglgpuandext gex left join openglextensions ext on ext.pk = gex.extensionid where ext.name = :filter_extension)";
            $params['filter_extension'] = $extension;
        }
    }
    
    // Compressed format
    if (isset($_REQUEST['filter']['compressedtextureformat'])) {
	    $compressedformat = $_REQUEST['filter']['compressedtextureformat'];
        if ($compressedformat != '') {
            $whereClause = "where reportid ".($negate ? "not" : "")." in (select reportid from compressedTextureFormats where formatEnum = (select enum from enumTranslationTable where text = :filter_compressedformat))";
            $params['filter_compressedformat'] = $compressedformat;            
        }
    }

    // Submitter
    if (isset($_REQUEST['filter']['submitter'])) {
	    $submitter = $_REQUEST['filter']['submitter'];
        if ($submitter != '') {
            $whereClause = "where submitter = :filter_submitter";
            $params['filter_submitter'] = $submitter;            
        }
    }

    // Capability
    if (($_REQUEST['filter']['capability'] != '') && ($_REQUEST['filter']['capabilityvalue'] != '')) {
        $columnname = $_REQUEST['filter']['capability'];
		// Check if capability column exists
		$result = DB::$connection->prepare("SELECT * from information_schema.columns where TABLE_NAME= 'openglcaps' and column_name = :columnname");
		$result->execute([":columnname" => $columnname]);
        if ($result->rowCount() == 0) {
            die("Invalid capability");
        }                
        $whereClause = "where reportid in (select reportid from openglcaps where `$columnname` = :filter_capability_value)";
        $params['filter_capability_value'] = $_REQUEST['filter']['capabilityvalue'];
    }        

    if (!empty($orderByColumn)) {
        $orderBy = "order by ".$orderByColumn." ".$orderByDir;
    }

    if ($orderByColumn == "api") {
        $orderBy = "order by length(".$orderByColumn.") ".$orderByDir.", ".$orderByColumn." ".$orderByDir;
    }

    $columns = " description, reportid as id, GL_VENDOR as vendor, GL_RENDERER as renderer, GL_VERSION as version, substring_index(gl_version, '.', 2) as glversion, GL_SHADING_LANGUAGE_VERSION as glslversion, os, date(submissiondate) as reportdate, contextTypeName(contexttype) as ctxtype ";
   
    $sql = "select ".$columns." from openglcaps ".$whereClause." ".$searchClause." ".$orderBy;

    $devices = DB::$connection->prepare($sql." ".$paging);
    $devices->execute($params);
    if ($devices->rowCount() > 0) { 
        foreach ($devices as $device) {
            $contexttype = (strpos($device["ctxtype"], "OpenGL ES") !== false) ? "OpenGL ES" : trim($device["ctxtype"]);
            $operatingsystem = trim($device["os"]);
            if (strpos($operatingsystem, "Linux") !== false) {
                $pos = strpos($operatingsystem, '-');
                $operatingsystem = substr($operatingsystem, 0, $pos);
            }	
            // Clean up version info string (e.g. "compatibility context for ATI")
            $versionreplace = array("Compatibility Profile Context", "Core Profile Forward-Compatible Context", "OpenGL ES");
            $gl_version = str_replace($versionreplace, "", trim($device["version"]));
            $gl_version = shorten($gl_version, 30);
            $glsl_version = trim($device["glslversion"]);
            // Extract version numbers
            preg_match("|[0-9]+(?:\.[0-9]*)?|", $gl_version, $gl_version_int);
            preg_match("|[0-9]+(?:\.[0-9]*)?|", $glsl_version, $glsl_version_int);
            							
            $data[] = array(
                'id' => $device["id"], 
                'description' => trim($device["description"]),
                'vendor' => trim($device["vendor"]),
                'renderer' => '<a href="displayreport.php?id='.$device["id"].'">'.trim(shorten($device["renderer"], 45)).'</a>',
                'version' => $gl_version,
                'glversion' => $gl_version_int[0],
                'glslversion' => $glsl_version_int[0],
                'submissiondate' => trim($device["reportdate"]),
                'contexttype' => $contexttype,
                'os' => $operatingsystem,
                'compare' => '<center><Button onClick="addToCompare('.$device['id'].',\''.trim(shorten($device["renderer"], 45)).'\')">Add</Button>'
            );
        }        
    }

    $filteredCount = 0;
    $stmnt = DB::$connection->prepare("select count(*) from openglcaps");
    $stmnt->execute();
    $totalCount = $stmnt->fetchColumn(); 

    $filteredCount = $totalCount;
    if (($searchClause != '') or ($whereClause != ''))  {
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute($params);
        $filteredCount = $stmnt->rowCount();     
    }

    $results = array(
        "draw" => isset($_REQUEST['draw']) ? intval( $_REQUEST['draw'] ) : 0,        
        "recordsTotal" => intval($totalCount),
        "recordsFiltered" => intval($filteredCount),
        "data" => $data);

    DB::disconnect();     

    echo json_encode($results);
?>