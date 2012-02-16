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
*	This file is a code template; 
*	copy the code and paste it in a new file to begin your own work.
*
*	@package dokeos.plugin
==============================================================================
*/
	
/*
==============================================================================
		INIT SECTION
==============================================================================
*/ 
// global settings initialisation 
// also provides access to main, database and display API libraries
$language_file[] = 'document';
$language_file[] = 'slideshow';
$language_file[] = 'podcast';
include("../inc/global.inc.php"); 
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
$this_section=SECTION_COURSES;
$dbl_click_id = 0; // used to avoid double-click
$is_allowed_to_edit = api_is_allowed_to_edit();
$curdirpathurl = 'podcast';
api_protect_course_script();

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


$tool_name = "Podcast"; // title of the page (should come from the language file) 
Display::display_header($tool_name);
api_display_tool_title($tool_name);
$interbreadcrumb[]= array ('url'=>'', 'name'=> 'Podcast');
$dir_array=explode("/",$curdirpath);
$array_len=count($dir_array);
$is_allowed_to_edit  = api_is_allowed_to_edit();
	
/*
==============================================================================
		FUNCTIONS
==============================================================================
*/ 

/**
 * Get the users to display on the current page.
 * @see SortableTable#get_table_data($from)
 */
function get_podcast($from, $number_of_items, $column, $direction)
{
	$user_table = Database :: get_course_table(TABLE_PODCAST);
	$sql = "SELECT
		u.title 	AS col0,
		u.comment 	AS col1,
		round(((u.size/1024)/1024),2)      AS col2,
		u.id		AS col3
             FROM
                 $user_table u";
	
	$sql .= " ORDER BY col0";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$users = array ();
	while ($user = Database::fetch_row($res))
	{
		$users[] = $user;
	}
	return $users;
}

function get_number_of_podcast()
{
	$user_table = Database :: get_course_table(TABLE_PODCAST);
	$sql = "SELECT
                 count(*) AS total_number_of_items
             FROM
                 $user_table u";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_object($res);
	return $obj->total_number_of_items;
}

function modify_filter($user_id,$url_params,$row)
{
	global $charset;
	
	//editar deshabilitado por ahora
	//$result .= '<a href="podcast.php?action=edit&id='.$user_id.'&cidReq=' .  $_GET['cidReq'] . '"><img src="../img/edit.gif" border="0" style="vertical-align: middle;" title="'.get_lang('Edit').'" alt="'.get_lang('Edit').'"/></a>&nbsp;';
	$result .= '<a href="podcast.php?action=delete&id='.$user_id.'&cidReq=' .  $_GET['cidReq'] . '"><img src="../img/delete.gif" border="0" style="vertical-align: middle;" title="'.get_lang('Delete').'" alt="'.get_lang('Delete').'"/></a>&nbsp;';
	return $result;
}

function delete_podcast ($id)
{
	$user_table = Database::get_course_table(TABLE_PODCAST);
	//Sacamos el path del archivo
	$sql = "select path from $user_table where id= $id";
       	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_array($res);
	//borramos archivo
	$path = "../../courses/" . $_GET['cidReq'] . "/podcast" . $obj["path"];
	unlink($path);
	//borramos fila
       	$sql = "delete from $user_table where id= $id";
       	api_sql_query($sql, __FILE__, __LINE__);
	return true;
}

function limpiar_acentos($cadena){
$tofind = "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿ";
$replac = "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuy";
return(strtr($cadena,$tofind,$replac));
}

