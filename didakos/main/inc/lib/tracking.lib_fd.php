<?php
// $Id: tracking.lib_fd.php 30-09-08
/*
==============================================================================
	Librería de FD para funciones del informe de seguimiento
	
==============================================================================
*/
/**
==============================================================================
*	This is the tracking library for Dokeos.
*	Include/require it in your code to use its functionality.
*
*	@package dokeos.library
==============================================================================
*/

class Tracking_fd {

function count_dmail($id_usuario,$id_course)
{
	// Función que sustituye a count_dropbox_file.
	// Cuenta el numero de dmail recibidos + enviados.
	$a_course = CourseManager :: get_course_information($id_course);
	$dmail_table = Database :: get_course_table(TABLE_DMAIL, $a_course['db_name']);	
	$sql = 'select count(*) as total from ' . $dmail_table . ' where (id_carpeta=2 and recibe=' . $id_usuario . ') or (id_carpeta=1 and envia=' . $id_usuario .')';
	$rs = api_sql_query($sql, __LINE__, __FILE__);
	$total=Database::fetch_array($rs);
	return $total['total'];		
}

function count_dropbox_file($student_id, $course_code) {
		// protect datas
		$student_id = intval($student_id);			
		$course_code = addslashes($course_code);

		// get the informations of the course 
		$a_course = CourseManager :: get_course_information($course_code);
		
		//echo var_dump($a_course);

		if(!empty($a_course['db_name']))
		{		
			// table definition
			$dropbox_file_table = Database :: get_course_table(TABLE_DROPBOX_FILE, $a_course['db_name']);		
			//echo "nombre de la tabla: ". $dropbox_file_table;
			$sql = 'SELECT id
						FROM ' . $dropbox_file_table . ' 
						WHERE uploader_id=' . $student_id;
						
			$rs = api_sql_query($sql, __LINE__, __FILE__);
			return Database::num_rows($rs);			
		}else {
			return 0;
		}				
	}	

/////////////////////////////////////// 

//Función que devuelve el número total de exámenes que tiene un curso
//Los Exámenes son los ejercicios activos (tabla QUIZ) que se encuentran en la tabla QUIZ_EXAM

function numero_total_examenes_curso($course_code) {

	$course_code = addslashes($course_code);
	
	// get the informations of the course 
	$a_course = CourseManager :: get_course_information($course_code);
	
	$num_filas=0;
	if(!empty($a_course['db_name']))
	{		
		// tabla QUIZ
		$quiz_table = Database :: get_course_table(TABLE_QUIZ_TEST, $a_course['db_name']);	
		// tabla QUIZ_EXAM
		$exam_table = Database :: get_course_table(TABLE_QUIZ_TEST_EXAM, $a_course['db_name']);		
		
		// total de exámenes activos		
		$sqlExams = "SELECT ex.id FROM ".$quiz_table." q, ". $exam_table." ex WHERE q.active=1 and q.id=ex.id";							
		
		//echo $sqlExams."<p>";
		$result = api_sql_query($sqlExams);
		//Guardamos el número de exámenes
		$num_filas=Database::num_rows($result);	
	}
	return $num_filas;
}	

//Función que devuelve el número total de exámenes que tiene un curso
//Los Exámenes son los ejercicios activos (tabla QUIZ) que se encuentran en la tabla QUIZ_EXAM

function numero_total_ejercicios_curso($course_code) {

	$course_code = addslashes($course_code);	

	// get the informations of the course 
	$a_course = CourseManager :: get_course_information($course_code);
	
	$num_filas=0;
	if(!empty($a_course['db_name']))
	{		
		// tabla QUIZ
		$quiz_table = Database :: get_course_table(TABLE_QUIZ_TEST, $a_course['db_name']);	
		// tabla QUIZ_EXAM
		$exam_table = Database :: get_course_table(TABLE_QUIZ_TEST_EXAM, $a_course['db_name']);		
		
		// total de ejercicios activos		
		$sqlExercices = "SELECT q.id FROM ".$quiz_table." q WHERE q.active=1 and q.id NOT IN (select ex.id from ". $exam_table." ex)";							
		
		//echo $sqlExercices."<p>";
		$result = api_sql_query($sqlExercices);
		//Guardamos el número de ejercicios
		$num_filas=Database::num_rows($result);	
	}
	return $num_filas;
}		
	

/////////////////////////////////////// 

					
//Función que calcula la media de las notas obtenidas en los últimos intentos de los exámenes de un alumno en un curso
function media_examenes_alumno_curso($student_id, $course_code, $numero_total_examenes_curso) {
//EXAMENES: 

	$student_id = intval($student_id);			
	$course_code = addslashes($course_code);
	$numero_total_examenes_curso = intval($numero_total_examenes_curso);			
	$resultado=0;

	// get the informations of the course 
	$a_course = CourseManager :: get_course_information($course_code);
	
	if(!empty($a_course['db_name']))
	{		
		// table QUIZ
		$quiz_table = Database :: get_course_table(TABLE_QUIZ_TEST, $a_course['db_name']);	
		// tabla QUIZ_EXAM
		$exam_table = Database :: get_course_table(TABLE_QUIZ_TEST_EXAM, $a_course['db_name']);					
		$examenes_table = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES, $a_course['db_name']);		
		
		//Hacemos la consulta que saca la suma de todas las notas de los exámenes (active=0) para el alumno y curso dado
		
		$sqlExamenes = "SELECT sum((ej1.exe_result/ej1.exe_weighting)*10) as suma 
		FROM $examenes_table as ej1, $quiz_table  as q1, $exam_table ex 
		WHERE ej1.exe_user_id =".$student_id."  AND ej1.exe_cours_id = '".$course_code."' 
		AND ej1.exe_exo_id=q1.id and q1.id=ex.id and q1.active='1'
		and ej1.exe_id in 
			(select max(ej.exe_id) FROM  $examenes_table as ej, $quiz_table as q, $exam_table ex 
			 WHERE ej.exe_user_id =".$student_id."  
			 AND ej.exe_cours_id = '".$course_code."' AND  ej.exe_exo_id=q.id and q.id=ex.id and q.active='1'
			 group by ej.exe_cours_id,ej.exe_user_id,ej.exe_exo_id)";

//echo $sqlExamenes; die();

		$resultExamenes = api_sql_query($sqlExamenes);	
		if (($row=Database::fetch_array($resultExamenes)) && ($numero_total_examenes_curso!=0)) 
		{
			$resultado=round($row['suma']/$numero_total_examenes_curso,2);
		}
//echo $row['suma']." ---- ".$numero_total_examenes_curso; die();

	}
	return $resultado;
}	


