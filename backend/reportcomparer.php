<?php

/**
 *
 * OpenGL hardware capability database server implementation
 *	
 * Copyright (C) 2016-2022 by Sascha Willems (www.saschawillems.de)
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

// Writes and reads reports to compare to the server session

session_start();
header("HTTP/1.1 200 OK");

$action = $_POST['action'];
$reportid = intval($_POST['reportid']);
$reportname = $_POST['reportname'];
$identifier = 'opengl_compare_reports';

switch($action) {
    case 'add':
        if ((!is_array($_SESSION[$identifier])) || (!array_key_exists($reportid, $_SESSION[$identifier]))) {
            $_SESSION[$identifier][$reportid] = $reportname;
        }
        break;
    case 'remove':
        if ((!is_array($_SESSION[$identifier])) || (array_key_exists($reportid, $_SESSION[$identifier]))) {
            unset($_SESSION[$identifier][$reportid]);
        }
        break;
    case 'clear':      
        $_SESSION[$identifier] = [];
        break;
}

$response = [];
if (is_array($_SESSION[$identifier])) {
    foreach ($_SESSION[$identifier] as $key => $value) {
        $response[] = [
            "id" => $key,
            "name" => $value
        ];
    }
}        
$json = json_encode($response);
echo $json;