<?php
// $Id: course_list.php 15245 2008-05-08 16:53:52Z juliomontoya $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2008 Dokeos SPRL
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

	Contact address: Dokeos, rue du Corbeau, 108, B-1030 Brussels, Belgium
	Mail: info@dokeos.com
==============================================================================
*/
/**
==============================================================================
*	@package dokeos.admin
============================================================================== 
*/
/*
==============================================================================
		Página modificada por Formación Digital

Autor: Eduardo García
Página incial: fd_bienvenida_send.php (versión 0.2)
Página actual: fd_bienvenida_send.php
Descripción: Página que muestra un listado de los cursos añadiendo la opción de enviar cartas
de bienvenida almacenadas en fd_bienvenida.html
		
==============================================================================
*/
/*
==============================================================================
		INIT SECTION
==============================================================================
*/

// name of the language file that needs to be included 
$language_file = 'admin';
$cidReset = true;
require ('../inc/global.inc.php');
$this_section = SECTION_PLATFORM_ADMIN;

api_protect_gestor_script();
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');


if (isset($_POST['id']))
{
	//hemos recibido el id de la carta que queremos enviar
	$_SESSION['id_carta'] = $_POST['id'];
}

/*
recibe el course_code y devuelve el numero de alumnos matriculados
*/
function get_number_of_alumnos($id_curso)
{
	$matriculaciones_table = Database :: get_main_table(TABLE_MAIN_MATRICULACIONES);
	$sql = "select count(*) as alumnos from $matriculaciones_table where course_code='". $id_curso ."' and user_id not in (1,2)";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_object($res);
	return $obj->alumnos;
}


/*
recibe el course_code y devuelve el nombre del curso
*/
function get_nombre_curso($id_curso)
{
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$sql = "select title as nombre_curso from $course_table where code='". $id_curso ."'";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_object($res);
	return $obj->nombre_curso;
}


/*
recibe el course_code y devuelve datos de los alumnos (nombre y apellidos y email)
*/

function get_datos_alumnos($id_curso)
{
	$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	$matriculaciones_table = Database :: get_main_table(TABLE_MAIN_MATRICULACIONES);
	$sql = "select u.firstname,u.lastname, u.email, u.username, u.password from $user_table u, $matriculaciones_table crl 
	where course_code='". $id_curso ."' and u.user_id=crl.user_id";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$users = array ();
	while ($user = Database::fetch_row($res))
	{
		$users[] = $user;
	}
	return $users;
}


/**
 * Get the number of courses which will be displayed
 */


function get_number_of_courses()
{
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$sql = "SELECT COUNT(code) AS total_number_of_items FROM $course_table";
	if (isset ($_GET['keyword']))
	{
		$keyword = Database::escape_string($_GET['keyword']);
		$sql .= " WHERE title LIKE '%".$keyword."%' OR code LIKE '%".$keyword."%'";
	}
	elseif (isset ($_GET['keyword_code']))
	{
		$keyword_code = Database::escape_string($_GET['keyword_code']);
		$keyword_title = Database::escape_string($_GET['keyword_title']);
		$keyword_category = Database::escape_string($_GET['keyword_category']);
		$keyword_language = Database::escape_string($_GET['keyword_language']);
		$keyword_visibility = Database::escape_string($_GET['keyword_visibility']);
		$keyword_subscribe = Database::escape_string($_GET['keyword_subscribe']);
		$keyword_unsubscribe = Database::escape_string($_GET['keyword_unsubscribe']);
		$sql .= " WHERE code LIKE '%".$keyword_code."%' AND title LIKE '%".$keyword_title."%' AND category_code LIKE '%".$keyword_category."%'  AND course_language LIKE '%".$keyword_language."%'   AND visibility LIKE '%".$keyword_visibility."%'    AND subscribe LIKE '".$keyword_subscribe."'AND unsubscribe LIKE '".$keyword_unsubscribe."'";
	}
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_object($res);
	return $obj->total_number_of_items;
}
/**
 * Get course data to display
 */