////////////
//Función que calcula la media de las notas obtenidas en los últimos intentos de los ejercicios de un alumno en un curso
function media_ejercicios_alumno_curso($student_id, $course_code, $numero_total_ejercicios_curso) {
//EJERCICIOS: 

	$student_id = intval($student_id);			
	$course_code = addslashes($course_code);
	$numero_total_ejercicios_curso = intval($numero_total_ejercicios_curso);			
	$resultado=0;

	// get the informations of the course 
	$a_course = CourseManager :: get_course_information($course_code);
	
	if(!empty($a_course['db_name']))
	{		
		// table QUIZ
		$quiz_table = Database :: get_course_table(TABLE_QUIZ_TEST, $a_course['db_name']);	
		// tabla QUIZ_EXAM
		$exam_table = Database :: get_course_table(TABLE_QUIZ_TEST_EXAM, $a_course['db_name']);			
		$ejercicios_table = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES, $a_course['db_name']);		
		
		//Hacemos la consulta que saca la suma de todas las notas de los últimos intentos de los ejercicios (active=1) para el alumno y curso dado
	
		$sqlEjercicios = "SELECT sum((ej1.exe_result/ej1.exe_weighting)*10) as suma FROM $ejercicios_table as ej1, $quiz_table  as q1 
		WHERE ej1.exe_user_id =".$student_id."  AND ej1.exe_cours_id = '".$course_code."' AND ej1.exe_exo_id=q1.id and q1.active='1'
		 and q1.id NOT IN (select ex.id from $exam_table ex)	
		 and ej1.exe_id in (select max(ej.exe_id) FROM  $ejercicios_table as ej, $quiz_table as q WHERE ej.exe_user_id =".$student_id."  
		AND ej.exe_cours_id = '".$course_code."' AND  ej.exe_exo_id=q.id and q.active='1'  and q.id NOT IN (select ex.id from $exam_table ex)
		 group by ej.exe_cours_id,ej.exe_user_id,ej.exe_exo_id)";

//echo $sqlEjercicios; die();
		
		//echo "sql: ".$sqlEjercicios;
		
		$resultEjercicios = api_sql_query($sqlEjercicios);	
		if (($row=Database::fetch_array($resultEjercicios)) && ($numero_total_ejercicios_curso!=0)) 		
		{
			$resultado=round($row['suma']/$numero_total_ejercicios_curso,2);
		}		
//echo $row['suma']." ---- ".$numero_total_ejercicios_curso; die();
	}
	return $resultado;
}	


