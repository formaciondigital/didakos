<?php // $Id: template.php,v 1.2 2006/03/15 14:34:45 pcool Exp $
/*
==============================================================================
	Dokeos - elearning and course management software
	
	Copyright (c) 2004-2006 Dokeos S.A.
	Copyright (c) Sally "Example" Programmer (sally@somewhere.net)
	//add your name + the name of your organisation - if any - to this list
	
	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".
	
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	See the GNU General Public License for more details.
	
	Contact address: Dokeos, 44 rue des palais, B-1030 Brussels, Belgium
	Mail: info@dokeos.com
==============================================================================
*/
/**
==============================================================================
*	This file is a code template; 
*	copy the code and paste it in a new file to begin your own work.
*
*	@package dokeos.plugin
==============================================================================
*/
	
/*
==============================================================================
		INIT SECTION
==============================================================================
*/ 
// global settings initialisation 
// also provides access to main, database and display API libraries
$language_file[] = 'document';
$language_file[] = 'slideshow';
$language_file[] = 'vlab';
include("../inc/global.inc.php");
include("funciones.php");
$this_section=SECTION_COURSES;
$dbl_click_id = 0; // used to avoid double-click
$is_allowed_to_edit = api_is_allowed_to_edit();
$curdirpathurl = 'vlab';
/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/ 
//the main_api.lib.php, database.lib.php and display.lib.php
//libraries are included by default

	
/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/ 


$tool_name = "Vlab"; // title of the page (should come from the language file) 
Display::display_header($tool_name);
api_display_tool_title($tool_name);
$interbreadcrumb[]= array ('url'=>'', 'name'=> 'Vlab');
$dir_array=explode("/",$curdirpath);
$array_len=count($dir_array);
$is_allowed_to_edit  = api_is_allowed_to_edit();
$maquina=$_GET['uid'];


	
/*
==============================================================================
		FUNCTIONS
==============================================================================
*/ 

/**
 * Get the users to display on the current page.
 * @see SortableTable#get_table_data($from)
 */
//********************************************************************************************


/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 


?>

<div style="width:194px; float: left"><img src="img/vlab.jpg" width="194" height="142" border="0"></div>
<div style="  margin-left: 220px;  padding:10px; ">
		<?php 
		$error = "";
		    //recibimos la practica que hay que detener y comprobamos que exista una peticion abierta de esa maquina para el usuario y el curso actual
            $peticiones_abiertas = GetPeticionesAbiertasXPractica($_GET["uid"]);
            if (count($peticiones_abiertas) <= 0){
			echo get_lang('langNoMachines');
            }else{
			  //por cada peticion abierta que ese usuario tenga en esa practica,hacemos una llamada a la api para que se detenga
			  foreach($peticiones_abiertas as $indice => $valor){
			  //echo $valor;
			  $datos = realizaPeticion($_configuration['url_vlab_api']."stop/".$valor."/"); 
	
				  if($datos["status"] == "ERROR")
				  {
				     $error .= $datos["reason"]."<br>";
				  }
				 
				  //aqui actualizmos el estado actual de la maquina 
				  comprobarPeticion($valor);

			  }
			  
			  if ($error == "")
			  {
			    Display::display_normal_message(get_lang('langMachineStop'));
			  }else{
			    Display::display_warning_message(get_lang('langMachineError').$error);
			  }
			  
			}

		?>
</div>
<div align="center">
<a href="index.php">Volver</a>
</div>
<?php
/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>
