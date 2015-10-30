<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset="ISO-8859-1">
	<title>OpenGL Hardware Database - © 2011-2015 by Sascha Willems</title>	
	<link rel="stylesheet" href="./libs/bootstrap.min.css">
	<link rel="stylesheet" href="./libs/dataTables.bootstrap.css">
	<link rel="stylesheet" href="./libs/dataTables.searchHighlight.css">
	<link rel="stylesheet" type="text/css" href="style.css"> 
	<script src="./libs/jquery.min.js"></script>
	<script src="./libs/jquery-ui/jquery-ui.min.js"></script>
	<script src="./libs/jquery.highlight.js"></script>
	<script src="./libs/jquery.dataTables.min.js"></script>
	<script src="./libs/dataTables.bootstrap.js"></script>
	<script src="./libs/dataTables.searchHighlight.min.js"></script>	
	<script src="./libs/bootstrap.min.js"></script>	
	<script>
		$(function() {
			$( "#tabs" ).tabs();
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
			<li><a href="./gl_listreports?sortby=date_desc">Reports</a></li>
			<li><a href="./gl_extensions.php">Extensions</a></li> 
			<li><a href="./gl_stats_caps.php">Capabilities</a></li> 
            <li><a href="./gl_stats_compressed_formats.php">Formats</a></li>
			<li><a href="./download.php">Download</a></li>			
			<li><a href="./gl_about.php">About</a></li> 
		  </ul>
		  <ul class="nav navbar-nav navbar-right">
			  <li class="dropdown">
				  <a class="dropdown-toggle" data-toggle="dropdown" href="#">gpuinfo.org
				  <span class="caret"></span></a>
				  <ul class="dropdown-menu">
					<li><a href="http://opengl.gpuinfo.org">OpenGL</a></li>
					<li><a href="http://opengles.gpuinfo.org">OpenGL ES</a></li>
					<li><a href="http://vulkan.gpuinfo.org">Vulkan</a></li> 
                    <li role="separator" class="divider"></li>
					<li><a href="http://www.gpuinfo.org">Launchpad</a></li> 
				  </ul>
			  </li>
		  </ul>		  
		</div>
	  </div>
	</nav>