function numero_scos_curso ($course_code) {

	$course_code = addslashes($course_code);

	// get the informations of the course 
	$a_course = CourseManager :: get_course_information($course_code);
		
	$numeroSCO=0;
		
	if(!empty($a_course['db_name']))
	{		
		// table definition
		$item_table = Database :: get_course_table(TABLE_LP_ITEM, $a_course['db_name']);		
	
		$sql = "SELECT id FROM " . $item_table . " WHERE item_type in ('sco','asset')";

		$rs = api_sql_query($sql, __LINE__, __FILE__);
		$numeroSCO = Database::num_rows($rs);
	}	
		
	return $numeroSCO;
}


function progreso_scos_curso ($student_id, $course_code, $num_scos) {

	$student_id = intval($student_id);			
	$num_scos = intval($num_scos);			
	$course_code = addslashes($course_code);

	// get the informations of the course 
	$a_course = CourseManager :: get_course_information($course_code);

	$suma_progreso=0;
	$progresoScos=0;
		
	if(!empty($a_course['db_name']))
	{		
		// table definition
		$item_table = Database :: get_course_table(TABLE_LP_ITEM, $a_course['db_name']);		
		$item_view_table = Database :: get_course_table(TABLE_LP_ITEM_VIEW, $a_course['db_name']);		
		$view_table = Database :: get_course_table(TABLE_LP_VIEW, $a_course['db_name']);		
	
		//$sql = "SELECT sum(iv.score) as suma_progreso FROM $item_table as i, $item_view_table  as iv, $view_table as v WHERE  i.id = iv.lp_item_id AND iv.lp_view_id=v.id AND v.user_id =".$student_id." AND i.item_type='sco'";
		$sql = "SELECT iv.score as progreso, i.item_type, iv.status FROM $item_table as i, $item_view_table  as iv, $view_table as v 
			WHERE  i.id = iv.lp_item_id AND iv.lp_view_id=v.id AND v.user_id =".$student_id." AND i.item_type in ('sco','asset')";

		$rsscos = api_sql_query($sql, __LINE__, __FILE__);				
		while ($row=Database::fetch_array($rsscos)) 		
		{
			if ($row['item_type']=='sco')	{
				$suma_progreso+=$row['progreso'];
			}else {
				// El tipo asset no guarda progreso, si el status=completed, se considera un 100% de progreso
				if ($row['item_type']=='asset' && $row['status']=='completed')	{
					$suma_progreso+=100;
				}
			}
		}		
		if ($num_scos!=0)
	    	{		
			$progresoScos=round($suma_progreso/$num_scos,2);
		}

	}	
		
	return $progresoScos;
}

