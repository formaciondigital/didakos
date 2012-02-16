<?php
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004-2008 Dokeos SPRL

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
 * @todo use constant for $this_section
 */
// name of the language file that needs to be included 
$language_file = array ('registration', 'index','tracking');
$cidReset=true;

require ('../inc/global.inc.php');
require (api_get_path(LIBRARY_PATH).'tracking.lib.php');
require_once(api_get_path(LIBRARY_PATH).'course.lib.php');
require_once(api_get_path(LIBRARY_PATH).'export.lib.inc.php');


//Incluimos librer?a de utilidades generales
require_once (api_get_path(LIBRARY_PATH).'tracking.lib_fd.php');

ob_start();

$export_csv = isset($_GET['export']) && $_GET['export'] == 'csv' ? true : false;
$csv_content = array();

//$nameTools= get_lang("MySpace");
//$this_section = "session_my_space";
 
api_block_anonymous_users();
if(!$export_csv)
{
	Display :: display_header($nameTools);
}
 
// Database table definitions
$tbl_user 					= Database :: get_main_table(TABLE_MAIN_USER);
$tbl_course 				= Database :: get_main_table(TABLE_MAIN_COURSE);
$tbl_course_user 			= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$tbl_class 					= Database :: get_main_table(TABLE_MAIN_CLASS);
$tbl_sessions 				= Database :: get_main_table(TABLE_MAIN_SESSION);
$tbl_session_course 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
$tbl_session_user 			= Database :: get_main_table(TABLE_MAIN_SESSION_USER);
$tbl_session_course_user 	= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
$tbl_admin					= Database :: get_main_table(TABLE_MAIN_ADMIN);


/********************
 * FUNCTIONS
 ********************/
 
function count_teacher_courses()
{
	global $nb_teacher_courses;
	return $nb_teacher_courses;
}

function count_coaches()
{
	global $total_no_coachs;
	return $total_no_coachs;
}

function sort_users($a,$b){
	$a = trim(strtolower($a[$_SESSION['tracking_column']]));
	$b = trim(strtolower($b[$_SESSION['tracking_column']]));
	if($_SESSION['tracking_direction'] == 'DESC')
		return strcmp($b, $a);
	else
		return strcmp($a, $b);
}



/**************************
 * MAIN CODE
 ***************************/

  
echo '<div align="left" style="float:left"><h4>'.$title.'</h4></div>
	  <div align="right">
		<a href="#" onclick="window.print()"><img align="absbottom" src="../img/printmgr.gif">&nbsp;'.get_lang('Print').'</a>				
	  </div>
	  <div class="clear"></div>';
//27-10-2010 cambio fd - se modifica el informe del responsable de recursos humanos para que sea como el del profesor,pero viendo todos los cursos
if($_user['status']==DRH)
{

	$courses_of_the_platform = CourseManager :: get_real_course_list();       
        $nb_teacher_courses = count($courses_of_the_platform);
      
}else{
    die("no tienen permisos para ver este informe");
}




echo '<div class="clear">&nbsp;</div>';



