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
	<form method="get" action="gl_comparereports.php?compare" style="margin-bottom:0px;">
		
		<table  border="0" id="reports" class="table table-striped table-bordered" cellspacing="0" width="100%">
			<?php					

					echo "<thead><tr>";
					echo "<td class='caption'>Renderer</td>";
					echo "<td class='caption'>Version</td>";
					echo "<td class='caption'>GL</td>";
					echo "<td class='caption'>GLSL</td>";
					echo "<td class='caption'>Context</td>";
					echo "<td class='caption'>OS</td>";
					echo "<td class='caption'>Date</td>";
					echo "<td class='caption' align=center><input type='submit' name='compare' value='compare'></td>";
					echo "</tr></thead><tbody>"; 
					
					$str = "SELECT *, date(submissiondate) as reportdate, contextTypeName(contexttype) as ctxType FROM openglcaps $like $sortorder";	  	   
										
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
						echo "<td class='valuezeroleft' style='background-color:".$bgcolor."'>".$versionint[0]."</td>\n";
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
				
				dbDisconnect();  
			?>   
		</tbody>
	</table>
	
	
</form>   

<script>
	$(document).ready(function() {
		$('#reports').DataTable({
			"order": [[ 1, "desc" ]],
			"columnDefs": [ { "visible": false, "targets": 1 } ],			
			"pageLength" : 50,
			"searchHighlight": true,
			"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
			"drawCallback": function ( settings ) {
						var api = this.api();
						var rows = api.rows( {page:'current'} ).nodes();
						var last=null;
			 
						api.column(1, {page:'current'} ).data().each( function ( group, i ) {
							if ( last !== group ) {
								$(rows).eq( i ).before(
									'<tr class="caption"><td colspan="7">'+group+'</td></tr>'
								);
			 
								last = group;
							}
						} );
					}			
		});
	} );	
</script>	 

<?php include("./gl_footer.inc");	?>
</div>

</body>
</html>