// Devuelve la nota de un examen para el curso y alumno dado
// Si no hay nota válida, devuelve '-'
function nota_examen_alumno  ($student_id, $course_code, $exercice_id) {

	$course_code = addslashes($course_code);
    // get the informations of the course 
	$a_course = CourseManager :: get_course_information($course_code);
	$notaExamen='-';

	
	if(!empty($a_course['db_name'])) {
		$ejercicios_table = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES, $a_course['db_name']);		
	}	

	//Hacemos la consulta que saca la nota del último intento del examen para un alumno y curso dado que se haya realizado para el id de ejercicio dado	
	$sqlScore = "SELECT exe_id , exe_result,exe_weighting FROM $ejercicios_table WHERE exe_user_id = ".$student_id." AND exe_cours_id = '".$course_code."' AND exe_exo_id = ".$exercice_id." ORDER BY exe_date DESC LIMIT 1";	
	
	//echo $sqlScore;
	$resultScore = api_sql_query($sqlScore);	

	$a_score=Database::fetch_array($resultScore);
	
	if(!empty($a_score)){
			if  ($a_score['exe_weighting']!=0) {
					$notaExamen=round(($a_score['exe_result']/$a_score['exe_weighting'])*10,2);
			}
	}
	
	//die($notaExamen);
	return $notaExamen;
	
	//var_dump($resultScore);
	//die();			
}


// Devuelve la nota de un ejercicio para el curso y alumno dado
// Si no hay nota válida, devuelve '-'
function nota_ejercicio_alumno  ($student_id, $course_code, $exercice_id) {

	$course_code = addslashes($course_code);
    // get the informations of the course 
	$a_course = CourseManager :: get_course_information($course_code);
	$notaEjercicio='-';

	
	if(!empty($a_course['db_name'])) {
		$ejercicios_table = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES, $a_course['db_name']);		
	}	

	//Hacemos la consulta que saca la nota del último intento del ejercicio para el curso, alumno y ejercicio dado
	$sqlScore = "SELECT exe_id , exe_result,exe_weighting FROM $ejercicios_table WHERE exe_user_id = ".$student_id." AND exe_cours_id = '".$course_code."' AND exe_exo_id = ".$exercice_id." ORDER BY exe_date DESC LIMIT 1";	
	
	//echo $sqlScore;
	$resultScore = api_sql_query($sqlScore);	

	$a_score=Database::fetch_array($resultScore);
	
	if(!empty($a_score)){
			if  ($a_score['exe_weighting']!=0) {
					$notaEjercicio=round(($a_score['exe_result']/$a_score['exe_weighting'])*10,2);
			}
	}
	
	//die($notaExamen);
	return $notaEjercicio;
	
	//var_dump($resultScore);
	//die();			
}


//Devuelve un resulset con los alumnos de un curso (excluyendo teletutores)
function alumnos_reales_curso  ($course_code) {

	$course_code = addslashes($course_code); 

	$alumnos_table=Database :: get_main_table(TABLE_MAIN_USER);
	$matriculados_table=Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	
	//NOTA: Hacemos la consulta que saca todos los alumnos del curso dado EXCLUYENDO LOS ALUMNOS DEMOS (se considera demo al que tiene como nombre y apellido una cadena que 	empieza por 'demo') y excluyendo también los teletutores (tienen el campo COURSE_REL_USER.STATUS=1)
	//Usada para que las notas de los demos NO influyan en las medias y tampoco las notas de los teletutores
	//$sqlAlumnos = "SELECT m.user_id FROM $alumnos_table a,  $matriculados_table m WHERE a.user_id=m.user_id and m.course_code='".$course_code."' and substr(lower(a.firstname),1,4)<>'demo' and substr(lower(a.lastname),1,4)<>'demo' and m.status<>1  ORDER BY lastname,firstname";	

	//NOTA: Hacemos la consulta que saca a todos los alumnos del curso y excluye a los teletutores (tienen el campo COURSE_REL_USER.STATUS=1)
	//Usada para que las notas de los teletutores NO influyan en las medias
	$sqlAlumnos = "SELECT m.user_id FROM $alumnos_table a,  $matriculados_table m WHERE a.user_id=m.user_id and m.course_code='".$course_code."' and m.status<>1  ORDER BY lastname,firstname";

	//echo "<p>".$sqlAlumnos;
	
	$resultAlumnos = api_sql_query($sqlAlumnos);	
	
	return $resultAlumnos;
}