//Esta es la funcion que genera el XML y el XSPF
function createplaylist ()
{
	// requerimos los archivos necesarios de la bibilioteca php que obtiene datos de
	// los ficheros mp3
	require_once('getid3/getid3/getid3.php');
	
	//nombre del curso
	$curso_nombre = $_SESSION['_course']['name'];
	//datos de rutas
	$dominio = api_get_path('WEB_COURSE_PATH');
	$curso = $_GET['cidReq'];
	$carpeta = "/podcast/";
	$dir = "../../courses/" . $_GET['cidReq'] . "/podcast";

	//*****************************************************
	$rss_titulo = 'Podcast del curso ' . $curso_nombre;
	$rss_url = "$dominio$curso$carpeta" . 'playlist.xml';
	$rss_docs = $dominio . $curso . $carpeta;
	$rss_descripcion = 'Podcast del curso ' . $curso_nombre;

	//*****************************************************

	//borramos los archivos anteriores si existen
	if (file_exists($dir .'/playlist.xml'))
	{
		unlink($dir .'/playlist.xml');	
		unlink($dir .'/playlist.xspf');	
	}

	//creamos los archivos
	$getid3 = new getID3;
	$getid3->encoding = 'UTF-8';
	//header('Content-Type: text/html; charset=UTF-8');
	
	    if ($gd = opendir($dir)) 
		{
			//------------- PLAYLIST --------------------------------
			$archivo = $dir . "/playlist.xspf"; 
			$puntero = fopen($archivo, 'w+'); 
			$ContenidoFinal =  '<?xml version="1.0" encoding="UTF-8"?>';
			$ContenidoFinal .=  '<playlist version="1" xmlns="http://xspf.org/ns/0/">';
			$ContenidoFinal .= '<title>Podcast</title>';
			$ContenidoFinal .= '<trackList>';
			// ---------- RSS ------------------------------------
			$rss = $dir . "/playlist.xml"; 
			$puntero2 = fopen($rss, 'w+'); 
			$ContenidoRSS = '<?xml version="1.0" encoding="iso-8859-1"?>';
			$ContenidoRSS .=
			'<rss version="0.92">
			  <channel>
		 		<docs>'.$rss_docs.'</docs>
				<title>'.$rss_titulo.'</title>
			  		<link>'.$rss_url.'</link>
				<description>'.$rss_descripcion.'</description>
		 		<language>es</language>';
			//------------------ELEMENTOS----------------------------------
			while (($archivo = readdir($gd)) !== false) 
			{
					if(strstr($archivo,'.mp3'))
					//buscamos solos archivos mp3
					{
					$ContenidoFinal .= "<track>";				
					$ContenidoFinal .= "<location>";				
					$ContenidoFinal .= "$dominio$curso$carpeta$archivo";
					$ContenidoFinal .= "</location>";

					try { 
					$getid3->Analyze($dir."/".$archivo);
					//---------- PLAYLIST item ----------------
					$ContenidoFinal .="<title>";				
					$ContenidoFinal .= @$getid3->info['id3v1']['title'];
					$ContenidoFinal .= "</title>";				
				
					$ContenidoFinal .= "<album>";				
					$ContenidoFinal .= @$getid3->info['id3v1']['album'];
					$ContenidoFinal .= "</album>";				
				
					$ContenidoFinal .= "<artist>";				
					$ContenidoFinal .= @$getid3->info['id3v1']['artist'];
					$ContenidoFinal .= "</artist>";				
				
					//---------- RSS item ----------------
					$ContenidoRSS .= "<item><title>".@$getid3->info['id3v1']['title']."</title>";
				    $ContenidoRSS .= "<link>$dominio$curso$carpeta$archivo</link>";
				    $ContenidoRSS .= "<description>".@$getid3->info['id3v1']['artist']."</description></item>";

						} 
					catch (Exception $e) 
						{ 
	       			echo 'Error ocurrido: ' .  $e->message; 
	       			echo "<br>";
						}
	 				$ContenidoFinal .= "</track>";	
					}		
				}
	    $ContenidoFinal .= "</trackList>";
		$ContenidoFinal .= "</playlist>";

		$ContenidoRSS .=  "</channel>";
	 	$ContenidoRSS .=  "</rss>";  
	 	
	fwrite($puntero, $ContenidoFinal); 
	fclose($puntero);
	fwrite($puntero2, $ContenidoRSS); 
	fclose($puntero2);    
			}
	closedir($gd);	
	return true;
}

/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

// Put your main code here. Keep this section short,
// it's better to use functions for any non-trivial code

if ($_GET['action'] == "delete")
	{
		$id = $_GET['id'];
		$res = delete_podcast($id);
		if ($res)
		{
			Display::display_confirmation_message(get_lang('PodcastEliminadoOk'));	
		}
		else
		{
		  Display::display_warning_message(get_lang('ErrorEliminar'));
		}
	}

