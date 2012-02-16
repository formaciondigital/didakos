<?php

require ('../inc/global.inc.php');
require_once (api_get_path(LIBRARY_PATH).'tracking.lib.php');
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once ('../newscorm/learnpath.class.php');

//Incluimos librería de utilidades generales
require_once (api_get_path(LIBRARY_PATH).'main_api.lib.php');
require_once (api_get_path(LIBRARY_PATH).'tracking.lib_fd.php');

// Database table definitions
$tbl_course 				= Database :: get_main_table(TABLE_MAIN_COURSE);
$tbl_user 					= Database :: get_main_table(TABLE_MAIN_USER);
$tbl_admin 					= Database :: get_main_table(TABLE_MAIN_ADMIN);
$tbl_stats_exercices 		= Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$tbl_stats_attempt 		= Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
$permitido=0;


if (!isset($_GET['student']) || !isset($_GET['course']) || !isset($_GET['exercice']) )  //Error
{
	echo "Error: Faltan parámetros.";	
}
else 
{
	$id_user=$_GET['student'];
	$id_course=$_GET['course'];	
	$id_exercice=$_GET['exercice'];		
	
	//Depuración: Para ver datos de sesión
	/*
	print_r($_SESSION);
	$id_user_session=$_SESSION[_user][user_id];  
	echo "<p>Usuario de la sesión: ". $id_user_session;
	*/
	
	// comprobamos que invoca la página un usuario que ha iniciado sesión como administrador (o gestor) o teletutor.
	$id_user_session=$_SESSION[_user][user_id];
	$sql_user = "SELECT user_id FROM ".$tbl_user." WHERE user_id=".$id_user_session." and   (status=1 or user_id in (select user_id from ". $tbl_admin."))";
	//echo $sql_user;
	$rs_user = api_sql_query($sql_user, __FILE__, __LINE__);
	if (Database :: num_rows($rs_user)>0) 
	{
		$permitido=1;
	}	
}

if ($permitido==0) 
{
	echo "No ha iniciado sesión con permisos para esta acción";
}	
else 
{	// Hacemos el borrado de nota y rastro del examen

		$id_course = Database::escape_string($id_course);

		// obtenemos el exe_id correspondiente en la tabla TRACK_E_EXERCICES para el alumno, curso y examen dado. Con este exe_id se localizan las 10 filas correspondientes al rastro de la corrección de cada pregunta del examen en la tabla TRACK_E_ATTEMPT
		$sql = "SELECT exe_id FROM ".$tbl_stats_exercices." WHERE exe_cours_id='".$id_course."' and exe_user_id=".$id_user." and exe_exo_id=".$id_exercice; 
		$rs = api_sql_query($sql, __FILE__, __LINE__);

		if ($row = Database :: fetch_array($rs))
		{
			$exe_id=$row['exe_id'];
	
			//eliminamos rastro (10 filas, una por cada pregunta del examen)
			$sql_elimina_rastro = "DELETE FROM ".$tbl_stats_attempt." WHERE course_code='".$id_course."' and user_id=".$id_user." and exe_id=".$exe_id; 
			$rs_elimina_rastro = api_sql_query($sql_elimina_rastro, __FILE__, __LINE__);
			//eliminamos nota (una fila con la nota del examen)
			$sql_elimina_nota = "DELETE FROM ".$tbl_stats_exercices." WHERE exe_cours_id='".$id_course."' and exe_user_id=".$id_user." and exe_exo_id=".$id_exercice; 
			$rs_elimina_nota = api_sql_query($sql_elimina_nota, __FILE__, __LINE__);		
		}

?>

		<script language="javascript">		
		function envia(usuario, curso)
		{			
				alert('Examen borrado');
				location.href="my_progress_details_course_x14.php?student="+usuario+"&details=true&course="+curso+"&origin=tracking_course";		
		}	
		</script>

<?php
		echo "<script language='javascript'>envia(".$id_user.",'".$id_course."');</script>";				
}		
 ?>

	 