//Devuelve un resulset con los datos de un alumno
function alumno_real_curso  ($course_code,$user_id) {

	$course_code = addslashes($course_code); 
	$alumnos_table=Database :: get_main_table(TABLE_MAIN_USER);
	$matriculados_table=Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$sqlAlumnos = "SELECT m.user_id FROM $alumnos_table a,  $matriculados_table m WHERE a.user_id=m.user_id and m.course_code='".$course_code."' and a.user_id= $user_id and m.status<>1  ORDER BY m.user_id";	
	$resultAlumnos = api_sql_query($sqlAlumnos);	
	return $resultAlumnos;
}

//Devuelve la nota media de los exámenes realizados por alumnos (se excluyen teletutores), para un curso y examen dado
function nota_media_examen ($course_code, $exercice_id) {
	
	$resultado=0;
	$cuantos=0;
	
	//NOTA: obtenemos alumnos reales del curso	
	$resultAlumnos = Tracking_fd :: alumnos_reales_curso($course_code);		
		
	while ($row = Database :: fetch_array($resultAlumnos)) 
	{
		//obtenemos nota para cada alumno en el curso para el examen tratado		
		$nota_alumno=Tracking_fd :: nota_examen_alumno($row['user_id'], $course_code, $exercice_id);		
		//echo "<p>-----nota alumno: " .$nota_alumno;
		if (is_numeric($nota_alumno))  
		{
			$resultado+=$nota_alumno;
			$cuantos+=1;			
			//echo "<p>----------RESULTADO: ". $resultado;
			//echo "<p>----------CUANTOS: ". $cuantos;
		}
	}
	if ($cuantos!=0)  
	{
		$resultado=round(($resultado/$cuantos),2);		
		//echo "<p>cuantos: ". $cuantos;
	}	
	//echo "<p>ejercicio $exercice_id, nota media x tema:  " .$resultado;
	return $resultado;				

}


//Devuelve la nota media de los ejercicios realizados por alumnos (se excluyen teletutores), para un curso y ejercicio dado
function nota_media_ejercicio ($course_code, $exercice_id) {
	
	$resultado=0;
	$cuantos=0;
	
	//NOTA: obtenemos alumnos reales del curso	
	$resultAlumnos = Tracking_fd :: alumnos_reales_curso($course_code);		
		
	while ($row = Database :: fetch_array($resultAlumnos)) 
	{
		//obtenemos nota para cada alumno en el curso para el ejercicio tratado		
		$nota_alumno=Tracking_fd :: nota_ejercicio_alumno($row['user_id'], $course_code, $exercice_id);		
		//echo "<p>-----nota alumno: " .$nota_alumno;
		if (is_numeric($nota_alumno))  
		{
			$resultado+=$nota_alumno;
			$cuantos+=1;			
			//echo "<p>----------RESULTADO: ". $resultado;
			//echo "<p>----------CUANTOS: ". $cuantos;
		}
	}
	if ($cuantos!=0)  
	{
		$resultado=round(($resultado/$cuantos),2);		
		//echo "<p>cuantos: ". $cuantos;
	}	
	//echo "<p>ejercicio $exercice_id, nota media x tema:  " .$resultado;
	return $resultado;				

}


