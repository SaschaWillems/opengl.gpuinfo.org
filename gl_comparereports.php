	<script>
		function showDiffOnly() {
			$('.same').toggle()
		}
		function toggleDiffCaps() {
			$('.sameCaps').toggle()
		}	
	</script>
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
			include './gl_config.php';
			
			dbConnect();	
			
			echo "<center><div id='reportdiv'>";
			
			$extDiffOnly = false;
			if (isset($_GET['extDiffOnly'])) {
				$extDiffOnly = true;
			}
			
			// Use url parameter to enable diff only display
			$diff = false;
			if (isset($_GET['diff'])) 
			{
				$diff = ($_GET['diff'] == 1);
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
				
				echo "<div class='header'>";
					echo "<h4 style='margin-left:10px;'>Comparing ".count($reportids)." reports</h4>";
				echo "</div>";					
				
				if ($reportlimit) {echo "<b>Note : </b>You selected more than 8 reports to compare, only displaying the first 8 selected reports.\n"; }	
				
				sort($reportids, SORT_NUMERIC);
				
				// Header
				$colspan = count($reportids) + 1;	
				
				echo "<table width='80%'><tr><td valign=top>"; 
				
				echo "<div id='tabs' style='font-size:12px;'>";
				echo "<ul class='nav nav-tabs'>";
				echo "	<li class='active'><a data-toggle='tab' href='#tab-implementation'>Implementation</a></li>";
				echo "	<li><a data-toggle='tab' href='#tab-extensions'>Extensions</a></li>";
				echo "	<li><a data-toggle='tab' href='#tab-compressed'>Compressed formats</a></li>";
				echo "</ul>";		
				
				// Implementation and capabilities
				echo "<div id='tab-implementation'>";
				echo "<button onclick='toggleDiffCaps();' class='btn btn-default'>Toggle all / diff only</button>";				
				echo "<table id='caps' width='100%' class='table table-striped table-bordered'>";

				// Table header
				echo "<thead><tr><td class='caption'>Capability</td>";
				foreach ($reportids as $reportId) {
					echo "<td class='caption'>Report $reportId</td>";
				}
				echo "</tr></thead><tbody>";
				
				$repids = implode(",", $reportids);   
				$sql       = "SELECT * FROM openglcaps WHERE ReportID IN (" . $repids . ")" ;
				$sqlresult = mysql_query($sql);
				$reportindex = 0;
				
				// Gather data into array
				$column    = array();
				$captions  = array();
				
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

					// Get min and max for this capability
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
					
					// Caption
					$fontStyle = ($minval < $maxval) ? "style='color:#FF0000;'" : "";					
					$headerFields = array("GL_VENDOR", "GL_RENDERER", "GL_VERSION", "GL_SHADING_LANGUAGE_VERSION", "Operating System", "Submitted by");
					if (!in_array($captions[$i], $headerFields)) {
						$className = ($minval < $maxval) ? "" : "class='sameCaps'";
					} else {
						$className = "";
					}
					echo "<tr style='background-color:$bgcolor;' $add $className>\n";
					echo "<td class='firstrow' $fontStyle>". $captions[$i] ."</td>\n";									
				
					// Values
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
			
			echo "</tbody></table></div>";	
			
			
			// Extensions
			echo "<div id='tab-extensions'>";
			echo "<button onclick='showDiffOnly();' class='btn btn-default'>Toggle all / diff only</button>";			
			echo "<table id='extensions' width='100%' class='table table-striped table-bordered'>";
			// Table header
			echo "<thead><tr><td class='caption'>Extension</td>";
			foreach ($reportids as $reportId) {
				echo "<td class='caption'>Report $reportId</td>";
			}
			echo "</tr></thead><tbody>";
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
			echo "</tbody></table></div>";	
			
			if ($extDiffOnly) 
			{
				?>
				<script>
					$('.same').hide();
					$(document).ready(function() {
						$('#tabs').tabs("option", "active", 1);				
						
					});		
				</script>
				<?php
			}
			
			// Compressed formats
			echo "<div id='tab-compressed'>";
			echo "<button onclick='showDiffOnly();' class='btn btn-default'>Toggle all / diff only</button>";
			
			echo "<table id='compressedformats' width='100%' class='table table-striped table-bordered'>";
			// Table header
			echo "<thead><tr><td class='caption'>Compressed format</td>";
			foreach ($reportids as $reportId) {
				echo "<td class='caption'>Report $reportId</td>";
			}
			echo "</tr></thead><tbody>";
			// Gather all compressed formats supported by at least one of the reports
			$str = "SELECT 
				DISTINCT text FROM compressedTextureFormats ctf LEFT JOIN enumTranslationTable ett ON ctf.formatEnum = ett.enum
			WHERE 
				ctf.ReportID IN ($repids)  
			ORDER 
				BY text ASC";	
			$sqlresult = mysql_query($str); 
			$formatcaptions = array();
			
			while($row = mysql_fetch_row($sqlresult)) 
			{	
				foreach ($row as $data) 
				{
					$formatcaptions[] = $data;	  
				}
			}			
			// Get compressed formats for each selected report into an array 
			$formatarray = array(); 
			
			foreach ($reportids as $repid) 
			{
				$str = "
				SELECT 
					DISTINCT text FROM compressedTextureFormats ctf LEFT JOIN enumTranslationTable ett ON ctf.formatEnum = ett.enum
				WHERE 
					ctf.ReportID = $repid";			
				$sqlresult = mysql_query($str); 
				$subarray = array();
				while($row = mysql_fetch_row($sqlresult)) 
				{	
					foreach ($row as $data) 
					{
						$subarray[] = $data;	  
					}
				}
				$formatarray[] = $subarray; 
			}
			
			// Generate table
			$colspan = count($reportids) + 1;	
			
			// Implementation info 
			$headerFields = array("GL_VENDOR", "GL_RENDERER", "GL_VERSION");
			$rowindex = 1;
			foreach ($headerFields as $headerField) 
			{
				echo "<tr class='firstrow'>";		
				echo "<td class='firstrow'>$headerField</td>";		 
				foreach ($reportids as $repid) 
				{
					$sqlresult = mysql_query("SELECT $headerField FROM openglcaps WHERE ReportID = $repid"); 
					$sqlrow = mysql_fetch_row($sqlresult);
					echo "<td class='valuezeroleftblack'><b>$sqlrow[0]</b></td>";
				}	
				echo "</tr>";
				$rowindex++;
			}		
			
			// Format count 	
			echo "<tr class='firstrow'><td class='firstrow'>Format count</td>"; 
			for ($i = 0, $arrsize = sizeof($formatarray); $i < $arrsize; ++$i) 
			{ 	  
				echo "<td class='valuezeroleftdark'>".count($formatarray[$i])."</td>";
			}
			echo "</tr>"; 		
			$rowindex++;
			
			foreach ($formatcaptions as $format)
			{
				// Check if missing it at least one report
				$missing = false;
				$index = 0;
				foreach ($reportids as $repid) {
					if (!in_array($format, $formatarray[$index])) { 
						$missing = true;
					}
					$index++;
				}  			
				
				$add = ($missing) ? 'color:#FF0000;' : '';
				$className = "same";
				$index = 0;
				foreach ($reportids as $repid) {
					if (!in_array($format, $formatarray[$index])) 
					{ 
						$className = "diff";
					}
					$index++;
				}
				echo "<tr style='background-color:$bgcolor;$add' class='$className'><td class='firstrow'>$format</td>\n";		 
				$index = 0;
				foreach ($reportids as $repid) 
				{
					if (in_array($format, $formatarray[$index])) 
					{ 
						echo "<td class='valuezeroleftdark'><img src='icon_check.png' width=16px></td>";
					} 
					else 
					{
						echo "<td class='valuezeroleftdark'><img src='icon_missing.png' width=16px></td>";
					}	
					$index++;
				}  
				$rowindex++;
				echo "</tr>"; 
			}	  
			echo "</tbody></table></div>";	
			
			if ($diff) 
			{
				?>
				<script>
					$('.same').hide();
					$(document).ready(function() {
						$('#tabs').tabs("option", "active", 1);				
						
					});		
				</script>
				<?php
			}

			
			
			dbDisconnect();
		include("./gl_footer.inc");	?>
		
	<script>
		$(document).ready(function() {
			$('#caps').DataTable({
				"pageLength" : -1,
				"paging" : false,
				"order": [], 
				"searchHighlight": true,
			});
			$('#extensions').DataTable({
				"pageLength" : -1,
				"order": [], 
				"searchHighlight": true,
				"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ]
			});
			$('#compressedformats').DataTable({
				"pageLength" : -1,
				"order": [], 
				"searchHighlight": true,
				"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ]
			});			
		} );	
	</script>
		
	</div>
</body>
</html>