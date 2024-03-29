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
	
	DB::connect();

	$format_count = DB::getCount("SELECT count(distinct(formatEnum)) FROM compressedTextureFormats", []);
?>
	<div class='header'>
		<h4 style='margin-left:10px;'>Listing all compressed texture formats (<?php echo $format_count ?>)</h4>
	</div>

<center>	
	<div class='parentdiv'>
		<div class='tablediv' style='width:auto; display: inline-block;'>	
			<table id="formats" class="table table-striped table-bordered table-hover reporttable" >
				<thead>
					<tr>			
						<th>Format</th>
						<th>Coverage</th>
					</tr>
				</thead>
				<tbody>
					<?php		
						try {
							$stmnt = DB::$connection->prepare("SELECT * FROM viewCompressedFormats"); 
							$stmnt->execute();
							while ($row = $stmnt->fetch(PDO::FETCH_NUM)) {
								$formatname = $row[0];
								if (!empty($formatname)) {
									echo "<tr>";						
									echo "<td class='firstcolumn'><a href='listreports.php?compressedtextureformat=".$formatname."'>".$formatname."</a> (<a href='listreports.php?compressedtextureformat=".$formatname."&option=not'>not</a>)</td>";
									echo "<td class='firstcolumn' align=center>".round($row[1], 2)."%</td>";
									echo "</tr>";	    
									$index++;
								}
							}    
						} catch (PDOException $e) {
							echo "<b>Error while fetching compressed formats list</b><br>";
						}
					?>   
				</tbody>
			</table> 
		</div>
	</div>

	<?php 
		DB::disconnect();
		include "footer.html";
	?>
	
</center>

	<script>
		$(document).ready(function() {
			var table = $('#formats').DataTable({
				"pageLength" : 50,
				"paging" : true,
				"stateSave": false, 
				"searchHighlight" : true,	
				"dom": 'fp',			
				"bInfo": false,	
				"order": [[ 0, "asc" ]]	
			});
		} );	
	</script>
	
</body>
</html>