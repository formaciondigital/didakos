<?php // $Id: user_add.php 15105 2008-04-25 08:38:20Z elixir_inter $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004-2008 Dokeos S.A.
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) Olivier Brouckaert
	Copyright (c) Bart Mollet, Hogeschool Gent

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact: Dokeos, rue du Corbeau, 108, B-1000 Brussels, Belgium, info@dokeos.com
==============================================================================
*/

// archivo de lenguaje que hay que cargar
$language_file = array('admin','registration');
$cidReset = true;

// including necessary libraries
require ('../inc/global.inc.php');
$libpath = api_get_path(LIBRARY_PATH);
include_once ($libpath.'fileManage.lib.php');
include_once ($libpath.'fileUpload.lib.php');
require_once ($libpath.'formvalidator/FormValidator.class.php');

// section for the tabs
$this_section=SECTION_PLATFORM_ADMIN;
//$this_section = 'session_my_progress';

// PARA QUE A ESTA PAGINA SOLO TENGA ACCESO EL ADMIN
api_protect_gestor_script();
//api_block_anonymous_users();


//nombre que aparece en la barra de navegacion como la pagina actual
$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));
$tool_name = "Nombre_de_la_herramienta";





// NOMBRE DE TABLAS A IMPORTAR
$table_admin 	= Database :: get_main_table(TABLE_MAIN_ADMIN);
$table_user 	= Database :: get_main_table(TABLE_MAIN_USER);


//ESTO ES POR SI QUIERES INCLUIR CODIGO JAVASCRIP EN  EL <HEAD>
$htmlHeadXtra[] = '<script language="JavaScript" type="text/JavaScript"></script>';


//SI LA PAGINA RECIBE UN MENSAJE DE OTRA LOS MUESTRA
if(!empty($_GET['message'])){
	$message = urldecode($_GET['message']);
}




//CREACION DEL FORMULARIO
/*la lista de parametros que puede incluir:
*nombre del formulario
*metodo (por defecto post)
*accion (por defecto se envia si mismo)*
*target (por defecto vacio)
*atributos extra de la etiqueta form
*trackSubmit (añadir un campo oculto para el seguir si el formulario ha sido enviado)
*/
$form = new FormValidator('formulario_dni');
/*AÑADIMOS LOS CAMPOS 
los tipos de elementos que se pueden crar estan en  main\inc\lib\pear\HTML\QuickForm  y  main\inc\lib\formvalidator\Element
o en http://pear.php.net/package/HTML_QuickForm/docs/latest/
*/
//en este ejemplo (tipo de elemento,nombre,texto junto al elemento,array de opciones)
$form->addElement('text', 'c_dni', get_lang('OfficialCode'), array('size' => '10', 'maxlength' => '10'));
/*añadimos una regla de validacion 
-nombre del campo que queremos validar
-texto en caso de no pasar la validacion
-nombre de la regla de validacion que vamos a usar*/
$form->addRule('c_dni', get_lang('ThisFieldIsRequired'), 'required');
$form->addRule('c_dni', "El DNI es incorrecto", 'dni');

$form->addElement('submit', 'submit', 'Enviar');





/*
==============================================================================
		HEADER
==============================================================================
*/


// Display form
Display::display_header($tool_name);
//api_display_tool_title($tool_name);
if(!empty($message)){
	Display::display_normal_message(stripslashes($message));
}


/*
==============================================================================
		BODY
==============================================================================
*/


if( $form->validate())//si recibimos datos operamos con los mismos
{
	   $user = $form->exportValues();
	   $dni = $user['c_dni'];
	   echo $dni;
	  
}else //si no  hemos recibido datos mostramos el formulario
{
$form->display();
}




/*
==============================================================================
		FOOTER
==============================================================================
*/
Display::display_footer();
?>