function get_conexiones_plataforma($student_id) {
	$tbl_track_login = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_LOGIN);
	$sql = 'SELECT login_date,logout_date FROM ' . $tbl_track_login . ' 
			WHERE login_user_id = ' . intval($student_id) . ' 
			ORDER BY login_date DESC';
	$rs = api_sql_query($sql,__FILE__,__LINE__);


	while ($conexion = Database::fetch_array($rs))
			{
				$conexiones [] = $conexion;
			}


	return $conexiones;
}

function get_conexiones_curso($course_code, $student_id) {
	$tbl_track__course_access = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_COURSE_ACCESS);
	$sql = "SELECT login_course_date,logout_course_date FROM $tbl_track__course_access " . 
			"WHERE user_id ="  . intval($student_id) . " and course_code= '$course_code' ORDER BY login_course_date DESC";
	$rs = api_sql_query($sql,__FILE__,__LINE__);


	while ($conexion = Database::fetch_array($rs))
			{
				$conexiones [] = $conexion;
			}


	return $conexiones;
}


function get_last_connection_date_on_the_course($student_id, $course_code) {
		
		// get the informations of the course 
		$a_course = CourseManager :: get_course_information($course_code);	
		
		// Fecha más reciente de inicio de sco's
		if(!empty($a_course['db_name']))  {		

			// tabla LP_ITEM
			$tbl_course_lp_item = Database :: get_course_table(TABLE_LP_ITEM, $a_course['db_name']);				
			$tbl_course_lp_view_item = Database :: get_course_table(TABLE_LP_ITEM_VIEW, $a_course['db_name']);		
			$tbl_course_lp_view = Database :: get_course_table(TABLE_LP_VIEW, $a_course['db_name']);		

			$sql = "SELECT  max(iv.start_time) as fecha_inicio_sco  
					FROM ".$tbl_course_lp_item."  as i, ".$tbl_course_lp_view_item."  as iv, ".$tbl_course_lp_view."  as v 
				 WHERE i.id = iv.lp_item_id AND iv.lp_view_id = v.id  AND v.user_id =" .$student_id."  AND i.item_type='sco' 
				 ORDER BY  i.id";

			$rs_sco   = api_sql_query($sql, __FILE__, __LINE__);
			if(Database::num_rows($rs_sco)>0){
				$max_start_time = Database::result($rs_sco,0,'fecha_inicio_sco');
			}
		}


		// Fecha de último acceso
		$tbl_track_login = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_COURSE_ACCESS);
		$sql = 'SELECT login_course_date FROM ' . $tbl_track_login . ' 
						WHERE user_id = ' . intval($student_id) . ' 
						AND course_code = "' . Database::escape_string($course_code) . '"
						ORDER BY login_course_date DESC LIMIT 0,1';

		$rs_access = api_sql_query($sql,__FILE__,__LINE__);

		$num_filas=0;
		$num_filas=Database::num_rows($rs_access);

		if($num_filas>0) { // Existen registros de último acceso, se compara con fecha de inicio de los sco's para tomar la más reciente 

			if ($last_login_date = Database::result($rs_access, 0, 0)) {
			// Se toma la última fecha entre el último acceso (TABLE_STATISTIC_TRACK_E_COURSE_ACCESS.login_course_date) y la fecha de inicio 
			// más reciente de un sco del curso, para mostrarla en el informe de seguimiento

				// última vez que se logó el usuario
				$timestamp = strtotime($last_login_date);
				
/*	
				$log="ultimo__acceso : ".$timestamp." fecha: ".format_locale_date(get_lang('DateFormatLongWithoutDay'), $timestamp);
				$log.="<br>max_start_time: ".$max_start_time." fecha: ".format_locale_date(get_lang('DateFormatLongWithoutDay'), $max_start_time);
*/

				if ($max_start_time>$timestamp) {
					$timestamp = $max_start_time;
				}
				
/*
				return $log."<br>-------------<br>FECHA MÁS RECIENTE: ".format_locale_date(get_lang('DateFormatLongWithoutDay'), $timestamp);
*/
				if (!empty($timestamp)) {
					return format_locale_date(get_lang('DateFormatLongWithoutDay'), $timestamp);
				}					
				
			}

		}else  {  // No hay registros de último acceso, se toma la fecha de inicio de los sco's más reciente para mostrar en informe
			if (!empty($max_start_time)) {
				return format_locale_date(get_lang('DateFormatLongWithoutDay'),$max_start_time); 	
			}
		}
		return false;
}


