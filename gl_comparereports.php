<head>
  <link rel="stylesheet" href="./libs/jquery-ui/themes/flick/jquery-ui.css">
  <script src="./libs/jquery.min.js"></script>
  <script src="./libs/jquery-ui/jquery-ui.min.js"></script>
  <script>
  $(function() {
    $( "#tabs" ).tabs();
  });
  function showDiffOnly() {
	$('.same').toggle()
  }
  </script>
</head>
<body>
<div>
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
  
	echo "<div id='content'>";
	$extDiffOnly = false;
	if (isset($_GET['extDiffOnly'])) {
		$extDiffOnly = true;
	}
  
	if (isset($_GET['compare'])) {
		$reportids = array();
		$reportlimit = false;

		foreach ($_REQUEST['id'] as $k => $v) {
			$reportids[] = $k;	
			if (count($reportids) > 7) {
				$reportlimit = true;	 
				break; 
			}
		}   

		if ($reportlimit) {echo "<b>Note : </b>You selected more than 8 reports to compare, only displaying the first 8 selected reports.\n"; }	

		sort($reportids, SORT_NUMERIC);

		// Header
		$colspan = count($reportids) + 1;	
		
	echo "<table width='80%'><tr><td valign=top>"; 
	
	echo "<div id='tabs' style='font-size:12px;'>";
	echo "<ul>";
	echo "<h2 style='margin-left:10px;'>Comparing OpenGL reports</h2>";
    echo "<li><a href='#tabs-1'>Implementation</a></li>";
    echo "<li><a href='#tabs-2'>Extensions</a></li>";
	echo "</ul>";		
		
	// Implementation and capabilities
	echo "<div id='tabs-1'>";
	echo "<table width='95%'>";
 

		$repids = implode(",", $reportids);   
		$sql       = "SELECT * FROM openglcaps WHERE ReportID IN (" . $repids . ")" ;
		$sqlresult = mysql_query($sql);
		$reportindex = 0;

		// Gather data into array
		$column    = array();
		$captions  = array();
		//   $maxval    = array();
		//$minval    = array();
		//$compare	  = array();

		while($row = mysql_fetch_row($sqlresult)) {
			$colindex = 0;
			$reportdata = array();		

			foreach ($row as $data) {
			$caption = mysql_field_name($sqlresult, $colindex);		  

			if (strpos($caption, 'GL_') !== false) {
				$reportdata[] = $data;	  
				$captions[]   = $caption;
			}

			if ($caption == 'submitter') {
				$reportdata[] = $data;	  
				$captions[]   = 'Submitted by';
			}

			if ($caption == 'os') {
				$reportdata[] = $data;	  
				$captions[]   = 'Operating System';
			}

			$colindex++;
			} 

			$column[] = $reportdata; 

			$reportindex++;
		}   

		// Generate table from selected reports
		$index = 1;  
		for ($i = 0, $arrsize = sizeof($column[0]); $i < $arrsize; ++$i) { 	  
			$add = "";
			$bgcolor  = $index % 2 == 0 ? $bgcolordef : $bgcolorodd; 	  
			
			if ($captions[$i] == 'GL_RENDERER') {
				echo "<tr style='background-color:$bgcolor;' $add>\n";
			} else {
				echo "<tr style='background-color:$bgcolor;' $add>\n";
			}
			echo "<td class='firstrow'>". $captions[$i]  ."</td>\n";
			
			
			if (is_numeric($column[0][$i])) {
			
				$minval = $column[0][$i];
				$maxval = $column[0][$i];
			
				for ($j = 0, $subarrsize = sizeof($column); $j < $subarrsize; ++$j) {	 			
					if ($column[$j][$i] < $minval) {
						$minval = $column[$j][$i];
					}
					if ($column[$j][$i] > $maxval) {
						$maxval = $column[$j][$i];
					}
				}
			}			
			
			for ($j = 0, $subarrsize = sizeof($column); $j < $subarrsize; ++$j) {	 
				$fontstyle = '';
				if ($captions[$i] == 'GL_RENDERER') {
					echo "<td class='valuezeroleftblack'><b>".$column[$j][$i] ."</b></td>";
				} else {
					if (is_numeric($column[$j][$i]) ) {
					
						if ($column[$j][$i] < $maxval) {
							$fontstyle = "style='color:#FF0000;'";
						}
					
						if ($captions[$i] == 'GL_SHADING_LANGUAGE_VERSION') {
							echo "<td class='valuezeroleftdark'>".number_format($column[$j][$i], 2, '.', ',')."</td>";
						} else {
							echo "<td class='valuezeroleftdark' $fontstyle>".number_format($column[$j][$i], 0, '.', ',')."</td>";
						}
					} else {
						echo "<td class='valuezeroleftdark'>".$column[$j][$i]."</td>";
					}
				}
			} 
			echo "</tr>\n";
			$index++;
		}   

	}
	else {	  
		echo "No reports to compare...";
	}
	
	echo "</table></div>";	
     
		
	// Extensions
	echo "<div id='tabs-2'>";
	echo "<button onclick='showDiffOnly();'>Toggle all / diff only</button>";
	
	echo "<table width='95%'>";
		// Gather all extensions supported by at least one of the reports
		$str = "SELECT DISTINCT Name FROM openglgpuandext LEFT JOIN openglextensions ON openglextensions.PK = openglgpuandext.ExtensionID WHERE openglgpuandext.ReportID IN ($repids)  ORDER BY FIELD(SUBSTR(openglextensions.Name, 1, 3), 'GL_') DESC, FIELD(SUBSTR(openglextensions.Name, INSTR(openglextensions.Name, '_')+1, 3), 'EXT', 'ARB') DESC, openglextensions.Name ASC";	
		$sqlresult = mysql_query($str); 
		$extcaption = array(); // Captions (for all gathered extensions)   

		while($row = mysql_fetch_row($sqlresult)) {	
			foreach ($row as $data) {
				$extcaption[] = $data;	  
			}
		}

		// Get extensions for each selected report into an array 
		$extarray = array(); 

		foreach ($reportids as $repid) {
			$str = "SELECT Name FROM openglgpuandext LEFT JOIN openglextensions ON openglextensions.PK = openglgpuandext.ExtensionID WHERE openglgpuandext.ReportID = $repid";			
			$sqlresult = mysql_query($str); 
			$subarray = array();
			while($row = mysql_fetch_row($sqlresult)) {	
					foreach ($row as $data) {
					$subarray[] = $data;	  
				}
			}
			$extarray[] = $subarray; 
		}

		// Generate table
		$colspan = count($reportids) + 1;	

		// Implementation info 
		$headerFields = array("GL_VENDOR", "GL_RENDERER", "GL_VERSION");
		$rowindex = 1;
		foreach ($headerFields as $headerField) {
			$bgcolor  = $rowindex % 2 == 0 ? $bgcolordef : $bgcolorodd; 
			echo "<tr class='firstrow' style='background-color:$bgcolor;'>";		
			echo "<td class='firstrow'>$headerField</td>";		 
				foreach ($reportids as $repid) {
					$sqlresult = mysql_query("SELECT $headerField FROM openglcaps WHERE ReportID = $repid"); 
					$sqlrow = mysql_fetch_row($sqlresult);
					echo "<td class='valuezeroleftblack'><b>$sqlrow[0]</b></td>";
				}	
			echo "</tr>";
			$rowindex++;
		}		

		// Extension count 	
		$bgcolor  = $rowindex % 2 == 0 ? $bgcolordef : $bgcolorodd; 
		echo "<tr class='firstrow' style='background-color:$bgcolor;'><td class='firstrow'>Extension count</td>"; 
		for ($i = 0, $arrsize = sizeof($extarray); $i < $arrsize; ++$i) { 	  
			echo "<td class='valuezeroleftdark'>".count($extarray[$i])."</td>";
		}
		echo "</tr>"; 		
		$rowindex++;
		
		foreach ($extcaption as $extension){
		
			// Check if missing it at least one report
			$missing = false;
			$index = 0;
			foreach ($reportids as $repid) {
				if (!in_array($extension, $extarray[$index])) { 
					$missing = true;
				}
				$index++;
			}  			
		
			$bgcolor  = $rowindex % 2 == 0 ? $bgcolordef : $bgcolorodd; 
			$add = '';
			if ($missing) {
				$add = 'color:#FF0000;';
			}
			$className = "same";
			$index = 0;
			foreach ($reportids as $repid) {
				if (!in_array($extension, $extarray[$index])) { 
					$className = "diff";
				}
				$index++;
			}
				echo "<tr style='background-color:$bgcolor;$add' class='$className'><td class='firstrow'>$extension</td>\n";		 
			$index = 0;
			foreach ($reportids as $repid) {
				if (in_array($extension, $extarray[$index])) { 
					echo "<td class='valuezeroleftdark'><img src='icon_check.png' width=16px></td>";
				} else {
					echo "<td class='valuezeroleftdark'><img src='icon_missing.png' width=16px></td>";
				}	
				$index++;
			}  
			$rowindex++;
		echo "</tr>"; 
		}	  
	echo "</table></div>";	
	
	if ($extDiffOnly) {
	?>
	<script>
		$('.same').hide();
	</script>
	<?php
	}
	dbDisconnect();
	include("./gl_footer.inc");	?>
</div>
</body>
</html>