function get_course_data($from, $number_of_items, $column, $direction)
{
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$users_table = Database :: get_main_table(TABLE_MAIN_USER);
	$course_users_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	
	$sql = "SELECT code AS col0, visual_code AS col1, title AS col2, course_language AS col3, category_code AS col4, subscribe AS col5, unsubscribe AS col6, code AS col7, tutor_name as col8, code AS col9, visibility AS col10 FROM $course_table";
	if (isset ($_GET['keyword']))
	{
		$keyword = Database::escape_string($_GET['keyword']);
		$sql .= " WHERE title LIKE '%".$keyword."%' OR code LIKE '%".$keyword."%'";
	}
	elseif (isset ($_GET['keyword_code']))
	{
		$keyword_code = Database::escape_string($_GET['keyword_code']);
		$keyword_title = Database::escape_string($_GET['keyword_title']);
		$keyword_category = Database::escape_string($_GET['keyword_category']);
		$keyword_language = Database::escape_string($_GET['keyword_language']);
		$keyword_visibility = Database::escape_string($_GET['keyword_visibility']);
		$keyword_subscribe = Database::escape_string($_GET['keyword_subscribe']);
		$keyword_unsubscribe = Database::escape_string($_GET['keyword_unsubscribe']);
		$sql .= " WHERE code LIKE '%".$keyword_code."%' AND title LIKE '%".$keyword_title."%' AND category_code LIKE '%".$keyword_category."%'  AND course_language LIKE '%".$keyword_language."%'   AND visibility LIKE '%".$keyword_visibility."%'    AND subscribe LIKE '".$keyword_subscribe."'AND unsubscribe LIKE '".$keyword_unsubscribe."'";
	}
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$courses = array ();


	while ($course = Database::fetch_row($res))
	{
		//place colour icons in front of courses
		$curso = $course[1];
		$course[1] = get_course_visibility_icon($course[10]).$course[1];
		$course[5] = $course[5] == SUBSCRIBE_ALLOWED ? get_lang('Yes') : get_lang('No');
		$course[6] = $course[6] == UNSUBSCRIBE_ALLOWED ? get_lang('Yes') : get_lang('No');

		$course[7] = get_number_of_alumnos ($curso);

		$course_rem = array($course[1],$course[2],$course[3],$course[4],$course[5],$course[6],$course[7],$course[8],$course[9]);
		$courses[] = $course_rem;
	}
	return $courses;
}
/**
 * Filter to display the edit-buttons
 */
function modify_filter($code)
{
	global $charset;
	$enlace = '<a href="fd_bienvenida_send.php?id_carta=' .$_SESSION['id_carta'] .'&course_code=' . $code .'" onclick="javascript:if(!confirm('."'".addslashes(htmlentities(get_lang("ConfirmYourChoice"),ENT_QUOTES,$charset))."'".')) return false;"><img src="../img/email.gif" border="0" style="vertical-align: middle" title="'.get_lang('Send').'" alt="'.get_lang('Send').'"/></a>&nbsp;';
	return $enlace;
}
/**
 * Return an icon representing the visibility of the course
 */
function get_course_visibility_icon($v)
{
	$path = api_get_path(REL_CODE_PATH);
	$style = 'style="margin-bottom:-5px;margin-right:5px;"';
	switch($v)
	{
		case 0:
			return '<img src="'.$path.'img/bullet_red.gif" title="'.get_lang('CourseVisibilityClosed').'" '.$style.' />';
			break;
		case 1:
			return '<img src="'.$path.'img/bullet_orange.gif" title="'.get_lang('Private').'" '.$style.' />';
			break;
		case 2:
			return '<img src="'.$path.'img/bullet_green.gif" title="'.get_lang('OpenToThePlatform').'" '.$style.' />';
			break;
		case 3:
			return '<img src="'.$path.'img/bullet_blue.gif" title="'.get_lang('OpenToTheWorld').'" '.$style.' />';
			break;
		default:
			return '';
	}
}



if (isset($_GET['course_code']) && isset($_GET['id_carta']) )
{
	$t_bienvenida = Database::get_main_table(TABLE_MAIN_BIENVENIDA);
	//Obtenemos datos de la carta de bienvenida si existe
	$sql = "select html from $t_bienvenida where id=" . $_GET['id_carta'];
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_array($res);
	$html = $obj["html"];
	
	//recibimos codigo de curso para mandar los emails
	$users = get_datos_alumnos($_GET['course_code']);
	$nombre_curso = get_nombre_curso($_GET['course_code']);

	foreach ($users as $user)
	{
		$body =	str_replace  ( '#alumno#'  , $user[0] . ' ' . $user[1]  ,  $html);
		$body =	str_replace  ( '#curso#'  , $nombre_curso,  $body);
		$body =	str_replace  ( '#usuario#'  , $user[3],  $body);
		$body =	str_replace  ( '#clave#'  , $user[4],  $body);

		api_send_mail($user[2],'Carta de bienvenida -'. $nombre_curso ,$body,"");
		api_send_mail($_SESSION["_user"]["mail"],'Carta de bienvenida -'. $nombre_curso . ' (' . $user[0] . ' ' . $user[1] . ')' ,$body,"");
	}

	$message= get_lang('EnviarCartasDeBienvenidaTexto1');
}


