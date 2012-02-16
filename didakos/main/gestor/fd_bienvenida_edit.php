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
/*
==============================================================================
		Página modificada por Formación Digital

Autor: Eduardo García
Página incial: ninguna
Página actual: fd_bienvenida_edit.php
Descripción: Página que muestra un editor de texto para generar la carta de bienvenida
para los alumnos. guarda los datos en formato html en la tabla fd_bienvenida en el campo html.
		
==============================================================================
*/
/**
==============================================================================
*	@package dokeos.admin
==============================================================================
*/

// name of the language file that needs to be included
$language_file = array('admin','registration');
$cidReset = true;

// including necessary libraries
require ('../inc/global.inc.php');
$libpath = api_get_path(LIBRARY_PATH);
require_once ($libpath.'formvalidator/FormValidator.class.php');

// section for the tabs
$this_section=SECTION_PLATFORM_ADMIN;

// user permissions
api_protect_gestor_script();

// Database table definitions

//barra de navegación
 
$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));
$tool_name = get_lang('EditarCartaBienvenida');

//Obtenemos datos de la carta de bienvenida si existe
$sql = "select html from fd_bienvenida where id=" . $_POST['id'];
$res = api_sql_query($sql, __FILE__, __LINE__);
$obj = Database::fetch_array($res);
$user_data['bienvenida'] = $obj["html"];	

// Create the form
$form = new FormValidator('bienvenida'); 
$form->addElement('hidden','id',$_POST['id']);
$form->add_html_editor('bienvenida', get_lang('CartaDeBienvenida'), false);
$form->addElement('submit', 'submit', get_lang('langSave'));
$form->setDefaults($user_data);

// Validate form
if( $form->validate())
{
	if (isset($_POST['bienvenida']))
	{
		//guardamos en bbdd	
		$t_bienvenida = Database::get_main_table(TABLE_MAIN_BIENVENIDA);	
		$sql = "update $t_bienvenida set html='" .$_POST['bienvenida']. "' where id=" . $_POST['id'];
       		api_sql_query($sql, __FILE__, __LINE__);
		$message = get_lang('EditarCartaBienvenidaTexto1');
	}
}

// Display form
Display::display_header($tool_name);
echo get_lang('EditarCartaBienvenidaTexto2');
if(!empty($message)){
	Display::display_normal_message($message);
}
/*
CREATE TABLE `fd_version02_main`.`fd_bienvenida` (
  `html` LONGTEXT  NOT NULL
)
ENGINE = MyISAM;
*/
$form->display();
/*
==============================================================================
		FOOTER
==============================================================================
*/
Display::display_footer();
?>
