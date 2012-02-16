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
// section for the tabs
$this_section=SECTION_PLATFORM_ADMIN;
// user permissions
api_protect_gestor_script();
// ckeditor
$htmlHeadXtra[] = "<script type='text/javascript' src='ckeditor/ckeditor.js'></script>";
// Database table definitions
$t_design = Database::get_main_table(TABLE_DIPLOMAS_DESIGN);               
//navigation
$interbreadcrumb[] = array ("url" => '../index.php', "name" => get_lang('PlatformAdmin'));
$tool_name = get_lang('design_edit');

/*
==============================================================================
		HEADER
==============================================================================
*/

Display::display_header($tool_name);

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
        $sql = "select * from $t_design where id=" . $_POST['id_design'];
        $res = api_sql_query($sql, __FILE__, __LINE__);
        $obj = Database::fetch_array($res);
?>

<form enctype="multipart/form-data" target="_self" name="f" method="POST" >
    <table> 
        <tr><td colspan="2"><p><? echo get_lang("designdesc");?></p></td></tr>
        <tr>
            <td width="10%">
              <b><?=get_lang('titulo')?>: </b>
            </td>
            <td  width="90%"><input type="hidden" name="id" value="<?=$_POST["id_design"];?>" /><input type="text" name="title" value="<?php echo $obj["title"];?>"></td>
        </tr>
        <tr>
            <td width="10%">
              <b><?=get_lang('imagen_fondo')?>: </b>
            </td>
            <td  width="90%"> <a href="<?php echo "design/". $obj["image"] . " " ;?>" target="_blank"><?php echo $obj["image"] . " " ;?></a><input type="file" name="imagen">
           </td>
        </tr>
        <tr>
            <td width="10%">
              <b><?=get_lang('contenidoup')?>: </b>
            </td>
            <td  width="90%">            
            <textarea cols="80" id="editor_up" name="editor_up" rows="4"><?php echo $obj["up_text"];?></textarea><br>
                <script type="text/javascript">
                //<![CDATA[
                        CKEDITOR.replace( 'editor_up',{ skin : 'kama' });
                //]]>
                </script>		
            </td>
        </tr>
        <tr>
            <td width="10%">
              <b><?=get_lang('contenidocenter')?>: </b>
            </td>
            <td  width="90%">            
            <textarea cols="80" id="editor_center" name="editor_center" rows="6"><?php echo $obj["center_text"];?></textarea><br>
                <script type="text/javascript">
                //<![CDATA[
                        CKEDITOR.replace( 'editor_center',{ skin : 'kama' });
                //]]>
                </script>		
            </td>
        </tr>
        <tr>
            <td width="10%">
              <b><?=get_lang('contenidobottom')?>: </b>
            </td>
            <td  width="90%">            
            <textarea cols="80" id="editor_bottom" name="editor_bottom" rows="6"><?php echo $obj["bottom_text"];?></textarea><br>
                <script type="text/javascript">
                //<![CDATA[
                        CKEDITOR.replace( 'editor_bottom',{ skin : 'kama' });
                //]]>
                </script>		
            </td>
        </tr>
        
        <tr><td colspan="2" align="right"><input type="button" onclick="javascript:Validar();" value="Aceptar" name="Aceptar"></td></tr>
    </table>
</form>    

<script language="javascript">
    function Validar()
    {
            if (this.f.title.value == '')
            {
                    alert ('<?php echo get_lang("mustinsertvalues")?>');
                    return 0;
            }
            else
            {
                    this.f.submit();
            }
    }
</script>


<?php
        
        
    }
    else
    {
        // load design 
        $sql = "select * from $t_design where id=" . $_POST['id'];
        $res = api_sql_query($sql, __FILE__, __LINE__);
        $obj = Database::fetch_array($res);
    
        if ($_FILES['imagen']['name']!="")
        {
            //New image
            $target_path = api_get_path(SYS_PATH) . 'main/gestor/diplomas/design/' . basename( $_FILES['imagen']['name']); 
            move_uploaded_file($_FILES['imagen']['tmp_name'], $target_path);   
            $imagen = basename( $_FILES['imagen']['name']);
            
        }
        else
        {
            //same one
            $imagen = $obj["image"];
        }
        
        // save
        $sql = "update $t_design set image='" .$imagen . "', title='". $_POST["title"] . "',up_text='". $_POST["editor_up"] . "',center_text='". $_POST["editor_center"] . "',bottom_text='". $_POST["editor_bottom"] . "' where id=". $_POST["id"];
        $res = api_sql_query($sql, __FILE__, __LINE__);
        
        if ($res==1)
            {
                Display::display_confirmation_message(get_lang("editok"));
            }
        else
            {
                Display::display_error_message(get_lang("editfail"));
            }
    }   
}
?>
<?php

/*
==============================================================================
		FOOTER
==============================================================================
*/
Display::display_footer();

?>