if ($_GET['action'] == "edit")
	{
		$id = $_GET['id'];
		echo get_lang('ModificamosPodcast') .' ' .$id;
	}
if ($_GET['action'] == "publicar")
	{
		$res = createplaylist();
		if ($res)
		{
		  Display::display_confirmation_message(get_lang('PodcastGeneradoOk'));
		}
	}

$playlisturl= api_get_path('WEB_COURSE_PATH') . $_GET['cidReq']. "/podcast/playlist.xspf" ;

if ($is_allowed_to_edit || $group_member_with_upload_rights)
	{
	?>
	<!-- file upload link -->
	<a href="upload.php?<?php echo api_get_cidreq();?>&path=<?php echo $curdirpathurl.$req_gid; ?>"><img src="../img/submit_file.gif" border="0" title="<?php echo get_lang('UplUploadDocument'); ?>" alt="" /></a>
	<a href="upload.php?<?php echo api_get_cidreq();?>&path=<?php echo $curdirpathurl.$req_gid; ?>"><?php echo get_lang('SubirPodcast'); ?></a>&nbsp;
	<!-- generar Xml para sindicación -->
	<a href="podcast.php?<?php echo api_get_cidreq();?>&dir=<?php echo $curdirpathurl.$req_gid; ?>"><img src="../img/filenew.gif" border="0" alt="" title="<?php echo get_lang('PublicarLista'); ?>" /></a>
	<a href="podcast.php?action=publicar&<?php echo api_get_cidreq();?>&dir=<?php echo $curdirpathurl.$req_gid; ?>"><?php echo get_lang('PublicarLista'); ?></a>&nbsp;&nbsp;
		 
	<?php
	// Create a sortable table with user-data
	$parameters['sec_token'] = Security::get_token();
	$table = new SortableTable('document','get_number_of_podcast','get_podcast',0);
	$table->set_additional_parameters($parameters);
	$table->set_header(1, get_lang('Comment'));
	$table->set_header(0, get_lang('Title'));
	$table->set_header(2, get_lang('Size'));
	$table->set_header(3, get_lang('Modify'));
	//$table->set_column_filter(6, 'email_filter');
	//$table->set_column_filter(7, 'status_filter');
	//$table->set_column_filter(8, 'active_filter');
	$table->set_column_filter(3, 'modify_filter');
	$table->display();

	?>
	<br>




	<?php
	}
	
/*
Cargamos reproductor mp3 con una lista de audios sacada de los archivos del curso en /document/audio.
Esta lista la genera el Tutor desde document/audio.
*/
$res = get_number_of_podcast();
if ($res>0)
{
?>

<p><b><?php echo get_lang('Tit_player'); ?></b></p>
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0"
width="400" height="150" >
        <param name="allowScriptAccess" value="sameDomain"/>
        <param name="movie" value="xspf_player/xspf_player.swf?playlist_url=<?php echo $playlisturl;?>"/>
        <param name="quality" value="high"/>
        <param name="bgcolor" value="#E6E6E6"/>
    <embed src="xspf_player/xspf_player.swf?playlist_url=<?php echo $playlisturl;?>"
    quality="high" bgcolor="#E6E6E6" name="xspf_player" allowscriptaccess="sameDomain"
    type="application/x-shockwave-flash"
    pluginspage="http://www.macromedia.com/go/getflashplayer"
    align="center" height="150" width="400"> </embed>
</object>
<?php
$playlisturl= "../../courses/" . $_GET['cidReq']. "/podcast/playlist.xml" ;
?>
<!-- Para todos (suscribirse a los podcast)-->
<br><br>
<a href="<?php echo $playlisturl;?>" target="_blank"><img src="../img/boton_rss_podcast.gif" border="0" alt="" title="<?php echo get_lang('Suscribirse_podcast'); ?>" />&nbsp;<?php echo get_lang('Suscribirse_podcast'); ?></a>&nbsp;&nbsp;
<?php
}
else
{echo '<br><b>'. get_lang('NoHayPodcast') .'</b>';}
/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>
