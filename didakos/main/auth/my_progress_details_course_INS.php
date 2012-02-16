<?php
unset($_GET['cidReq']);
// name of the language file that needs to be included
$language_file = array('registration','tracking','exercice');

$cidReset = true;

require ('../inc/global.inc.php');
require_once (api_get_path(LIBRARY_PATH).'tracking.lib.php');
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once ('../newscorm/learnpath.class.php');

//Incluimos librería de exportación a CSV, XLS,... de FD
require_once (api_get_path(LIBRARY_PATH).'export.lib.inc_FD.php');

//Incluimos librería de utilidades generales
require_once (api_get_path(LIBRARY_PATH).'main_api.lib.php');
require_once (api_get_path(LIBRARY_PATH).'tracking.lib_fd.php');


$nameTools    = get_lang('MyProgress');
$this_section = 'session_my_progress';

api_block_anonymous_users();


/*#########################################################*/
/*				SI SE VA A EXPORTAR, NO SE MUESTRA NADA          */
/*#########################################################*/
$export_csv = isset($_GET['export']) && $_GET['export'] == 'csv' ? true : false;
if($export_csv){
	ob_start();
}
else{
	Display :: display_header($nameTools);
}
$csv_content = array();



// Database table definitions
$tbl_course 				     = Database :: get_main_table(TABLE_MAIN_COURSE);
$tbl_user 					     = Database :: get_main_table(TABLE_MAIN_USER);
$tbl_session 				     = Database :: get_main_table(TABLE_MAIN_SESSION);
$tbl_course_user 			   = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$tbl_course_user_fd 		 = Database :: get_main_table(TABLE_MAIN_COURSE_USER_FD);
$tbl_stats_lastaccess 	 = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_LASTACCESS);
$tbl_stats_exercices 		 = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$tbl_course_lp_view 		 = Database :: get_course_table('lp_view');
$tbl_course_lp_view_item = Database :: get_course_table('lp_item_view');
$tbl_course_lp 				   = Database :: get_course_table('lp');
$tbl_course_lp_item 		 = Database :: get_course_table('lp_item');
$tbl_course_quiz 			   = Database :: get_course_table('quiz');
$tbl_course_quiz_exam 			   = Database :: get_course_table('quiz_exam');


/*************************************************************************************************/
/* 						SEGUIMIENTO DE FD												    	*/			
/*************************************************************************************************/

$nombre_alumno = '';
$id_course     = $_GET['course'];
$profesor      = 0;
$parametros    = '';
//SIMPRE VA A SER UN INSPECTOR EL QUE VEA ESTE INFORME
/*if (!isset($_GET['student'])) //Estamos en página para el alumno
{
	$id_usuario = $_user['user_id'];
	$parametros = "?course=$id_course";
}
else //Estamos en página para el profesor o el admin
{*/
	$id_usuario    = $_GET['student'];
	$student_datas = UserManager :: get_user_info_by_id($id_usuario);
	$nombre_alumno = ' - '.$student_datas['lastname'].', '.$student_datas['firstname'];
	$profesor      = 1;
	$parametros    = "?student=$id_usuario&details=true&course=$id_course&origin=tracking_course";
//}

	$student_datas = UserManager :: get_user_info_by_id($id_usuario);
	$nombre_alumno = ' - '.$student_datas['lastname'].', '.$student_datas['firstname'];


/* Actualización de la nota global por parte del teletutor
if (($profesor==1) && (isset($_POST['nota_global'])) && ($_POST['cambia_nota']=='1') ){	
		if ($_POST['nota_global']==''){
			$nota_global='null';	
		}
		else{
			$nota_global=intval($_POST['nota_global']);	
		}
				
		$sql_cambia_nota ="UPDATE ".$tbl_course_user_fd." SET nota_global=".$nota_global." WHERE course_code='".$id_course."' and user_id=".$id_usuario;
		//echo "<p>".$sql_cambia_nota;
		$result = api_sql_query($sql_cambia_nota, __FILE__, __LINE__);
}*/

