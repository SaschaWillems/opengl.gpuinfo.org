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

	$sqlResult = mysql_query("SELECT count(*) FROM openglcaps");
	$sqlCount = mysql_result($sqlResult, 0);
	echo "<div class='header'>";
		echo "<h4 style='margin-left:10px;'>Listing all available reports ($sqlCount)</h4>";
	echo "</div>";				
?>
<center>
	<div class="reportdiv">

	<form method="get" action="gl_comparereports.php?compare" style="margin-bottom:0px;">

		<table id="reports" class="table table-striped table-bordered table-hover reporttable">
			<?php

				// Submitter
				if($_GET['submitter'] != '') {
					$submitter = mysql_real_escape_string(strtolower($_GET['submitter']));
					echo "<caption class='tableheader'>Reports submitted by <b>$submitter</b></class>";
				}

				$negate = false;
				$caption = "";

				$colspan = 7;
				$searchType = '';

				// External search (e.g. via statistics page)
				if($_GET['listreportsbyextension'] != '') {
					$searchstring  = mysql_real_escape_string(strtolower($_GET['listreportsbyextension']));
					$searchType = 'extension';
				}

				if($_GET['listreportsbyextensionunsupported'] != '') {
					$searchstring  = mysql_real_escape_string(strtolower($_GET['listreportsbyextensionunsupported']));
					$negate = true;
					$searchType = 'extension';
				}

				if($_GET['compressedtextureformat'] != '') {
					$searchstring  = mysql_real_escape_string(strtolower($_GET['compressedtextureformat']));
					$searchType = 'compressedformat';
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
					// echo "<caption class='tableheader'><b>";
					// quickstats();
					// echo "</b></class>";
				}
				
				echo "<thead><tr>";
				echo "	<td class='caption'>Renderer</td>";
				echo "	<td class='caption'>Version</td>";
				echo "	<td class='caption'>GL</td>";
				echo "	<td class='caption'>GLSL</td>";
				echo "	<td class='caption'>Context</td>";
				echo "	<td class='caption'>OS</td>";
				echo "	<td class='caption'>Date</td>";
				echo "	<td class='caption' align=center><input type='submit' name='compare' value='compare'></td>";
				echo "</tr>";
				echo "</thead><tbody>";

				$str = "SELECT *, date(submissiondate) as reportdate, contextTypeName(contexttype) as ctxType FROM openglcaps ORDER BY reportid desc";

				if ($submitter != '') {
					$str = "SELECT *, date(submissiondate) as reportdate, contextTypeName(contexttype) as ctxType FROM openglcaps where submitter = '$submitter' order by ReportID desc";
				}

				// Search by compressed texture format support
				if ( ($searchType == 'compressedformat')  && ($searchstring != '') ) {
					echo "<b>compressedtextureformat</b>";
					$str  = "SELECT *, date(submissiondate) as reportdate, contextTypeName(contexttype) as ctxType FROM openglcaps where";
					$str .= " reportid in (select reportid from compressedTextureFormats where formatEnum =";
					$str .= " (select enum from enumTranslationTable where text = '$searchstring')) ORDER BY reportid desc";
				}

				$sqlresult = mysql_query($str);

				while($row = mysql_fetch_object($sqlresult))
				{
					$description = trim($row->description);
					$reportid    = trim($row->ReportID);
					$vendor	  = trim($row->GL_VENDOR);
					$renderer	  = trim($row->GL_RENDERER);
					$submissiondate = "<nobr>".trim($row->reportdate)."</nobr>";
					$ctxtype = trim($row->ctxType);
					
					// Clean up OS name (for all the linux distros out there)
					$os = $row->os;
					$os = trim($row->os);
					if (strpos($os, "Linux") !== false) 
					{
						$pos = strpos($os, '-');
						$os = substr($os, 0, $pos);
					}			
					
					// Remove certain unnecessary strings from version info (e.g. "compatibility context for ATI")
					$versionreplace = array("Compatibility Profile Context", "Core Profile Forward-Compatible Context", "OpenGL ES");
					$version = str_replace($versionreplace, "", trim($row->GL_VERSION));
					$glslsversion = trim($row->GL_SHADING_LANGUAGE_VERSION);

					// Search by extension support
					if ( ($searchstring != '') && ($searchType == 'extension') ) {
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

					// Extract version numbers
					preg_match("|[0-9]+(?:\.[0-9]*)?|", $version, $versionint);
					preg_match("|[0-9]+(?:\.[0-9]*)?|", $glslsversion, $glslsversionint);

					echo "<tr>";
					echo "	<td class='firstrow'><a href='gl_generatereport.php?reportID=$reportid'>$renderer</a></td>";
					echo "	<td class='valuezeroleft'>".$version."</td>";
					echo "	<td class='valuezeroleft''>".$versionint[0]."</td>\n";
					echo "	<td class='valuezeroleft'>".$glslsversionint[0]."</td>";
					echo "	<td class='valuezeroleft'>".$ctxtype."</td>";
					echo "	<td class='valuezeroleft'>$os</td>";
					echo "	<td class='valuezeroleft'>$submissiondate</td>";
					echo "	<td align='center'><input type='checkbox' name='id[$reportid]'></td>";
					echo "</tr>";

				}

				dbDisconnect();
			?>
		</tbody>
		</table>


	</form>

	<script>
		$(document).ready(function() {
			$('#reports').DataTable({
				"order": [[ 6, "desc" ]],
				"deferRender": true,
				"pageLength" : 50,
				"stateSave": false,
				"searchHighlight": true,
				"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
				"orderCellsTop": true,

				initComplete: function () {
					var api = this.api();

					api.columns().indexes().flatten().each( function ( i ) {
						if ((i>1) && (i<6)) {						
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
		
	  function stopPropagation(evt) {
			if (evt.stopPropagation !== undefined) {
				evt.stopPropagation();
			} else {
				evt.cancelBubble = true;
			}
		}		
	</script>

	<?php include("./gl_footer.inc");	?>
</div>
</center>

</body>
</html>
