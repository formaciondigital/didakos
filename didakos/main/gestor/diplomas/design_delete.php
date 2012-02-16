<?php // $Id: template.php,v 1.2 2006/03/15 14:34:45 pcool Exp $
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
==============================================================================
*/
	
/*
==============================================================================
		INIT SECTION
==============================================================================
*/ 

// name of the language file that needs to be included
$language_file = array('diplomas');
$cidReset = true;
// including necessary libraries
require ('../../inc/global.inc.php');
$libpath = api_get_path(LIBRARY_PATH);
require_once ($libpath.'formvalidator/FormValidator.class.php');
// section for the tabs
$this_section=SECTION_PLATFORM_ADMIN;
// user permissions
api_protect_gestor_script();
// Database table definitions
$t_design = Database::get_main_table(TABLE_DIPLOMAS_DESIGN);               
//navigation
$interbreadcrumb[] = array ("url" => '../index.php', "name" => get_lang('PlatformAdmin'));
$tool_name = get_lang('design_delete');

/*
==============================================================================
		HEADER
==============================================================================
*/


/*
===========================================================================
                BODY
===========================================================================
 */


if(sizeof($_POST)) 
{
    if (isset($_POST['id_design']))
    {
        // load design 
        $sql = "delete from $t_design where id=" . $_POST['id_design'];
        $res = api_sql_query($sql, __FILE__, __LINE__);
        $message = get_lang("designdeleteok");
    }
}

$sql = "select id,title from $t_design order by title";
$res = api_sql_query($sql, __FILE__, __LINE__);
//cargamos el combo
$options = array();
while ($carta = Database::fetch_array($res))
	{
		$options [$carta[0]] = $carta['1'];
	}

$form = new FormValidator('diplomas'); 
$form->addElement('select', 'id_design', get_lang("selectdesign"),$options);
$form->addElement('submit', 'submit', 'Aceptar');
Display::display_header($tool_name);
if(!empty($message)){
	Display::display_normal_message($message);
}
echo get_lang("designdeletedesc");
$form->display();
/*
==============================================================================
		FOOTER
==============================================================================
*/
Display::display_footer();
?>

