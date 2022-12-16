<?php 	
	/* 		
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
	include './header.html';	
?>

<div id='reportdiv'>	   
	<div class="panel panel-default">
		<div class="panel-body" style="margin-left:50px; width:65%px;">    
			<div class="page-header">
				<h2>Downloads</h2>
			</div>
			<div>
				The OpenGL Hardware Capability Viewer is open source, you can always build the most recent version yourself using the sources from <a href="https://github.com/SaschaWillems/glCapsViewer">https://github.com/SaschaWillems/glCapsViewer</a>.<br>
			</div>
			<div class="page-header">
				<h3>Changelog</h3>
				<h4>Version 1.1 - 2016-07-24</h4>
				<ul>
					<li>Updated user interface</li>
					<li>Added GL_VIEWPORT_SUBPIXEL_BITS capability (GL_ARB_viewport_array)</li>
					<li>Added GL_MAX_SHADER_COMPILER_THREADS_ARB (GL_ARB_parallel_shader_compile)</li>
					<li>Store all dimensions for indexed capabilities:
						<ul>
							<li>GL_MAX_VIEWPORT_DIMS (2)</li>
							<li>GL_MAX_COMPUTE_WORK_GROUP_COUNT (3)</li>
							<li>GL_MAX_COMPUTE_WORK_GROUP_SIZE (3)</li>
						</ul>
					</li>
				</ul>
			</div>
			<div class="page-header">
				<h3>Windows</h3>
				<ul>
					<li><a href="downloads/glcapsviewer_1_1_windows.zip">Version 1.1 (32/64-Bit)</a></li>
				</ul>
			<div>
			</div>
			<div class="page-header">
				<h3>Linux</h3>
				<ul>
					<li><a href="downloads/glcapsviewer_1_1_linux64.tar.gz">Version 1.1 (64-Bit)</a></li>
				</ul>
			</div>
		</div>    
	</div>
</div>
	
<center>
<?php include "footer.html"; ?>
</center>

</body>
</html>