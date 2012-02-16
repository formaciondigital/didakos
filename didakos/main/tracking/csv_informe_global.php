<?php
/*
 * ==============================================================================
	SEGUIMIENTO AMPLIADO FD SOBRE ALUMNOS - EJERCICIOS PARA UN CURSO
==============================================================================
*/
/**
==============================================================================
*	@author Thomas Depraetere
*	@author Hugues Peeters
*	@author Christophe Gesche
*	@author Sebastien Piraux
*	@author Toon Keppens (Vi-Host.net)
*
*	@package dokeos.tracking
==============================================================================
*/

/*
==============================================================================
		INIT SECTION
==============================================================================
*/
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=archivo.xls");
header("Pragma: no-cache");
header("Expires: 0");

include('../inc/global.inc.php');



$is_allowedToTrack = $is_courseAdmin || $is_platformAdmin || $is_courseCoach || $is_sessionAdmin;

if(!$is_allowedToTrack)
{
	Display :: display_header(null);
	api_not_allowed();
	Display :: display_footer();
}

//includes for SCORM and LP
require_once(api_get_path(LIBRARY_PATH).'tracking.lib.php');
require_once(api_get_path(LIBRARY_PATH).'course.lib.php');
require_once(api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once (api_get_path(LIBRARY_PATH).'export.lib.inc_FD.php');

//Incluimos librería de utilidades generales
require_once (api_get_path(LIBRARY_PATH).'tracking.lib_fd.php');






/*
-----------------------------------------------------------
	Constants and variables
-----------------------------------------------------------
*/
// regroup table names for maintenance purpose
$TABLETRACK_ACCESS      = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_LASTACCESS);
$TABLETRACK_LINKS       = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_LINKS);
$TABLETRACK_DOWNLOADS   = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_DOWNLOADS);
$TABLETRACK_ACCESS_2    = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ACCESS);
$TABLECOURSUSER	        = Database::get_main_table(TABLE_MAIN_COURSE_USER);
$TABLECOURSE	        = Database::get_main_table(TABLE_MAIN_COURSE);
$TABLECOURSE_LINKS      = Database::get_course_table(TABLE_LINK);
$table_user = Database::get_main_table(TABLE_MAIN_USER);

// Comentadas tablas sobre itinerarios del curso
$tbl_learnpath_main = Database::get_course_table(TABLE_LP_MAIN);
$tbl_learnpath_item = Database::get_course_table(TABLE_LP_ITEM);
$tbl_learnpath_view = Database::get_course_table(TABLE_LP_VIEW);
$tbl_learnpath_item_view = Database::get_course_table(TABLE_LP_ITEM_VIEW);
//tabla de ejercicios
$tbl_course_quiz = Database :: get_course_table('quiz');
//tabla de exámenes
$tbl_course_exam = Database :: get_course_table('quiz_exam');

//tabla de notas globales
$tbl_course_user_fd = Database :: get_main_table(TABLE_MAIN_COURSE_USER_FD);

include(api_get_path(LIBRARY_PATH)."statsUtils.lib.inc.php");
include("../resourcelinker/resourcelinker.inc.php");

function average($a){
  return array_sum($a)/count($a) ;
}


// No usamos la lista de alumnos devuelta por la clase CourseManager de Dokeos, sino una función propia de tracking.lib_fd.php donde excluimos los teletutores,
// para que sólo aparezcan alumnos en el seguimiento FD 
//$a_students = CourseManager :: get_student_list_from_course_code($_course['id'], true, (empty($_SESSION['id_session'])?null:$_SESSION['id_session']));
//$nbStudents = count($a_students);
//echo $nbStudents;
$a_students = Tracking_fd :: alumnos_reales_curso($_course['id']);
$nbStudents = Database::num_rows($a_students);
//echo $nbStudents;
//var_dump($a_students);
//print_r($a_students);

 $num_ej = 0;
 $num_ex = 0;
 


echo "<table border=1>";

        //títulos de ejercicios
		$sql_titulos_ejercicios = "SELECT q.title FROM ".$tbl_course_quiz." q WHERE q.active=1 and q.id NOT IN (select ex.id from ". $tbl_course_exam." ex) order by q.id";		
		$rs_ejercicios = api_sql_query($sql_titulos_ejercicios, __FILE__, __LINE__);
		
		 //títulos de examenes
		$sql_titulos_examenes = "SELECT q.* FROM ".$tbl_course_quiz." q, " .$tbl_course_exam. " ex WHERE q.active=1 and q.id=ex.id order by q.id";
		$rs_examenes = api_sql_query($sql_titulos_examenes, __FILE__, __LINE__);

		
echo "<tr><td><b>Fecha de seguimiento:". date ( "j/m/Y" , time() )."</b></td></tr>";	
echo "<tr><td><b>Curso:".$_SESSION["_course"]["name"]."</b></td></tr>";		  
echo "<tr><td>&nbsp;</td><td>&nbsp;</td>";
echo "<td bgcolor='gold' colspan='".Database :: num_rows($rs_ejercicios)."' align='center'>Ejercicios</td>";	
echo "<td>&nbsp;</td>";	
echo "<td bgcolor='gold' colspan='".Database :: num_rows($rs_examenes)."' align='center'>Examanes</td></tr>";
echo "<tr><td>&nbsp;</td><td>&nbsp;</td>";

		while($row = Database :: fetch_array($rs_ejercicios))				
		{	
           echo "<td bgcolor='salmon'>".$row['title']."</td>";
		   
		}
		
		echo "<td>&nbsp;</td>";
		
		
		while($row = Database :: fetch_array($rs_examenes))				
		{	
           echo "<td bgcolor='salmon'>".$row['title']."</td>";
   	   
		}
		
		echo "<td></td>";
		
		echo "<td bgcolor='salmon'>Evaluacion global</td>";
		