//Obtenemos la nota global de alumno en el curso
$sql    = "SELECT nota_global FROM  ".$tbl_course_user_fd." WHERE course_code='".$id_course."' and user_id=".$id_usuario;
$result = api_sql_query($sql, __FILE__, __LINE__);

if ($row = Database :: fetch_array($result)) {	
	$nota_global=$row['nota_global'];
}
		
$course        = Database::escape_string($id_course);
$a_infosCours  = CourseManager::get_course_information($course);
//$possible_status = array('not attempted','incomplete','completed','passed','failed','browsed');
$estados_scorm = array("not attempted"=>get_lang('Sin_intentar'),"incomplete"=>get_lang('Incompleto'),"completed"=>get_lang('Completado'),"passed"=>get_lang('Aprobado'),"failed"=>get_lang('Suspenso'),"browsed"=>get_lang('Visionado'));


/*
 CAMBIO EN PRESENTACIÓN DE TIEMPO TOTAL EN INFORME	
 Debido a que no siempre se graba la fecha de acceso a la plataforma o de inicio del sco del curso, para evitar casos inconsistentes en los 
 tiempos mostrados (tiempo total en el apartado General MENOR que el tiempo del Resumen del Seguimiento del curso (que es la sumatoria de los tiempos
 invertidos en cada sco del curso), se mostrará el tiempo MAYOR entre estos dos datos. 
*/
//$tiempo_del_curso = api_time_to_hms(Tracking :: get_time_spent_on_the_course($id_usuario, $id_course));

$tiempo_total_general = Tracking :: get_time_spent_on_the_course($id_usuario, $id_course);
$tiempo_total_sco = Tracking_fd :: get_time_total_sco($id_usuario, $id_course);

/*
$log_tiempo="tiempo_total_general: ".$tiempo_total_general."<br>tiempo_total_sco: ".$tiempo_total_sco;
*/
if ($tiempo_total_general>$tiempo_total_sco)  {
	$tiempo_del_curso=api_time_to_hms($tiempo_total_general);
}else  {
	$tiempo_del_curso=api_time_to_hms($tiempo_total_sco);	
}
/*
$log_tiempo=$log_tiempo."<br>---------------<br>TIEMPO MAYOR: ".$tiempo_del_curso;
*/

/*
 CAMBIO EN PRESENTACIÓN DE FECHAS EN INFORME	
 Para obtener el último acceso, se presenta en el informe de seguimiento, la última fecha, fecha más reciente entre 
 la última vez que se logó el usuario y la última fecha de inicio de los sco's del seguimiento del curso. 
 Esto se hace para evitar casos inconsistentes en la presentación de fechas, como que se muestre un último acceso  
 anterior a alguna fecha de inicio del sco del curso
 (debido a que no siempre se graba la fecha de acceso a la plataforma o de inicio del sco del curso).
*/
//$lastConnexion    = Tracking :: get_last_connection_date_on_the_course($id_usuario,$id_course);
$lastConnexion    = Tracking_fd :: get_last_connection_date_on_the_course($id_usuario,$id_course);
$firstConnexion   = Tracking :: get_first_connection_date_on_the_course($id_usuario,$id_course);


/*#########################     FORMATEADO PARA QUE NO POSEA EL ESTILO DEL WARNING     ###########################*/
$lastConnexion_tmp  = substr( $lastConnexion, strpos($lastConnexion, '>')+1 , strlen($lastConnexion) );
$lastConnexion_csv  = substr( $lastConnexion_tmp, 0, strpos($lastConnexion_tmp, '<') );

$num_mensajes       = Tracking ::count_student_messages($id_usuario,$id_course);
$num_mensajes_buzon = Tracking_fd ::count_dropbox_file($id_usuario,$id_course);

/*************************************************************************************************/
/* 						FIN SEGUIMIENTO DE FD												    	*/			
/*************************************************************************************************/




