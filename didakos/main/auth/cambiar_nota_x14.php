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
$tbl_stats_attempt 		    = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
$tbl_course_lp_view 		= Database :: get_course_table('lp_view');
$tbl_course_lp_view_item 	= Database :: get_course_table('lp_item_view');
$tbl_course_lp 				= Database :: get_course_table('lp');
$tbl_course_lp_item 		= Database :: get_course_table('lp_item');
$tbl_course_quiz 			= Database :: get_course_table('quiz');
$tbl_course_quiz_rel_question			= Database :: get_course_table('quiz_rel_question');
$tbl_course_quiz_answer			= Database :: get_course_table('quiz_answer');

$permitido=0;









/************************************************************************
                   FUNCIONES
************************************************************************/










//******************************************************************************************************
//funcion que obtiene el id de la respuesta (correcta o incorrecta segun parametro) de una pregunta dada
//******************************************************************************************************
function get_respuesta_examen($es_correcta,$id_pregunta,$id_curso)
{
$tbl_course_quiz_answer	= Database :: get_course_table('quiz_answer');
$nombre_db_curso  =  get_course_db_name ($id_curso);


//si buscamos un respuesta correcta a un examen el campo ponderation debe tener un valor de 10 (0 en caso de ser incorrecta)
if($es_correcta == 1)
{
$ponderation = 10;
}else{
$ponderation = 0;
}

$sql_respuesta = "SELECT id FROM ".$nombre_db_curso.".".$tbl_course_quiz_answer." WHERE question_id = ".$id_pregunta." AND correct = ".$es_correcta." AND ponderation = ".$ponderation." LIMIT 1";
$rs_respuesta = api_sql_query($sql_respuesta, __FILE__, __LINE__);
$row = Database::fetch_row($rs_respuesta);
return $row[0];

}



//************************************************************************************
//funcion que inserta la respuesta de una pregunta dada en el seguimiento
//************************************************************************************
function inserta_respuesta_examen($id_alumno,$id_curso,$exe_id,$id_pregunta,$es_correcta)
{
$tbl_stats_attempt 		    = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);

$id_respuesta = get_respuesta_examen($es_correcta,$id_pregunta,$id_curso);

if($es_correcta == 1)
{
$marks = 10;
}else{
$marks = 0;
}

$sql = "INSERT INTO ".$tbl_stats_attempt." (exe_id,user_id,question_id,answer,marks,course_code,tms) VALUES (".$exe_id.",".$id_alumno.",".$id_pregunta.",".$id_respuesta.",".$marks.",'".$id_curso."',sysdate())";
$rs = api_sql_query($sql, __FILE__, __LINE__);

return $sql;
}



//***********************************************************************************************
//funcion que devuelve el nombre de la base de datos de un curso dado
//***********************************************************************************************
function get_course_db_name($id_curso)
{
$course = Database::escape_string($id_curso);
$a_infosCours = CourseManager::get_course_information($course);
return $a_infosCours['db_name'];
}



//************************************************************************************
//funcion que devuelve la nota de un examen dado
//************************************************************************************
function get_nota_examen ($id_usuario,$id_course,$id_examen)
{
$tbl_stats_exercices 		= Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
	//Hacemos la consulta que saca la nota y la ponderación del examen  (sólo se puede realizar UNA VEZ cada examen) que se haya realizado para el id de ejercicio sacado de la tabla quiz.	
	$sqlScore = "SELECT exe_id , exe_result,exe_weighting FROM ".$tbl_stats_exercices." WHERE exe_user_id = ".$id_usuario." AND exe_cours_id = '".$id_course."' AND exe_exo_id = ".$id_examen." ORDER BY exe_date DESC LIMIT 1";
	$resultScore = api_sql_query($sqlScore);	
	$notaExamen = "";
	//COMPROBAMOS SI HAY EXAMEN
	$examenes_hechos=Database::num_rows($resultScore);
	if($examenes_hechos>0){
		while($a_score = Database::fetch_array($resultScore))
		{
			$notaExamen=round(($a_score['exe_result']/$a_score['exe_weighting'])*10,2);
			$nota=$nota+$notaExamen;
		}					
	}
	return $notaExamen;
}


