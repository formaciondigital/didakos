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
$this_section = SECTION_PLATFORM_ADMIN;

api_protect_gestor_script();
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'course.lib.fd.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');
include(dirname ( __FILE__ )."../../main/inc/conf/configuration.php");

$t_diplomas_course = Database :: get_main_table(TABLE_DIPLOMAS_COURSES);
/**
 * Get the number of courses which will be displayed
 */
function get_number_of_courses()
{
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
        $diplomas_course_table = Database :: get_main_table(TABLE_DIPLOMAS_COURSES);
        
	$sql = "SELECT COUNT(d.course_code) AS total_number_of_items FROM $course_table c, $diplomas_course_table d where c.code = d.course_code";
	if (isset ($_GET['keyword']))
	{
		$keyword = Database::escape_string($_GET['keyword']);
		$sql .= " and d.course_code LIKE '%".$keyword."%'";
	}
	elseif (isset ($_GET['keyword_code']))
	{
		$keyword_code = Database::escape_string($_GET['keyword_code']);		
		$sql .= " and d.course_code LIKE '%".$keyword_code."%'";
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
	$diplomas_course_table = Database :: get_main_table(TABLE_DIPLOMAS_COURSES);
        
	$sql = "SELECT c.code AS col0,
	               c.visual_code AS col1,
				   c.title AS col2,
				    c.course_language AS col3,
					 c.category_code AS col4,
					  c.subscribe AS col5,
					   c.unsubscribe AS col6,
					    c.code AS col7,
						 c.tutor_name as col8,
						  c.code AS col9,
						   c.visibility AS col10,
						    c.expiration_date AS col11,
        d.design_id as col12
			FROM $course_table c,$diplomas_course_table d where c.code=d.course_code";
	if (isset ($_GET['keyword']))
	{
		$keyword = Database::escape_string($_GET['keyword']);
		$sql .= " and d.course_code LIKE '%".$keyword."%'";
	}
	elseif (isset ($_GET['keyword_code']))
	{
		$keyword_code = Database::escape_string($_GET['keyword_code']);
		$sql .= " AND d.course_code LIKE '%".$keyword_code."%'";
	}
	$sql .= " LIMIT $from,$number_of_items";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$courses = array ();
	

	while ($course = Database::fetch_row($res))
	{
                $herramientas = ' <a target="_self" href="courses_edit.php?code='. $course[1] . '"><img title="'.  get_lang("edit").'" src="../../img/edit.gif"></a> <a target="_self" href="courses.php?action=delete&code='. $course[1] . '"><img title="'.  get_lang("delete").'" src="../../img/delete.gif"  return false;" onclick="javascript:if(!confirm('."'".addslashes(htmlentities(get_lang("Confirmdelete"),ENT_QUOTES,$charset))."'".'))"></a>';
                
		
		$course[1] = get_course_visibility_icon($course[10]).$course[1];
		$course[5] = $course[5] == SUBSCRIBE_ALLOWED ? get_lang('Yes') : get_lang('No');
		// $course[6] = $course[6] == UNSUBSCRIBE_ALLOWED ? get_lang('Yes') : get_lang('No');
                // Get design
                $design_table = Database :: get_main_table(TABLE_DIPLOMAS_DESIGN);
                $sql = "select * from $design_table where id=" . $course[12];
                $res2 = api_sql_query($sql, __FILE__, __LINE__);
                $design = Database::fetch_row($res2);
                
                if (count($design)>1)
                {
                    $course[6] = $design[2] . ' | <a href="design/' . $design[1] .'" target="_blank">Ver imagen</a>';        
                }
                else
                {
                    $course[6] = "-";
                }
                
                
		$course[7] = CourseManager :: is_virtual_course_from_system_code($course[7]) ? get_lang('Yes') : get_lang('No');
		$course_rem = array($course[1] . ' / ' . $course[2],$course[3],$course[4],$course[5],$course[6],$course[8],$herramientas);
		$courses[] = $course_rem;
	}
	return $courses;
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


/*
//vemos si viene la orden de finalizar algún curso
if (isset ($_GET['finalizar']) && $_GET['finalizar'] == '1')
{
	$codigo_curso = $_GET['course_code'];
	//Podremos recibir una fecha concreta, actualmente no se permite
	if (isset ($_GET['fecha']))
	{
		$fecha_fin= $_GET['fecha'];
	}
	else
	{
		$fecha_fin= date("Y-m-d H:i:s");
	}

	$sql = "update course_rel_user_fd set f_finalizacion='$fecha_fin' where course_code=$codigo_curso";
	api_sql_query($sql, __FILE__, __LINE__);

}


//vemos si viene la orden de cerrar el curso
if (isset ($_GET['cerrar']))
{
	$codigo_curso = $_GET['course_code'];
	
	CourseManagerFD::close_course($codigo_curso,$_GET['cerrar']);

}
*/



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
	$interbreadcrumb[] = array ("url" => '../index.php', "name" => get_lang('PlatformAdmin'));
	$interbreadcrumb[] = array ("url" => 'courses.php', "name" => get_lang("courselist"));
	$tool_name = get_lang("search");
	Display :: display_header($tool_name);
	
	//api_display_tool_title($tool_name);
	$form = new FormValidator('advanced_course_search', 'get');
	$form->add_textfield('keyword_code', get_lang('CourseCode'), false);
	$form->setDefaults($defaults);
	$form->display();
}
else
{
	$interbreadcrumb[] = array ("url" => '../index.php', "name" => get_lang('PlatformAdmin'));
	$tool_name = get_lang("courselist");
	Display :: display_header($tool_name);
	/*
        if ($_GET['finalizar'] == '1')
	{
		Display :: display_normal_message('El acceso al curso ' .$_GET['course_code']. ' ha sido finalizado para todos los alumnos');
	}
	if (isset ($_GET['cerrar']))
	{
	   if($_GET['cerrar'] == 1)
	   {
		Display :: display_normal_message('El curso ' .$_GET['course_code']. ' ha sido cerrado');
	   }else{
	    Display :: display_normal_message('El curso ' .$_GET['course_code']. ' ha sido abierto');
	   }
	}
        */
	
	//api_display_tool_title($tool_name);
        
	if (isset ($_GET['action']) && ($_GET['action']=='delete'))
	{
            $course_code = $_GET["code"];
            $sql = "delete from $t_diplomas_course where course_code='" . $course_code . "'";
            $res = api_sql_query($sql, __FILE__, __LINE__);
            
            $db_prefix = $_configuration['db_prefix'];
            $t_course = $db_prefix . $course_code . ".". Database::get_course_table(TABLE_TOOL_LIST);
            // insert tool to course
            $sql = "delete from $t_course  where name='diplomas'";
            $res = api_sql_query($sql, __FILE__, __LINE__);        
            
            Display::display_confirmation_message(get_lang("deleteok"));
	}
        
         if (isset($_GET["messageedit"]))
         {
              Display::display_confirmation_message(get_lang("editok"));
         }
         
         if (isset($_GET["messageadd"]))
         {
              Display::display_confirmation_message(get_lang("addok"));
         }
         
	// Create a search-box
	$form = new FormValidator('search_simple','get','','',null,false);
	$renderer =& $form->defaultRenderer();
	$renderer->setElementTemplate('<span>{element}</span> ');
	$form->addElement('text','keyword',get_lang('keyword'));
	$form->addElement('submit','submit',get_lang('Search'));
	// $form->addElement('static','search_advanced_link',null,'<a href="courses.php?search=advanced">'.get_lang('AdvancedSearch').'</a>');
        $form->addElement('static','add_link',null,'| <img src="../../img/view_more_stats.gif"><a href="courses_add.php">'.get_lang('courseadd').'</a>');
	$form->display();
	// Create a sortable table with the course data
	$table = new SortableTable('courses', 'get_number_of_courses', 'get_course_data',2);
	$parameters=array();
	$table->set_additional_parameters($parameters);
	//$table->set_header(0, '', false);
	//$table->set_header(1, get_lang('Code'));
	$table->set_header(0, get_lang('Title'));
	$table->set_header(1, get_lang('Language'));
	$table->set_header(2, get_lang('Category'));
	$table->set_header(3, get_lang('SubscriptionAllowed'));
	$table->set_header(4, get_lang('template'));
	//$table->set_header(7, get_lang('IsVirtualCourse'));
	$table->set_header(5, get_lang('Teacher'));
	$table->set_header(6, get_lang('tools'));
	
	//$table->set_header(9, get_lang('Modify'), false,'width="120"');	
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
