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
    
	$sqlResult = mysql_query("select count(distinct(formatEnum)) from compressedTextureFormats") or die(mysql_error());
	$sqlCount = mysql_result($sqlResult, 0);    ;
	echo "<div class='header'>";
		echo "<h4 style='margin-left:10px;'>Maximum supported OpenGL versions per device</h4>";
	echo "</div>";	    
?>

<center>
<div id="reportdiv">
	
	<form method="get" action="gl_comparereports.php?compare" style="margin-bottom:0px;">	
		
	<table id="reports" class="table table-striped table-bordered table-hover reporttable">
			<?php
								
				echo "<caption class='tableheader'></caption>";
				echo "<thead><tr>";
				echo "	<td class='caption'>Device</td>";				
				echo "	<td class='caption'>Version</td>";				
				echo "	<td align='center'><input type='submit' name='compare' value='compare'></td>\n";
				echo "</tr></thead><tbody>"; 
				
				$str = "select * from viewDeviceMaxVersions";	  	   			
				$sqlresult = mysql_query($str) or die(mysql_error()); 				
				
				while($row = mysql_fetch_object($sqlresult))
				{
					$name = trim($row->name);
					$version = $row->maxversion;
					$reportid = trim($row->repid);	 
									
					echo "<tr>";				
					echo "	<td class='firstrow'><a href='gl_generatereport.php?reportID=$reportid'>$name</a></td>";		 
					echo "	<td class='valuezeroleftblack'>$version</td>";
					echo "	<td align='center'><input type='checkbox' name='id[$reportid]'></td>";				
					echo "</tr>";					
				}
				
				dbDisconnect();  
			?>   
		</tbody></table>
		
	</form> 
	
	<script>
		$(document).ready(function() {
			$('#reports').DataTable({
				"pageLength" : 50,
				"searchHighlight": true,
				"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
				
				initComplete: function () {
					var api = this.api();

					api.columns().indexes().flatten().each( function ( i ) {
						if (i == 1) {						
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
	</script>	
	
	<?php include("./gl_footer.inc");	?>
</div>

</body>
</html>