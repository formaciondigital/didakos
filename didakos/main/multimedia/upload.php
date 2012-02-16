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

$language_file = 'multimedia';
include("../inc/global.inc.php");
include("funciones.php");

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

if($is_allowed_to_edit) //admin for "regular" upload, no group documents
{
	$to_group_id = 0;
	$req_gid = '';
}
else  //no course admin and no group member...
{
	api_not_allowed(true);
}

//what's the current path?
if(isset($_GET['path']) && $_GET['path']!='')
{
	$path = $_GET['path'];
}
elseif (isset($_POST['curdirpath']))
{
	$path = $_POST['curdirpath'];
}
else
{
	$path = '/';
}

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
if(!DocumentManager::get_document_id($_course,$path))
{
	$path = '/';
}

//if we want to unzip a file, we need the library
if (isset($_POST['unzip']) && $_POST['unzip'] == 1)
{
	include(api_get_path(LIBRARY_PATH).'pclzip/pclzip.lib.php');
}
/*
-----------------------------------------------------------
	Variables
-----------------------------------------------------------
*/
$max_filled_space = DocumentManager::get_course_quota();

/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/

$nameTools = get_lang("AddMultimedia");
$interbreadcrumb[]=array("url"=>"./index.php?", "name"=> get_lang("Multimedia"));
Display::display_header($nameTools,"Doc");
api_display_tool_title($nameTools);


/*
-----------------------------------------------------------
	Here we do all the work
-----------------------------------------------------------
*/