echo "</tr>";		

	
	if(count($a_students)>0)
	{
	  
		//Montamos cabecera de la tabla, con los ejercicios dinámicamente según los ejercicios que tenga el curso	
		$course_code = $_course['id'];
		
		
		
		// Rellenamos la tabla con los datos de alumnos y ejercicios para el curso
		//foreach($a_students as $student_id => $student)
		while ($alumno = Database :: fetch_array($a_students))
		{
		  echo "<tr align='center'>";
		
			$student_id=$alumno['user_id'];															
			$student_datas = UserManager :: get_user_info_by_id($student_id);	
		
					
			 echo  "<td>".$student_datas['lastname'].",".$student_datas['firstname']."</td>";
			 echo "<td></td>";
			//pintamos las notas de los ejercicios		
			$sql_titulos_ejercicios = "SELECT q.id, q.title FROM ".$tbl_course_quiz." q WHERE q.active=1 and q.id NOT IN (select ex.id from ". $tbl_course_exam." ex) order by q.id";					
			$rs = api_sql_query($sql_titulos_ejercicios, __FILE__, __LINE__);		
			while($detalle = Database :: fetch_array($rs))				
			{			
				// Obtenemos nota del alumno en cada ejercicio del curso
			   echo  "<td>".Tracking_fd ::nota_ejercicio_alumno ($student_id, $course_code, $detalle['id'])."</td>";				
			}
			
			echo "<td>&nbsp;</td>";
			
			//pintamos las notas de los examenes
			$sql_titulos_examenes = "SELECT q.* FROM ".$tbl_course_quiz." q, " .$tbl_course_exam. " ex WHERE q.active=1 and q.id=ex.id order by q.id";
			$rs = api_sql_query($sql_titulos_examenes, __FILE__, __LINE__);		
			while($detalle = Database :: fetch_array($rs))				
			{			
				
				// Obtenemos nota del alumno en cada examen del curso
			   echo  "<td>".Tracking_fd ::nota_examen_alumno ($student_id, $course_code, $detalle['id'])."</td>";	
										
			}	
			
			echo "<td>&nbsp;</td>";
			
		    // Obtenemos nota global para cada alumno en el curso
			
			$medias_notas = Array();
				
			$sql_nota ="SELECT nota_global FROM  ".$tbl_course_user_fd." WHERE course_code='".$course_code."' and user_id=".$student_id;
			
			$result_nota = api_sql_query($sql_nota, __FILE__, __LINE__);
			if ($row_nota = Database :: fetch_array($result_nota)) {	
				echo  "<td>".$row_nota['nota_global']."</td>";
				$medias_notas[] = $row_nota['nota_global'];
			}else{
			   echo "<td>&nbsp;</td>";
			}		
			
           
		
	       echo "</tr>";
		}//fin por cada alumno	


        //ahora mostramos las medias	
		
		// Rellenamos una fila más resumen con las medias de los ejercicios, sacamos los ejercicios por el mismo orden que al montar la cabecera de 
		// la tabla para hacer corresponder cada resultado con su título en cabecera
	    $sql_resumen_ejercicios = "SELECT q.id FROM ".$tbl_course_quiz." q WHERE q.active=1 and q.id NOT IN (select ex.id from ". $tbl_course_exam." ex) order by q.id";		
		//echo $sql_resumen_ejercicios;
		$rs_resumen = api_sql_query($sql_resumen_ejercicios, __FILE__, __LINE__);			
		
						
		echo '<tr align="center"><td><b>Media</b></td><td></td>';			
		while($row_resumen = Database :: fetch_array($rs_resumen))				
		{		
			$media_ejercicio=Tracking_fd ::nota_media_ejercicio ($course_code,$row_resumen['id']);		
			//echo "<p>media ejercicio: ".$media_ejercicio;
			 echo "<td bgcolor='wheat'>".$media_ejercicio."</td>";  		
		}

		
        echo "<td></td>";
		
		
		
		// Rellenamos una fila más resumen con las medias de los exámenes, sacamos los exámenes ordenados por el orden de visualización del itinerario, igual que al montar la cabecera de 
		// la tabla para hacer corresponder cada resultado con su título en cabecera
			$sql_resumen_examenes = "SELECT q.id FROM ".$tbl_course_quiz." q, " .$tbl_course_exam. " ex WHERE q.active=1 and q.id=ex.id order by q.id";
		//echo $sql_resumen_examenes;
		$rs_resumen = api_sql_query($sql_resumen_examenes, __FILE__, __LINE__);			
		
		
		while($row_resumen = Database :: fetch_array($rs_resumen))				
		{		
			$media_examen=Tracking_fd ::nota_media_examen ($course_code,$row_resumen['id']);		
			//echo "<p>media examen: ".$media_examen;
			echo "<td bgcolor='wheat'>".$media_examen."</td>"; 	
		}				
    	
        echo "<td></td>";		
		echo "<td bgcolor='wheat'>".average($medias_notas)."</td>"; 	
		
		echo "</tr>";	
	echo "</table>";
	}
	else
	{
		echo get_lang('NoUsersInCourseTracking');
	}	
	

?>