// get course list
$sql = 'SELECT course_code FROM '.$tbl_course_user.' WHERE user_id='.$id_usuario;
$rs = api_sql_query($sql, __FILE__, __LINE__);
$Courses = array();
while($row = Database :: fetch_array($rs)){
	$Courses[$row['course_code']] = CourseManager::get_course_information($row['course_code']);
}

//Obtenemos nombre del curso para mostrarlo
$sql ="SELECT title as nombre_curso FROM  ".$tbl_course." WHERE code='".$id_course."'";
$result = api_sql_query($sql, __FILE__, __LINE__);
if ($row = Database :: fetch_array($result)) {	
	$nombre_curso = $row['nombre_curso'];
}


//##########  CSV	 ##########
	/*$csv_content[] = array($row['nombre_curso'].$nombre_alumno);
	$csv_content[] = array("");
	$csv_content[] = array(get_lang('GENERAL'));
	$csv_content[] = array(get_lang('TiempoTotal'),get_lang('MensajesForo'),get_lang('MensajesBuzon'),get_lang('FirstAccess'),get_lang('LastAccess'),get_lang('EvaluacionGlobal'));
	$csv_content[] = array($tiempo_del_curso,$num_mensajes,$num_mensajes_buzon,$firstConnexion,$lastConnexion_csv,$nota_global);*/
?>

<!--
<script language="javascript">
	function compruebaNota(){			
		var nota;
		nota=document.formulario_nota.nota_global.value;		
		
		if (isNaN(nota) || (nota.indexOf(",")>= 0) || (nota.indexOf(".")>= 0) || (nota<0) || (nota>10)){
			document.formulario_nota.cambia_nota.value=0;
			alert("<?php// echo get_lang('Debe introducir un valor numérico entero comprendido entre 0 y 10'); ?>");						
			document.formulario_nota.nota_global.focus();
		}					
		else{
			document.formulario_nota.cambia_nota.value=1;
			document.formulario_nota.submit();
		}			
	}
	
	function disableEnterKey() { 
		if (window.event.keyCode == 13) window.event.keyCode = 0; 
	}
</script>-->

<?php echo "<h2>".$row['nombre_curso'].$nombre_alumno."</h2>"; 	?>


		
<form action="<?php echo $_SERVER['PHP_SELF'].$parametros ?>" method="POST" id="formulario_nota" name="formulario_nota" onKeyPress="disableEnterKey()">	
<table class="data_table" width="100%">
<tr class="tableName"><td colspan="6"><strong><?php echo get_lang('GENERAL'); ?></strong></td></tr>
<tr class="tableName">
	<th><?php echo get_lang('MensajesForo'); ?></th> 
	<th><?php echo get_lang('FirstAccess'); ?></th> 
	<th><?php echo get_lang('LastAccess'); ?></th> 
	<th><?php echo get_lang('EvaluacionGlobal'); ?></th> 	
</tr>
<tr class='<?php echo $i?'row_odd':'row_even'; ?>'>
	<td><?php echo $num_mensajes ?></td>
	<td><?php echo $firstConnexion ?></td>
	<td><?php echo $lastConnexion ?></td>
	<td align='right'><?php
	// EN ESTE INFORME SOLO SE PUEDE VER LA NOTA,NUNCA CAMBIARLA
			echo $nota_global;?>
    </td>
</tr>
</table>
</form>
<p></p>
<?php
//##########  CSV	 ##########
	/*$csv_content[] = array("");
	$csv_content[] = array("EXÁMENES");
	$csv_content[] = array("Título","Nota");*/
	
	
//EXAMENES: 
//Los exámenes son los ejercicios activos (de la tabla QUIZ) que se encuentran en la tabla QUIZ_EXAM
	
$sqlExercices = "SELECT q.title,q.id 
									 FROM ".$a_infosCours['db_name'].".".$tbl_course_quiz." q, ".$a_infosCours['db_name'].".".$tbl_course_quiz_exam." ex 
									WHERE q.id=ex.id and q.active=1 order by q.id";	

$resultExercices = api_sql_query($sqlExercices);

//Guardamos el número de exámenes
$numExamen=Database::num_rows($resultExercices);
$nota=0;
$media='-';
?>


