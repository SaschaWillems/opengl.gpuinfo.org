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

	include 'header.php';

	$negate = false;
    $searchType = '';
	$headeradd = '';
	
	// Header
	$defaultHeader = true;
	$alertText = null;	
    $negate = false;
	if (isset($_GET['option'])) {
		if ($_GET['option'] == 'not') {
			$negate = true;
		}
	}
		
	// Extension
	$filter["extension"] = null;
	$extension = $_GET['extension'];
	if($_GET['listreportsbyextension'] != '') {
		$extension = $_GET['listreportsbyextension'];		
	}
	if ($extension != '') {
		$filter["extension"] = $extension;
		$defaultHeader = false;
		$headerClass = $negate ? "header-red" : "header-green";			
		$caption = "Reports ".($negate ? "<b>not</b>" : "")." supporting <b>".$extension."</b>";	
		$caption .= " (<a href='listreports.php?extension=".$extension.($negate ? "" : "&option=not")."'>toggle</a>)";
	}

	$filter["compressedtextureformat"] = null;
    if($_GET['compressedtextureformat'] != '') {
		$filter["compressedtextureformat"] = $_GET['compressedtextureformat'];
		$defaultHeader = false;
		$headerClass = $negate ? "header-red" : "header-green";			
		$caption = "Reports ".($negate ? "<b>not</b>" : "")." supporting format <b>".$filter["compressedtextureformat"]."</b>";	
		$caption .= " (<a href='listreports.php?compressedtextureformat=".$filter["compressedtextureformat"].($negate ? "" : "&option=not")."'>toggle</a>)";
	}
	
	$filter["submitter"] = null;
    if($_GET['submitter'] != '') {
		$filter["submitter"] = $_GET['submitter'];
		$defaultHeader = false;
		$headerClass = "header-blue";
		$caption = "Reports submitted by <b>".$filter["submitter"]."</b>";	
	}
	
	$filter["capability"] = null;
    if (($_GET['capability'] != '') && ($_GET['value'] != '')) {
		$filter["capability"] = $_GET['capability'];
		$filter["capabilityvalue"] = $_GET['value'];
		$defaultHeader = false;
		$headerClass = "header-blue";
		$link = "displaycapability.php?name=".$filter["capability"];
		$caption = "Reports with <a href=".$link.">".$filter["capability"]."</a> = ".$filter["capabilityvalue"];	
	}	
	
	if ($defaultHeader) {
		echo "<div class='header'>";	
		echo "	<h4>Listing reports</h4>";
		echo "</div>";		
	}			
?>

<center>

	<!-- Compare block (only visible when at least one report is selected) -->
	<div id="compare-div" class="well well-sm" role="alert" style="text-align: center; display: none; margin-bottom: 0px;">
		<div class="compare-header">Selected reports for compare:</div>
		<span id="compare-info"></span>
		<div class="compare-footer">
			<Button onClick="clearCompare()"><span class='glyphicon glyphicon-button glyphicon-erase'></span> Clear</Button>
			<Button onClick="compare()"><span class='glyphicon glyphicon-button glyphicon-duplicate'></span> Compare</Button>
		</div>
	</div>

	<div class="tablediv">
		<table id="reports" class="table table-striped table-bordered table-hover reporttable" style='width:auto'>
			<?php
				if (!$defaultHeader) {
					echo "<caption class='".$headerClass." header-span'>".$caption."</caption>";
				}
			?>			
			<thead>
				<tr>
					<th></th>
					<th>renderer</th>
					<th>version</th>
					<th>gl</th>
					<th>glsl</th>
					<th>context</th>
					<th>os</th>
					<th></th>
				</tr>
				<tr>
					<th>id</th>
					<th>Renderer</th>
					<th>Version</th>
					<th>GL</th>
					<th>GLSL</th>
					<th>Context</th>
					<th>OS</th>
					<th>Compare</th>
				</tr>
			</thead>
		</table>
		<div id="errordiv" style="color:#D8000C;"></div>		
	</div>

<script src="js/reportcompare.js"></script>

<script>
	$(document).on("keypress", "form", function(event) { 
    	return event.keyCode != 13;
	});		

	$(document).ready(function() {

		$.get(comparerUrl, null, function (response) {
			displayCompare(response);
		});		

		var table = $('#reports').DataTable({
			"processing": true,
			"serverSide": true,
			"paging" : true,		
			"searching": true,	
			"lengthChange": false,
			"dom": 'lrtip',	
			"pageLength" : 25,		
			"order": [[ 0, 'desc' ]],
			"columnDefs": [
				{ 
					"searchable": false, "targets": [ 0, 7 ],
					"orderable": false, "targets": 7,
			    }
			],
			"ajax": {
				url :"backend/reports.php",
				data: {
					"filter": {
						'option' : '<?php echo $_GET["option"] ?>',
						'extension' : '<?php echo $filter["extension"] ?>',		
						'compressedtextureformat': '<?php echo $filter["compressedtextureformat"] ?>',
						'submitter': '<?php echo $filter["submitter"] ?>',
						'capability': '<?php echo $filter["capability"] ?>',
						'capabilityvalue': '<?php echo $filter["capabilityvalue"] ?>'						
					}
				},
				error: function (xhr, error, thrown) {
					$('#errordiv').html('Could not fetch data (' + error + ')');
					$('#reports_processing').hide();
				}				
			},
			"columns": [
				{ data: 'id' },
				{ data: 'renderer' },
				{ data: 'version' },
				{ data: 'glversion' },
				{ data: 'glslversion' },
				{ data: 'contexttype' },
				{ data: 'os' },
				{ data: 'compare' },
			],
			// Pass order by column information to server side script
			fnServerParams: function(data) {
				data['order'].forEach(function(items, index) {
					data['order'][index]['column'] = data['columns'][items.column]['data'];
				});
			},
		});   

		// Per-Column filter boxes
		$('#reports thead th').each( function (i) {
			var title = $('#reports thead th').eq( $(this).index() ).text();
			if ((title !== 'id') && (title !== '')) {
				var w = (title != 'device') ? 120 : 240;
				$(this).html( '<input type="text" placeholder="'+title+'" data-index="'+i+'" style="width: '+w+'px;" class="filterinput" />' );
			}
		}); 
		$(table.table().container() ).on('keyup', 'thead input', function () {
			table
				.column($(this).data('index'))
				.search(this.value)
				.draw();
		});		

	});
</script>

<?php include "footer.html"; ?>
</center>

</body>
</html>