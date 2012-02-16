<?php	
// name of the language file that needs to be included
$language_file = array('registration','tracking','exercice');

$cidReset = true;

require ('../inc/global.inc.php');
require_once (api_get_path(LIBRARY_PATH).'tracking.lib.php');
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once ('../newscorm/learnpath.class.php');

//Incluimos librería de utilidades generales
require_once (api_get_path(LIBRARY_PATH).'main_api.lib.php');
require_once (api_get_path(LIBRARY_PATH).'tracking.lib_fd.php');


$nameTools=get_lang('MyProgress');

$this_section = 'session_my_progress';

api_block_anonymous_users();

Display :: display_header($nameTools);

// Database table definitions
$tbl_course 				= Database :: get_main_table(TABLE_MAIN_COURSE);
$tbl_user 					= Database :: get_main_table(TABLE_MAIN_USER);
$tbl_admin 					= Database :: get_main_table(TABLE_MAIN_ADMIN);
$tbl_session 				= Database :: get_main_table(TABLE_MAIN_SESSION);
$tbl_course_user 			= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$tbl_session_course 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
$tbl_session_course_user 	= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
$tbl_stats_lastaccess 		= Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_LASTACCESS);
$tbl_stats_exercices 		= Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$tbl_course_lp_view 		= Database :: get_course_table('lp_view');
$tbl_course_lp_view_item 	= Database :: get_course_table('lp_item_view');
$tbl_course_lp 				= Database :: get_course_table('lp');
$tbl_course_lp_item 		= Database :: get_course_table('lp_item');
$tbl_course_quiz 			= Database :: get_course_table('quiz');


// comprobamos que invoca la página un usuario que ha iniciado sesión como administrador (o gestor) o teletutor.
	$id_user_session=$_SESSION[_user][user_id];
	$sql_user = "SELECT user_id FROM ".$tbl_user." WHERE user_id=".$id_user_session." and   (status=1 or user_id in (select user_id from ". $tbl_admin."))";
	//echo $sql_user;
	$rs_user = api_sql_query($sql_user, __FILE__, __LINE__);
	if (Database :: num_rows($rs_user)==0) 
	{
		 die('No ha iniciado sesión con permisos para esta acción');	
	}	

/*************************************************************************************************/
/* 						SEGUIMIENTO DE FD												    	*/			
/*************************************************************************************************/
?>
<script language="javascript">
	function confirmar_eliminar_examen(usuario, curso, examen)
	{		
		if (confirm("¿Está seguro de eliminar este examen?"))
		{
				location.href="borrar_examen_x14.php?student="+usuario+"&course="+curso+"&exercice="+examen;
		}			
	}
	
    function aleatorio(inferior,superior){
		
		numPosibilidades = superior - inferior
		aleat = Math.random() * numPosibilidades
		return Math.round(parseInt(inferior) + aleat)
	}

	
	function set_nota_aleatoria(ejercicio)
	{
	document.getElementById(ejercicio).value = aleatorio(5,10);
	}
	
	function valida_notas(f)
	{
	/*recorremos todos los elementos tipo input text del formulario*/
		for(var q=0;q<f.length;q++) 
		{ 
			if(f[q].type=="text") 
			{ 
			/*comprobamos que el valor de la nota sea un entero entre 0 y 10*/
			  if(f[q].value != '')
			  {//alert("no vacio");
			    if((parseInt(f[q].value) != parseFloat(f[q].value)) ||  f[q].value < 0 || f[q].value > 10)
				{
				alert("la nota debe ser un numero entero comprendido entre 1 y 10");
				return false;
				}
			  }	
			} 
		} 
	return true; 
	}
	
</script>
<?php
//Depuración: Para ver datos de sesión
/*
print_r($_SESSION);
$id_user_session=$_SESSION[_user][user_id];  
echo "<p>Usuario de la sesión: ". $id_user_session;
*/

$nombre_alumno='';
$id_course=$_GET['course'];
if (!isset($_GET['student'])) //Estamos en página para el alumno
{
	$id_usuario=$_user['user_id'];
}
else //Estamos en página para el profesor o el admin
{
	$id_usuario=$_GET['student'];
	$student_datas = UserManager :: get_user_info_by_id($id_usuario);
	$nombre_alumno=' - '.$student_datas['lastname'].', '.$student_datas['firstname'];
}



$course = Database::escape_string($id_course);
$a_infosCours = CourseManager::get_course_information($course);
//$possible_status = array('not attempted','incomplete','completed','passed','failed','browsed');
$estados_scorm=array("not attempted"=>"Sin intentar","incomplete"=>"Incompleto","completed"=>"Completado","passed"=>"Aprobado","failed"=>"Suspenso","browsed"=>"Visionado");