<table  class="data_table" border='1' width=100%>
    <tr class="tableName">	
		<td colspan=3><strong><?php echo get_lang('EXAMENES'); ?></strong></td>
	</tr>
	<tr>
		<th><?php echo get_lang('langWorkTitle'); ?></th>
		<th><?php echo get_lang('Nota'); ?></th>
<?php if ($profesor==1) { ?>
		<th><?php echo get_lang('Ver_examen'); ?></th>
<?php } ?>

	</tr>

<?php
if($numExamen>0){
	while($a_exercices = Database::fetch_array($resultExercices)){
			//Hacemos la consulta que saca la nota y la ponderación del examen
			//(se muestra la nota obtenida en el último intento del examen) que se haya realizado para el id de ejercicio sacado de la tabla quiz.	
			$sqlScore = "SELECT exe_id, exe_result, exe_weighting FROM $tbl_stats_exercices 
										WHERE exe_user_id = ".$id_usuario." AND exe_cours_id = '".$id_course."' AND exe_exo_id = ".$a_exercices['id']." 
										ORDER BY exe_date DESC LIMIT 1";
			
			$resultScore = api_sql_query($sqlScore);	
			$notaExamen  = "-";
					
			//COMPROBAMOS SI HAY EXAMEN
			$examenes_hechos = Database::num_rows($resultScore);
	
			if($examenes_hechos>0){
				while($a_score = Database::fetch_array($resultScore)){
					$notaExamen = round(($a_score['exe_result']/$a_score['exe_weighting'])*10,2);
					$nota       = $nota+$notaExamen;
					$exe_id     = $a_score['exe_id'];
				}					
				$media = round($nota/$numExamen,2);						
			}
	
	
			//Mostramos el nombre del título del examen		
			echo "<tr><td>".$a_exercices['title']."</td><td>".$notaExamen."</td>";
	
	
	//##########  CSV	 ##########
			//$csv_content[] = array($a_exercices['title'],$notaExamen);
			
			//if ($profesor==1){
					echo "<td align='center'>";
					if (is_numeric($notaExamen)) {
						// mostramos detalle del examen, las preguntas  que le salieron al alumno y las respuestas que seleccionó		
						// ejemplo de llamada
						// http://version03.formaciondigital.com/main/exercice/exercise_show.php?origin=tracking_course&id=3&cidReq=10282ED1&student=5
						echo "<a href='../exam/exercise_show_fd.php?origin=tracking_course&id=".$exe_id."&cidReq=".$id_course."&student=".$id_usuario."'>ver</a>";								
					}		
					echo "&nbsp;</td>";	
			//}

			echo "</tr>";	
	}
}			

//##########  CSV	 ##########
//$csv_content[] = array("Media de los Exámenes",$media);
?>
<tr><td><?php echo get_lang('MoyenneExamenes');?></td>
    <td><?php echo $media ?></td></tr>
</table>


<?php
// SCO'S
$lesson_status = '-';
$score         = '-';
$title         = '-';
$time          = '-';
$start_time    = '-';
		
$sql = "SELECT iv.status as estado,  iv.score as progreso, iv.total_time as tiempo_sco, i.id as id_elemento, 
							 i.title as titulo_sco,  i.item_type as tipo_elemento, iv.start_time as fecha_inicio_sco  
					FROM ".$a_infosCours['db_name'].".".$tbl_course_lp_item."  as i, ".$a_infosCours['db_name'].".".$tbl_course_lp_view_item."  as iv, 
							 ".$a_infosCours['db_name'].".".$tbl_course_lp_view."  as v 
				 WHERE i.id = iv.lp_item_id AND iv.lp_view_id = v.id  AND v.user_id =" .$id_usuario."  AND (i.item_type='sco' or i.item_type='asset')
				 ORDER BY id_elemento";

	$result   = api_sql_query($sql, __FILE__, __LINE__);
	$num_scos = Database :: num_rows($result);

