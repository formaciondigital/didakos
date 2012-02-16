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

Autor: Francisco Javier Rubio Campos
Página incial: fd_alumnos_ins.php (1.8.5)
Página actual: fd_alumnos_ins_listado.php
Descripción: 
		
Página que muestra varios datos de los alumnos inscrito en un curso dado.

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
require_once(api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');
//Incluimos librería de exportación a CSV, XLS,... de FD
require_once (api_get_path(LIBRARY_PATH).'export.lib.inc_FD.php');


$export_csv = isset($_GET['export']) && $_GET['export'] == 'csv' ? true : false;
if($export_csv)
{
	ob_start();
	$csv_content = array();
  $csv_content[0] = array ('CURSO','DNI','APELLIDOS','NOMBRE','DOMIC','CP','LOCAL','PROVINCIA','FNTO','SEXO','COD','ESTUDIOS','NACION','MINUSVALIA','MAIL','TFNO','SITUACION','ACTIVIDAD','ANTIGUEDAD','PRESTACIONES','EXPERIENCIA','BAREMO','OBJETIVA','PROFESIONAL','PSICOTECNICO','ENTREVISTA','SELECCION','RESERVA','EXCLUIDO','INICIO','FIN','EVAL','F_INSCRIPCION','PRIORIDAD','USER_NAME','PASSWORD','MODO ACCESO', 'PUBLICIDAD');
	
	$users = printcsv();
	foreach ($users as $user)
		$csv_content[] =$user;	
	
	ob_end_clean();
	Export_FD :: export_table_xls($csv_content, 'alumnos_inscritos_'.$_SESSION['curso']);
}

//Funciones auxiliares

function uclatin($variable) { 
 	$variable = strtr(strtoupper($variable),"àèìòùáéíóúçäëïöüñ","ÀÈÌÒÙÁÉÍÓÚÇÄËÏÖÜÑ"); 
  return $variable; 
} 


function orderMultiDimensionalArray ($toOrderArray, $field, $inverse = 'ASC') {  
	$position = array();  
	$newRow = array();  
	
	foreach ($toOrderArray as $key => $row) {  
		$position[$key]  = $row[$field];  
		$newRow[$key] = $row;  
	}  
	
	if ($inverse == 'DESC')  
		arsort($position);  
	else   
		asort($position);  
		
	$returnArray = array();  
	
	foreach ($position as $key => $pos) {       
		$returnArray[] = $newRow[$key];  
	}  
	
	return $returnArray;  
}  


/**
 * Get the total number of users on the course
 * @see SortableTable#get_total_number_of_items()
 */
 

function get_number_of_users()
{
	$curso = $_SESSION['curso']; 
	$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER_FD);
			
	$sql="SELECT count(u.user_id) as num_alumnos FROM ".$course_table ." c, ".$user_table." u
   WHERE c.user_id = u.user_id and u.status = 5 and c.course_code = '".$curso."'";
	
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$alumno = Database::fetch_array($res);
	return $alumno['num_alumnos'];
}


