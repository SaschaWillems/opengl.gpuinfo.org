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
	
    include 'header.html';		
	include 'dbconfig.php';   
?>

	<div class='header'>
		<h4>Maximum supported OpenGL versions per device</h4>
	</div>


<center>

	<div class='parentdiv'>
		<div class='tablediv' style='width:auto; display: inline-block;'>	
	
	<form method="get" action="compare.php?compare" style="margin-bottom:0px;">	
		
	<table id="reports" class="table table-striped table-bordered table-hover reporttable">
		<thead>
			<tr>
				<th></th>				
				<th></th>
				<th align='center'><input type='submit' name='compare' value='compare' class='button'></th>
			</tr>
			<tr>
				<th>Device</th>				
				<th>Version</th>
				<th></th>
			</tr>
		</thead>
		<tbody>		
			<?php
				DB::connect(); 
				try {				
					$stmnt = DB::$connection->prepare("SELECT * from viewDeviceMaxVersions");
					$stmnt->execute([]);
					while($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
						$name = trim($row["name"]);
						$version = $row["maxversion"];
						$reportid = trim($row["repid"]);
						echo "<tr>";				
						echo "	<td class='firstrow'><a href='displayreport.php?id=$reportid'>$name</a></td>";		 
						echo "	<td class='valuezeroleftblack'>$version</td>";
						echo "	<td align='center'><input type='checkbox' name='id[$reportid]'></td>";				
						echo "</tr>";					
					}
				} catch (PDOException $e) {
					echo "<b>Error while fetching supported versions</b><br>";
				}				
				DB::disconnect();  
			?>   
		</tbody></table>
		
	</form> 
	
	<script>
		$(document).ready(function() {
			$('#reports').DataTable({
				"pageLength" : 50,
				"paging" : true,
				"stateSave": false, 
				"searchHighlight" : true,	
				"dom": 'fp',			
				"bInfo": false,	
				"order": [[ 0, "asc" ]],
				
				initComplete: function () {
					var api = this.api();

					api.columns().indexes().flatten().each( function ( i ) {
						if (i == 1) {						
							var column = api.column( i );
							var select = $('<br/><select onclick="stopPropagation(event);"><option value=""></option></select>')
							.appendTo( $(column.header()) )
							.on( 'change', function () {
								var val = $.fn.dataTable.util.escapeRegex(
								$(this).val()
								);

								column
								.search( val ? '^'+val+'$' : '', true, false )
								.draw();
							} );	

							column.data().unique().sort().each( function ( d, j ) {
								select.append( '<option value="'+d+'">'+d+'</option>' )
							} );
						};
					} );
				}				
				
			});
			
		} );	
	</script>	
	
	<?php include 'footer.html'	?>

	</div>
</div>

</body>
</html>