//##########  CSV	 ##########
	/*$csv_content[] = array("");
	$csv_content[] = array(strtoupper(get_lang('CourseTracking')));
	$csv_content[] = array("","","","(%)","");
	$csv_content[] = array(get_lang('langWorkTitle'),get_lang('StartDate'),get_lang('langScormStatusColumn'),get_lang('Progress')."(%)",get_lang('Time'));*/

/* Antiguo 
//##########  CSV	 ##########
	$csv_content[] = array("");
	$csv_content[] = array("SEGUIMIENTO CURSO");
	$csv_content[] = array("T�tulo","Fecha Inicio","Estado","Progreso(%)","Tiempo");
*/
?>	
<p></p>
<p></p>
<table class="data_table" border='1' width='100%'>
	<tr class="tableName">	<td colspan=5><strong><?php echo strtoupper(get_lang('CourseTracking')); ?></strong></td></tr>
	<tr>
	<th><?php echo get_lang('langWorkTitle'); ?></th>
	<th><?php echo get_lang('StartDate'); ?></th>
	<th><?php echo get_lang('Progress'); ?>(%)</th>
	</tr>
<?php
$time_for_total = 0;
$score_media    = '-';

if ($num_scos>0) {		
	while ($row = Database :: fetch_array($result)) {
		//$lesson_status = $row['estado'];
		$indice         = $row['estado'];
		$lesson_status  = $estados_scorm[$indice];
		$score          = $row['progreso'];
		
		$time_for_total = $time_for_total+$row['tiempo_sco'];
		$title          = $row['titulo_sco'];
		$time           = api_time_to_hms($row['tiempo_sco']);		
		$start_time     = '-';
		
		if ($row['fecha_inicio_sco']<>0) {
			$start_time =  format_locale_date(get_lang('DateFormatLongWithoutDay'),$row['fecha_inicio_sco']); 		
		}	
		//$type;
		$scoIdentifier = $row['id_elemento'];

		/* El tipo ASSET no guarda valor en LP_ITEM_VIEW.SCORE (el progreso).
		   Si LP_ITEM_VIEW.STATUS == 'completed'     --> se muestra un 100% en progreso.	
		   Si LP_ITEM_VIEW.STATUS == 'not attempted' --> se muestra un   0% en progreso.					   				
		*/
		if ($row['tipo_elemento']=='asset') { 
			if ($indice=='completed')  {			
				$score = 100;					
			}
			else {
				$score = 0;					
			}		
		}	

		$score_media    = $score_media+$score;

		echo "<tr><td>".$title."</td><td>".$start_time."</td><td>".$score."%</td></tr>";	

//##########  CSV	 ##########
	 //$csv_content[] = array($title,$start_time,$lesson_status,$score,$time);

	}
	$score_media    = round($score_media/$num_scos,2)."%";	
	$time_for_total = api_time_to_hms($time_for_total);
} 
else {
// el alumno no ha entrado aún en el itinerario de aprendizaje, no tiene filas en la tabla LP_ITEM_VIEW, por eso usamos la tabla LP_ITEM
	$sql ="SELECT i.id as id_elemento, i.title as titulo_sco,  i.item_type as tipo_elemento  
					 FROM ".$a_infosCours['db_name'].".".$tbl_course_lp_item."  as i 
					WHERE i.item_type='sco' or i.item_type='asset'
					ORDER BY id_elemento";
	
	$result = api_sql_query($sql, __FILE__, __LINE__);
	while ($row = Database :: fetch_array($result)) {
			$title = $row['titulo_sco'];
			echo "<tr><td>".$title."</td><td>".$start_time."</td><td>".$score."</td></tr>";	

//##########  CSV	 ##########
			//$csv_content[] = array($title,$start_time,$lesson_status,$score,$time);
	}	
}	

//##########  CSV	 ##########
	//$csv_content[] = array(get_lang('SynthesisView'),"","",$score_media,$time_for_total);
?>
	<tr><td><?php echo get_lang('SynthesisView'); ?></td><td>&nbsp;</td><td><?php echo $score_media ?></td></tr>
</table>