//echo "curso: ". $_GET['course']. "<br>";
//echo "usuario: ". $id_usuario. "<br>";

$tiempo_del_curso=api_time_to_hms(Tracking :: get_time_spent_on_the_course($id_usuario, $id_course));
//echo "tiempo del curso: ". $tiempo_del_curso. "<br>";

$lastConnexion = Tracking :: get_last_connection_date_on_the_course($id_usuario,$id_course);
$firstConnexion = Tracking :: get_first_connection_date_on_the_course($id_usuario,$id_course);

//echo "primera conexión: ". $firstConnexion. "<br>";
//echo "ultima conexión: ". $lastConnexion. "<br>";


$num_mensajes=Tracking ::count_student_messages($id_usuario,$id_course);
//echo "número de mensajes del foro: ". $num_mensajes . "<br>";


$num_mensajes_buzon=Tracking_fd ::count_dropbox_file($id_usuario,$id_course);
//echo "número de mensajes del buzón: ". $num_mensajes_buzon . "<br>";



/*************************************************************************************************/
/* 						FIN SEGUIMIENTO DE FD												    	*/			
/*************************************************************************************************/





// get course list
$sql = 'SELECT course_code FROM '.$tbl_course_user.' WHERE user_id='.$id_usuario;
$rs = api_sql_query($sql, __FILE__, __LINE__);
$Courses = array();
while($row = Database :: fetch_array($rs))
{
	$Courses[$row['course_code']] = CourseManager::get_course_information($row['course_code']);
}

// get the list of sessions where the user is subscribed as student
$sql = 'SELECT DISTINCT course_code FROM '.Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER).' WHERE id_user='.intval($id_usuario);
$rs = api_sql_query($sql, __FILE__, __LINE__);
while($row = Database :: fetch_array($rs))
{
	$Courses[$row['course_code']] = CourseManager::get_course_information($row['course_code']);
}

//api_display_tool_title($nameTools);
//Obtenemos nombre del curso para mostrarlo
$sql ="SELECT title as nombre_curso FROM  ".$tbl_course." WHERE code='".$id_course."'";
$result = api_sql_query($sql, __FILE__, __LINE__);
if ($row = Database :: fetch_array($result)) {	
	echo "<h2>".$row['nombre_curso'].$nombre_alumno."</h2>"; 	
}


$now=date('Y-m-d');

?>

<table class="data_table" width="100%">
<tr class="tableName">
	<td colspan="6">
		<strong>GENERAL</strong>
	</td>
</tr>
<tr class="tableName">
	<th>Tiempo Total</th> 
	<th>Mensajes Foro</th> 
	<th>Mensajes Buzón</th> 
	<th>Primer Acceso</th> 
	<th>Último Acceso</th> 
	<th>Evaluación Global</th> 	
</tr>
<tr class='<?php echo $i?'row_odd':'row_even'; ?>'>
	<td><?php echo $tiempo_del_curso ?></td>
	<td><?php echo $num_mensajes ?></td>
	<td><?php echo $num_mensajes_buzon ?></td>
	<td><?php echo $firstConnexion ?></td>
	<td><?php echo $lastConnexion ?></td>
	<td><?php 
		// pendiente de programar
		$evaluacion_global=" - ";
		echo $evaluacion_global ?>
	</td>
</tr>
</table>
<p></p>
<?php
//EXAMENES: 
//HACEMOS LA SELECT DE LA TABLA QUIZ PARA OBTENER LOS EXAMENES QUE PERTENECEN AL ITINERARIO DE APRENDIZAJE (ACTIVE=0 OJO DESACTIVO)
									
$sqlExercices = "SELECT q.title,q.id FROM ".$a_infosCours['db_name'].".".$tbl_course_quiz." q, ".$a_infosCours['db_name'].".".$tbl_course_lp_item." lp WHERE q.id=lp.path and lp.item_type='quiz' and q.active='0' order by lp.display_order";	

//echo $sqlExercices;
$resultExercices = api_sql_query($sqlExercices);

//Guardamos el número de exámenes
$numExamen=Database::num_rows($resultExercices);
//echo "número de exámenes: " .$numExamen;
$nota=0;
$media='-';
?>
<form name="f_cambia_nota" action="cambiar_nota_x14.php" method="post" onSubmit="javascript:return valida_notas(this);">
<input type="hidden" name="curso" value="<?php echo $id_course?>">
<input type="hidden" name="alumno" value="<?php echo $id_usuario?>">
<table  class="data_table" border='1' width=100%>

    <tr class="tableName">	
		<td colspan=4><strong>EXÁMENES</strong></td>
	</tr>
	<tr><th>Título</th><th>Nota</th><th>Borrar Examen</th></tr>
