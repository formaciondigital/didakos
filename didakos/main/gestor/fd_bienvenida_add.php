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
Página actual: fd_bienvenida_add.php
Descripción: Crea una carta de benvenida en blanco.

	
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
$tool_name = get_lang('CrearCartaBienvenida');

// Create the form
$form = new FormValidator('bienvenida'); 
$form->addElement('text','nombre',get_lang('Name'));
$form->applyFilter('nombre','html_filter');
$form->applyFilter('nombre','trim');
$form->addRule('nombre', get_lang('ThisFieldIsRequired'), 'required');
$form->addElement('submit', 'submit', get_lang('langSave'));

// Validate form
if( $form->validate())
{
	if (isset($_POST['nombre']))
	{
		$t_bienvenida = Database::get_main_table(TABLE_MAIN_BIENVENIDA);
		//revisamos que no exista una carta con el mismo nombre
		$sql = "select count(*) as total from $t_bienvenida where nombre='" . $_POST['nombre'] . "'";
       		$res = api_sql_query($sql, __FILE__, __LINE__);
		$obj = Database::fetch_object($res);
			if ($obj->total > 0)
			{
				$message  = get_lang("CrearCartaBienvenidaTexto2").' "' . $_POST['nombre'] . '". '.get_lang("CrearCartaBienvenidaTexto3");
				$tipo = 'error';
			}
			else
			{
				//guardamos en bbdd		
				$sql = "insert into $t_bienvenida (nombre,html) values ('" .$_POST['nombre']. "','".get_lang("CrearCartaBienvenidaTexto4")."')";
		       		api_sql_query($sql, __FILE__, __LINE__);
				$message = get_lang("CrearCartaBienvenidaTexto5").' "' . $_POST['nombre'] . '"';	
				$tipo = 'normal';
			}
	}
}


// Display form
Display::display_header($tool_name);
echo get_lang('CrearCartaBienvenidaTexto1');
if(!empty($message)){
	if ($tipo=='normal')
	{
		Display::display_normal_message($message);
	}
	else
	{
		Display::display_error_message($message);
	}
}

$form->display();
/*
==============================================================================
		FOOTER
==============================================================================
*/
Display::display_footer();
?>
