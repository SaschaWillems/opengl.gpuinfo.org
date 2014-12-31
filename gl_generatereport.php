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
  
  if(isset($_POST['compare'])) {
	// Compare mutliple reports
	$reportids = array();
	$reportlimit = false;

	foreach ($_REQUEST['checkbox_report'] as $k => $v) {
		$reportids[] = $k;	
		if (count($reportids) > 7) {
			$reportlimit = true;	 
			break; 
		}
	}   
	
   if ($reportlimit) {echo "<b>Note : </b>You selected more than 8 reports to compare, only displaying 8 reports.\n"; }	

   sort($reportids, SORT_NUMERIC);
	
   // Header
   $colspan = count($reportids) + 1;	
   echo "<table>";
   echo "<TR> <TD id='title' colspan=$colspan>Comparing ". count($reportids) ." reports (<a href='#extensions'>Jump to extensions</a>) (<a href='listreports.php'>Back to database</a>)</TD></TR>\n";
	
   echo "<tr><td id='reporttableheader'>OpenGL identifier</td>";
   foreach ($reportids as $repid)
    {
  	 echo "<td id='reporttableheader'>Report No. $repid</td>";
    }
   echo "</tr>";
	    
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
	$index = 0;  
	for ($i = 0, $arrsize = sizeof($column[0]); $i < $arrsize; ++$i) { 	  
		$add = "";
		$bgcolor  = $index % 2 == 0 ? $bgcolordef : $bgcolorodd; 	  
		echo "<tr style='background-color:$bgcolor;' $add>\n";
		echo "<td class='reportrow'>". $captions[$i]  ."</td>\n";
		for ($j = 0, $subarrsize = sizeof($column); $j < $subarrsize; ++$j) {	 
			echo "<td class='reportrow'>".$column[$j][$i] ."</td>";
		} 
		echo "</tr>\n";
		$index++;
	}   
	
  }
  else {	  
   // list single reports
   $reportID = $_GET['reportID']; 

   $sqlresult = mysql_query("SELECT GL_RENDERER FROM openglcaps WHERE ReportID = $reportID");
   $row = mysql_fetch_array($sqlresult);
     
	echo "<table><tr><td valign=top>"; 
	echo "<table><tr><td valign=top>";
	echo "<TBODY>";
	echo "<tr><td id='tableheader' colspan=4><b>Report for ".$row['GL_RENDERER']."</b></td></tr>";

   $sqlresult = mysql_query("SELECT * FROM openglcaps WHERE ReportID = $reportID");
   $colindex  = 0;    
   $index = 0;
   $emptyCaps = array();
   
	while($row = mysql_fetch_row($sqlresult))
	{
		foreach ($row as $data)
		{
			$caption = mysql_field_name($sqlresult, $colindex);		  
			$bgcolor  = $index % 2 != 0 ? $bgcolordef : $bgcolorodd; 	  

			if (!is_null($data)) {
				if ($caption == 'submitter') {
					if ($data != '') {
						echo "<tr style='background-color:$bgcolor;'><td class='firstrow'>Submitted by</td>";
						echo "<td class='valuezeroleftdark'><a href='listreports2.php?submitter=$data'>$data</a></td></tr>";
						// Check report history
						$sqlHistoryResult = mysql_query("SELECT date,submitter from reportHistory where Reportid = $reportID order by Id desc");						
						$historyCount = mysql_num_rows($sqlHistoryResult);
						$historyRow = mysql_fetch_row($sqlHistoryResult);
						if ($historyCount > 0) {
							$index++;
							$bgcolor  = $index % 2 != 0 ? $bgcolordef : $bgcolorodd; 	  
							echo "<tr style='background-color:$bgcolor;'><td class='firstrow'>Last update</td>";
							echo "<td class='valuezeroleftdark'>$historyRow[0] by $historyRow[1] (<a href='gl_reporthistory.php?reportId=$reportID'>history</a>)</td></tr>";
						}
						echo "</td></tr>";
					} else {
						$index++;
					}
				}

				if ($caption == 'os') {
					echo "<tr style='background-color:$bgcolor;'><td class='firstrow'>Operating system</td>";
					echo "<td class='valuezeroleftdark'>$data</td></tr>";
				}

				if ($caption == 'contexttype') {
					$contextType = "OpenGL";
					if ($data == "core") {
						$contextType = "OpenGL core";
					}
					if ($data == "es2") {
						$contextType = "OpenGL ES 2.0";
					}
					if ($data == "es3") {
						$contextType = "OpenGL ES 3.0";
					}
					echo "<tr style='background-color:$bgcolor;'><td class='firstrow'>Context type</td>";
					echo "<td class='valuezeroleftdark'>$contextType</td></tr>";
				}
				
				if (strpos($caption, 'GL_') !== false) {
					echo "<tr style='background-color:$bgcolor;'><td class='firstrow'>$caption</td>";

					if ((is_numeric($data) && ($caption!=='GL_SHADING_LANGUAGE_VERSION')) ) {
						echo "<td class='valuezeroleftdark'>".number_format($data)."</td></tr>";
					} else {
						echo "<td class='valuezeroleftdark'>$data</td></tr>";
					}
					
				}
			}

			if (strpos($caption, 'extensions') !== false) {
				$extstr = $data; 	  
			};
			if ((strpos($caption, 'GL_') !== false) && (is_null($data))) {	
				$emptyCaps[] = $caption;
			}
			$colindex++;
			$index++;
		}

	}	
	// List of caps not available for this report
	echo "<tr><td class='firstrow'>";	
	echo "<p style='font-size:0.75em;'>";
	if (sizeof($emptyCaps) > 0)
	{
		$missingCapsList = implode("<br>", $emptyCaps);  
		echo "<b>Capabilites not available in this report :</b><br>";
		echo $missingCapsList;
	}
	echo "</p>";
	echo "</td></tr>";	
	echo "</tbody></table>";
	echo "</td>";	
  }
     
		
	// List Extensions
	if(isset($_POST['compare'])) {  	   
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
	$extarray   = array(); 

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
	echo "<TR><TD id='reporttableheader' colspan=$colspan><a name='extensions'>Supported extensions</a></TD></TR>"; 
	$rowindex = 0;
	foreach ($extcaption as $extension){
		$bgcolor  = $rowindex % 2 == 0 ? $bgcolordef : $bgcolorodd; 
		echo "<tr style='background-color:$bgcolor;'><td class='reportrow'>$extension</td>\n";		 
		$index = 0;
		foreach ($reportids as $repid) {
			if (in_array($extension, $extarray[$index])) { 
				echo "<td class='reportrow'><img src='icon_yes.png'/></td>";
			} else {
				echo "<td class='reportrow'><img src='icon_no.png'/></td>";
			}	
			$index++;
		}  
		$rowindex++;
	echo "</tr>\n"; 
	}	  
	echo "</table>";	
	}
	else {	   
		// Single report
		echo "<td valign=top>";
		$reportID = $_GET['reportID'];         
		$str = "SELECT Name FROM openglgpuandext LEFT JOIN openglextensions ON openglextensions.PK = openglgpuandext.ExtensionID WHERE openglgpuandext.ReportID = $reportID ORDER BY FIELD(SUBSTR(openglextensions.Name, 1, 3), 'GL_') DESC, FIELD(SUBSTR(openglextensions.Name, INSTR(openglextensions.Name, '_')+1, 3), 'EXT', 'ARB') DESC, openglextensions.Name ASC";  
		$sqlresult = mysql_query($str);  
		$extarray = array();
		while($row = mysql_fetch_row($sqlresult)) {	
			foreach ($row as $data) {
				$extarray[] = $data;
			}
		}	 
		echo "<table><TBODY>";
		echo "<tr><td id='tableheader' colspan=4><b>".count($extarray)." supported extensions</b></td></tr>";
		
		$index = 0;
		foreach ($extarray as $extension) {
			$bgcolor  = $index % 2 != 0 ? $bgcolordef : $bgcolorodd; 	   
			echo "<tr><td class='firstrow' style='background-color:$bgcolor;'><a href='listreports2.php?listreportsbyextension=$extension'>$extension</a></td><tr/>";
			$index++;
		}
		echo "</tbody></table>"; 	
		echo "</td></tr></table>";
	}	
   
   ?>     

<?php mysql_close();  ?>
 
<?php include("./gl_footer.inc");	?>
</body>
</html>