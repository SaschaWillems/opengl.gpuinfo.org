 <?php
	/*
		*
		* OpenGL ES hardware capability database server implementation
		*
		* Copyright (C) 2013-2018 by Sascha Willems (www.saschawillems.de)
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
 
	include 'header.html';
	include 'serverconfig/gles_config.php';	
	
	dbConnect();  
	
	$sqlResult = mysql_query("SELECT count(*) FROM reports") or die();
	$reportcount = mysql_result($sqlResult, 0);	
?>

	<script>
		$(document).ready(function() {
            var tableNames = [ "#table-es20", "#table-es30", '#table-es31', '#table-es32' ];
	        for (var i=0; i < tableNames.length; i++) 
            {           			
				$(tableNames[i]).DataTable({
					"pageLength" : -1,
					"paging" : false,
					"stateSave": false, 
					"searchHighlight" : true,	
					"dom": 'fp',			
					"bInfo": false,	
					"order": [[ 0, "asc" ]],
					"deferRender": true,
					"processing": true
				});
			}
		} );	
	</script>

	<div class='header'>
		<h4>Listing all available GLES capabilities</h4>
	</div>

	<center>	

		<!-- Navigation -->
		<div>
			<ul class='nav nav-tabs'>
				<li class='active'><a data-toggle='tab' href='#tabs-gles20'>2.0</a></li>
				<li><a data-toggle='tab' href='#tabs-gles30'>3.0</a></li>
				<li><a data-toggle='tab' href='#tabs-gles31'>3.1</a></li>
				<li><a data-toggle='tab' href='#tabs-gles32'>3.2</a></li>
			</ul>
		</div>

		<div class='parentdiv'>

			<div class='tablediv tab-content' style='width:auto; display: inline-block;'>

				<div id='tabs-gles20' class='tab-pane fade in active reportdiv'>
					<h4 class="headercaption">OpenGL ES 2.0 capabilities</h4>
					<table id="table-es20" class="table table-striped table-bordered table-hover" >
						<thead>
							<tr>				
								<th>Capability name</th>
								<th>Coverage</th>
							</tr>
						</thead>
						<tbody>
							<?php										
								$sqlresult = mysql_query("SELECT column_name from information_schema.columns where TABLE_NAME='reports_es20caps' and column_name != 'reportid'") or die(mysql_error());  							
								while ($row = mysql_fetch_row($sqlresult)) {
									$sqlResult = mysql_query("SELECT count(*) FROM reports_es20caps WHERE `$row[0]` != 0") or die(mysql_error());  	
									$sqlCount = mysql_result($sqlResult, 0);												
									echo "<tr>";						
									echo "<td class='subkey'><a href='displaycapability.php?name=$row[0]&esversion=2'>$row[0]</a></td>";
									echo "<td align=center>".round($sqlCount / $reportcount * 100, 1)."%</td>";
									echo "</tr>";	    
								}            			
							?>   															
						</tbody>
					</table> 
				</div>

				<div id='tabs-gles30' class='tab-pane fade reportdiv'>
					<h4 class="headercaption">OpenGL ES 3.0 capabilities</h4>
					<table id="table-es30" class="table table-striped table-bordered table-hover" >
						<thead>
							<tr>				
								<th>Capability name</th>
								<th>Coverage</th>
							</tr>
						</thead>
						<tbody>		
							<?php										
								$sqlresult = mysql_query("SELECT column_name from information_schema.columns where TABLE_NAME='reports_es30caps' and column_name != 'reportid'") or die(mysql_error());  							
								while ($row = mysql_fetch_row($sqlresult)) {
									$sqlResult = mysql_query("SELECT count(*) FROM reports_es30caps WHERE `$row[0]` != 0") or die(mysql_error());  	
									$sqlCount = mysql_result($sqlResult, 0);												
									echo "<tr>";						
									echo "<td class='subkey'><a href='displaycapability.php?name=$row[0]&esversion=3'>$row[0]</a></td>";
									echo "<td align=center>".round($sqlCount / $reportcount * 100, 1)."%</td>";
									echo "</tr>";	    
								}            			
							?>   									
						</tbody>
					</table> 
				</div>

				<div id='tabs-gles31' class='tab-pane fade reportdiv'>
					<h4 class="headercaption">OpenGL ES 3.1 capabilities</h4>
					<table id="table-es31" class="table table-striped table-bordered table-hover" >
						<thead>
							<tr>				
								<th>Capability name</th>
								<th>Coverage</th>
							</tr>
						</thead>
						<tbody>		
							<?php										
								$sqlresult = mysql_query("SELECT column_name from information_schema.columns where TABLE_NAME='reports_es31caps' and column_name != 'reportid'") or die(mysql_error());  							
								while ($row = mysql_fetch_row($sqlresult)) {
									$sqlResult = mysql_query("SELECT count(*) FROM reports_es31caps WHERE `$row[0]` != 0") or die(mysql_error());  	
									$sqlCount = mysql_result($sqlResult, 0);												
									echo "<tr>";				
									echo "<td class='subkey'><a href='displaycapability.php?name=$row[0]&esversion=31'>$row[0]</a></td>";
									echo "<td align=center>".round($sqlCount / $reportcount * 100, 1)."%</td>";
									echo "</tr>";	    
								}            			
							?>   											
						</tbody>
					</table> 
				</div>

				<div id='tabs-gles32' class='tab-pane fade reportdiv'>
					<h4 class="headercaption">OpenGL ES 3.2 capabilities</h4>
					<table id="table-es32" class="table table-striped table-bordered table-hover" >
						<thead>
							<tr>				
								<th>Capability name</th>
								<th>Coverage</th>
							</tr>
						</thead>
						<tbody>		
							<?php										
								$sqlresult = mysql_query("SELECT column_name from information_schema.columns where TABLE_NAME='reports_es32caps' and column_name != 'reportid'") or die(mysql_error());  							
								while ($row = mysql_fetch_row($sqlresult)) {
									$sqlResult = mysql_query("SELECT count(*) FROM reports_es32caps WHERE `$row[0]` != 0") or die(mysql_error());  	
									$sqlCount = mysql_result($sqlResult, 0);												
									echo "<tr>";				
									echo "<td class='subkey'><a href='displaycapability.php?name=$row[0]&esversion=32'>$row[0]</a></td>";
									echo "<td align=center>".round($sqlCount / $reportcount * 100, 1)."%</td>";
									echo "</tr>";	    
								}            			
							?>   											
						</tbody>
					</table> 
				</div>


			</div>
		</div>
	</center>
	
	<?php 
		dbDisconnect();		
		include "footer.html";
	?>

</body>
</html>