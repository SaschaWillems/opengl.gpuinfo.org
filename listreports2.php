<head>
	<link rel="stylesheet" href="./libs/jquery-ui/themes/flick/jquery-ui.css">
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
	<link rel="stylesheet" href="//cdn.datatables.net/plug-ins/3cfcc339e89/integration/bootstrap/3/dataTables.bootstrap.css">	
	<link rel="stylesheet" href="//cdn.datatables.net/plug-ins/3cfcc339e89/features/searchHighlight/dataTables.searchHighlight.css">	
	<script src="./libs/jquery.min.js"></script>
	<script src="./libs/jquery-ui/jquery-ui.min.js"></script>
	<script src="//bartaz.github.io/sandbox.js/jquery.highlight.js"></script>
	<script src="//cdn.datatables.net/1.10.4/js/jquery.dataTables.min.js"></script>
	<script src="//cdn.datatables.net/plug-ins/3cfcc339e89/integration/bootstrap/3/dataTables.bootstrap.js"></script>
	<script src="//cdn.datatables.net/plug-ins/3cfcc339e89/features/searchHighlight/dataTables.searchHighlight.min.js"></script>
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
	
	function quickstats()
	{
		if ($_GET['groupby'] != '') {	
			
			if ($_GET['groupby'] == "renderer") {
				$sqlresult = mysql_query("SELECT DISTINCT replace(trim(replace(gl_renderer, '\n', '')), '\r', '') as renderer FROM openglcaps");  	   
				$rowcount = mysql_num_rows($sqlresult); 
				echo "Listing $rowcount Devices, grouped by renderer\n";
			}
			
			if ($_GET['groupby'] == "os") {
				$sqlresult = mysql_query("SELECT * FROM openglcaps");  	   
				$rowcount = mysql_num_rows($sqlresult); 
				echo "Listing $rowcount Devices, grouped by operating system\n";
			}			
			
			if ($_GET['groupby'] == "version") {
				$sqlresult = mysql_query("SELECT * FROM openglcaps");  	   
				$rowcount = mysql_num_rows($sqlresult); 
				echo "Listing $rowcount Devices, grouped by OpenGL version\n";
			}
			
			} else {
			if ($_POST['filter'] != '') {
				$filter  = mysql_real_escape_string(strtolower($_POST['filter']));
				$like = ' where GL_RENDERER like "%'.$filter.'%" ';
				$sqlresult = mysql_query("SELECT description FROM openglcaps $like");  	   
				$rowcount = mysql_num_rows($sqlresult); 
				echo "Listing $rowcount Reports (Renderer like $filter)\n";
				} else {
				$sqlresult = mysql_query("SELECT description FROM openglcaps");  	   
				$rowcount = mysql_num_rows($sqlresult); 
				echo "Listing $rowcount Reports\n";
			}
		}
	}	 
?>


