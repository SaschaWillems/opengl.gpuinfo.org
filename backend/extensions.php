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

    $data = array();
    $params = array();    
             
    // Ordering
    $orderByColumn = '';
    $orderByDir = '';
    if (isset($_REQUEST['order']) && count($_REQUEST['order'] > 0)) {
        $orderByColumn = $_REQUEST['order'][0]['column'];
        $orderByDir = $_REQUEST['order'][0]['dir'];
    }

    // Paging
    $paging = '';
    if (isset($_REQUEST['start'] ) && $_REQUEST['length'] != '-1') {
        $paging = "LIMIT ".$_REQUEST["length"]. " OFFSET ".$_REQUEST["start"];
    }  

    // Filtering
    $searchColumns = array('id', 'name', 'coverage');

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
	// Filter
	if (isset($_REQUEST['filter']['name'])) {
	    $name = $_REQUEST['filter']['name'];
        if ($name != '') {
            $whereClause = "where name ".($negate ? "!=" : "=")." :filter_name";
            $params['filter_name'] = $name;
        }
    }

    if (!empty($orderByColumn)) {
        $orderBy = "order by ".$orderByColumn." ".$orderByDir;
    }

    if ($orderByColumn == "api") {
        $orderBy = "order by length(".$orderByColumn.") ".$orderByDir.", ".$orderByColumn." ".$orderByDir;
    }

    $sql = "SELECT id, name, coverage from viewExtensions ".$whereClause." ".$searchClause." ".$orderBy;
  
    $extensions = DB::$connection->prepare($sql." ".$paging);
    $extensions->execute($params);
    if ($extensions->rowCount() > 0) { 
        foreach ($extensions as $extension) {           							

            $link = str_replace("GL_", "", $extension["name"]);
            $extparts = explode("_", $link);
            $vendor = $extparts[0];
            $link = str_replace($vendor."_", "", $link);						

            $data[] = array(
                'id' => $extension["id"], 
                'name' => "<a href='listreports.php?extension=".$extension["name"]."'>".$extension["name"]."</a> (<a href='listreports.php?extension=".$extension["name"]."&option=not'>not</a>) [<a href='http://www.opengl.org/registry/specs/$vendor/$link.txt' target='_blank' title='Show specification for this extensions'>?</a>]",
                'coverage' => "<center>".round(($extension["coverage"]), 2)."%</center>"
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