function get_time_total_sco($student_id, $course_code) {

		$total_time=0;
		
		// get the informations of the course 
		$a_course = CourseManager :: get_course_information($course_code);	
		
		// Fecha más reciente de inicio de sco's
		if(!empty($a_course['db_name']))  {		

			// tabla LP_ITEM
			$tbl_course_lp_item = Database :: get_course_table(TABLE_LP_ITEM, $a_course['db_name']);				
			$tbl_course_lp_view_item = Database :: get_course_table(TABLE_LP_ITEM_VIEW, $a_course['db_name']);		
			$tbl_course_lp_view = Database :: get_course_table(TABLE_LP_VIEW, $a_course['db_name']);		

			$sql = "SELECT  sum(iv.total_time) as total_time 
					FROM ".$tbl_course_lp_item."  as i, ".$tbl_course_lp_view_item."  as iv, ".$tbl_course_lp_view."  as v 
				 WHERE i.id = iv.lp_item_id AND iv.lp_view_id = v.id  AND v.user_id =" .$student_id."  AND i.item_type='sco' 
				 ORDER BY  i.id";

			$rs_sco   = api_sql_query($sql, __FILE__, __LINE__);
			if(Database::num_rows($rs_sco)>0){
				$total_time = Database::result($rs_sco,0,'total_time');
			}
		}

		return $total_time;
}


function get_intentos_ejercicios($course_code, $student_id) {

	$tbl_track_e_exercice = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);	
	$quiz_table = Database :: get_course_table(TABLE_QUIZ_TEST, $a_course['db_name']);	
	$exam_table = Database :: get_course_table(TABLE_QUIZ_TEST_EXAM, $a_course['db_name']);	
	
	$sql = " SELECT exe_date,title,exe_result,exe_weighting
             FROM $tbl_track_e_exercice ce,$quiz_table q
             where exe_cours_id = '".$course_code."'
             and ce.exe_user_id = ".$student_id."
             and q.id = ce.exe_exo_id and q.active=1
             and exe_exo_id NOT IN (select id from $exam_table)
             order by exe_user_id,exe_exo_id;";
			 
    
	
    $rs = api_sql_query($sql,__FILE__,__LINE__);

	$ar_ejercicios = Array();
	while ($ejercicios = Database::fetch_array($rs))
			{

				$ar_ejercicios[] = $ejercicios;
			}


	return $ar_ejercicios;
}



function get_intentos_examenes($course_code, $student_id) {

	$tbl_track_e_exercice = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);	
	$quiz_table = Database :: get_course_table(TABLE_QUIZ_TEST, $a_course['db_name']);
	$exam_table = Database :: get_course_table(TABLE_QUIZ_TEST_EXAM, $a_course['db_name']);		
	
	$sql = " SELECT exe_date,title,exe_result,exe_weighting
             FROM $tbl_track_e_exercice ce, $quiz_table q, $exam_table ex
             where exe_cours_id = '".$course_code."'
             and ce.exe_user_id = ".$student_id."
             and q.id = ce.exe_exo_id and q.id=ex.id and q.active=1
             order by exe_date asc";
			 
    
	
    $rs = api_sql_query($sql,__FILE__,__LINE__);

	$ar_examenes = Array();
	while ($examenes = Database::fetch_array($rs))
			{

				$ar_examenes[] = $examenes;
			}


	return $ar_examenes;
}



}
?>
