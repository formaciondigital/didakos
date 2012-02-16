<?php
/*
==============================================================================
		INIT SECTION
==============================================================================
*/ 
// global settings initialisation 
// also provides access to main, database and display API libraries
$language_file[] = 'glosario';

include("../inc/global.inc.php"); 
$libpath = api_get_path(LIBRARY_PATH);
require_once ($libpath.'formvalidator/FormValidator.class.php');

$dbl_click_id = 0; // used to avoid double-click
$is_allowed_to_edit = api_is_allowed_to_edit();
//$curdirpathurl = 'glosario';
api_protect_course_script();
$web_code_path = api_get_path(WEB_CODE_PATH);

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/ 
//the main_api.lib.php, database.lib.php and display.lib.php
//libraries are included by default
/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/ 

$tool_name = get_lang('GlosarioAdd'); // title of the page (should come from the language file) 
$interbreadcrumb[]=array("url"=>"./index.php?letra=a", "name"=> get_lang("Glosario"));
Display::display_header($tool_name);
api_display_tool_title($tool_name);
/*
==============================================================================
		FUNCTIONS
==============================================================================

*/
if (isset($_POST['palabra']) && isset($_POST['descripcion']) && $_POST['descripcion']!='' && $_POST['palabra']!='')
{
    //Guardamos y redirigimos con mensaje a la index.php
    $table_glosario = Database::get_course_table(TABLE_GLOSARIO);
	$sql = "INSERT into " . $table_glosario . " (palabra,descripcion) 
	values ('" . $_POST["palabra"] . "','". $_POST["descripcion"] ."')";
	$res = api_sql_query($sql, __FILE__, __LINE__);
    	if ($res==1)
		{
			Display::display_confirmation_message(get_lang("ContenidoAdd"));
		}
		else
		{
			Display::display_error_message(get_lang("ErrorInsertar"));
		}
}

?>
<form name="add" action="glosarioAdd.php" METHOD="POST">
	<table>
		<tr>
			<td width="30"></td>
			<td valign="top" width="125"><b><?php echo get_lang("Palabra"); ?></b></td>
			<td valign="top" width="175"><input type="text" name="palabra" MAXLENGTH="150" size="65"></td>
			<td valign="top"><i><?php echo get_lang("CampoObligatorio"); ?></i></td>
		</tr>
		<tr>
			<td width="30"></td>
			<td valign="top" width="125"><b><?php echo get_lang("Descripcion"); ?></b></td>
			<td valign="top" width="175"><textarea name="descripcion" cols="50" rows="10"></textarea></td>
			<td valign="top"><i><?php echo get_lang("CampoObligatorio"); ?></i></td>
		</tr>
		<tr>
			<td width="30"></td>
			<td valign="top" width="125"></td>
			<td valign="top" width="175" align="right"><input type="submit" value="<?php echo get_lang('Aceptar');?>"></td>
			<td valign="top"></td>
		</tr>
	</table>
</form>

<?php
Display::display_footer(); 
?>

