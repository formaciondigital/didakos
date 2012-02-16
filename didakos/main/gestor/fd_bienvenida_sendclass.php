<?php
// $Id: course_list.php 15245 2008-05-08 16:53:52Z juliomontoya $
/*
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
*	@package dokeos.admin
============================================================================== 
*/
/*
==============================================================================
		Página modificada por Formación Digital

Autor: Eduardo García
Página incial: fd_bienvenida_sendone.php (versión 0.2)
Página actual: fd_bienvenida_sendclass.php
Descripción: Página que muestra un listado de los cursos de un alumno para enviar las cartas de bienvenida
		
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
require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.fd.php');

if (isset($_POST['id']))
{
	//hemos recibido el id de la carta que queremos enviar
	$_SESSION['id_carta'] = $_POST['id'];
}

if (isset($_POST['id_clase']))
{
	//hemos recibido el id de clase
	$_SESSION['id_clase'] = $_POST['id_clase'];
}

function get_course_data($id_clase)
{
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$class_rel_table = Database :: get_main_table(TABLE_MAIN_COURSE_CLASS);

	$sql = "select c.code, c.title from $course_table c, $class_rel_table crc where crc.class_id=$id_clase and crc.course_code=c.code order by c.title";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$courses = array ();

	while ($course = Database::fetch_row($res))
	{
		$course_rem = array($course[0],$course[1]);
		$courses[] = $course_rem;
	}
	return $courses;
}

function get_nombre_curso($id_curso)
{
	$sql = "select title as nombre_curso from course where code='". $id_curso ."'";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_object($res);
	return $obj->nombre_curso;
}


/*
recibe el course_code y devuelve datos de los alumnos (nombre y apellidos y email)
*/

function get_datos_alumnos($course_code, $id_clase)
{
	$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	$matriculaciones_table = Database :: get_main_table(TABLE_MAIN_MATRICULACIONES);
	$class_user_table = Database :: get_main_table(TABLE_MAIN_CLASS_USER);
	$class_course_table = Database :: get_main_table(TABLE_MAIN_COURSE_CLASS);

	$sql = "select firstname,lastname, email, username, password from $user_table u, $class_user_table cut, $class_course_table cct ".
  	$sql = $sql . "where u.user_id = cut.user_id and cut.class_id=$id_clase and cct.class_id= cut.class_id and cct.course_code='$course_code'";

	$res = api_sql_query($sql, __FILE__, __LINE__);
	$users = array ();
	while ($user = Database::fetch_row($res))
	{
		$users[] = $user;
	}
	return $users;
}

//Estas 3 funcines pintan una tabla, esta dividida en tres partes,
// ## FdTablaCabecera: Pinta el inicio de la tabla y la primera fila como cabecera. Recibe un array de una dimensión con tantos valores como columnas deseamos. También puede recibir contenido extra para la tabla, el TR y el TH (html)
// ## FdTablaFila: Pinta las filas recibiendo un array bidimensional (fila,columna). También puede recibir contenido extra para la tabla, el TD (html)
// ## FdTablaFila: Pinta el fron de tabla
// *****************************************************************************************
function FdTablaCabecera($columnas,$extraTabla=null, $extraTR=null, $extraTH=null)
{
	$tabla = '<table ' . $extraTabla .  '><thead><tr ' . $extraTR .'>';
	for ($i = 0; $i < count($columnas); $i++)
		{
		$tabla .= '<th ' . $extraTH . '><b>' . $columnas[$i] . '</b></th>'; 
		}
	$tabla .= '</tr></thead>';
	echo $tabla;
}

//esta función pinta todas las filas de una tabla, recibe un array.

function FdTablaFila($columnas, $extraTD=null)
{
	for ($i = 0; $i < count($columnas); $i++)
		{
			$tabla .= '<tr>';
			for ($j = 0; $j < count($columnas[$i]); $j++)
				{
					$tabla .= '<td ' . $extraTD . '>' . $columnas[$i][$j] . '</td>'; 
				}
			$tabla .= '</tr>';
		}
	return $tabla;
}

function FdTablaFin()
{
	return '</table>';
}
//********************************************************************************************


