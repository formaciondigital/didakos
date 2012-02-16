<?php
// $Id: course_information.php 12903 2007-08-29 14:04:04Z elixir_julian $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004 Dokeos S.A.
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) Olivier Brouckaert

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact: Dokeos, 181 rue Royale, B-1000 Brussels, Belgium, info@dokeos.com
==============================================================================
*/
/**
============================================================================== 
	@author Bart Mollet
*	@package dokeos.admin
============================================================================== 
*/
/*
==============================================================================
		Página modificada por Formación Digital

Autor: Eduardo García
Página incial: course_information.php (1.8.5)
Página actual: fd_curso_info.php
Descripción: Página que muestra información de un curso.
		
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
require ('../inc/lib/course.lib.fd.php');
require_once(api_get_path(LIBRARY_PATH).'sortabletable.class.php');
$this_section=SECTION_PLATFORM_ADMIN;

api_protect_gestor_script();
/**
 * 
 */
function get_course_usage($course_code)
{
	$table = Database::get_main_table(TABLE_MAIN_COURSE);
	$sql = "SELECT * FROM $table WHERE code='".$course_code."'";
	$res = api_sql_query($sql,__FILE__,__LINE__);
	$course = mysql_fetch_object($res);
	// Learnpaths
	$table = Database :: get_course_table(TABLE_LP_MAIN, $course->db_name);
	$usage[] = array (get_lang(ucfirst(TOOL_LEARNPATH)), Database::count_rows($table));
	// Forums
	$table = Database :: get_course_table(TABLE_FORUM, $course->db_name);
	$usage[] = array (get_lang('Forums'), Database::count_rows($table));
	// Quizzes
	$table = Database :: get_course_table(TABLE_QUIZ_TEST, $course->db_name);
	$usage[] = array (get_lang(ucfirst(TOOL_QUIZ)), Database::count_rows($table));
	// Documents
	$table = Database :: get_course_table(TABLE_DOCUMENT, $course->db_name);
	$usage[] = array (get_lang(ucfirst(TOOL_DOCUMENT)), Database::count_rows($table));
	// Groups
	$table = Database :: get_course_table(TABLE_GROUP, $course->db_name);
	$usage[] = array (get_lang(ucfirst(TOOL_GROUP)), Database::count_rows($table));
	// Calendar
	$table = Database :: get_course_table(TABLE_AGENDA, $course->db_name);
	$usage[] = array (get_lang(ucfirst(TOOL_CALENDAR_EVENT)), Database::count_rows($table));
	// Link
	$table = Database::get_course_table(TABLE_LINK, $course->db_name);
	$usage[] = array(get_lang(ucfirst(TOOL_LINK)), Database::count_rows($table));
	// Announcements
	$table = Database::get_course_table(TABLE_ANNOUNCEMENT, $course->db_name);
	$usage[] = array(get_lang(ucfirst(TOOL_ANNOUNCEMENT)), Database::count_rows($table));
	return $usage;
}
/*****************************************************************/

$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));
$interbreadcrumb[] = array ("url" => 'fd_finalizar_alumnos.php', "name" => get_lang('Courses'));
$table_course = Database :: get_main_table(TABLE_MAIN_COURSE);


//fd - cambio. si se recibe la orden se abre o cierra el acceso al curso de un alumno
if(isset($_GET['finalizar']))
{

CourseManagerFD::finalizar_alumno($_GET['finalizar'],$_GET['user_id'],$_GET['code']);
  
  if($_GET['finalizar'] == 0) 
  {
     $acceso = get_lang('AccesoAbierto');
  }
  if($_GET['finalizar'] == 1) 
  {
     $acceso = get_lang('AccesoCerrado');
  }
  
}

$code = $_GET['code'];


$sql = "SELECT * FROM $table_course WHERE code = '".$code."'";
$res = api_sql_query($sql,__FILE__,__LINE__);
$course = mysql_fetch_object($res);
$tool_name = $course->title.' ('.$course->code.')';
Display::display_header($tool_name);

if(isset($acceso)){
Display::display_normal_message($acceso);
echo '<center><a href="fd_finalizar_alumnos.php?code='.$code.'">'.get_lang('Volver').'</a></center>';
}else{
//api_display_tool_title($tool_name);

/**
 * Show all users subscribed in this course
 */
echo '<h4>'.get_lang('Users').'</h4>';
echo '<blockquote>';
$table_course_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$table_user = Database :: get_main_table(TABLE_MAIN_USER);
$sql = 'SELECT *,cfd.f_finalizacion,cu.status as course_status FROM '.$table_course_user.' cu, '.$table_user." u, course_rel_user_fd cfd WHERE cfd.course_code = cu.course_code and cfd.user_id = cu.user_id and cu.user_id = u.user_id AND cu.course_code = '".$code."' ";
$res = api_sql_query($sql,__FILE__,__LINE__);
if (mysql_num_rows($res) > 0)
{
	$users = array ();
	while ($obj = mysql_fetch_object($res))
	{
		$user = array ();
		$user[] = $obj->official_code;
		$user[] = $obj->firstname;
		$user[] = $obj->lastname;
		$user[] = Display :: encrypted_mailto_link($obj->email, $obj->email);
		$user[] = $obj->course_status == 5 ? get_lang('Student') : get_lang('Teacher');
		
		//comprobamos si el curso esta abierto
		
		$fecha_f = $obj->f_finalizacion;
		
		if($fecha_f != '' && (strtotime($fecha_f) - time()) < 0)
		{
		 $user[] = "<a href='fd_finalizar_alumnos.php?user_id=".$obj->user_id."&code=".$code."&finalizar=0'><img src = '../img/wrong.gif'></a>";
		}else{
		 $user[] = "<a href='fd_finalizar_alumnos.php?user_id=".$obj->user_id."&code=".$code."&finalizar=1'><img src = '../img/right.gif'></a>";
		}
		
		
		
		
		//$user[] = '<a href="fd_finalizar_alumnos.php?user_id='.$obj->user_id.'"><img src="../img/synthese_view.gif" border="0" /></a>';
		$users[] = $user;
	}
	$table = new SortableTableFromArray($users,0,20,'user_table');
	$table->set_additional_parameters(array ('code' => $_GET['code']));
	$table->set_other_tables(array('usage_table','class_table'));
	$table->set_header(0,get_lang('OfficialCode'), true);
	$table->set_header(1,get_lang('FirstName'), true);
	$table->set_header(2,get_lang('LastName'), true);
	$table->set_header(3,get_lang('Email'), true);
	$table->set_header(4,get_lang('Status'), true);
	$table->set_header(5,'', false);
	$table->display();
}
else
{
	echo get_lang('NoUsersInCourse');
}
echo '</blockquote>';
}
/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>