<div id="content">
	
	<!--
		<table border=0>
		<tr><td class="firstrow">
		<form method="get" action="" style="margin-bottom:0px;">
		Filter renderer (like) : 
		<input type="text" name="searchstring" size="40" value="<?=$_GET['searchstring']?>">      
		<input type="hidden" name="sortby" value="<?=$_GET['sortby']?>" />				
		<input type="submit" value="filter">
		</form> 
		</tr></td>			
		</table>		
	-->
	
	<form method="get" action="gl_comparereports.php?compare" style="margin-bottom:0px;">
		
		<table  border="0" id="reports" class="table table-striped table-bordered" cellspacing="0" width="100%">
			<?php					
								
				// Submitter
				if($_GET['submitter'] != '') {
					$submitter = mysql_real_escape_string(strtolower($_GET['submitter']));
					echo "<caption class='tableheader'>Reports submitted by <b>$submitter</b></class>";	 
				}	
				
				$groupby = $_GET['groupby'];
				
				$sortorder = "ORDER BY reportid desc";
				$vendorheader = false;
				$negate = false;
				$caption = "";
				
				$colspan = 7;
				
				// Like filter
				$like = '';
				$andlike = '';
				if(isset($_GET['searchstring'])) {	 
					$filter  = mysql_real_escape_string(strtolower($_GET['searchstring']));
					$like = ' where GL_RENDERER like "%'.$filter.'%" ';
					$andlike = ' and GL_RENDERER like "%'.$filter.'%" ';
				}
				
				// Searching via in-page form
				$searchstring = '';
				//if(isset($_POST['submit'])) {	 
				$searchstring  = mysql_real_escape_string(strtolower($_POST['searchstring']));
				//} 
				
				// External search (e.g. via statistics page)
				if($_GET['listreportsbyextension'] != '') {
					$searchstring  = mysql_real_escape_string(strtolower($_GET['listreportsbyextension']));
				}
				
				if($_GET['listreportsbyextensionunsupported'] != '') {
					$searchstring  = mysql_real_escape_string(strtolower($_GET['listreportsbyextensionunsupported']));
					$negate = true;
				}
				
				if ($searchstring != '')	 
				{
					echo "<caption class='tableheader'>";
					if ($negate == false) {
						echo "Displaying all reports supporting <b>".strtoupper($searchstring)." </b>";	   	  
					}
					if ($negate == true) {
						echo "Displaying all reports not supporting <b>".strtoupper($searchstring)." </b>";	 
					}
					echo "</caption>";		  
				}	 
				
				
				if (($searchstring == '') and ($submitter == '')) {   
					echo "<caption class='tableheader'><b>";				
					quickstats();  
					echo "</b></class>";		  
				} 
				
				
				if ($groupby != '') {
					// Group reports by renderer, os, etc.
					echo "<thead>";
					echo "<tr>";
					echo "	<td class='caption'>Renderer</td>";
					echo "	<td class='caption'>GL</td>";
					echo "	<td class='caption'>SL</td>";
					echo "	<td class='caption'>OS</td>";
					echo "	<td class='caption'>Date</td>";
					echo "	<td align=center><input type='submit' name='compare' value='compare'></td>";
					echo "</tr>";
					echo "</thead><tbody>"; 
					
					if ($groupby == 'renderer') {
						
						$sqlresult = mysql_query("SELECT DISTINCT replace(trim(replace(gl_renderer, '\n', '')), '\r', '') as renderer FROM openglcaps order by renderer"); 
						
						while($row = mysql_fetch_object($sqlresult)) {
							echo "<tr><td id='tableheader' style='padding-top:20px; font-size: 12px; padding-left:10px;' colspan = 6><b>$row->renderer</b></td></tr>";			
							
							$sqlsubresult = mysql_query("SELECT *, date(submissiondate) as reportdate from openglcaps WHERE replace(trim(replace(gl_renderer, '\n', '')), '\r', '') = '$row->renderer' ORDER BY GL_VERSION desc");
							
							$index = 0;
							while($subrow = mysql_fetch_object($sqlsubresult)) {
								echo "<tr>";
								$bgcolor  = $index % 2 != 0 ? $bgcolordef : $bgcolorodd;   			
								$index++;
								
								$versionreplace = array("Compatibility Profile Context");
								$version = str_replace($versionreplace, "", trim($subrow->GL_VERSION));
								
								echo "<td class='valuezeroleft' style='background-color:".$bgcolor."'><a href='gl_generatereport.php?reportID=$subrow->ReportID'>$subrow->GL_VERSION</a></td>";
								
								preg_match("|[0-9]+(?:\.[0-9]*)?|", $subrow->GL_VERSION, $versionint);	 
								echo "<td class='valuezeroleft' style='background-color:".$bgcolor."'>".$versionint[0]."</td>\n";
								
								preg_match("|[0-9]+(?:\.[0-9]*)?|", $subrow->GL_SHADING_LANGUAGE_VERSION, $glslsversionint);	 
								if ($glslsversionint[0] == '') {
									echo "<td class='valuezeroleft' style='background-color:".$bgcolor."'>-</td>\n";				
									} else {
									echo "<td class='valuezeroleft' style='background-color:".$bgcolor."'>".$glslsversionint[0]."</td>\n";				
								}
								
								echo "<td class='valuezeroleft' style='background-color:".$bgcolor."'>$subrow->os</td>";
								echo "<td class='valuezeroleft' style='background-color:".$bgcolor."'>$subrow->reportdate</td>";
								echo "<td align='center' style='font-size: 12px; background-color:".$bgcolor."'><input type='checkbox' name='id[$subrow->ReportID]'></td>\n";				
								echo "</tr>";
							}
							
						}
					}
					
					if ($groupby == 'os') {
						
						$sqlresult = mysql_query("select distinct trim(os) as operatingsystem from openglcaps order by 1 desc"); 
						
						
						while($row = mysql_fetch_object($sqlresult)) {
							
							$os = trim($row->operatingsystem) != '' ? $row->operatingsystem : 'Unknown';
							
							//echo "<tr><td id='tableheader' style='padding-top:20px; font-size: 12px; padding-left:10px;' colspan = 6><b>$os</b></td></tr>";			
							
							$sqlsubresult = mysql_query("SELECT *, date(submissiondate) as reportdate from openglcaps WHERE trim(os) = '$row->operatingsystem' ORDER BY GL_VERSION desc");
							
							$index = 0;
							while($subrow = mysql_fetch_object($sqlsubresult)) {
								echo "<tr>";
								$bgcolor  = $index % 2 != 0 ? $bgcolordef : $bgcolorodd;   			
								$index++;
								
								$versionreplace = array("Compatibility Profile Context");
								$version = str_replace($versionreplace, "", trim($subrow->GL_VERSION));
								
								echo "<td style='padding-left:15px; padding-right:15px; font-size: 12px; background-color:".$bgcolor."'><a href='gl_generatereport.php?reportID=$subrow->ReportID'>$subrow->GL_RENDERER $version</a></td>";
								
								preg_match("|[0-9]+(?:\.[0-9]*)?|", $subrow->GL_VERSION, $versionint);	 
								echo "<td style='padding-right:15px; font-size: 12px; background-color:".$bgcolor."'>".$versionint[0]."</td>\n";
								
								preg_match("|[0-9]+(?:\.[0-9]*)?|", $subrow->GL_SHADING_LANGUAGE_VERSION, $glslsversionint);	 
								if ($glslsversionint[0] == '') {
									echo "<td style='padding-right:15px; font-size: 12px; background-color:".$bgcolor."'>-</td>\n";				
									} else {
									echo "<td style='padding-right:15px; font-size: 12px; background-color:".$bgcolor."'>".$glslsversionint[0]."</td>\n";				
								}
								
								echo "<td style='font-size: 12px; background-color:".$bgcolor."'>$subrow->operatingsystem</td>";
								echo "<td style='font-size: 12px; background-color:".$bgcolor."'>$subrow->reportdate</td>";
								echo "<td align='center' style='font-size: 12px; background-color:".$bgcolor."'><input type='checkbox' name='id[$subrow->ReportID]'></td>\n";				
								echo "</tr>";
							}
							
						}
					}
					
					if ($groupby == 'version') {
						
						$sqlresult = mysql_query("select distinct left(trim(GL_VERSION),3) as GL_VERSION from openglcaps order by 1 desc"); 
						
						
						while($row = mysql_fetch_object($sqlresult)) {
							
							$sqlsubresult = mysql_query("SELECT *, date(submissiondate) as reportdate from openglcaps WHERE left(trim(GL_VERSION),3) = '$row->GL_VERSION' ORDER BY GL_VERSION desc");
							$rowcount = mysql_num_rows($sqlsubresult); 
							
							echo "<tr><td id='tableheader' style='padding-top:20px; font-size: 12px; padding-left:10px;' colspan = 6><b>OpenGL $row->GL_VERSION ($rowcount reports)</b></td></tr>";			
							
							$index = 0;
							while($subrow = mysql_fetch_object($sqlsubresult)) {
								echo "<tr>";
								$bgcolor  = $index % 2 != 0 ? $bgcolordef : $bgcolorodd;   			
								$index++;
								
								echo "<td style='padding-left:15px; padding-right:15px; font-size: 12px; background-color:".$bgcolor."'><a href='gl_generatereport.php?reportID=$subrow->ReportID'>$subrow->GL_RENDERER $subrow->GL_VERSION</a></td>";
								
								preg_match("|[0-9]+(?:\.[0-9]*)?|", $subrow->GL_VERSION, $versionint);	 
								echo "<td style='padding-right:15px; font-size: 12px; background-color:".$bgcolor."'>".$versionint[0]."</td>\n";
								
								preg_match("|[0-9]+(?:\.[0-9]*)?|", $subrow->GL_SHADING_LANGUAGE_VERSION, $glslsversionint);	 
								if ($glslsversionint[0] == '') {
									echo "<td style='padding-right:15px; font-size: 12px; background-color:".$bgcolor."'>-</td>\n";				
									} else {
									echo "<td style='padding-right:15px; font-size: 12px; background-color:".$bgcolor."'>".$glslsversionint[0]."</td>\n";				
								}
								
								echo "<td style='font-size: 12px; background-color:".$bgcolor."'>$subrow->operatingsystem</td>";
								echo "<td style='font-size: 12px; background-color:".$bgcolor."'>$subrow->reportdate</td>";
								echo "<td align='center' style='font-size: 12px; background-color:".$bgcolor."'><input type='checkbox' name='id[$subrow->ReportID]'></td>\n";				
								echo "</tr>";
							}
							
						}
					}
					
					
					} else {   
					// Normal listing without vendor specific headers
					echo "<thead><tr>";
					echo "<td class='caption'>Renderer</td>";
					#echo "<a href='".$_SERVER['PHP_SELF']."?sortby=description_asc'><img src='sort_asc.png' title='Sort ascending' width='12px'></a>";
					#echo "<a href='".$_SERVER['PHP_SELF']."?sortby=description_desc'><img src='sort_desc.png' title='Sort descending' width='12px'></a></td>";
					echo "<td class='caption'>Version</td>";
					#echo "<a href='".$_SERVER['PHP_SELF']."?sortby=version_asc'><img src='sort_asc.png' title='Sort ascending' width='12px'></a>";
					#echo "<a href='".$_SERVER['PHP_SELF']."?sortby=version_desc'><img src='sort_desc.png' title='Sort descending' width='12px'></a></b></td>";
					echo "<td class='caption'>GLSL</td>";
					echo "<td class='caption'>Context</td>";
					echo "<td class='caption'>OS</td>";
					echo "<td class='caption'>Date</td>";
					#echo "<a href='".$_SERVER['PHP_SELF']."?sortby=date_asc'><img src='sort_asc.png' title='Sort ascending' width='12px'></a>";
					#echo "<a href='".$_SERVER['PHP_SELF']."?sortby=date_desc'><img src='sort_desc.png' title='Sort descending' width='12px'></a></b></td>";
					echo "<td class='caption' align=center><input type='submit' name='compare' value='compare'></td>";
					echo "</tr></thead><tbody>"; 
					
					$str = "SELECT *, date(submissiondate) as reportdate, contextTypeName(contexttype) as ctxType  FROM openglcaps $like $sortorder";	  	   
					
					if ($submitter != '') {
						$str = "SELECT *, date(submissiondate) as reportdate, contextTypeName(contexttype) as ctxType FROM openglcaps where submitter = '$submitter' $andlike order by ReportID desc";	  	   
					}
					
					$sqlresult = mysql_query($str); 
					
					$currentvendor  = ""; 
					
					$index       = 0;
					
					while($row = mysql_fetch_object($sqlresult))
					{
						$description = trim($row->description);		
						$reportid    = trim($row->ReportID);	 
						$vendor	  = trim($row->GL_VENDOR);
						$renderer	  = trim($row->GL_RENDERER);
						$submissiondate = trim($row->reportdate);
						$os          = trim($row->os);
						$ctxtype = trim($row->ctxType);
						
						
						// Remove certain unnecessary strings from version info (e.g. "compatibility context for ATI"
						$versionreplace = array("Compatibility Profile Context");
						$version = str_replace($versionreplace, "", trim($row->GL_VERSION));
						$glslsversion = trim($row->GL_SHADING_LANGUAGE_VERSION); 
						
						if ($searchstring != '')
						{	 
							$str = "SELECT Name FROM openglgpuandext LEFT JOIN openglextensions ON openglextensions.PK = openglgpuandext.ExtensionID WHERE openglgpuandext.ReportID = $reportid";			
							
							$sqlsubresult = mysql_query($str);	 
							$subarray = array();
							
							while($subrow = mysql_fetch_row($sqlsubresult))
							{	
								foreach ($subrow as $data)
								{
									$subarray[] = strtolower($data);	  
								}
							}	 		  
							
							if ($negate == true) {
								if (in_array($searchstring, $subarray)) { continue; } 
							}
							
							if ($negate == false) {
								if (!in_array($searchstring, $subarray)) { continue; } 
							} 
						}
						
						$logo = 'logo_unknown.png';
						
						if (strpos($description, 'ATI') !== false) {
						$logo = 'logo_ati.png'; }
						
						elseif (strpos($description, 'NVIDIA')!== false) {
						$logo = 'logo_nvidia.png'; }         
						
						elseif (strpos($description, 'Intel') !== false) {
						$logo = 'logo_intel.png'; }         
						
						echo "<tr>";
						echo "<td class='firstrow' style='background-color:".$bgcolor."'><a href='gl_generatereport.php?reportID=$reportid'>";
						echo "$renderer</a></td>";
						
						preg_match("|[0-9]+(?:\.[0-9]*)?|", $version, $versionint);	 
						//echo "<td class='valuezeroleft' style='background-color:".$bgcolor."'>".$versionint[0]."</td>\n";
						echo "<td class='valuezeroleft'>".$version."</td>";	 
						
						preg_match("|[0-9]+(?:\.[0-9]*)?|", $glslsversion, $glslsversionint);	 
						echo "<td class='valuezeroleft'>".$glslsversionint[0]."</td>";
						
						echo "<td class='valuezeroleft'>".$ctxtype."</td>";		 		 
						
						echo "<td class='valuezeroleft'>$os</td>";
						echo "<td class='valuezeroleft'>$submissiondate</td>";	 
						echo "<td align='center' style='font-size: 12px;'><input type='checkbox' name='id[$reportid]'></td>";
						echo "</tr>";
						$index++;
						
					}
				}
				
				dbDisconnect();  
			?>   
		</tbody>
	</table>
	
	
</form>   

<script>
	$(document).ready(function() {
		$('#reports').DataTable({
			"order": [[ 5, "desc" ]],
			"pageLength" : 50,
			"searchHighlight": true,
			"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ]
		});
	} );	
</script>	 

<?php include("./gl_footer.inc");	?>
</div>

</body>
</html>