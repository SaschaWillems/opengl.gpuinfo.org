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

 <table border="0">
  <TBODY>    
  	<tr><td id='tableheader' colspan=4><b>OpenGL version coverage</b></td></tr>
   
<?php
  // Get total report count for several calculations (e.g. percentage)
  $str             = "SELECT * FROM openglcaps";	  	   
  $sqlresult       = mysql_query($str) or die(mysql_error());  
  $totalnumreports = 0;
  while($row = mysql_fetch_object($sqlresult)) {
   $totalnumreports++; }
   
  // Stats for supported OpenGL versions

  // GL 1.x
  $gl_version_1_x   = 0;
  $gl_version_1_0   = 0;
  $gl_version_1_1   = 0;
  $gl_version_1_2   = 0;
  $gl_version_1_2_1 = 0;
  $gl_version_1_3   = 0;
  $gl_version_1_4   = 0;
  $gl_version_1_5   = 0;
  
  // GL 2.x
  $gl_version_2_x   = 0;
  $gl_version_2_0   = 0;
  $gl_version_2_1   = 0;

  // GL 3.x
  $gl_version_3_x   = 0;
  $gl_version_3_0   = 0;
  $gl_version_3_1   = 0;
  $gl_version_3_2   = 0;
  $gl_version_3_3   = 0;

  // GL 4.x
  $gl_version_4_x   = 0;
  $gl_version_4_0   = 0;
  $gl_version_4_1   = 0;
  $gl_version_4_2   = 0;
  $gl_version_4_3   = 0;
  $gl_version_4_4   = 0;
  $gl_version_4_5   = 0;
  
  //
  $str             = "SELECT GL_VERSION FROM openglcaps";	  	   
  $sqlresult       = mysql_query($str) or die(mysql_error());  
  
  function add_to_gl_percentage($major, $minor, $minorminor)
   {
   global $gl_version_1_x;
   global $gl_version_1_0;
   global $gl_version_1_1;
   global $gl_version_1_2;
   global $gl_version_1_2_1;
   global $gl_version_1_3;
   global $gl_version_1_4;
   global $gl_version_1_5;
   
   global $gl_version_2_x;
   global $gl_version_2_0;
   global $gl_version_2_1;
   
   global $gl_version_3_x;
   global $gl_version_3_0;
   global $gl_version_3_1;
   global $gl_version_3_2;
   global $gl_version_3_3;
   
   global $gl_version_4_x;
   global $gl_version_4_0;
   global $gl_version_4_1;
   global $gl_version_4_2;
   global $gl_version_4_3;
   global $gl_version_4_4;
   global $gl_version_4_5;
   
    switch ($major)
	 {
	  case 1 : 
	   {
		$gl_version_1_x++;
		
		switch ($minor) 
		 {
		  case 0 : 
		   {
		    $gl_version_1_0++;
			break;
		   }
		  case 1 : 
		   {
		    $gl_version_1_0++;
		    $gl_version_1_1++;
			break;
		   }
		  case 2 : 
		   {
		    $gl_version_1_0++;
		    $gl_version_1_1++;
		    $gl_version_1_2++;
			
			if ($minorminor == 1) {
			 $gl_version_1_2_1++; }
			
			break;
		   }

   	      case 3 : 
		   {
		    $gl_version_1_0++;
		    $gl_version_1_1++;
		    $gl_version_1_2++;
		    $gl_version_1_2_1++;
		    $gl_version_1_3++;
			break;
		   }

   	      case 4 : 
		   {
		    $gl_version_1_0++;
		    $gl_version_1_1++;
		    $gl_version_1_2++;
		    $gl_version_1_2_1++;
		    $gl_version_1_3++;
		    $gl_version_1_4++;
			break;
		   }

          case 5 : 
		   {
		    $gl_version_1_0++;
		    $gl_version_1_1++;
		    $gl_version_1_2++;
		    $gl_version_1_2_1++;
		    $gl_version_1_3++;
		    $gl_version_1_4++;
		    $gl_version_1_5++;
			break;
		   }
		   
		   
		 }
		
		break;
	   }
	  case 2 : 
	   {
	    add_to_gl_percentage(1, 5, 0);
	    $gl_version_2_x++;
		
		switch ($minor) 
		 {
		  case 0 : 
		   {
		    $gl_version_2_0++;
			break;
		   }
		  case 1 : 
		   {
		    $gl_version_2_0++;
		    $gl_version_2_1++;
			break;
		   }		
		}
		
		break;
	   }
	  case 3 : 
	   {
	    add_to_gl_percentage(2, 1, 0);
	    $gl_version_3_x++;
		
		switch ($minor) 
		 {
		  case 0 :
		   {
     	    $gl_version_3_0++;
			break;
           }
		  case 1 : 
		   {
		    $gl_version_3_0++; 
		    $gl_version_3_1++; 
			break;
		   }	
		  case 2 : 
		   {
		    $gl_version_3_0++; 
		    $gl_version_3_1++; 
		    $gl_version_3_2++; 
			break;
		   }	
		  case 3 : 
		   {
		    $gl_version_3_0++; 
		    $gl_version_3_1++; 
		    $gl_version_3_2++; 
		    $gl_version_3_3++; 
			break;
		   }	
		 }
				
		
		break;
	   }
	  case 4 : 
	   {
	    add_to_gl_percentage(3, 3, 0);
		$gl_version_4_x++;
		
		switch ($minor) 
		 {
		  case 0 :
		   {
     	    $gl_version_4_0++;
			break;
           }
		  case 1 : 
		   {
		    $gl_version_4_0++; 
		    $gl_version_4_1++; 
			break;
		   }	
		  case 2 : 
		   {
		    $gl_version_4_0++; 
		    $gl_version_4_1++; 
		    $gl_version_4_2++; 
			break;
		   }	
		  case 3 : 
		   {
		    $gl_version_4_0++; 
		    $gl_version_4_1++; 
		    $gl_version_4_2++; 
		    $gl_version_4_3++; 
			break;
		   }	
		  case 4 : 
		   {
		    $gl_version_4_0++; 
		    $gl_version_4_1++; 
		    $gl_version_4_2++; 
		    $gl_version_4_3++; 
		    $gl_version_4_4++; 
			break;
		   }	
		  case 5 : 
		   {
		    $gl_version_4_0++; 
		    $gl_version_4_1++; 
		    $gl_version_4_2++; 
		    $gl_version_4_3++; 
		    $gl_version_4_4++; 
		    $gl_version_4_5++; 
			break;
		   }	
		 }
		
		break;
	   }
	 }
   }
  

  while($row = mysql_fetch_object($sqlresult)) 
    {
	$version =  explode('.', $row->GL_VERSION, 3);
    add_to_gl_percentage($version[0], $version[1], $version[2]);
    }  	
	 
  // OpenGL 1.x	 
	echo "<tr><td colspan=2>&nbsp;</td></tr>";
	echo "<tr id='tableheader'><td colspan=2><b>OpenGL 1</b></tr>";
	echo "<tr><td class='caption'>Version</td><td class='caption'>Coverage</td></tr>";
	echo "<tr><td class='firstrow'>1.0</td>   <td class='valuezeroleft'>".$gl_version_1_0." (".round(($gl_version_1_0/$totalnumreports*100), 2)."%)</td></tr>";
	echo "<tr><td class='firstrow'>1.1</td>   <td class='valuezeroleft'>".$gl_version_1_1." (".round(($gl_version_1_1/$totalnumreports*100), 2)."%)</td></tr>";
	echo "<tr><td class='firstrow'>1.2</td>   <td class='valuezeroleft'>".$gl_version_1_2." (".round(($gl_version_1_2/$totalnumreports*100), 2)."%)</td></tr>";
	echo "<tr><td class='firstrow'>1.2.1</td> <td class='valuezeroleft'>".$gl_version_1_2_1." (".round(($gl_version_1_2_1/$totalnumreports*100), 2)."%)</td></tr>";
	echo "<tr><td class='firstrow'>1.3</td>   <td class='valuezeroleft'>".$gl_version_1_3." (".round(($gl_version_1_3/$totalnumreports*100), 2)."%)</td></tr>";
	echo "<tr><td class='firstrow'>1.4</td>   <td class='valuezeroleft'>".$gl_version_1_4." (".round(($gl_version_1_4/$totalnumreports*100), 2)."%)</td></tr>";
	echo "<tr><td class='firstrow'>1.5</td>   <td class='valuezeroleft'>".$gl_version_1_5." (".round(($gl_version_1_5/$totalnumreports*100), 2)."%)</td></tr>";

	// OpenGL 2.x
	echo "<tr><td colspan=2>&nbsp;</td></tr>";
	echo "<tr id='tableheader'><td colspan=2><b>OpenGL 2</b></tr>";
	echo "<tr><td class='caption'>Version</td><td class='caption'>Coverage</td></tr>";
	echo "<tr><td class='firstrow'>2.0</td>   <td class='valuezeroleft' >".$gl_version_2_0." (".round(($gl_version_2_0/$totalnumreports*100), 2)."%)</td></tr>";
	echo "<tr><td class='firstrow'>2.1</td>   <td class='valuezeroleft' >".$gl_version_2_1." (".round(($gl_version_2_1/$totalnumreports*100), 2)."%)</td></tr>";

	// OpenGL 3.x
	echo "<tr><td colspan=2>&nbsp;</td></tr>";
	echo "<tr id='tableheader'><td colspan=2><b>OpenGL 3</b></tr>";
	echo "<tr><td class='caption'>Version</td><td class='caption'>Coverage</td></tr>";
	echo "<tr><td class='firstrow'>3.0</td>   <td class='valuezeroleft' >".$gl_version_3_0." (".round(($gl_version_3_0/$totalnumreports*100), 2)."%)</td></tr>";
	echo "<tr><td class='firstrow'>3.1</td>   <td class='valuezeroleft' >".$gl_version_3_1." (".round(($gl_version_3_1/$totalnumreports*100), 2)."%)</td></tr>";
	echo "<tr><td class='firstrow'>3.2</td>   <td class='valuezeroleft' >".$gl_version_3_2." (".round(($gl_version_3_2/$totalnumreports*100), 2)."%)</td></tr>";
	echo "<tr><td class='firstrow'>3.3</td>   <td class='valuezeroleft' >".$gl_version_3_3." (".round(($gl_version_3_3/$totalnumreports*100), 2)."%)</td></tr>";

	// OpenGL 4.x
	echo "<tr><td colspan=2>&nbsp;</td></tr>";
	echo "<tr id='tableheader'><td colspan=2><b>OpenGL 4</b></tr>";
	echo "<tr><td class='caption'>Version</td><td class='caption'>Coverage</td></tr>";
	echo "<tr><td class='firstrow'>4.0</td>   <td class='valuezeroleft' >".$gl_version_4_0." (".round(($gl_version_4_0/$totalnumreports*100), 2)."%)</td></tr>";
	echo "<tr><td class='firstrow'>4.1</td>   <td class='valuezeroleft' >".$gl_version_4_1." (".round(($gl_version_4_1/$totalnumreports*100), 2)."%)</td></tr>";
	echo "<tr><td class='firstrow'>4.2</td>   <td class='valuezeroleft' >".$gl_version_4_2." (".round(($gl_version_4_2/$totalnumreports*100), 2)."%)</td></tr>";
	echo "<tr><td class='firstrow'>4.3</td>   <td class='valuezeroleft' >".$gl_version_4_3." (".round(($gl_version_4_3/$totalnumreports*100), 2)."%)</td></tr>";
	echo "<tr><td class='firstrow'>4.4</td>   <td class='valuezeroleft' >".$gl_version_4_4." (".round(($gl_version_4_4/$totalnumreports*100), 2)."%)</td></tr>";
	echo "<tr><td class='firstrow'>4.5</td>   <td class='valuezeroleft' >".$gl_version_4_5." (".round(($gl_version_4_5/$totalnumreports*100), 2)."%)</td></tr>";
  
	dbDisconnect();	
?>

</tbody></table>  
  
</div>
</body>
</html>