<?php

/**
 *
 * OpenGL hardware capability database server implementation
 *
 * Copyright (C) 2011-2021 by Sascha Willems (www.saschawillems.de)
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

include '../../dbconfig.php';

if (in_array(strtolower($_SERVER['HTTP_ORIGIN']), ['https://gpuinfo.org', 'https://www.gpuinfo.org', 'http://gpuinfo.org', 'http://www.gpuinfo.org', 'http://localhost:8000'])) {
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
	header("Access-Control-Allow-Credentials: true");
	header("Vary: Origin");
}

DB::connect();
try {
	$stmnt = DB::$connection->prepare("SELECT count(*) from openglcaps");
	$stmnt->execute([]);
	echo json_encode(['count' => $stmnt->fetchColumn()]);
} catch (PDOException $e) {
	header('HTTP/ 500 server error');
	echo 'Server error: Could not get report count!';
}
DB::disconnect();