//user has submitted a file
if(isset($_FILES['archivo']))
{
	
	//echo("<pre>");
	//print_r($_FILES['user_upload']);
	//echo("</pre>");

	$upload_ok = process_uploaded_file($_FILES['archivo']);
	if($upload_ok)
	{
		//Esto sube el archivo e inserta una fila en la tabla
		$new_path = handle_uploaded_multimedia($_course, $_FILES['archivo'],$base_work_dir,$_POST['curdirpath'],$_user['user_id'],$to_group_id,$to_user_id,$max_filled_space,$_POST['unzip'],$_POST['if_exists']);
		//Ahora completamos los datos
	   	$new_title = trim($_POST['title']);    	
		$new_comment = trim($_POST['description']);
	   	$new_duration = ($_POST['min']*60) + $_POST['seg'];
		$new_tipo = $_POST['tipo'];
    	if ($new_path && $new_title)
    	if (($docid = DocumentManager::get_multimedia_id($_course, $new_path)))
    	{
        	$table_document = Database::get_course_table(TABLE_MULTIMEDIA);
        	$ct = '';
        	api_sql_query("UPDATE $table_document SET title='" . $new_title . "',description='". $new_comment ."',duration=" . $new_duration . ",source_id=".$new_tipo."
        	WHERE id = '$docid'", __FILE__, __LINE__);	
	        //Actualizamos el orden
	        $sql = "update " . $table_document . " set orden=" .  GetLastElementOrder() . " where orden=0";
        	$res = api_sql_query($sql, __FILE__, __LINE__);
    	}
		//check for missing images in html files
		$missing_files = check_for_missing_files($base_work_dir.$new_path);
		if($missing_files)
		{
			//show a form to upload the missing files
			Display::display_normal_message(build_missing_files_form($missing_files,$_POST['curdirpath'],$_FILES['archivo']['name']),false);
		}
	}
}
else
{
	if (isset($_POST["title"]))
	{
		//submit sin files
		$table_document = Database::get_course_table(TABLE_MULTIMEDIA);
		$duration = ($_POST['min']*60) + $_POST['seg'];
		$sql = "INSERT into " . $table_document . " (title,description,duration,source_id,target,date,user_id,orden) 
		values ('" . $_POST["title"] . "','". $_POST["description"] ."'," . $duration . ",". $_POST['tipo'].",'". $_POST['url'] . "',now(),".api_get_user_id().",0)";
		$res = api_sql_query($sql, __FILE__, __LINE__);    
	    $sql = "update " . $table_document . " set orden=" . GetLastElementOrder() . " where orden=0";
    	$res = api_sql_query($sql, __FILE__, __LINE__);
		if ($res==1)
		{
			Display::display_confirmation_message(get_lang("ContenidoExternoCorrecto"));
		}
		else
		{
			Display::display_error_message(get_lang("ErrorInsertar"));
		}
	}
}


//tracking not needed here?
//event_access_tool(TOOL_DOCUMENT);

/*============================================================================*/
?>

<?php
if ( !isset($_GET["tipo"]) )
{
?>
<table width="100%" cellpadding="0" cellspacing="20">
	<tr>
		<td width="50%" valign="top" style="border:1px #4171B5 solid; padding: 4px;">
			<ul>
			    <li><a href="upload.php?tipo=interno&<?php echo api_get_cidreq();?>"><?php echo get_lang("SubirArchivo"); ?></a><br/>
			    <?php echo get_lang("SubirArchivoDesc"); ?>
			    </li>
			    <li><a href="upload.php?tipo=externo&<?php echo api_get_cidreq();?>"><?php echo get_lang("SubirExterno"); ?></a><br/>
			    <?php echo get_lang("SubirExternoDesc");  ?>
			    </li>
 				<li><a href="index.php?<?php echo api_get_cidreq();?>"><?php echo get_lang("VerContenido"); ?></a><br/>
			    <?php echo get_lang("VerContenidoDesc");  ?>
			    </li>
		    </ul>
		</td>
	</tr>
</table>
<?php
	}
else
	{
		// Está definido el tipo de subida.
		// mostramos el formulario dependiente del tipo seleccionado
		if (isset($_GET["tipo"]) && $_GET["tipo"]=="interno")
		{
			// Formulario de subida de un archivo
			?>
			<script languaje="javascript">
				function validar()
				{
				  	//valido el nombre 
				   	if (document.upload.title.value.length==0){ 
				      	 alert("<?php echo get_lang("ValidarTitulo"); ?>") 
				      	 document.upload.title.focus() 
				      	 return 0; } 
					else
					{
						if (document.upload.description.value.length==0){ 
					      	 alert("<?php echo get_lang("ValidarDescripcion"); ?>") 
					      	 document.upload.description.focus() 
					      	 return 0; }
						else
						{
							if (document.upload.archivo.value.length==0){ 
						      	 alert("<?php echo get_lang("ValidarArchivo"); ?>") 
						      	 document.upload.archivo.focus() 
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
									alert('<?php echo get_lang("ValidarItems"); ?>');
									return 0;				
								}
							}
						}
				   	} 
				}
			</script>
			<p> <?php echo get_lang("TodosObligatoriosInt"); ?></p>			
			<table>
			<form name="upload" action="upload.php" method="post" enctype="multipart/form-data"">			
				<tr>								
					<td width="100"><?php echo get_lang("SeleccionarArchivo"); ?></td>
					<td width="100"><input name="archivo" type="file"></td>
				</tr>
				<tr>
					<td width="50"><?php echo get_lang("Titulo"); ?>:</td>
					<td><input type="text" name="title" size="45"></td>
				</tr>
				<tr>
					<td><?php echo get_lang("Descripcion"); ?>:</td>
					<td><textarea name="description" wrap="virtual" style="width:300px;"></textarea></td>
				</tr>
				<tr>
					<td><?php echo get_lang("Duracion"); ?>:</td>
					<td><input type="text" name="min" size="1"><?php echo get_lang("Minutos"); ?><input type="text" name="seg" size="1"><?php echo get_lang("Segundos"); ?></td>
				</tr>							
				<tr>
					<td width="100"><?php echo get_lang("Tipo"); ?></td>
					<td width="100">
						<input type="radio" name="tipo" value="5">mp3 <br>
						<!--<input type="radio" name="tipo" value="6">flv <br> eliminamos temporalmente-->
					</td>
				</tr>
				<tr><td colspan="2" align="right"><input type="button" value="<?php echo get_lang("Aceptar"); ?>" onclick="validar();"></td></tr>
			</form>
			</table>
			<?php
		}
		else
		{
			// Formulario de subida de una URL
			?>
			<script languaje="javascript">
				function validar()
				{
				  	//valido el nombre 
				   	if (document.upload.title.value.length==0){ 
				      	 alert("<?php echo get_lang("ValidarTitulo"); ?>") 
				      	 document.upload.title.focus() 
				      	 return 0; } 
					else
					{
						if (document.upload.description.value.length==0){ 
					      	 alert("<?php echo get_lang("ValidarDescripcion"); ?>") 
					      	 document.upload.description.focus() 
					      	 return 0; }
						else
						{
							if (document.upload.url.value.length==0){ 
						      	 alert("<?php echo get_lang("ValidarId"); ?>") 
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
									alert('<?php echo get_lang("ValidarItems"); ?>');
									return 0;				
								}
							}
						}
				   	} 
				}
			</script>
			<p><?php echo get_lang("TodosObligatoriosExt"); ?></p>			
			<table>
			<form name="upload" action="upload.php" method="post" enctype="multipart/form-data" onsubmit="">
				<tr>
					<td width="100"><?php echo get_lang("IdVideo"); ?></td>
					<td width="100"><input name="url" type="text" size="45"></td>
				</tr>
				<tr>
					<td width="50"><?php echo get_lang("Titulo"); ?>:</td>
					<td><input type="text" name="title" size="45"></td>
				</tr>
				<tr>
					<td><?php echo get_lang("Descripcion"); ?>:</td>
					<td><textarea name="description" wrap="virtual" style="width:300px;"></textarea></td>
				</tr>
				<tr>
					<td><?php echo get_lang("Duracion"); ?>:</td>
					<td><input type="text" name="min" size="1"><?php echo get_lang("Minutos"); ?><input type="text" name="seg" size="1"><?php echo get_lang("Segundos"); ?></td>
				</tr>			
				<tr>
					<td width="100"><?php echo get_lang("Tipo"); ?></td>
					<td width="100">
						<input type="radio" name="tipo" value="1">Youtube <br>
						<input type="radio" name="tipo" value="2">Vimeo <br>
						<input type="radio" name="tipo" value="3">Dailymotion <br>
						<input type="radio" name="tipo" value="4">Ivoox <br>
					</td>
				</tr>
				<tr><td colspan="2" align="right"><input type="button" value="<?php echo get_lang("Aceptar"); ?>" onclick="validar();"></td></tr>
			</form>
			</table>
			<?php
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