$form = new FormValidator('clases','post'); 
$table_class = Database :: get_main_table(TABLE_MAIN_CLASS);
//Obtenemos datos de las clases
$sql = "select id,name from $table_class order by name";
$res = api_sql_query($sql, __FILE__, __LINE__);
//cargamos el combo
$options = array();
while ($clase = Database::fetch_array($res))
	{
		$options [$clase[0]] = $clase['1'];
	}
$form->addElement('select', 'id_clase', 'Seleccione la clase',$options);
$form->addElement('submit', 'submit', 'Aceptar');


//Si recibimos los datos para el envio
if (isset($_GET['course_code']) && isset($_GET['id_clase']) && isset ($_GET['id_carta']))
{
	//Obtenemos datos de la carta de bienvenida si existe
	$sql = "select html from fd_bienvenida where id=" . $_GET['id_carta'];
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_array($res);
	$html = $obj["html"];
	
	//recibimos codigo de curso para mandar los emails
	$users = get_datos_alumnos($_GET['course_code'], $_GET['id_clase'] );
	$nombre_curso = get_nombre_curso($_GET['course_code']);

	foreach ($users as $user)
	{
		$body =	str_replace  ( '#alumno#'  , $user[0] . ' ' . $user[1]  ,  $html);
		$body =	str_replace  ( '#curso#'  , $nombre_curso,  $body);
		$body =	str_replace  ( '#usuario#'  , $user[3],  $body);
		$body =	str_replace  ( '#clave#'  , $user[4],  $body);

		$error_alumno = api_send_mail($user[2],'Carta de bienvenida -'. $nombre_curso ,$body,"");
		$error_gestor = api_send_mail($_SESSION["_user"]["mail"],'Carta de bienvenida -'. $nombre_curso . ' (' . $user[0] . ' ' . $user[1] . ')' ,$body,"");
	}

	$message= "Las cartas de bienvenida han sido enviadas correctamentea los alumnos que dispongan de e-mail";
}

$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));
$interbreadcrumb[] = array ("url" => 'fd_bienvenida_sendclass.php', "name" => 'Enviar cartas de bienvenida a una clase');

if ( isset($_GET['message']))
{
	$message=$_GET['message'];
}
Display::display_header($tool_name);


//si recibimos datos del formulario de clase operamos con los mismos.
if( $form->validate()  )
{
	$table_class = Database :: get_main_table(TABLE_MAIN_CLASS);
	//Obtenemos datos de la clase
	$sql = "select name from $table_class where id=" . $_SESSION['id_clase'];
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_object($res);

	echo '<p>A continuaci&oacute;n se muestran los datos de la clase seleccionado y una lista de los cursos en los que se encuentra inscrita. Haga click sobre el icono "enviar" para mandar la carta de bienvenida al curso.</p>';
	echo "<b>Nombre de clase:</b> " . $obj->name . "<br><br>";

		$cursos = get_course_data($_SESSION['id_clase']);

		//retocamos los datos de la tercera columna para meter el enlace
		
			for ( $i=0 ; $i<count($cursos) ; $i ++)
			{
				$cursos [$i][2] = '<a href="fd_bienvenida_sendclass.php?course_code='. $cursos [$i][0] .'&id_clase='. $_SESSION['id_clase'] . '&id_carta=' . $_SESSION['id_carta']. '" onclick="javascript:if(!confirm('."'".addslashes(htmlentities(get_lang("ConfirmYourChoice"),ENT_QUOTES,$charset))."'".')) return false;"><img src="../img/email.gif" border="0" style="vertical-align: middle" title="Enviar" alt="Enviar"/></a>&nbsp;';
			}
		
			//Pintamos la tabla
			$columnas = array ("C&oacute;digo del curso","Nombre del curso","Enviar carta");
			echo FdTablaCabecera($columnas, 'class="data_table" align="center" cellpadding="4" border="0" cellspacing="0" width="300" cellpadding="4"','bgcolor="DOKEOSLIGHTGREY" align="center"');
			echo FdTablaFila($cursos);
			echo FdTablaFin();

}
else
{
	echo "<p>Seleccione la clase a la que quiere enviar las cartas de bienvenida</p><br>";
	$form->display();	
}
	
if ($message!='')
{
Display::display_normal_message(stripslashes($message));
}

/*
==============================================================================
		FOOTER 
==============================================================================
*/
Display :: display_footer();
?>
