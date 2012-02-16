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
Página actual: fd_bienvenida_seleccion.php
Descripción: Página que muestra una lista de las cartas de bienvenida. dependiendo del parametro que recibe por GET envía a un sitio o a otro.
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
$tool_name = get_lang('SeleccionarCartaBienvenida');

//Recogemos el tipo de acción que se ha seleccionado
if (isset ($_GET['accion']))
{
	$accion=$_GET['accion'];
}
else
{
	if (isset($_POST['accion']))
	{
		$accion = $_POST['accion'];
	}
}

//Dependiendo del valor de $accion vamos a enviar a una página o a otra.
switch ($accion)
{
	case 'editar':
		$url= 'fd_bienvenida_edit.php';
		$texto=get_lang('SeleccionarCartaBienvenidaEditar');
		break;
	case 'eliminar':
		$url= 'fd_bienvenida_delete.php';
		$texto=get_lang('SeleccionarCartaBienvenidaEliminar');
		break;
	case 'curso':
		$url= 'fd_bienvenida_send.php';
		$texto=get_lang('SeleccionarCartaBienvenidaEnviarACurso');
		break;
	case 'alumno':
		$url= 'fd_bienvenida_sendone.php';
		$texto=get_lang('SeleccionarCartaBienvenidaEnviarAAlumno');
		break;
	case 'clase':
		$url= 'fd_bienvenida_sendclass.php';
		$texto=get_lang('SeleccionarCartaBienvenidaEnviarAClase');
		break;
}

// Create the form
$form = new FormValidator('bienvenida','post',$url); 

//Obtenemos datos de la carta de bienvenida si existe
$t_bienvenida = Database::get_main_table(TABLE_MAIN_BIENVENIDA);
$sql = "select id,nombre from $t_bienvenida order by nombre";
$res = api_sql_query($sql, __FILE__, __LINE__);
//cargamos el combo
$options = array();
while ($carta = Database::fetch_array($res))
	{
		$options [$carta[0]] = $carta['1'];
	}
$form->addElement('select', 'id', get_lang('SeleccionarCartaBienvenidaSeleccione'),$options);
$form->addElement('hidden', 'accion', $accion);
$form->addElement('submit', 'submit', get_lang('Ok'));

// Display form
Display::display_header($tool_name);

//mostramos el texto y el formulario
echo $texto;
$form->display();


/*
==============================================================================
		FOOTER
==============================================================================
*/
Display::display_footer();
?>
