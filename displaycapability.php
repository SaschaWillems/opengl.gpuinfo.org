 <?php
	/*
		*
		* OpenGL hardware capability database server implementation
		*
		* Copyright (C) 2011-2021 by Sascha Willems (www.saschawillems.de)
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
	include './includes/chart.php';
	
	$name = null;
	$esversion = 2;
	if (isset($_GET['name'])) {
		$name = $_GET['name'];
	}

	// Check if capability is valid
	DB::connect();
	$result = DB::$connection->prepare("SELECT * from information_schema.columns where TABLE_NAME = 'openglcaps' and column_name = :columnname");
	$result->execute([":columnname" => $name]);
	DB::disconnect();
	if ($result->rowCount() == 0) {
		echo "<center>";
		?>
			<div class="alert alert-danger error">
			<strong>This is not the <strike>droid</strike> capability you are looking for!</strong><br><br>
			You may have passed a wrong capability name.
			</div>				
		<?php
		include "footer.html";
		echo "</center>";
		die();
	}	

	$compare = ' > 0';
	if (($name === 'GL_VENDOR') || (($name === 'GL_RENDERER'))) {
		$compare = 'is not null';
	}
	if (stripos($name, "GL_MIN") !== false) {
		$compare = 'is not null';
	}

	// Gather data		
	$labels = [];
	$counts = [];
	DB::connect();
	$result = DB::$connection->prepare("SELECT `$name` as value, count(0) as reports from openglcaps where `$name` ".$compare." group by 1 order by 2 desc");
	$result->execute();
	$rows = $result->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rows as $row) {
		$labels[] = $row['value'];
		$counts[] = $row['reports'];
	}
	DB::disconnect();	

?>
	<div class='header'>
		<h4 class='headercaption'>Value distribution for <?php echo $name ?></h4>
	</div>

	<center>	
		<div class='chart-div'>
			<div id="chart"></div>
			<div class='valuelisting'>
				<table id="caps" class="table table-striped table-bordered table-hover reporttable" >
					<thead>
						<tr>				
							<th>Value</th>
							<th>Reports</th>
						</tr>
					</thead>
					<tbody>				
						<?php	
						for ($i = 0; $i < count($labels); $i++) {
							$color_style = "style='border-left: ".Chart::getColor($i)." 3px solid'";
							$link ="listreports.php?capability=$name&value=".$labels[$i];
							echo "<tr>";						
							echo "<td $color_style>".$labels[$i]."</td>";
							echo "<td><a href='$link'>".$counts[$i]."</a></td>";
							echo "</tr>";	    
						}
						?>
					</tbody>
				</table> 

			</div>
		</div>
		<?php 
			include "footer.html";
		?>		
	</center>
	
    <script type="text/javascript">
		$(document).ready(function() {
			var table = $('#caps').DataTable({
				"pageLength" : -1,
				"paging" : false,
				"stateSave": false, 
				"searchHighlight" : true,	
				"dom": '',			
				"bInfo": false,	
				"order": [[ 0, "asc" ]]	
			});
		} );
		<?php			
			Chart::draw($labels, $counts);
		?>
	</script>
</body>
</html>