//Fecha: 06-10-2008. Informe que ve el profesor, adaptado a FD
// 27-10-2010. se añade el perfil de Recursos humanos al informe del profesor


	if($nb_teacher_courses)
	{
		
					  
		$table = new SortableTable('tracking_list_course', 'count_teacher_courses');
		$parameters['view'] = 'teacher';
		$table->set_additional_parameters($parameters);
		$table -> set_header(0, get_lang('CourseTitle'), false, 'align="center"');		
		$table -> set_header(1, get_lang('NbStudents'), false);		
		$table -> set_header(2, get_lang('AvgTimeSpentInTheCourse'), false);
		//a?adimos t?tulos del informe de FD para el profesor	
		//$table -> set_header(3, get_lang('AvgStudentsProgress'), false);
		$table -> set_header(3, 'Media de Mensajes en Foros', false);					
		//$table -> set_header(4, get_lang('AvgStudentsScore'), false);
		$table -> set_header(4, 'Media de Mensajes en Buzón', false);		
		//$table -> set_header(5, get_lang('AvgMessages'), false);
		$table -> set_header(5, 'Media de Exámenes', false);		
		//$table -> set_header(6, get_lang('AvgAssignments'), false);
		$table -> set_header(6, 'Progreso Medio de Seguimiento en Curso (%)', false);
		$table -> set_header(7, 'Media de Ejercicios', false);		
		$table -> set_header(8, get_lang('Details'), false);

	

          
		
		foreach($courses_of_the_platform as $course)
		{
			//print_r($course);

			$course_code = $course['code'];

			
			$avg_assignments_in_course = $avg_messages_in_course = $nb_students_in_course = $avg_progress_in_course = $avg_score_in_course = $avg_time_spent_in_course = 0;
			
			// Variable para mensajes en buz?n
			$avg_messages_in_dropbox=0;
			
			// Variable para la media de los ex?menes
			$avg_media_examenes=0;
			
			// Variable para la media de los ejercicios
			$avg_media_ejercicios=0;
			
			// Variable para la media del progreso de los scos
			$avg_progreso_scos=0;
			
			// students directly subscribed to the course
			$sql = "SELECT user_id FROM $tbl_course_user as course_rel_user WHERE course_rel_user.status='5' AND course_rel_user.course_code='$course_code'";
			$rs = api_sql_query($sql, __FILE__, __LINE__);

                        
			
			//Obtenemos el n?mero total de ex?menes para el curso tratado
			$total_examenes=Tracking_fd ::numero_total_examenes_o_ejercicios_curso($course_code,0);
			//Obtenemos el n?mero total de ejercicios para el curso tratado
			$total_ejercicios=Tracking_fd ::numero_total_examenes_o_ejercicios_curso($course_code,1);
			
			//Obtenemos el n?mero total de scos para el curso tratado
			$total_scos=Tracking_fd ::numero_scos_curso($course_code);
			
			/*
			echo "total examenes: ".$total_examenes."<br>";
			echo "total ejercicios: ".$total_ejercicios."<br>";
			echo "total scos: ".$total_scos."<br>";
			*/
			
			while($row = Database::fetch_array($rs))
			{
                            
				$nb_students_in_course++;
				
				// tracking datas
				// tiempo de permanencia en el curso
				$avg_time_spent_in_course += Tracking :: get_time_spent_on_the_course ($row['user_id'], $course_code);
				// N?mero de mensajes en foros				
				$avg_messages_in_course += Tracking :: count_student_messages ($row['user_id'], $course_code);
				// N?mero de mensajes en buz?n								
				$avg_messages_in_dropbox += Tracking_fd ::count_dropbox_file ($row['user_id'], $course_code);
				
				// Nota media de los ex?menes para el alumno y curso tratado
				$avg_media_examenes += Tracking_fd ::media_examenes_alumno_curso ($row['user_id'], $course_code, $total_examenes);
				
				// Nota media de las notas de los ?ltimos intentos de los ejercicios para el alumno y curso tratado
				$avg_media_ejercicios += Tracking_fd ::media_ejercicios_alumno_curso ($row['user_id'], $course_code, $total_ejercicios);
				
				// Progreso medio en los scos para el alumno y curso tratado
				$avg_progreso_scos += Tracking_fd ::progreso_scos_curso ($row['user_id'], $course_code, $total_scos);
				
				
				/////////////////			
				/*
				$avg_progress_in_course += Tracking :: get_avg_student_progress ($row['user_id'], $course_code);
				$avg_score_in_course += Tracking :: get_avg_student_score ($row['user_id'], $course_code);
				
				$avg_messages_in_course += Tracking :: count_student_messages ($row['user_id'], $course_code);
				$avg_assignments_in_course += Tracking :: count_student_assignments ($row['user_id'], $course_code);
				$a_course_students[] = $row['user_id'];
				*/				
			}
			
			// students subscribed to the course through a session
			/*
			if(api_get_setting('use_session_mode') == 'true')
			{
				$sql = 'SELECT id_user as user_id
						FROM '.$tbl_session_course_user.'
						WHERE course_code="'.addslashes($course_code).'" ORDER BY course_code';
				$rs = api_sql_query($sql, __FILE__, __LINE__);
				while($row = Database::fetch_array($rs))
				{
					if(!in_array($row['user_id'], $a_course_students))
					{
						$nb_students_in_course++;
						
						// tracking datas
						$avg_progress_in_course += Tracking :: get_avg_student_progress ($row['user_id'], $course_code);
						$avg_score_in_course += Tracking :: get_avg_student_score ($row['user_id'], $course_code);
						$avg_time_spent_in_course += Tracking :: get_time_spent_on_the_course ($row['user_id'], $course_code);
						$avg_messages_in_course += Tracking :: count_student_messages ($row['user_id'], $course_code);
						$avg_assignments_in_course += Tracking :: count_student_assignments ($row['user_id'], $course_code);
						$a_course_students[] = $row['user_id'];
					}
				}
			}
			*/
			
			if($nb_students_in_course>0)
			{ // Obtenemos el resultado de los c?lculos anteriores divididos entre el n?mero de alumnos del curso
			
				// tiempo medio por alumno
				$avg_time_spent_in_course = api_time_to_hms($avg_time_spent_in_course / $nb_students_in_course);			
			
				// n?mero medio de mensajes en foros por alumno
				$avg_messages_in_course = round($avg_messages_in_course / $nb_students_in_course,2);
			
				// n?mero medio de mensajes en buz?n por alumno
				$avg_messages_in_dropbox = round($avg_messages_in_dropbox / $nb_students_in_course,2);
			
				// nota media en ex?menes por alumno
				$avg_media_examenes=round($avg_media_examenes / $nb_students_in_course,2);
				
				// nota media en ejercicios por alumno
				$avg_media_ejercicios=round($avg_media_ejercicios / $nb_students_in_course,2);			
						
				// progreso medio en scos por alumno
				$avg_progreso_scos=round($avg_progreso_scos / $nb_students_in_course,2);								
			
			
			/*
				$avg_time_spent_in_course = api_time_to_hms($avg_time_spent_in_course / $nb_students_in_course);
				$avg_progress_in_course = round($avg_progress_in_course / $nb_students_in_course,2).' %';
				$avg_score_in_course = round($avg_score_in_course / $nb_students_in_course,2).' %';
				$avg_messages_in_course = round($avg_messages_in_course / $nb_students_in_course,2);
				$avg_assignments_in_course = round($avg_assignments_in_course / $nb_students_in_course,2);
				
			*/	
			}
			
			$table_row = array();
			$table_row[] = $course['title'];
			$table_row[] = $nb_students_in_course;
			$table_row[] = $avg_time_spent_in_course;				
			$table_row[] = $avg_messages_in_course;
			$table_row[] = $avg_messages_in_dropbox;			
			$table_row[] = $avg_media_examenes;
			$table_row[] = $avg_progreso_scos." %";
			$table_row[] = $avg_media_ejercicios;
						
			/*
			$table_row[] = $avg_progress_in_course;
			$table_row[] = $avg_score_in_course;			
			$table_row[] = $avg_messages_in_course;
			$table_row[] = $avg_assignments_in_course;
			*/
			$table_row[] = '<a href="../tracking/courseLog_FD_DHR.php?cidReq='.$course_code.'&studentlist=true"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';

			//Comentado CSV
			/* 						
			$csv_content[] = array(
								html_entity_decode($course['title']),
								$nb_students_in_course,
								$avg_time_spent_in_course,
								$avg_progress_in_course,
								$avg_score_in_course,
								$avg_messages_in_course,
								$avg_assignments_in_course,
								);
			*/					
			
			$table -> addRow($table_row, 'align="right"');
			
			$a_course_students = array();
			
		}
		$table -> updateColAttributes(0,array('align'=>'left'));
		$table -> updateColAttributes(8,array('align'=>'center'));
		$table -> display();
			
	}else{
            echo "no hay cursos";
        }


if(!$export_csv)
{
	Display::display_footer();
}
?>
