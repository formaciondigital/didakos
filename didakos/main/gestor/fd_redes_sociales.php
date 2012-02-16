<?php

// name of the language file that needs to be included
$language_file = array('redes_sociales');
$cidReset = true;
// including necessary libraries
require ('../inc/global.inc.php');
$libpath = api_get_path(LIBRARY_PATH);
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

// section for the tabs
$this_section=SECTION_PLATFORM_ADMIN;
// user permissions
api_protect_gestor_script();
// Database table definitions
$t_keys = Database::get_main_table(TABLE_REDES_SOCIALES);            
//navegation
$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));
$tool_name = get_lang('redessocialesconfig');

/*
==============================================================================
		HEADER
==============================================================================
*/

Display::display_header($tool_name);
$form = new FormValidator('keys');     

echo '<p>' . get_lang('redessocialesdesc') . '</p>';

// get config from database
$sql = "select * from $t_keys order by id";
$result = api_sql_query($sql,__FILE__,__LINE__);
    while ($temp_row = Database::fetch_array($result))
    {
        switch ($temp_row['name'])
        {
            case "twitter":
                $form->add_textfield('tck',get_lang('Tconsumerkey'),false);
                $form->add_textfield('tcs',get_lang('Tconsumersecret'),false);
                $form->setDefaults(array('tck'=>$temp_row['consumer_key']));
                $form->setDefaults(array('tcs'=>$temp_row['consumer_secret']));                
                break;
            case "facebook":
                $form->add_textfield('fck',get_lang('Fconsumerkey'),false);
                $form->add_textfield('fcs',get_lang('Fconsumersecret'),false);
                $form->setDefaults(array('fck'=>$temp_row['consumer_key']));
                $form->setDefaults(array('fcs'=>$temp_row['consumer_secret']));
                break;
            default:    
        }
    }
$form->addElement('submit','submit',get_lang('save'));

if($form->validate())
{
	$values = $form->exportValues();
        $sql = "update $t_keys set consumer_key='".$values["tck"]."',consumer_secret='".$values["tcs"]."' where id=1";
        $result = api_sql_query($sql,__FILE__,__LINE__);
        $sql = "update $t_keys set consumer_key='".$values["fck"]."',consumer_secret='".$values["fcs"]."' where id=2";
        $result = api_sql_query($sql,__FILE__,__LINE__);

        if ($result==1)
	{
		Display::display_confirmation_message(get_lang("configok"));
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