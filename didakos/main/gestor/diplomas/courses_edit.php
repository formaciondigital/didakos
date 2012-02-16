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
$t_diplomas_course = Database :: get_main_table(TABLE_DIPLOMAS_COURSES);
$t_course = Database :: get_main_table(TABLE_MAIN_COURSE);
//navigation
$interbreadcrumb[] = array ("url" => '../index.php', "name" => get_lang('PlatformAdmin'));
$tool_name = get_lang('courseedit');


if(sizeof($_POST)) 
{
    // save data and redirect to list.
    $sql = "update $t_diplomas_course set design_id =". $_POST["design"] .",back_text='". $_POST["editor_back"]."' where course_code='".$_POST["course_code"]."'";
    $res = api_sql_query($sql, __FILE__, __LINE__);
    header('Location: courses.php?messageedit=ok');
}
else
{
    $course_code= $_GET["code"];
    //Look for actual data
    $sql = "select d.id,d.title,c.back_text from $t_design d, $t_diplomas_course c where d.id=c.design_id and c.course_code='" .$course_code . "'";
    $res = api_sql_query($sql, __FILE__, __LINE__);
    $actual_design = Database::fetch_row($res);
    
    $sql = "select id,title from $t_design order by title";
    $res = api_sql_query($sql, __FILE__, __LINE__);
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
?>

<form target="_self" name="f" method="POST" >
    <table> 
        <tr>
            <td width="10%">
                <b><?php echo get_lang("course");?></b>
            </td>
            <td>
                <?php
                // get_course_name
                $sql = "select title from $t_course where code='" . $course_code ."'";
                $res2 = api_sql_query($sql, __FILE__, __LINE__);
                $course = Database::fetch_row($res2);
                echo $course_code . ' ' . $course[0];
                ?>
            </td>
        </tr>
        
        <tr>
            <td width="10%">
                <b><?php echo get_lang("design_selection");?></b>
            </td>
            <td  width="90%"><input type="hidden" name="course_code" value="<?=$course_code?>" />
                <select name="design">
                    <?php
                        while ($design = Database::fetch_row($res))
                        {
                            if ($design[0]==$actual_design[0])
                            {
                                echo '<option value="'.$design[0].'" selected>'.$design[1].'</option>';
                            }
                            else
                            {
                                echo '<option value="'.$design[0].'">'.$design[1].'</option>';
                            }
                        }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td width="10%">
              <b><?=get_lang('contenidoback')?>: </b>
            </td>
            <td  width="90%">            
            <textarea cols="80" id="editor_back" name="editor_back" rows="8"><?php echo $actual_design[2];?></textarea><br>
                <script type="text/javascript">
                //<![CDATA[
                        CKEDITOR.replace( 'editor_back',{ skin : 'kama' });
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
            if (this.f.design.value == '')
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
/*
==============================================================================
		FOOTER
==============================================================================
*/
Display::display_footer();

?>