function printcsv ()
{
	$curso = $_SESSION['curso']; 
	$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER_FD);
	$user_table = Database :: get_main_table(TABLE_MAIN_USER); 
	$exercise_table = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
	$field_values_table = Database :: get_main_table(TABLE_MAIN_USER_FIELD_VALUES);
	$field_table = Database :: get_main_table(TABLE_MAIN_USER_FIELD);
		
	//Obtenemos los datos de la tabla USER
  
	$sql = "SELECT u.user_id, official_code as DNI, replace (lastname, '#', ' ') as APELLIDOS, firstname as NOMBRE, email as MAIL, phone as TFNO, username,password,    f_matriculacion  
	  FROM ".$course_table ." c, ".$user_table ." u
	  WHERE c.user_id = u.user_id
    and u.status = 5
    and c.course_code = '".$curso."'";
		
	  $res = api_sql_query($sql, __FILE__, __LINE__);
	  $users = array ();
			
	  while ($aux_user = Database::fetch_row($res))
	  {
		  $id_user = $aux_user[0];
			
			//CURSO - 0
			if ($curso == '10427EX')
				$user[0] = '178/2009/01';
			else if ($curso == '10467EX')
				$user[0] = '178/2009/02';	
			else
				$user[0] = '';	
					
			//DNI - 1
			$user[1]  = $aux_user[1];
			//APELLIDOS - 2
			$user[2]  = uclatin($aux_user[2]);
	
			//NOMBRE - 3		
			$user[3]  = uclatin($aux_user[3]);
			
			//MAIL - 14
			$user[14] = uclatin($aux_user[4]);
			
			//TFNO - 15
			$user[15] = uclatin($aux_user[5]);
					
			//PRESTACIONES - 19
			$user[19] = "FALSO";
			
			//EXPERIENCIA - 20
			$user[20] = "FALSO";
										
			//BAREMO - 21
			$sql = "SELECT exe_result as baremo FROM ".$exercise_table." te WHERE  te.exe_user_id = ".$id_user." AND te.exe_cours_id = '".$curso."'";
	
			$res2 = api_sql_query($sql, __FILE__, __LINE__);
			$aux = Database::fetch_row($res2);			
	
			$tam = count($aux);
			if ($tam > 1)
				$user[21] = $aux[1];
			else
				$user[21] = $aux[0];
			
			//OBJETIVA - 22
			$user[22] = "";
			
			//PROFESIONAL - 23		
			$user[23] = "VERDADERO";
			
			//PSICOTECNICO - 24
			$user[24] = "";
			
			//ENTREVISTA - 25
			$user[25] = "";
				
			//SELECCION - 26
			$user[26] = "VERDADERO";
			
			//RESERVA - 27
			$user[27] = "FALSO";
			
			//EXCLUIDO - 28
			$user[28] = "FALSO";
			
			//INICIO - 29
			$user[29] = "";
	
			//FIN - 30
			$user[30] = "";
			
			//EVAL - 31
			$user[31] = "";
			
			//F_INSCRIPCION - 32
			$user[32] = uclatin($aux_user[8]);
			
			//USER_NAME - 34
			$user[34] = $aux_user[6];
				
			//PASSWORD - 35
			$user[35] = uclatin($aux_user[7]);
							
			//Para cada alumno tenemos que obtener el resto de campos
										
			$sql = "SELECT * FROM ".$field_table."uf, ".$field_values_table." ufv WHERE uf.id = ufv.field_id AND ufv.user_id = ".$id_user.";";
			$res3 = api_sql_query($sql, __FILE__, __LINE__);
			
			$aux_user = array ();
					
			while ($aux_fetch = Database::fetch_object($res3))
							$aux_user[$aux_fetch->field_variable] = $aux_fetch->field_value;
			
			//DOMIC - 4
			$user[4] = uclatin($aux_user['direccion']);
							
			//CP - 5
			$user[5] = $aux_user['cp'];
			
			//LOCAL - 6
			$user[6] = uclatin($aux_user['localidad']);
				
			//PROVINCIA - 7
			$user[7] = uclatin($aux_user['provincia']);
			
			//FNTO - 8
			$user[8] = $aux_user['fecha_nacimiento'];
			
			//SEXO - 9
			$user[9] = $aux_user['sexo'];
			
			//COD - 10
			$user[10] = $aux_user['nivel_educativo'];
			
			//ESTUDIOS - 11
			$user[11] = $aux_user['nivel_educativo'];
			
			//NACION - 12
			$user[12] = uclatin($aux_user['nacionalidad']);
			
			//MINUSVALIA - 13
			$user[13] = $aux_user['discapacitado'];
			
			//SITUACION - 16
				
			if ($aux_user['sit_laboral'] == 4)
			{
				$user[16] = 'A';
				//ACTIVIDAD - 17
				$user[17] = uclatin($aux_user['act_empresa']);
			}
			else
			{
				$user[16] = 'D';
				//ACTIVIDAD - 17
				$user[17] = "";
			}
					
			//ANTIGÜEDAD - 18
			$user[18] = $aux_user['fecha_inem'];
	
	
			//PRIORIDAD - 33
			if ($aux_user['prioridad_1'] == $curso)
				$user[33] = 1;
			else if ($aux_user['prioridad_2'] == $curso)
					$user[33] = 2;
			else
					$user[33] = 0;
							
			//MODO ACCESO -  36
			$user[36] = $aux_user['modo_acceso'];
			
			//PUBLICIDAD -  37
			$user[37] = $aux_user['publicidad'];
			
			ksort($user);
				
		$users[] = $user;
	
	}	
		
	return $users;
}

/**
 * Get the users to display on the current page.
 * @see SortableTable#get_table_data($from)
 */