//************************************************************************************
//funcion que modifica (o inserta en caso de no exixtir) la nota de un examen,teniendo en cuenta de que el seguimiento sea acorde con la nota
//************************************************************************************
function set_nota_examen($id_alumno,$id_curso,$id_ejercicio,$nota_nueva)
{
$tbl_stats_attempt 		    = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
$tbl_stats_exercices 		= Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$tbl_course_quiz_rel_question			= Database :: get_course_table('quiz_rel_question');
$nombre_db_curso            =  get_course_db_name ($id_curso);


//si existia una nota insertada la eliminamos


		// obtenemos el exe_id correspondiente en la tabla TRACK_E_EXERCICES para el alumno, curso y examen dado. Con este exe_id se localizan las 10 filas correspondientes al rastro de la corrección de cada pregunta del examen en la tabla TRACK_E_ATTEMPT
		$sql = "SELECT exe_id FROM ".$tbl_stats_exercices." WHERE exe_cours_id='".$id_curso."' and exe_user_id=".$id_alumno." and exe_exo_id=".$id_ejercicio; 
		$rs = api_sql_query($sql, __FILE__, __LINE__);

		if ($row = Database :: fetch_array($rs))
		{
			$exe_id=$row['exe_id'];
	
			//eliminamos rastro (10 filas, una por cada pregunta del examen)
			$sql_elimina_rastro = "DELETE FROM ".$tbl_stats_attempt." WHERE course_code='".$id_curso."' and user_id=".$id_alumno." and exe_id=".$exe_id; 
			$rs_elimina_rastro = api_sql_query($sql_elimina_rastro, __FILE__, __LINE__);
			//eliminamos nota (una fila con la nota del examen)
			$sql_elimina_nota = "DELETE FROM ".$tbl_stats_exercices." WHERE exe_cours_id='".$id_curso."' and exe_user_id=".$id_alumno." and exe_exo_id=".$id_ejercicio; 
			$rs_elimina_nota = api_sql_query($sql_elimina_nota, __FILE__, __LINE__);		
		}
		
		
//insertamos la nota del examen

			$sql_inserta_nota = "INSERT INTO ".$tbl_stats_exercices." (exe_user_id,exe_date,exe_cours_id,exe_exo_id,exe_result,exe_weighting) VALUES (".$id_alumno.",sysdate(),'".$id_curso."',".$id_ejercicio.",".($nota_nueva*10).",100) ";
		    $rs_inserta_nota = api_sql_query($sql_inserta_nota, __FILE__, __LINE__);
		   
		   //obtenemos el id propio de la tabla track_e_exercices (exe_id) resultado del registro que acabamos de insertar
			$exe_id_insertado = Database::get_last_insert_id();

			
//insertamos el seguimiento del examen (10 filas,una por cada respuesta) 

   
			//-obtenemos 10 preguntas aleatorias del examen
			  $sql_preguntas = "select question_id from ".$nombre_db_curso.".".$tbl_course_quiz_rel_question." WHERE exercice_id = ".$id_ejercicio." ORDER BY RAND() LIMIT 10";
			  $rs_preguntas_examen = api_sql_query($sql_preguntas, __FILE__, __LINE__);
			  $contador_nota = 0;
			  $mas_sql = "";
			  while($row = Database :: fetch_array($rs_preguntas_examen))
			  {
			    $id_pregunta = $row['question_id'];
			    if($contador_nota < $nota_nueva)
				{//las primeras N inserciones (siendo N el valor de la nota a insertar) sera de respuestas correctas
			            //$mas_sql = $mas_sql.inserta_respuesta_examen($id_alumno,$id_curso,$exe_id_insertado,$id_pregunta,1)."<br>";
						inserta_respuesta_examen($id_alumno,$id_curso,$exe_id_insertado,$id_pregunta,1);
						$contador_nota++;
				}else
				{//el resto sera de respuestas incorrectas
				        //$mas_sql = $mas_sql.inserta_respuesta_examen($id_alumno,$id_curso,$exe_id_insertado,$id_pregunta,0)."<br>"; 
						inserta_respuesta_examen($id_alumno,$id_curso,$exe_id_insertado,$id_pregunta,0);
				}
			  }

//return  "<br>".$sql_elimina_rastro."<br>".$sql_elimina_nota."<br>".$mas_sql."<br>".$sql_inserta_nota."<br>";
return 1;

}








