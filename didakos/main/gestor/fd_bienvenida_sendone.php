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
Página incial: fd_bienvenida_send.php (versión 0.2)
Página actual: fd_bienvenida_sendone.php
Descripción: Página que muestra un listado de los cursos de un alumno para enviar las cartas de bienvenida
		
==============================================================================
*/
/*
==============================================================================
		INIT SECTION
==============================================================================
*/

// name of the language file that needs to be included 
$language_file = array('admin','registration');
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

function get_course_data($user_id)
{
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	$matriculaciones_table = Database :: get_main_table(TABLE_MAIN_MATRICULACIONES);
	
	$sql = "SELECT ct.code, ct.title, ut.user_id FROM $course_table ct,$matriculaciones_table mt,$user_table ut where mt.user_id=ut.user_id and mt.course_code=ct.code and ut.user_id=" . $user_id;
	$sql .= " ORDER BY ct.code";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$courses = array ();


	while ($course = Database::fetch_row($res))
	{
		$course_rem = array($course[0],$course[1],$course[2]);
		$courses[] = $course_rem;
	}
	return $courses;
}

function get_nombre_curso($id_curso)
{
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$sql = "select title as nombre_curso from $course_table where code='". $id_curso ."'";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_object($res);
	return $obj->nombre_curso;
}


/*
recibe el course_code y devuelve datos de los alumnos (nombre y apellidos y email)
*/

function get_datos_alumnos($user_id)
{
	$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	$sql = "select firstname,lastname, email, username, password from $user_table where user_id=" . $user_id;
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

$form_dni = new FormValidator('formulario_dni');
$form_dni->addElement('text', 'c_dni', get_lang('OfficialCode'), array('size' => '10', 'maxlength' => '10'));
$form_dni->addElement('hidden', 'tipo_usuario', $_GET["tipo_usuario"] );
$form_dni->addRule('c_dni', get_lang('ThisFieldIsRequired'), 'required');
$form_dni->addRule('c_dni', get_lang('DNIIncorrecto'), 'dni');
$form_dni->addElement('submit', 'submit', get_lang('Send'));

//Si recibimos los datos para el envio
if (isset($_GET['course_code']) && isset($_GET['user_id']) && isset ($_GET['id_carta']))
{
	$t_bienvenida = Database::get_main_table(TABLE_MAIN_BIENVENIDA);
	//Obtenemos datos de la carta de bienvenida si existe
	$sql = "select html from $t_bienvenida where id=" . $_GET['id_carta'];
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_array($res);
	$html = $obj["html"];
	
	//recibimos codigo de curso para mandar los emails
	$users = get_datos_alumnos($_GET['user_id'] );
	$nombre_curso = get_nombre_curso($_GET['course_code']);

	foreach ($users as $user)
	{
		$body =	str_replace  ( '#alumno#'  , $user[0] . ' ' . $user[1]  ,  $html);
		$body =	str_replace  ( '#curso#'  , $nombre_curso,  $body);
		$body =	str_replace  ( '#usuario#'  , $user[3],  $body);
		$body =	str_replace  ( '#clave#'  , $user[4],  $body);

		$error_alumno = api_send_mail($user[2],get_lang('CartaDeBienvenida').' - '. $nombre_curso ,$body,"");
		$error_gestor = api_send_mail($_SESSION["_user"]["mail"],get_lang('CartaDeBienvenida').' - '. $nombre_curso . ' (' . $user[0] . ' ' . $user[1] . ')' ,$body,"");
	}

	//devuelven 0 si el email ha dado fallo (en teoria que pa mi que va a ser que no)
	if ($error_alumno==0 && $error_gestor==0)
	{
		header ('Location: fd_bienvenida_sendone.php?message='.get_lang('EnviarCartasDeBienvenidaIndividualesTexto5'));
	}
	else
	{
		if ($error_alumno==0)
		{
			header ('Location: fd_bienvenida_sendone.php?message='.get_lang('EnviarCartasDeBienvenidaIndividualesTexto6'));
		}
		else
		{
			if ($error_gestor==0)
			{
				header ('Location: fd_bienvenida_sendone.php?message='.get_lang('EnviarCartasDeBienvenidaIndividualesTexto7'));
			}
			else
			{
				header ('Location: fd_bienvenida_sendone.php?message='.get_lang('EnviarCartasDeBienvenidaIndividualesTexto8'));
			}
		}
	}
	exit();
}

$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));
$interbreadcrumb[] = array ("url" => 'fd_bienvenida_sendone.php', "name" => get_lang('EnviarCartasDeBienvenidaIndividuales'));
if ( isset($_GET['message']))
{
	$message=$_GET['message'];
}
Display::display_header($tool_name);


//si recibimos datos del formulario de DNI operamos con los mismos.
if( $form_dni->validate()  )
{
	$user = $form_dni->exportValues();
	$dni = $user['c_dni'];
	$usuario = UserManagerFD::get_user_info_by_dni($dni);

	if ($usuario == '')
	{
		//el dni insertado no corresponde con ningún alumno de la BBDD
		$message = get_lang('EnviarCartasDeBienvenidaIndividualesTexto4').' ('.$dni.')';

		echo "<p>".get_lang('EnviarCartasDeBienvenidaIndividualesTexto1')."</p><br>";
		$form_dni->display();
	}
	else
	{
		//el dni que ha insertado existe en la BBDD, mostramos los datos del alumno y un listado de cursos
		//en los que se encuentra matriculado

		echo '<p>'.get_lang('EnviarCartasDeBienvenidaIndividualesTexto2').'.</p>';
		echo "<b>".get_lang('OfficialCode').":</b> " . $usuario['official_code']. "<br>";
		echo "<b>".get_lang('Alumno').":</b> " . $usuario['firstname'] . " " . $usuario['lastname']. "<br>";
		echo "<b>".get_lang('langEmail').":</b> " . $usuario['email']. "<br>";
		echo "<b>".get_lang('PhoneNumber').":</b> " . $usuario['phone']. "<br><br>";

		if ($usuario['email']=='')
		{
			$message=get_lang('EnviarCartasDeBienvenidaIndividualesTexto3');
		}
		else
		{
			$cursos = get_course_data($usuario['user_id']);
			//retocamos los datos de la tercera columna para meter el enlace
			for ( $i=0 ; $i<count($cursos) ; $i ++)
			{
				$cursos [$i][2] = '<a href="fd_bienvenida_sendone.php?course_code='. $cursos [$i][0] .'&user_id='.$cursos [$i][2] . '&id_carta=' . $_SESSION['id_carta']. '" onclick="javascript:if(!confirm('."'".addslashes(htmlentities(get_lang("ConfirmYourChoice"),ENT_QUOTES,$charset))."'".')) return false;"><img src="../img/email.gif" border="0" style="vertical-align: middle" title="'.get_lang('Send').'" alt="'.get_lang('Send').'"/></a>&nbsp;';
			}
		
			//Pintamos la tabla
			$columnas = array (get_lang('EnviarCartasDeBienvenidaIndividualesColumna1'),get_lang('EnviarCartasDeBienvenidaIndividualesColumna2'),get_lang('EnviarCartasDeBienvenidaIndividualesColumna3'));
			echo FdTablaCabecera($columnas, 'class="data_table" align="center" cellpadding="4" border="0" cellspacing="0" width="300" cellpadding="4"','bgcolor="DOKEOSLIGHTGREY" align="center"');
			echo FdTablaFila($cursos);
			echo FdTablaFin();
		}
	
}
}
else
{
	echo "<p>".get_lang('EnviarCartasDeBienvenidaIndividualesTexto1')."</p><br>";
	$form_dni->display();	
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