function get_user_data($from, $number_of_items, $column, $direction='ASC')
{
   $curso = $_SESSION['curso']; 
	 $user_table = Database :: get_main_table(TABLE_MAIN_USER);
	 $course_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER_FD);
	 $user_table = Database :: get_main_table(TABLE_MAIN_USER); 
	 $field_values_table = Database :: get_main_table(TABLE_MAIN_USER_FIELD_VALUES);

	 // Obtenemos los primeros datos
		
	 $sql="SELECT u.user_id, official_code as DNI, replace (lastname, '#', ' ') as col1, firstname as col2, 
	 email as MAIL, f_matriculacion 
	 FROM ".$course_table ." c, ".$user_table." u
   WHERE c.user_id = u.user_id
   and u.status = 5
   and c.course_code = '".$curso."'";
  		
	 //$sql .= " LIMIT $from,$number_of_items";
	 
	
	
	 $res = api_sql_query($sql, __FILE__, __LINE__);
	 $users = array ();
			
	 while ($aux_user = Database::fetch_row($res))
	 {
		$id_user = $aux_user[0];
						
		$user= array (  uclatin($aux_user[1]), uclatin($aux_user[2]), uclatin($aux_user[3]));
		$user[4] = uclatin($aux_user[4]);
	  $user[6] = uclatin($aux_user[5]);
		
		
		$exercise_table = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
		$sql = "SELECT exe_result as baremo FROM ".$exercise_table." te WHERE  te.exe_user_id = ".$id_user." AND te.exe_cours_id = '".$curso."'";
		
		$res2 = api_sql_query($sql, __FILE__, __LINE__);
		$aux = Database::fetch_row($res2);			
		
		$tam = count($aux);
		if ($tam > 1)
			$user[5] = $aux[1];
		else
		  $user[5] = $aux[0];
						
		//Para cada alumno tenemos que obtener el resto de campos
		
		$field_table = Database :: get_main_table(TABLE_MAIN_USER_FIELD);
		$field_values_table = Database :: get_main_table(TABLE_MAIN_USER_FIELD_VALUES);
		$exercise_table = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
		
	  $sql = "SELECT field_display_text, field_value, field_variable, field_id FROM ".$field_table." uf, ".$field_values_table." ufv WHERE uf.id = ufv.field_id AND ufv.user_id = ".$id_user." AND ufv.field_id in (3, 4, 5, 11, 12) order by ufv.field_id";
		
		$res3 = api_sql_query($sql, __FILE__, __LINE__);
					
		while ($aux = Database::fetch_row($res3))
		{
		  
			 switch ($aux[3]) {
				case 3:
					$user[3] = uclatin($aux[1]);
					break;
				case 4:
					$user[3].=", ".uclatin($aux[1]);
					break;
				case 5:
					$user[3].=", ".uclatin($aux[1]);
					break;
				case 11:
					if ($aux[1] == $curso )
						$user[7] = 1;
					break;	
			  case 12:
					if ($aux[1] == $curso)
						$user[7] = 2;
					break;					
		   }//switch
		}
			  
	$users [] = $user;
		
	} 
  $users = orderMultiDimensionalArray ($users, $column, $direction);
	$users = array_slice($users,$from,$number_of_items);
	return $users;

}

// el dato del curso viene en primera instancia por post, luego se pasa por get dentro de las cabeceras de la tabla
if (isset($_POST['curso']))
{
	$curso = $_POST['curso'];
	$_SESSION['curso'] = $_POST['curso'];
}
else
$curso = $_SESSION['curso'];


$a_course = CourseManager :: get_course_information($curso);

$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));
$interbreadcrumb[] = array ("url" => 'fd_alumnos_ins.php', "name" => 'Alumnos inscritos por curso');
$tool_name = 'Listado de alumnos ' . $a_course['code'] . ' ' . $a_course['title'];
Display :: display_header($tool_name);


echo '<div align="right">';
echo ' <a href="'.api_get_self().'?'.api_get_cidreq().'&export=csv"><img align="absbottom" src="../img/excel.gif">&nbsp;'.'Exportar'.'</a>';
echo '</div>';
   
		
	$parameters['sec_token'] = Security::get_token();
	$table = new SortableTable('users', 'get_number_of_users', 'get_user_data', 1, 20, 'ASC');
	$table->set_header(0, 'DNI');
	$table->set_header(1, 'APELLIDOS');
	$table->set_header(2, 'NOMBRE');
	$table->set_header(3, 'DOMICILIO');
	$table->set_header(4, 'E-MAIL');
	$table->set_header(5, 'BAREMO');
	$table->set_header(6, 'FECHA_INSCRICPION');
	$table->set_header(7, 'PRIORIDAD');
	$table->display();

?>