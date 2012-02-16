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
		INIT SECTION
==============================================================================
*/
// name of the language file that needs to be included 
$language_file = array('diplomas','admin');
$cidReset = true;
require ('../../inc/global.inc.php');
require_once(api_get_path(LIBRARY_PATH).'sortabletable.class.php');
$this_section=SECTION_PLATFORM_ADMIN;

api_protect_gestor_script();

if (!isset ($_GET['code']))
{
	api_not_allowed();
}

$table_course = Database :: get_main_table(TABLE_MAIN_COURSE);
$code = $_GET['code'];
$sql = "SELECT * FROM $table_course WHERE code = '".$code."'";
$res = api_sql_query($sql,__FILE__,__LINE__);
$course = mysql_fetch_object($res);
$interbreadcrumb[] = array ("url" => '../index.php', "name" => get_lang('PlatformAdmin'));
$tool_name = get_lang("trackdiplomas");
Display :: display_header($tool_name);
//api_display_tool_title($tool_name);
echo ('<p>' . get_lang("Selectedcourse"). ': ' .  $course->code . ' ' . $course->title. '<br/></p>');

/**
 * Show all users subscribed in this course
 */
echo '<h4>'.get_lang('Users').'</h4>';
echo '<blockquote>';
$table_course_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$table_user = Database :: get_main_table(TABLE_MAIN_USER);
$table_track = Database :: get_main_table(TABLE_DIPLOMAS_TRACK);

$sql = 'SELECT u.firstname,u.lastname,u.official_code,u.username,u.password,cu.status as course_status, ds.download_date, count(ds.id) as descargas
FROM '.$table_user.' u inner join '.$table_course_user." cu on (u.user_id=cu.user_id)
LEFT OUTER JOIN $table_track ds ON (ds.user_id = u.user_id) and (ds.course_code='".$code."')
where cu.course_code ='" .$code . "' group by u.user_id";

$res = @api_sql_query($sql,__FILE__,__LINE__);
if (mysql_num_rows($res) > 0)
{
	$users = array ();
	while ($obj = mysql_fetch_object($res))
	{
		$user = array ();
		$user[] = $obj->official_code;
		$user[] = $obj->firstname;
		$user[] = $obj->lastname;
		$user[] = $obj->username;
		$user[] = '-';
		$user[] = $obj->course_status == 5 ? get_lang('Student') : get_lang('Teacher');
		$user[] = $obj->descargas;
		$user[] = $obj->download_date;
		$users[] = $user;
	}
	$table = new SortableTableFromArray($users,0,20,'user_table');
	$table->set_additional_parameters(array ('code' => $_GET['code']));
	$table->set_other_tables(array('usage_table','class_table'));
	$table->set_header(0,get_lang('OfficialCode'), true);
	$table->set_header(1,get_lang('FirstName'), true);
	$table->set_header(2,get_lang('LastName'), true);
	$table->set_header(3,get_lang('username'), true);
	$table->set_header(4,get_lang('password'), true);
	$table->set_header(5,get_lang('Status'), true);
	$table->set_header(6,get_lang("descargas"), true);
	$table->set_header(7,get_lang("firstdate"));
	$table->display();
}
else
{
	echo get_lang('NoUsersInCourse');
}
echo '</blockquote>';

/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>