if (isset ($_GET['search']) && $_GET['search'] == 'advanced')
{
	// Get all course categories
	$table_course_category = Database :: get_main_table(TABLE_MAIN_CATEGORY);
	$sql = "SELECT code,name FROM ".$table_course_category." WHERE auth_course_child ='TRUE' ORDER BY tree_pos";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$categories['%'] = get_lang('All');
	while ($cat = Database::fetch_array($res))
	{
		$categories[$cat['code']] = '('.$cat['code'].') '.$cat['name'];
	}
	$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));
	$interbreadcrumb[] = array ("url" => 'fd_bienvenida_send.php', "name" => get_lang('EnviarCartasDeBienvenida'));
	$tool_name = get_lang('SearchACourse');
	Display :: display_header($tool_name);
	//api_display_tool_title($tool_name);
	$form = new FormValidator('advanced_course_search', 'get');
	$form->add_textfield('keyword_code', get_lang('CourseCode'), false);
	$form->add_textfield('keyword_title', get_lang('Title'), false);
	$form->addElement('select', 'keyword_category', get_lang('CourseFaculty'), $categories);
	$el = & $form->addElement('select_language', 'keyword_language', get_lang('CourseLanguage'));
	$el->addOption(get_lang('All'), '%');
	$form->addElement('radio', 'keyword_visibility', get_lang("CourseAccess"), get_lang('OpenToTheWorld'), COURSE_VISIBILITY_OPEN_WORLD);
	$form->addElement('radio', 'keyword_visibility', null, get_lang('OpenToThePlatform'), COURSE_VISIBILITY_OPEN_PLATFORM);
	$form->addElement('radio', 'keyword_visibility', null, get_lang('Private'), COURSE_VISIBILITY_REGISTERED);
	$form->addElement('radio', 'keyword_visibility', null, get_lang('CourseVisibilityClosed'), COURSE_VISIBILITY_CLOSED);
	$form->addElement('radio', 'keyword_visibility', null, get_lang('All'), '%');
	$form->addElement('radio', 'keyword_subscribe', get_lang('Subscription'), get_lang('Allowed'), 1);
	$form->addElement('radio', 'keyword_subscribe', null, get_lang('Denied'), 0);
	$form->addElement('radio', 'keyword_subscribe', null, get_lang('All'), '%');
	$form->addElement('radio', 'keyword_unsubscribe', get_lang('Unsubscription'), get_lang('AllowedToUnsubscribe'), 1);
	$form->addElement('radio', 'keyword_unsubscribe', null, get_lang('NotAllowedToUnsubscribe'), 0);
	$form->addElement('radio', 'keyword_unsubscribe', null, get_lang('All'), '%');
	$form->addElement('submit', 'submit', get_lang('Ok'));
	$defaults['keyword_language'] = '%';
	$defaults['keyword_visibility'] = '%';
	$defaults['keyword_subscribe'] = '%';
	$defaults['keyword_unsubscribe'] = '%';
	$form->setDefaults($defaults);
	$form->display();
}
else
{
	$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));
	$tool_name = get_lang('EnviarCartasDeBienvenida');
	Display :: display_header($tool_name);
	if(!empty($message)){
	Display::display_normal_message($message);
	}
	//api_display_tool_title($tool_name);
	if (isset ($_GET['delete_course']))
	{
		CourseManager :: delete_course($_GET['delete_course']);
	}
	// Create a search-box
	$form = new FormValidator('search_simple','get','','',null,false);
	$renderer =& $form->defaultRenderer();
	$renderer->setElementTemplate('<span>{element}</span> ');
	$form->addElement('text','keyword',get_lang('keyword'));
	$form->addElement('submit','submit',get_lang('Search'));
	$form->addElement('static','search_advanced_link',null,'<a href="fd_bienvenida_send.php?search=advanced">'.get_lang('AdvancedSearch').'</a>');
	$form->display();
	// Create a sortable table with the course data
	$table = new SortableTable('courses', 'get_number_of_courses', 'get_course_data',2);
	$parameters=array();
	$table->set_additional_parameters($parameters);
	//$table->set_header(0, '', false);
	$table->set_header(0, get_lang('Code'));
	$table->set_header(1, get_lang('Title'));
	$table->set_header(2, get_lang('Language'));
	$table->set_header(3, get_lang('Category'));
	$table->set_header(4, get_lang('SubscriptionAllowed'));
	$table->set_header(5, get_lang('UnsubscriptionAllowed'));
	$table->set_header(6, get_lang('Alumnos'));
	$table->set_header(7, get_lang('Teacher'));
	$table->set_header(8, get_lang('Opciones'));	
	$table->set_column_filter(8,'modify_filter');
	
	//$table->set_form_actions(array ('delete_courses' => get_lang('DeleteCourse')),'course');
	$table->display();
}

/*
==============================================================================
		FOOTER 
==============================================================================
*/
Display :: display_footer();
?>