<?php
if($numExamen>0){
	while($a_exercices = Database::fetch_array($resultExercices))
	{
	//Mostramos el nombre del título del examen
	echo "<tr><td>".$a_exercices['title']."</td>";
	
	//Hacemos la consulta que saca la nota y la ponderación del examen  (sólo se puede realizar UNA VEZ cada examen) que se haya realizado para el id de ejercicio sacado de la tabla quiz.	
	$sqlScore = "SELECT exe_id , exe_result,exe_weighting FROM $tbl_stats_exercices WHERE exe_user_id = ".$id_usuario." AND exe_cours_id = '".$id_course."' AND exe_exo_id = ".$a_exercices['id']." ORDER BY exe_date DESC LIMIT 1";
	
	$resultScore = api_sql_query($sqlScore);	
	$notaExamen = "";
			
	//COMPROBAMOS SI HAY EXAMEN
	$examenes_hechos=Database::num_rows($resultScore);
	//echo "<p>examenes hechos: " .$examenes_hechos;
	if($examenes_hechos>0){
		while($a_score = Database::fetch_array($resultScore))
		{
			$notaExamen=round(($a_score['exe_result']/$a_score['exe_weighting'])*10,2);
			$nota=$nota+$notaExamen;
		}					
		$media=round($nota/$numExamen,2);						
	}
		
	echo "<td><input type=\"text\" size=\"2\" maxlength=\"2\" name=\"".$a_exercices['id']."\" id=\"".$a_exercices['id']."\" value=\"".$notaExamen."\">&nbsp;&nbsp;<a href=\"javascript:set_nota_aleatoria(".$a_exercices['id'].")\">Poner Nota Aleatoria</a></td>";
	echo "<td>";
	if (is_numeric($notaExamen)) 
	{
		//echo "<a href=borrar_examen_x13.php?student=".$id_usuario."&course=".$id_course."&exercice=".$a_exercices['id'].">Borrar</a>";					
		echo "<a href=\"javascript:confirmar_eliminar_examen(".$id_usuario.",'".$id_course."',".$a_exercices['id'].")\">Borrar</a>";				
	}		
	echo "&nbsp;</td>";	
	echo "</tr>";
	}

}			


?>
<tr><td>Media de los Exámenes</td><td><?php echo $media ?></td><td align="right"><input type="submit" value="Cambiar Notas"></td>
</tr>

</table>
</form>
<?php
// SCO'S
$lesson_status = '-';
$score = '-';
$title = '-';
$time = '-';
$start_time = '-';
		
$sql ="SELECT iv.status as estado,  iv.score as progreso, iv.total_time as tiempo_sco, i.id as id_elemento, i.title as titulo_sco,  i.item_type as tipo_elemento, iv.start_time as fecha_inicio_sco  FROM  ".$a_infosCours['db_name'].".".$tbl_course_lp_item."  as i, ".$a_infosCours['db_name'].".".$tbl_course_lp_view_item."  as iv, ".$a_infosCours['db_name'].".".$tbl_course_lp_view."  as v WHERE i.id = iv.lp_item_id AND iv.lp_view_id = v.id  AND v.user_id =" .$id_usuario."  AND i.item_type='sco' ORDER BY id_elemento";

	$result = api_sql_query($sql, __FILE__, __LINE__);
	$num_scos = Database :: num_rows($result);
	//echo "<br>número de SCOs ".$num."<br>";	
?>	
<p></p><p></p>
<table class="data_table" border='1' width='100%'>
	<tr class="tableName">	
		<td colspan=5><strong>SEGUIMIENTO CURSO</strong></td>
	</tr>
	<tr><th>Título</th><th>Fecha Inicio</th><th>Estado</th>
	<th>Progreso(%)</th>
	<th>Tiempo</th></tr>
