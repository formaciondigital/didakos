<?php

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

// Database table definitions
$t_config = Database::get_main_table(TABLE_DIPLOMAS_CONFIG);
$t_design = Database::get_main_table(TABLE_DIPLOMAS_DESIGN);
                
//navegation
$interbreadcrumb[] = array ("url" => '../index.php', "name" => get_lang('PlatformAdmin'));
$tool_name = get_lang('dipconfig');

/*
==============================================================================
		HEADER
==============================================================================
*/

Display::display_header($tool_name);

if(sizeof($_POST)) 
{
    // POST
    // save all new config
    
    $sql = "update $t_config set value='". $_POST["globalscorevalue"]. "',status='".(isset($_POST["globalscorecheck"]) ? "1":"0")."' where property='GLOBALSCORE'";
    $res= api_sql_query($sql, __FILE__, __LINE__);
    $sql = "update $t_config set value='". $_POST["quizaveragevalue"]. "',status='".(isset($_POST["quizaveragecheck"]) ? "1":"0")."' where property='QUIZSCOREAVERAGE'";
    $res= api_sql_query($sql, __FILE__, __LINE__);
    $sql = "update $t_config set value='". $_POST["scoreaveragevalue"]. "',status='".(isset($_POST["scoreaveragecheck"]) ? "1":"0")."' where property='SCOSCOREAVERAGE'";
    $res=api_sql_query($sql, __FILE__, __LINE__);
    $sql = "update $t_config set value='". $_POST["template"]. "',status='1' where property='DEFAULTTEMPLATE'";
    $res= api_sql_query($sql, __FILE__, __LINE__);
    
        if ($res==1)
	{
		Display::display_confirmation_message(get_lang("configok"));
	}
	else
	{
		Display::display_error_message(get_lang("configfail"));
	}
}

// get config from database
$property = array();
$design = array();
$sql = "select * from $t_config order by id";
$result = api_sql_query($sql,__FILE__,__LINE__);
    while ($temp_row = Database::fetch_array($result))
    {
            // Extract all values into variables for easy use
            switch ($temp_row["property"])
            {
                case "GLOBALSCORE":
                    $gs_value = $temp_row["value"];
                    $gs_status = ($temp_row["status"]==0 ? "unchecked" : "checked");
                    break;
                case "QUIZSCOREAVERAGE":
                    $qs_value = $temp_row["value"];
                    $qs_status = ($temp_row["status"]==0 ? "unchecked" : "checked");
                    break;
                case "SCOSCOREAVERAGE":
                    $ss_value = $temp_row["value"];
                    $ss_status = ($temp_row["status"]==0 ? "unchecked" : "checked");
                    break;
                   case "DEFAULTTEMPLATE":
                    $dt_value = $temp_row["value"];
                    // Get all templates
                    $sql = "select id,title from $t_design order by id";
                    $result2 = api_sql_query($sql,__FILE__,__LINE__);
                    while ($template_row = Database::fetch_array($result2))
                    {
                        $design[] = $template_row;
                    }
                    break;
                default:
            }
    }
?>

<form target="_self" name="f" method="POST">
    
    <table> 
                <tr><td colspan="4"><p><? echo get_lang("configdesc");?></p></td></tr>
		<tr>
                        <td valign="top" width="10"><input type="checkbox" <?php echo $gs_status; ?> name="globalscorecheck"></td>
			<td valign="top" width="150"><b><?php echo get_lang("globalscore");?></b></td>
                        <td valign="top">
                            <select name="globalscorevalue">
                                <?php
                                    //Fill options
                                    for ($c=50;$c<=100;$c+=10)
                                    {
                                        if ($c==$gs_value)
                                        {
                                            echo '<option value="'.$c.'" selected>'.$c.'</option>';
                                        }
                                        else
                                        {
                                            echo '<option value="'.$c.'">'.$c.'</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </td>
                        <td><?php echo get_lang("globalscoredesc");?></td>
		</tr>
                <tr>
                        <td valign="top" width="10"><input type="checkbox" <?php echo $qs_status; ?> name="quizaveragecheck"></td>
			<td valign="top" width="150"><b><?php echo get_lang("quizaverage");?></b></td>
                        <td valign="top">
                            <select name="quizaveragevalue">
                            <?php
                                    //Fill options
                                    for ($c=50;$c<=100;$c+=10)
                                    {
                                        if ($c==$qs_value)
                                        {
                                            echo '<option value="'.$c.'" selected>'.$c.'</option>';
                                        }
                                        else
                                        {
                                            echo '<option value="'.$c.'">'.$c.'</option>';
                                        }
                                    }
                                ?>        
                            </select>
                        </td>
                        <td><?php echo get_lang("quizaveragedesc");?></td>
		</tr>
                <tr>
                        <td valign="top" width="10"><input type="checkbox" <?php echo $ss_status; ?> name="scoreaveragecheck"></td>
			<td valign="top" width="150"><b><?php echo get_lang("scoreaverage");?></b></td>
                        <td valign="top">
                            <select name="scoreaveragevalue">
                            <?php
                                    //Fill options
                                    for ($c=50;$c<=100;$c+=10)
                                    {
                                        if ($c==$ss_value)
                                        {
                                            echo '<option value="'.$c.'" selected>'.$c.'</option>';
                                        }
                                        else
                                        {
                                            echo '<option value="'.$c.'">'.$c.'</option>';
                                        }
                                    }
                                ?>        
                            </select>
                        </td>
                        <td><?php echo get_lang("scoreaveragedesc");?></td>
		</tr>
                <tr>
                        <td valign="top" width="10"></td>
			<td valign="top" width="150"><b><?php echo get_lang("template");?></b></td>
                        <td valign="top">
                            <select name="template">
                                <?php
                                    foreach ($design as $template)
                                    {
                                        if ($template["id"] == $dt_value)
                                        {
                                            echo '<option value="'.$template["id"].'" selected>'.$template["title"].'</option>';
                                        }
                                        else
                                        {
                                            echo '<option value="'.$template["id"].'">'.$template["title"].'</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </td>
                         <td><?php echo get_lang("templatedesc");?></td>
		</tr>
                <tr><td colspan="4" align="right"><input type="button" onclick="javascript:Validar();" value="Aceptar" name="Aceptar"></td></tr>
    </table>
                
    
    
    
    
</form>


<script language="javascript">
    function Validar()
    {
            if (!this.f.globalscorecheck.checked && !this.f.quizaveragecheck.checked && !this.f.scoreaveragecheck.checked)
            {
                    alert ('<?php echo get_lang("mustselectone")?>');
                    return 0;
            }
            else
            {
                    this.f.submit();
            }
    }
</script>
                
<?php

/*
==============================================================================
		FOOTER
==============================================================================
*/

Display::display_footer();

?>