<p></p>
<?php
//##########  CSV	 ##########
	/*$csv_content[] = array("");
	$csv_content[] = array("EJERCICIOS COMPLEMENTARIOS");
	$csv_content[] = array("Título","Nota Último Intento","Número Intentos");*/

//EJERCICIOS: 
//Son los ejercicios activos (de la tabla QUIZ) que NO se encuentran en la tabla QUIZ_EXAM

$sqlExercices    = "SELECT title,id FROM ".$a_infosCours['db_name'].".".$tbl_course_quiz." q WHERE active='1' and q.id not in (SELECT ex.id FROM ".$a_infosCours['db_name'].".".$tbl_course_quiz_exam." ex) order by title";	
$resultExercices = api_sql_query($sqlExercices);

//Guardamos el número de ejercicios
$numEjercicios = Database::num_rows($resultExercices);
$nota          = 0;
$media         = '-';
?>

<table  class="data_table" border='1' width=100%>
    <tr class="tableName">	
		<td colspan=3><strong><?php echo get_lang('EJERCICIOSCOMPLEMENTARIOS'); ?></strong></td>
	</tr>
	<tr><th><?php echo get_lang('langWorkTitle'); ?></th><th><?php echo get_lang('NotaUltimoIntento'); ?></th><th><?php echo get_lang('NumeroIntentos'); ?></th></tr>
<?php
if($numEjercicios>0){
	while($a_exercices = Database::fetch_array($resultExercices)){
	
	//Hacemos la consulta que saca la nota y la ponderación del último ejercicio que se haya realizado para el id de ejercicio sacado de la tabla quiz.
	$sqlScore      = "SELECT exe_id , exe_result,exe_weighting FROM $tbl_stats_exercices 
										 WHERE exe_user_id = ".$id_usuario." AND exe_cours_id = '".$id_course."' AND exe_exo_id = ".$a_exercices['id']." 
										 ORDER BY exe_date DESC LIMIT 1";
	$resultScore   = api_sql_query($sqlScore);	
	$notaEjercicio = "-";
			
	//COMPROBAMOS SI HAY EJERCICIO
	$ejercicios_hechos = Database::num_rows($resultScore);

	if($ejercicios_hechos>0){
		while($a_score = Database::fetch_array($resultScore)){
			$notaEjercicio = round(($a_score['exe_result']/$a_score['exe_weighting'])*10,2);
			$nota          = $nota+$notaEjercicio;
		}					
		$media = round($nota/$numEjercicios,2);		
	}		
	
	//Obtenemos el número de intentos para cada ejercicio		
	$sqlIntentos     = "SELECT exe_exo_id FROM $tbl_stats_exercices 
											 WHERE exe_user_id = ".$id_usuario." AND exe_cours_id = '".$id_course."' AND exe_exo_id = ".$a_exercices['id'];	
	$resultIntentos  = api_sql_query($sqlIntentos);	
	$numero_intentos = "-";		
	$numero_intentos = Database::num_rows($resultIntentos);

	echo "<tr><td>".$a_exercices['title']."</td><td>".$notaEjercicio."</td><td>".$numero_intentos."</td></tr>";

//##########  CSV	 ##########
	$csv_content[] = array($a_exercices['title'],$notaEjercicio,$numero_intentos);

	}					
}			


//##########  CSV	 ##########
	$csv_content[] = array(get_lang('MoyenneTest'),$media,"");
?>
<tr><td><?php echo get_lang('MoyenneTest'); ?></td><td><?php echo $media ?></td><td></td>
</tr>
</table>

<?php

if($export_csv)
{

	ob_end_clean();
	Export_FD :: export_table_xls($csv_content, 'progreso_'.  $student_datas['official_code'] . '_' . $id_course);
//	Export :: export_table_csv($csv_content, 'progreso_');
}
//$id_course.'_'. $nombre_alumno.'--'

if ($_POST['cambia_nota']=='1') {
?>
<script LANGUAGE="JavaScript">
<!--
	alert("Nota global del alumno actualizada");
-->
</script>
<?php
}
Display :: display_footer();
?>