<?php	   
$time_for_total=0;
$score_media='-';
if ($num_scos>0) {		
	while ($row = Database :: fetch_array($result)) {
		//$lesson_status = $row['estado'];
		$indice=$row['estado'];
		$lesson_status = $estados_scorm[$indice];
		
		
		$score = $row['progreso'];
		$score_media=$score_media+$score;
		$time_for_total = $time_for_total+$row['tiempo_sco'];
		$title = $row['titulo_sco'];
		$time = api_time_to_hms($row['tiempo_sco']);		
		$start_time='-';
		if ($row['fecha_inicio_sco']<>0) {
			$start_time =  format_locale_date(get_lang('DateFormatLongWithoutDay'),$row['fecha_inicio_sco']); 		
		}	
		//$type;
		$scoIdentifier = $row['id_elemento'];
		echo "<tr><td>".$title."</td><td>".$start_time."</td><td>".$lesson_status."</td><td>".$score."%</td><td>".$time."</td></tr>";	
	}
	$score_media=round($score_media/$num_scos,2)."%";	
	$time_for_total=api_time_to_hms($time_for_total);
} else {
// el alumno no ha entrado aún en el itinerario de aprendizaje, no tiene filas en la tabla LP_ITEM_VIEW, por eso usamos la tabla LP_ITEM
	$sql ="SELECT  i.id as id_elemento, i.title as titulo_sco,  i.item_type as tipo_elemento  FROM  ".$a_infosCours['db_name'].".".$tbl_course_lp_item."  as i WHERE  i.item_type='sco' ORDER BY id_elemento";
	$result = api_sql_query($sql, __FILE__, __LINE__);
	while ($row = Database :: fetch_array($result)) {
			$title = $row['titulo_sco'];
			echo "<tr><td>".$title."</td><td>".$start_time."</td><td>".$lesson_status."</td><td>".$score."</td><td>".$time."</td></tr>";	
	}	
}	
?>
<tr><td>Resumen</td><td>&nbsp;</td><td>&nbsp;</td><td><?php echo $score_media ?></td><td><?php echo $time_for_total ?></td></tr>

</table>

<p></p>
<?php
//EJERCICIOS: 
//HACEMOS LA SELECT DE LA TABLA QUIZ PARA OBTENER LOS EJERCICIOS QUE NO PERTENECEN AL ITINERARIO DE APRENDIZAJE (ACTIVE=1 OJO ACTIVO, APARECEN SÓLO EN APARTADO EJERCICIOS)
									
$sqlExercices = "SELECT title,id FROM ".$a_infosCours['db_name'].".".$tbl_course_quiz." WHERE active='1'";	
//echo $sqlExercices;
$resultExercices = api_sql_query($sqlExercices);

//Guardamos el número de ejercicios
$numEjercicios=Database::num_rows($resultExercices);
$nota=0;
$media='-';
?>

<table  class="data_table" border='1' width=100%>
    <tr class="tableName">	
		<td colspan=3><strong>EJERCICIOS COMPLEMENTARIOS</strong></td>
	</tr>
	<tr><th>Título</th><th>Nota Último Intento</th><th>Número Intentos</th></tr>
<?php
if($numEjercicios>0){
	while($a_exercices = Database::fetch_array($resultExercices))
	{
	//Mostramos el nombre del título del ejercicio
	echo "<tr><td>".$a_exercices['title']."</td>";
	
	//Hacemos la consulta que saca la nota y la ponderación del último ejercicio que se haya realizado para el id de ejercicio sacado de la tabla quiz.
	
	$sqlScore = "SELECT exe_id , exe_result,exe_weighting FROM $tbl_stats_exercices WHERE exe_user_id = ".$id_usuario." AND exe_cours_id = '".$id_course."' AND exe_exo_id = ".$a_exercices['id']." ORDER BY exe_date DESC LIMIT 1";
	$resultScore = api_sql_query($sqlScore);	
	$notaEjercicio = "-";
			
	//COMPROBAMOS SI HAY EJERCICIO
	$ejercicios_hechos=Database::num_rows($resultScore);
	//echo "<p>ejercicios hechos: " .$ejercicios_hechos;
	if($ejercicios_hechos>0){
		while($a_score = Database::fetch_array($resultScore))
		{
			$notaEjercicio=round(($a_score['exe_result']/$a_score['exe_weighting'])*10,2);
			$nota=$nota+$notaEjercicio;
		}					
		$media=round($nota/$numEjercicios,2);		
	}		
	echo "<td>".$notaEjercicio."</td>";
	
	//Obtenemos el número de intentos para cada ejercicio		
	$sqlIntentos = "SELECT exe_exo_id FROM $tbl_stats_exercices WHERE exe_user_id = ".$id_usuario." AND exe_cours_id = '".$id_course."' AND exe_exo_id = ".$a_exercices['id'];	
	$resultIntentos = api_sql_query($sqlIntentos);	
	$numero_intentos = "-";		
	$numero_intentos=Database::num_rows($resultIntentos);
	echo "<td>".$numero_intentos."</td></tr>";		
	}					
}			


?>
<tr><td>Media de los Ejercicios</td><td><?php echo $media ?></td><td></td>
</tr>
</table>

<?php
Display :: display_footer();
?>
