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
 
	include 'header.php';
    include 'dbconfig.php';
?>

	<script>
		$(document).ready(function() {
			$('#capabilities').DataTable({
				"pageLength" : 50,
				"paging" : true,
				"stateSave": false, 
				"searchHighlight" : true,	
				"dom": 'fp',			
				"bInfo": false,	
				"order": [[ 0, "asc" ]],
				"deferRender": true,
				"processing": true
			});
		} );	
	</script>

	<div class='header'>
		<h4>Listing all available capabilities</h4>
	</div>

	<center>	
		<div class='parentdiv'>
			<div class='tablediv' style='width:auto; display: inline-block;'>
				<table id="capabilities" class="table table-striped table-bordered table-hover" >
					<thead>
						<tr>				
							<th>Capability name</th>
							<th>Coverage</th>
						</tr>
					</thead>
					<tbody>
						<?php																	
							DB::connect();		
							try {
								$reportcount = DB::getCount("SELECT count(*) from openglcaps", []);	
								$stmnt = DB::$connection->prepare("SELECT column_name from information_schema.columns where TABLE_NAME='openglcaps' and left(column_name,3) = 'GL_' and column_name not in ('GL_VENDOR', 'GL_VERSION', 'GL_RENDERER')"); 
								$stmnt->execute();
								while ($row = $stmnt->fetch(PDO::FETCH_NUM)) {
									$supportedCount = DB::getCount("SELECT count(*) FROM openglcaps WHERE `$row[0]` is not null", []);
									echo "<tr>";						
									echo "<td class='subkey'><a href='displaycapability.php?name=$row[0]'>$row[0]</a></td>";
									echo "<td align=center>".round($supportedCount / $reportcount * 100, 1)."%</td>";
									echo "</tr>";	    
								}    
							} catch (PDOException $e) {
								echo "<b>Error while fetching capability list</b><br>";
							}							        			
							DB::disconnect();
						?>   															
					</tbody>
				</table> 
			</div>
		</div>
		<?php 
			include "footer.html";
		?>		
	</center>

</body>
</html>