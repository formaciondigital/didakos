<?php 

// $Id: upload.php 14802 2008-04-09 12:53:59Z elixir_inter $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004-2005 Dokeos S.A.
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) various contributors

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

==============================================================================
*/

/*
==============================================================================
		INIT SECTION
==============================================================================
*/

$language_file[] = 'document';
$language_file[] = 'multimedia';
include("../inc/global.inc.php");


/*
-----------------------------------------------------------
	Variables
	- some need defining before inclusion of libraries
-----------------------------------------------------------
*/
$is_allowed_to_edit = api_is_allowed_to_edit();
$courseDir   = $_course['path']."/multimedia";
$sys_course_path = api_get_path(SYS_COURSE_PATH);
$base_work_dir = $sys_course_path.$courseDir;
$noPHP_SELF=true;

api_protect_course_script();

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/

//many useful functions in main_api.lib.php, by default included

include_once(api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php');
include_once(api_get_path(LIBRARY_PATH) . 'events.lib.inc.php');
include_once(api_get_path(LIBRARY_PATH) . 'document.lib.php');

//check the path
//if the path is not found (no document id), set the path to /

/*
-----------------------------------------------------------
	Variables
-----------------------------------------------------------
*/
function get_multimedia($id)
{
	$multimedia_table = Database :: get_course_table(TABLE_MULTIMEDIA);
	$sql = 'SELECT * FROM ' .$multimedia_table. ' where id=' . $id;
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$multimedia = array ();
	$element = Database::fetch_row($res);
	return $element;
}
/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/

$nameTools = get_lang('EditarContenidoMultimedia');
$interbreadcrumb[]=array("url"=>"./index.php?", "name"=> 'Multimedia');
Display::display_header($nameTools,"Doc");
api_display_tool_title($nameTools);


if(isset($_POST['title']))
{
	$multimedia_table = Database :: get_course_table(TABLE_MULTIMEDIA);
	// hacemos update
	if ($_POST['tipo']==5)
		{
			$sql = "update  $multimedia_table  set title='" . $_POST['title']. "' ,description='" . $_POST['description']. "' ,duration=" . (($_POST['min']*60) + $_POST['seg']) . " where id=" . $_POST['id']; 
			$result = api_sql_query($sql, __FILE__, __LINE__);
			if ($result==1)
			{
				Display::display_confirmation_message("<?php echo get_lang('ContenidoModificadoCorrectamente'); ?>");
			}
			else
			{
				Display::display_error_message("<?php echo get_lang('ErrorAlModificar'); ?>");
			}
		}
	else
		{
			$sql = "update  $multimedia_table  set title='" . $_POST['title']. "' ,description='" . $_POST['description']. "' ,duration=" . (($_POST['min']*60) + $_POST['seg']) . ",target='" .$_POST['url']. "',source_id=" . $_POST['tipo'] . " where id=" . $_POST['id']; 
			$result = api_sql_query($sql, __FILE__, __LINE__);
			if ($result==1)
			{
				Display::display_confirmation_message(get_lang('ContenidoModificadoCorrectamente'));
			}
			else
			{
				Display::display_error_message(get_lang('ErrorAlModificar'));
			}
		}

	echo '<p align="center"><a href="index.php">Volver al listado </a></p>';

}
else
{
	// mostramos formulario
	$multimedia = get_multimedia($_GET['id']);
	$minutos = floor($multimedia[3]/60);
	$segundos = $multimedia[3]%60;

	if ($_GET['source_id']==5)
	{
		//archivo interno
	?>
		<script languaje="javascript">
			function validar()
			{
			  	//valido el nombre 
			   	if (document.upload.title.value.length==0){ 
			      	 alert("<?php echo get_lang('DebeInsertarTitulo'); ?>") 
			      	 document.upload.title.focus() 
			      	 return 0; } 
				else
				{
					if (document.upload.description.value.length==0){ 
				      	 alert("<?php echo get_lang('DebeInsertarDescripcion'); ?>") 
				      	 document.upload.description.focus() 
				      	 return 0; }
					else
					{
						document.upload.submit();
					}
			   	} 
			}
		</script>
		<p><?php echo get_lang('LosCamposMostradosSonModificables'); ?></p>
		<table>
		<form name="upload" action="edit.php" method="post" enctype="multipart/form-data"">			
			<tr>
				<td width="50"><?php echo get_lang('Titulo'); ?>:</td>
				<td>
					<input type="text" name="title" size="45" value="<?php echo $multimedia[1]; ?>">
					<input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
					<input type="hidden" name="tipo" value="<?php echo $_GET['source_id']; ?>">
				</td>
			</tr>
			<tr>
				<td><?php echo get_lang('Descripcion'); ?>:</td>
				<td><textarea name="description" wrap="virtual" style="width:300px;"><?php echo $multimedia[2]; ?></textarea></td>
			</tr>
			<tr>
				<td><?php echo get_lang('Duracion'); ?>:</td>
				<td><input type="text" name="min" size="1" value="<?php echo $minutos; ?>"><?php echo get_lang('Minutos'); ?><input type="text" name="seg" size="1" value="<?php echo $segundos; ?>"><?php echo get_lang('Segundos'); ?></td>
			</tr>							
			<tr><td colspan="2" align="right"><input type="button" value="<?php echo get_lang('Ok'); ?>" onclick="validar();"></td></tr>
		</form>
		</table>
	<?php
	}
	else
	{
		// archivo externo
	?>
		<script languaje="javascript">
			function validar()
			{
			  	//valido el nombre 
			   	if (document.upload.title.value.length==0){ 
			      	 alert("<?php echo get_lang('DebeInsertarTitulo'); ?>") 
			      	 document.upload.title.focus() 
			      	 return 0; } 
				else
				{
					if (document.upload.description.value.length==0){ 
				      	 alert("<?php echo get_lang('DebeInsertarDescripcion'); ?>") 
				      	 document.upload.description.focus() 
				      	 return 0; }
					else
					{
						if (document.upload.url.value.length==0){ 
					      	 alert("<?php echo get_lang('DebeInsertarIdentificadorExterno'); ?>") 
					      	 document.upload.url.focus() 
					      	 return 0; }
						else 
						{
							var marcado=0; 
							var i=0;
							for(i=0; ele=document.upload.elements[i]; i++)
							{ 
								if (ele.type=='radio') 
								{
									if (ele.checked)
									{
										marcado=1;
										document.upload.submit();
									} 							
								}
							} 
							if (marcado==0)	
							{
								alert('<?php echo get_lang('DebeSeleccionarItem'); ?>');
								return 0;				
							}
						}
					}
			   	} 
			}
		</script>
		<table>
		<form name="upload" action="edit.php" method="post" enctype="multipart/form-data" onsubmit="">
			<tr>
				<td width="100"><?php echo get_lang('IDVideoExterno'); ?></td>
				<td width="100"><input name="url" type="text" size="45" value="<?php echo $multimedia[5]; ?>"></td>
			</tr>
			<tr>
				<td width="50"><?php echo get_lang('Titulo'); ?>:</td>
				<td>
					<input type="text" name="title" size="45" value="<?php echo $multimedia[1]; ?>">
					<input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
				</td>
			</tr>
			<tr>
				<td><?php echo get_lang('Descripcion'); ?>:</td>
				<td><textarea name="description" wrap="virtual" style="width:300px;"><?php echo $multimedia[2]; ?></textarea></td>
			</tr>
			<tr>
				<td><?php echo get_lang('Duracion'); ?>:</td>
				<td><input type="text" name="min" size="1" value="<?php echo $minutos; ?>"><?php echo get_lang('Minutos'); ?> <input type="text" name="seg" size="1" value="<?php echo $segundos; ?>"><?php echo get_lang('Segundos'); ?></td>
			</tr>			
			<tr>
				<td width="100"><?php echo get_lang('Tipo'); ?></td>
				<td width="100">
					<input type="radio" name="tipo" value="1" <?php if ($multimedia[4]==1) echo "checked";?>>Youtube <br>
					<input type="radio" name="tipo" value="2" <?php if ($multimedia[4]==2) echo "checked";?>>Vimeo <br>
					<input type="radio" name="tipo" value="3" <?php if ($multimedia[4]==3) echo "checked";?>>Dailymotion <br>
					<input type="radio" name="tipo" value="4" <?php if ($multimedia[4]==4) echo "checked";?>>Ivoox <br>
				</td>
			</tr>
			<tr><td colspan="2" align="right"><input type="button" value="<?php echo get_lang('Ok'); ?>" onclick="validar();"></td></tr>
		</form>
		</table>
	<?php
	}
}

/*============================================================================*/

/*
==============================================================================
		FOOTER
==============================================================================
*/
Display::display_footer();
?>
