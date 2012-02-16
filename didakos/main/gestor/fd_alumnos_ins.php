<?php
/*
$Id: course_list.php 15245 2008-05-08 16:53:52Z juliomontoya $

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
*    @package dokeos.admin
==============================================================================
*/
/*
==============================================================================
        Página modificada por Formación Digital

Autor: Francisco Javier Rubio Campos
Página incial: fd_alumnos_ins.php (1.8.5)
Página actual: fd_alumnos_ins_listado.php
Descripción: Muestra el listado de alumnos inscritos que han hecho el examen de
 acceso a un curso.
       
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

/**
 * Get all courses
 */
function get_courses()
{
    $table_option = Database :: get_main_table(TABLE_MAIN_USER_FIELD_OPTIONS);
    $sql = "SELECT option_display_text, option_value, option_order FROM ".$table_option."
  WHERE field_id = 11 order by option_order asc;";
    $res = api_sql_query($sql, __FILE__, __LINE__);
    while ($course = Database::fetch_array($res))
    {
        if ($course['option_value'] != '0')
				 	$courses[$course['option_value']] = $course['option_display_text'];
    }

    return $courses;
}

$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));
$tool_name = 'Alumnos inscritos por curso (Pruebas de acceso)';
Display :: display_header($tool_name);

echo '<span>Debe seleccionar los datos que se muestran a continuaci&oacute;n y pulsar aceptar para obtener los datos de los alumnos<br><br></span>';
$form = new FormValidator('alumnos_ins','post','fd_alumnos_ins_listado.php','',array('style' => 'width: 60%; float: '.($text_dir=='rtl'?'right;':'left;')));
$form->addElement('select', 'curso', 'Seleccione el curso', get_courses());
$form->addElement('submit', null, get_lang('Ok'));
$form->display();


/*
==============================================================================
        FOOTER
==============================================================================
*/
Display :: display_footer();
?>
