<?php
/**
 *
 * OpenGL hardware capability database server implementation
 *
 * Copyright (C) 2011-2022 by Sascha Willems (www.saschawillems.de)
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

session_set_cookie_params(0, '/', '.gpuinfo.org');
session_name('gpuinfo');
session_start();

$data_theme = null;
$data_theme_icon = 'moon';
if (($_SESSION['theme']) && ($_SESSION['theme'] == 'dark')) {
	$data_theme = 'data-theme="dark"';
	$data_theme_icon = 'sun';
}

?>
<html <?= $data_theme ?>>

<head>
	<meta http-equiv="Content-Type" content="text/html" charset="ISO-8859-1">
	<meta name="robots" content="index, nofollow" />
	<title>OpenGL Hardware Database by Sascha Willems</title>	

	<link rel="icon" type="image/png" href="/images/OpenGL_LogoBug_32px_Nov17.png" sizes="32x32">

	<link rel="stylesheet" type="text/css" href="external/css/bootstrap.min.css"/>
	<link rel="stylesheet" type="text/css" href="external/css/dataTables.bootstrap.min.css"/>
	<link rel="stylesheet" type="text/css" href="external/css/fixedHeader.bootstrap.min.css"/>
	<link rel="stylesheet" type="text/css" href="external/css/responsive.bootstrap.min.css"/>
	<link rel="stylesheet" type="text/css" href="external/bootstrap-toggle.min.css" rel="stylesheet">

	<link rel="stylesheet" type="text/css" href="stylenew.css">

	<script src="external/apexcharts/apexcharts.js"></script>

	<script type="text/javascript" src="external/jquery-2.2.0.min.js"></script>
	<script type="text/javascript" src="external/bootstrap.min.js"></script>
	<script type="text/javascript" src="external/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="external/dataTables.bootstrap.min.js"></script>
	<script type="text/javascript" src="external/bootstrap-toggle.min.js"></script>	
	<script type="text/javascript" src="external/dataTables.fixedHeader.min.js"></script>

<!--	<script type="text/javascript" src="external/dataTables.responsive.min.js"></script> -->
	<script type="text/javascript" src="external/responsive.bootstrap.min.js"></script>
	
	<script>
		$(document).ready(function () {
				$.each($('#navbar').find('li'), function() {
						$(this).toggleClass('active',
								'/' + $(this).find('a').attr('href') == window.location.pathname);
				});
		});	
	</script>
	
</head>
<body>
<!-- Header -->
<!-- Bootstrap nav bar -->
	<nav class="navbar navbar-default navbar-fixed-top">
	  <div class="container-fluid">
		<div class="navbar-header">
		  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span> 
		  </button>
		  <a href="#">
			<img src="./images/opengl.png" height="48px">
			</a>
		</div>
		<div class="collapse navbar-collapse" id="myNavbar">
		  <ul class="nav navbar-nav">
			<li><a href="listreports?sortby=date_desc">Reports</a></li>
			<li><a href="listextensions.php">Extensions</a></li> 
			<li><a href="listcapabilities.php">Capabilities</a></li> 
      		<li><a href="listcompressedformats.php">Formats</a></li>
			<li><a href="versionsupport.php">Versions</a></li>
			<li><a href="download.php">Download</a></li>			
			<li><a href="about.php">About</a></li> 
			<li><a href="toggletheme.php" title="Toggle dark/light themes"><img id="mode-toggle" class="mode-toggle" src="./images/<?= $data_theme_icon ?>.svg"/></a> </li>
		  </ul>
		  <ul class="nav navbar-nav navbar-right">
			  <li class="dropdown">
				  <a class="dropdown-toggle" data-toggle="dropdown" href="#">gpuinfo.org
				  <span class="caret"></span></a>
				  <ul class="dropdown-menu">
					<li><a href="https://opengl.gpuinfo.org">OpenGL</a></li>
					<li><a href="https://opengles.gpuinfo.org">OpenGL ES</a></li>
					<li><a href="https://opencl.gpuinfo.org">OpenCL</a></li>
					<li><a href="https://vulkan.gpuinfo.org">Vulkan</a></li> 
          				<li role="separator" class="divider"></li>
					<li><a href="https://android.gpuinfo.org">Android</a></li> 
          				<li role="separator" class="divider"></li>
					<li><a href="https://www.gpuinfo.org">Launchpad</a></li> 
				  </ul>
			  </li>
		  </ul>		  
		</div>
	  </div>
	</nav>