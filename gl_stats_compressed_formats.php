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
		echo "<h4 style='margin-left:10px;'>Listing all compressed texture formats ($sqlCount)</h4>";
	echo "</div>";	    
?>

<center>
<div id="reportdiv">
	
	<table id="formats" class="table table-striped table-bordered table-hover reporttable">
</span></i></caption>
		<thead>
			<tr>
				<td class="caption">Compressed texture format</td>
				<td class="caption">Coverage</td>
			</tr>
		</thead>
		
		<?php		
			$sqlresult = mysql_query("select * from viewCompressedFormats") or die(mysql_error());  			
			while($row = mysql_fetch_row($sqlresult))
			{
				echo "<tr>";
				echo "	<td class='firstrow'><a href='gl_listreports.php?compressedtextureformat=$row[0]'>$row[0]</a></td>";
				echo "  <td class='firstrow' align=center>".round(($row[1]), 2)."%</td>";
				echo "</tr>";
			}
			
			dbDisconnect();	
		?>   
	</tbody>
</table>  

<script>
	$(document).ready(function() {
		$('#formats').DataTable({
			"pageLength" : -1,
			"stateSave": true, 
			"searchHighlight" : true,		
			"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ]
		});
	} );	
</script>
<?php include("./gl_footer.inc");	?>
</div>
</center>
</body>
</html>