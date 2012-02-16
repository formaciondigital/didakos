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
include_once(api_get_path(LIBRARY_PATH).'database.lib.php');

$this_section=SECTION_COURSES;
$dbl_click_id = 0; // used to avoid double-click
$is_allowed_to_edit = api_is_allowed_to_edit();
$curdirpathurl = 'vlab';

$t_peticiones = Database::get_main_table(TABLE_MAIN_VLAB_PETICIONES);

$htmlHeadXtra[] = '';


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




//a partir del id de practica y del curso en el que estamos obtenemos la maquina y el timpo que tenemos que arrancarla
$id_curso = $_SESSION[_cid];
$id_practica = $_GET['uid'];

$datos_practica = GetDatosPractica($id_practica,$id_curso);

if(count($datos_practica) > 0){
}else{
die(get_lang('langWrongPractice'));
}

$maquina = $datos_practica[0]["id_maquina"];
$tiempo = $datos_practica[0]["tiempo"];



	
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


$email = $_SESSION['_user']['mail'];
if ($email == ""){
die(get_lang('langNoEmail'));
}







?>
<div style="width:194px; float: left"><img src="img/vlab.jpg" width="194" height="142" border="0"></div>
<div style="  margin-left: 220px;  padding:10px; ">
		<?php 
		
		         //ANTES DE NADA HAY QUE COMPROBA QUE ESE USUARIO TENGA YA UNA MAQUINA ABIERTA
				$peticionesAbiertas = GetPeticionesAbiertas();
				if (count($peticionesAbiertas) > 0 )
				{
				Display::display_warning_message(get_lang('langMachineAlready'));
				$id_peticion = $peticionesAbiertas[0];
				}else{
				
					//insertamos la peticion en la base de datos par amantener un control de las mismas
					$sql = "insert into $t_peticiones (user_id,code,maquina_id,minutos,status,fecha_peticion,id_practica) values (".$_SESSION[_user][user_id].",'".$_SESSION[_cid]."','".$maquina."',".$tiempo.",null,".time().",".$id_practica.")";
					api_sql_query($sql);
					
					//la secuencia de esta tabla nos dara el id de peticion que usaremos para la creacion de las maquinas
					$id_peticion = Database::insert_id();
					//echo  $id_peticion;
	
	
	
					//peticion de maquina que vamos a realizar
					$url = $_configuration['url_vlab_api']."new/".$id_peticion."/".$tiempo."/".$maquina."/".$email."/";
					
					//echo $url;
					//die();
					$peticion = realizaPeticion($url);
					
					if ($peticion["status"] == "OK"){
							  Display::display_normal_message(get_lang('langLaunchSuccess1').' '.$email.' '.get_lang('langLaunchSuccess2'));
							
		
					}else{
					//print_r($peticion);
					  Display::display_warning_message(get_lang('langLaunchError')." ./n  ERROR:".$peticion["reason"]);
					  $sql = "update $t_peticiones set status = 'error' where id_peticion = '".$id_peticion."'";
					  api_sql_query($sql);
					}
				}
				
		?>
</div>
<div align="center">
<a href="index.php">[<?php echo get_lang('Back');?>]</a>
<br />
<?php// echo get_lang('langDownloadGuide');?> 
<br /><br />
    
  <!-- <a href="pdf/TUTORIAL ACCESO LABORATORIO VIRTUAL linux.pdf" target="_blank"><img src="img/linux.jpg" border="0" /></a>
  <a href="pdf/TUTORIAL ACCESO LABORATORIO VIRTUAL win.pdf" target="_blank"><img src="img/windows.jpg"  border="0" /></a>
  <a href="pdf/TUTORIAL ACCESO LABORATORIO VIRTUAL linux.pdf" target="_blank"><img src="img/mac.jpg" border="0" /></a> -->
</div>
<?php
/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>