/************************************************************************
                   MODIFICACION DE NOTAS DE ALUMNOS
************************************************************************/








if (!isset($_POST['alumno']) || !isset($_POST['curso']) ) //Si no tenemos los parametros que nos identifiquen curso y alumno
{
	echo "Error: Faltan parámetros.";	
}
else //si los tenemos
{
	$id_alumno=$_POST['alumno'];
	$id_curso=$_POST['curso'];
	
	//echo "alumno:".$id_alumno."<br>";
	//echo "curso:".$id_curso."<br>";

	
	
	// comprobamos que invoca la página un usuario que ha iniciado sesión como administrador (o gestor) o teletutor.
	$id_user_session=$_SESSION[_user][user_id];
	$sql_user = "SELECT user_id FROM ".$tbl_user." WHERE user_id=".$id_user_session." and   (status=1 or user_id in (select user_id from ". $tbl_admin."))";
	$rs_user = api_sql_query($sql_user, __FILE__, __LINE__);
	if (Database :: num_rows($rs_user)>0) 
	{
		$permitido=1;
	}	
}

if ($permitido==0) //si el usuario no tiene permisos de tutor o administrador
{
	echo "No ha iniciado sesión con permisos para esta acción";
}	
else  //si el usuario tiene permisos para cambiar las notas
{	
       
            $nombre_db_curso  =  get_course_db_name ($id_curso);

            //HACEMOS LA SELECT DE LA TABLA QUIZ PARA OBTENER LOS EXAMENES QUE PERTENECEN AL ITINERARIO DE APRENDIZAJE (ACTIVE=0 OJO DESACTIVO) DEL CURSO RECIBIDO
            $sqlExercices = "SELECT q.id FROM ".$nombre_db_curso.".".$tbl_course_quiz." q, ".$nombre_db_curso.".".$tbl_course_lp_item." lp WHERE q.id=lp.path and lp.item_type='quiz' and q.active='0' order by lp.display_order";	
            
            //RECORREMOS LOS ID DE LOS EXAMENES DEL CURSO RECIBIDO
			$rs = api_sql_query($sqlExercices, __FILE__, __LINE__);
			while($row = Database :: fetch_array($rs))
			{
			   //para cada examen vemos si hemos recibido un nota
			       $id_ejercicio = $row['id'];
				   //echo "ejercicio ". $id_ejercicio;
				   
				   //si recibimos una nota
				   if(isset($_POST[$id_ejercicio]) && $_POST[$id_ejercicio] != "" ){
				   
				           $nota_nueva = $_POST[$id_ejercicio];
						   $nota_antigua = get_nota_examen($id_alumno,$id_curso,$id_ejercicio);
						   
						   //echo  " - nota recibida:" . $_POST[$id_ejercicio]. " - nota anterior:" . $nota_antigua;
							
							 //y esa nota ha cambiado
							
							  if  ($nota_nueva != $nota_antigua)
							  {
								 //echo " - la nota ha cambiado";
								  //la modificamos
								  /*echo*/ set_nota_examen($id_alumno,$id_curso,$id_ejercicio,$nota_nueva);
							  }
				   
				   }
				//echo "<br>"; 
			}
			//die();
     
?>

		<script language="javascript">		
		function envia(usuario, curso)
		{			
				alert('Notas Modificadas');
				location.href="my_progress_details_course_x14.php?student="+usuario+"&details=true&course="+curso+"&origin=tracking_course";		
		}	
		</script>

<?php
		echo "<script language='javascript'>envia(".$id_alumno.",'".$id_curso."');</script>";				
}		
 ?>