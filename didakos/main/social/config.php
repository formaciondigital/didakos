<?php // $Id: template.php,v 1.2 2006/03/15 14:34:45 pcool Exp $
//session_start ();
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

Este archivo es para gestionar la confirguración del panel social de la plataforma.

==============================================================================
		INIT SECTION
==============================================================================
*/ 
// global settings initialisation 
// also provides access to main, database and display API libraries
$language_file = "redes_sociales";
include("../inc/global.inc.php"); 
$libpath = api_get_path(LIBRARY_PATH);

if (!isset($_SESSION["_cid"]) || !isset($_SESSION["_user"]["firstName"]))
{
header('Location: ../../index.php?loginFailed=1');
}
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

$tool_name = get_lang('ConfigurarPanel'); // title of the page (should come from the language file) 
Display::display_header($tool_name);

	
/*
==============================================================================
		FUNCTIONS
==============================================================================
*/ 

// put your functions here
// if the list gets large, divide them into different sections:
// display functions, tool logic functions, database functions	
// try to place your functions into an API library or separate functions file - it helps reuse
	
/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

// Put your main code here. Keep this section short,
// it's better to use functions for any non-trivial code

api_display_tool_title($tool_name);
session_start ();

if (api_is_allowed_to_edit())
{
echo get_lang("BienvenidaConfig") . "<br><br>";

// Tabla para guardar la configuracion
/*
CREATE TABLE `social_config` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `elementos` int(10) unsigned NOT NULL,
  `eventos` int(10) unsigned NOT NULL,
  `orden` varchar(4) DEFAULT NULL,
  `social` int(10) unsigned NOT NULL,
  `otros` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `visibility` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=latin1
*/

$config= Database::get_course_table(TABLE_SOCIAL_CONFIG);

if ( isset($_POST['social']))
{
	// recogemos los valores de los checkbox y componemos la cadena que vamos a guardar 1=activo 0 inactivo.
	$visibility = '';
	if ( in_array('Anuncio', $_POST['visibility']) ) {$visibility='1';} else {$visibility='0';}
	if ( in_array('Blog', $_POST['visibility']) ) {$visibility= $visibility .',1';} else {$visibility=$visibility . ',0';}
	if ( in_array('ComentarioBlog', $_POST['visibility']) ) {$visibility= $visibility .',1';} else {$visibility=$visibility . ',0';}
	if ( in_array('Agenda', $_POST['visibility']) ) {$visibility= $visibility .',1';} else {$visibility=$visibility . ',0';}
	if ( in_array('Nota', $_POST['visibility']) ) {$visibility= $visibility .',1';} else {$visibility=$visibility . ',0';}
	if ( in_array('Curso', $_POST['visibility']) ) {$visibility= $visibility .',1';} else {$visibility=$visibility . ',0';}
	if ( in_array('Chat', $_POST['visibility']) ) {$visibility= $visibility .',1';} else {$visibility=$visibility . ',0';}
	if ( in_array('Dmail', $_POST['visibility']) ) {$visibility= $visibility .',1';} else {$visibility=$visibility . ',0';}
	if ( in_array('Foro', $_POST['visibility']) ) {$visibility= $visibility .',1';} else {$visibility=$visibility . ',0';}
	if ( in_array('Multimedia', $_POST['visibility']) ) {$visibility= $visibility .',1';} else {$visibility=$visibility . ',0';}
	if ( in_array('ComentarioMultimedia', $_POST['visibility']) ) {$visibility= $visibility .',1';} else {$visibility=$visibility . ',0';}
	if ( in_array('Enlace', $_POST['visibility']) ) {$visibility= $visibility .',1';} else {$visibility=$visibility . ',0';}

	//Hemos insertado datos, borramos datos previos y guardamos los nuevos
	$sql = "delete from  $config";
	$res = api_sql_query($sql,__FILE__,__LINE__);
	$sql = "insert into  $config (elementos,eventos,orden,social,notificaciones,visibility) values (".$_POST['elementos']. "," . 
	$_POST['eventos'] . ",'" . $_POST['orden'] . "'," . $_POST['social'] . "," . $_POST['notificaciones']. ",'" . $visibility . "')";

	$res = api_sql_query($sql,__FILE__,__LINE__);
	if ($res==1)
	{
		Display::display_normal_message(stripslashes(get_lang("GuardadoOk")));
	}
	else
	{
		//Meter error
	}

}
else
{
	//Buscamos si hay configuración
	$sql = "Select * from  $config";
	$res = api_sql_query($sql,__FILE__,__LINE__);
	$post = Database::fetch_array($res);

	if ($post['social']<>"")
	{
		//hay, cargamos sus valores
		$elementos = $post['elementos'];
		$eventos = $post['eventos'];
		$orden = $post['orden'];
		$social = $post['social'];
		$notificaciones = $post['notificaciones'];
		//descomponemos la cadena en un array y cargamos valores
		$visibility = preg_split("/,/",$post['visibility']);
		$anuncio = $visibility[0];
		$blog = $visibility[1];
		$comentario_blog = $visibility[2];
		$agenda = $visibility[3];
		$nota = $visibility[4];
		$curso = $visibility[5];
		$chat = $visibility[6];
		$dmail = $visibility[7];
		$foro = $visibility[8];
		$multimedia = $visibility[9];
		$comentario_multimedia = $visibility[10];
		$link = $visibility[11];
	}
	else
	{
		//No hay, metemos valores básicos
		$elementos = "4";
		$eventos = "30";
		$orden = "DESC";
		$social = "1";
		$notificaciones = "1";
		$anuncio = 1;		
		$blog = 1;
		$comentario_blog = 1;
		$agenda = 1;
		$nota = 1;
		$curso = 1;
		$chat = 1;
		$dmail = 1;
		$foro = 1;
		$multimedia = 1;
		$comentario_multimedia = 1;
		$link = 1;
	}
	//Mostramos el formulario
?>

<form name="configuracion" action="config.php" METHOD="POST">
	<table>
		<tr>
			<td width="30"></td>
			<td valign="top" width="300"><b><?php echo get_lang("MaximoTipos"); ?></b></td>
			<td valign="top" width="175"><input type="text" value="<?php echo $elementos?>" name="elementos" MAXLENGTH="1" size="2"></td>
			<td valign="top"><i><?php echo get_lang("ValorRecomendado"); ?> 4, <?php echo get_lang("Maximo"); ?> 9</i></td>
		</tr>
		<tr>
			<td width="30"></td>
			<td valign="top"><b><?php echo get_lang("MaximoElementos"); ?></b></td>
			<td valign="top"><input type="text" value="<?php echo $eventos?>" name="eventos" MAXLENGTH="2" size="2"></td>
			<td valign="top"><i><?php echo get_lang("ValorRecomendado"); ?> 30, <?php echo get_lang("Maximo"); ?> 99</i></td>
		</tr>
		<tr>
			<td width="30"></td>
			<td valign="top"><b><?php echo get_lang("OrdenFechas"); ?></b></td>
			<td valign="top"> 
				<?php echo get_lang("Ascendente"); ?><input type="radio" name="orden" value="ASC" <?php if ($orden=='ASC'){echo 'checked';}?>/><br>
				<?php echo get_lang("Descendente"); ?><input type="radio" name="orden" value="DESC" <?php if ($orden=='DESC'){echo 'checked';}?> /><br>
				<?php echo get_lang("Agrupar"); ?><input type="radio" name="orden" value="GROUP" <?php if ($orden=='GROUP'){echo 'checked';}?>/></td>
			<td valign="top"><i><?php echo get_lang("ValorRecomendado"); ?>&nbsp; <?php echo get_lang("Descendente"); ?></i></td>
		</tr>
		<tr>
			<td width="30"></td>
			<td valign="top"><b><?php echo get_lang("IncluirRedes"); ?></b></td>
			<td valign="top"> 
				<?php echo get_lang("Si"); ?><input type="radio" name="social" value="1" <?php if ($social=='1'){echo 'checked';}?>/><br>
				<?php echo get_lang("No"); ?><input type="radio" name="social" value="0" <?php if ($social=='0'){echo 'checked';}?>/></td>
			<td valign="top"><i><?php echo get_lang("ValorRecomendado"); ?> &nbsp;<?php echo get_lang("Si"); ?></i></td>
		</tr>
		<tr>
			<td width="30"></td>
			<td valign="top"><b><?php echo get_lang("MostrarHerramientas"); ?></b></td>
			<td valign="top"> 
				<?php echo get_lang("Anuncio"); ?><input type="checkbox" name="visibility[]" value="Anuncio"  <?php if ($anuncio=='1'){echo 'checked';}?>/><br>
				<?php echo get_lang("Blog"); ?><input type="checkbox" name="visibility[]" value="Blog" <?php if ($blog=='1'){echo 'checked';}?>/><br>
				<?php echo get_lang("ComentarioBlog"); ?><input type="checkbox" name="visibility[]" value="ComentarioBlog" <?php if ($comentario_blog=='1'){echo 'checked';}?>/><br>
				<?php echo get_lang("Agenda"); ?><input type="checkbox" name="visibility[]" value="Agenda" <?php if ($agenda=='1'){echo 'checked';}?>/><br>
				<?php echo get_lang("Nota"); ?><input type="checkbox" name="visibility[]" value="Nota" <?php if ($nota=='1'){echo 'checked';}?>/><br>
				<?php echo get_lang("Curso"); ?><input type="checkbox" name="visibility[]" value="Curso" <?php if ($curso=='1'){echo 'checked';}?>/><br>
				<?php echo get_lang("Chat"); ?><input type="checkbox" name="visibility[]" value="Chat" <?php if ($chat=='1'){echo 'checked';}?>/><br>
				<?php echo get_lang("Dmail"); ?><input type="checkbox" name="visibility[]" value="Dmail" <?php if ($dmail=='1'){echo 'checked';}?>/><br>
				<?php echo get_lang("Foro"); ?><input type="checkbox" name="visibility[]" value="Foro" <?php if($foro=='1'){echo 'checked';}?>/><br>
				<?php echo get_lang("Multimedia"); ?><input type="checkbox" name="visibility[]" value="Multimedia" <?php if($multimedia=='1'){echo 'checked';}?>/><br>
				<?php echo get_lang("MultimediaComentario"); ?><input type="checkbox" name="visibility[]" value="ComentarioMultimedia" <?php if($comentario_multimedia=='1'){echo 'checked';}?>/><br>
				<?php echo get_lang("Link"); ?><input type="checkbox" name="visibility[]" value="Enlace" <?php if($link=='1'){echo 'checked';}?>/><br>
			<td valign="top"><i><?php echo get_lang("ValorRecomendado"); ?> Todos activos</i></td>
		</tr>
		<tr>
			<td width="30"></td>
			<td valign="top"><b><?php echo get_lang("IncluirNotificaciones"); ?></b></td>
			<td valign="top"> 
				<?php echo get_lang("Si"); ?><input type="radio" name="notificaciones" value="1" <?php if ($notificaciones=='1'){echo 'checked';}?>/><br>
				<?php echo get_lang("No"); ?><input type="radio" name="notificaciones" value="0" <?php if ($notificaciones=='0'){echo 'checked';}?>/></td>
			<td valign="top"><i><?php echo get_lang("ValorRecomendado"); ?> &nbsp;<?php echo get_lang("Si"); ?></i></td>
		</tr>
		<tr>
			<td colspan="4" align="right"><input type="submit" value="<?php echo get_lang("Guardar"); ?>" /></td>
		</tr>
	</table>
</form>

<?php
}
}
else
{
	echo "<p>" . get_lang("AccesoDenegado") . "</p>";
}
/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>
