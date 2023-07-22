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
?>

	<div class='header'>
		<h4 style='margin-left:10px;'>Listing all available extensions</h4>
	</div>

	<center>	
		<div class='parentdiv'>
			<div class='tablediv' style='width:auto; display: inline-block;'>	
				<table id="extensions" class="table table-striped table-bordered table-hover reporttable" style="min-width:512px;">						
					<thead>
						<tr>			
							<th>id</th>
							<th>Name</th>
							<th>Coverage</th>
						</tr>
					</thead>
				</table>  
				<div id="errordiv" style="color:#D8000C;"></div>
			</div>
		</div>
		<?php
			include "footer.html";
		?>
	</center>

	<script>
		$( document ).ready(function() {

			var table = $('#extensions').DataTable({
				"processing": true,
				"serverSide": true,
				"paging" : true,		
				"searching": true,	
				"lengthChange": false,
				"dom": 'fpr',	
				"pageLength" : 25,		
				"order": [[ 0, 'asc' ]],
				"columnDefs": [
					{ 
						"searchable": false, "targets": [ 0, 2 ],
					}
				],
				"ajax": {
					url :"backend/extensions.php",
					data: {
						"filter": {
							'option' : '<?php echo $_GET["option"] ?>',
							'name' : '<?php echo $filter["name"] ?>',		
						}
					},
					error: function (xhr, error, thrown) {
						$('#errordiv').html('Could not fetch data (' + error + ')');
						$('#reports_processing').hide();
					}				
				},
				"columns": [
					{ data: 'id' },
					{ data: 'name' },
					{ data: 'coverage' },
				],
				// Pass order by column information to server side script
				fnServerParams: function(data) {
					data['order'].forEach(function(items, index) {
						data['order'][index]['column'] = data['columns'][items.column]['data'];
					});
				},
			});   

			$(table.table().container() ).on('keyup', 'input', function () {
				table
					.column(1)
					.search(this.value)
					.draw();
			});		

		});
	</script>
	
	
</body>
</html>