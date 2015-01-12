<head>
	<link rel="stylesheet" href="./libs/jquery-ui/themes/flick/jquery-ui.css">
	<link rel="stylesheet" href="./libs/bootstrap.min.css">
	<link rel="stylesheet" href="./libs/dataTables.bootstrap.css">	
	<link rel="stylesheet" href="./libs/dataTables.searchHighlight.css">	
	<script src="./libs/jquery.min.js"></script>
	<script src="./libs/jquery-ui/jquery-ui.min.js"></script>
	<script src="./libs/jquery.highlight.js"></script>
	<script src="./libs/jquery.dataTables.min.js"></script>
	<script src="./libs/dataTables.bootstrap.js"></script>
	<script src="./libs/dataTables.searchHighlight.min.js"></script>
</head>

<?php 
	/* 		
		*
		* OpenGL hardware capability database server implementation
		*	
		* Copyright (C) 2011-2015 by Sascha Willems (www.saschawillems.de)
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
	
	include './gl_htmlheader.inc';	
	include './gl_menu.inc';
	include './gl_config.php';
	
	dbConnect();	 
?>

<div id="content">
	
	<table border="0" id="caps" class="table table-striped table-bordered" cellspacing="0" width="100%">
		<caption class='tableheader'>Displaying available OpenGl implementation capabilities </caption>
		<thead>
			<tr>
				<td class="caption">Capability name</td>
				<td class="caption">No. of reports</td>
			</tr>
		</thead>
		
		<?php		
			$sqlresult = mysql_query("SELECT * FROM openglcaps WHERE ReportID = 1") or die(mysql_error());  	
			
			$colindex = 0;
			$glcapnames = array();
			$skipFields = array('GL_VENDOR', 'GL_VERSION', 'GL_RENDERER');
			while($row = mysql_fetch_row($sqlresult))
			{
				foreach ($row as $data)
				{   
					$caption = mysql_field_name($sqlresult, $colindex);
					$colindex++;	 
					if (in_array($caption, $skipFields)) {
						continue;
					}
					if ($searchstring != '') {
						if (stripos($caption, $searchstring) === FALSE) {
							continue;
						}
					}
					if (strpos($caption, 'GL_') !== FALSE) {
						$glcapnames[] = $caption;
					}
				}
			}
			
			foreach ($glcapnames as $glcapname) {
				$sqlResult = mysql_query("SELECT count(*) FROM openglcaps WHERE $glcapname is not null") or die(mysql_error());  	
				$sqlCount = mysql_result($sqlResult, 0);
				echo "<tr>";
				echo "<td class='firstrow'><a href='gl_stats_caps_single.php?listreportsbycap=$glcapname'>$glcapname</a></td>";
				echo "<td class='firstrow' align='center'>$sqlCount</td>";
				echo "</tr>";
			}
			
			dbDisconnect();	
		?>   
	</tbody>
</table>  

<script>
	$(document).ready(function() {
		$('#caps').DataTable({
			"pageLength" : -1,
			"stateSave": true, 
			"searchHighlight" : true,		
			"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ]
		});
	} );	
</script>
<?php include("./gl_footer.inc");	?>
</div>
</body>
</html>