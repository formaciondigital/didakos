<?php
/*
 * ==============================================================================
	SEGUIMIENTO AMPLIADO FD SOBRE ALUMNOS - EXÁMENES PARA UN CURSO
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
$pathopen = isset($_REQUEST['pathopen']) ? $_REQUEST['pathopen'] : null;
// name of the language file that needs to be included 

$language_file[] = 'tracking';
$language_file[] = 'scorm';

include('../inc/global.inc.php');

//cambio fd - 07/03/2011 - si el usuario no es insector no le permitimos ver el informe
if($_SESSION["_user"]["status"]!=4 ){
    Display :: display_header(null);
	api_not_allowed();
	Display :: display_footer();
}




//includes for SCORM and LP
require_once('../newscorm/learnpath.class.php');
require_once('../newscorm/learnpathItem.class.php');
require_once('../newscorm/learnpathList.class.php');
require_once('../newscorm/scorm.class.php');
require_once('../newscorm/scormItem.class.php');
require_once(api_get_path(LIBRARY_PATH).'tracking.lib.php');
require_once(api_get_path(LIBRARY_PATH).'course.lib.php');
require_once(api_get_path(LIBRARY_PATH).'usermanager.lib.php');
//require_once (api_get_path(LIBRARY_PATH).'export.lib.inc.php');
//Incluimos librería de exportación a CSV, XLS,... de FD
require_once (api_get_path(LIBRARY_PATH).'export.lib.inc_FD.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

//Incluimos librería de utilidades generales
require_once (api_get_path(LIBRARY_PATH).'tracking.lib_fd.php');


$export_csv = isset($_GET['export']) && $_GET['export'] == 'csv' ? true : false;
if($export_csv)
{
	ob_start();
}
$csv_content = array();

// charset determination
if (!empty($_GET['scormcontopen']))
{
	$tbl_lp = Database::get_course_table('lp');
	$contopen = (int) $_GET['scormcontopen'];
	$sql = "SELECT default_encoding FROM $tbl_lp WHERE id = ".$contopen;
	$res = api_sql_query($sql,__FILE__,__LINE__);
	$row = Database::fetch_array($res);
	$lp_charset = $row['default_encoding'];
	//header('Content-Type: text/html; charset='. $row['default_encoding']);
}

$htmlHeadXtra[] = "<style type='text/css'>
/*<![CDATA[*/
.secLine {background-color : #E6E6E6;}
.content {padding-left : 15px;padding-right : 15px; }
.specialLink{color : #0000FF;}
/*]]>*/
</style>
<style media='print' type='text/css'>

</style>";


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

//$table_scormdata = Database::get_scorm_table(TABLE_SCORM_SCO_DATA);
//$table_scormmain = Database::get_scorm_table(TABLE_SCORM_MAIN);
//$tbl_learnpath_main = Database::get_course_table(TABLE_LEARNPATH_MAIN);
//$tbl_learnpath_item = Database::get_course_table(TABLE_LEARNPATH_ITEM);
//$tbl_learnpath_chapter = Database::get_course_table(TABLE_LEARNPATH_CHAPTER);

// Comentadas tablas sobre itinerarios del curso
$tbl_learnpath_main = Database::get_course_table(TABLE_LP_MAIN);
$tbl_learnpath_item = Database::get_course_table(TABLE_LP_ITEM);
$tbl_learnpath_view = Database::get_course_table(TABLE_LP_VIEW);
$tbl_learnpath_item_view = Database::get_course_table(TABLE_LP_ITEM_VIEW);
//tabla de ejercicios
$tbl_course_quiz = Database :: get_course_table('quiz');
//tabla de exámenes
$tbl_course_exam = Database :: get_course_table('quiz_exam');



$view = (isset($_REQUEST['view'])?$_REQUEST['view']:'');

$nameTools = get_lang('Tracking');

Display::display_header($nameTools, "Tracking");
include(api_get_path(LIBRARY_PATH)."statsUtils.lib.inc.php");
include("../resourcelinker/resourcelinker.inc.php");


// No usamos la lista de alumnos devuelta por la clase CourseManager de Dokeos, sino una función propia de tracking.lib_fd.php donde excluimos teletutores,
// para que sólo aparezcan alumnos en el seguimiento FD 
//$a_students = CourseManager :: get_student_list_from_course_code($_course['id'], true, (empty($_SESSION['id_session'])?null:$_SESSION['id_session']));
//$nbStudents = count($a_students);
//echo $nbStudents;
$a_students = Tracking_fd :: alumnos_reales_curso($_course['id']);
$nbStudents = Database::num_rows($a_students);
//echo $nbStudents;
//var_dump($a_students);
//print_r($a_students);



/**
 * count the number of students in this course (used for SortableTable)
 */
function count_student_in_course()
{
	global $nbStudents;
	return $nbStudents;
}



function sort_users($a,$b){
	$a = trim(strtolower($a[$_SESSION['tracking_column']]));
	$b = trim(strtolower($b[$_SESSION['tracking_column']]));
	if($_SESSION['tracking_direction'] == 'DESC')
		return strcmp($b, $a);
	else
		return strcmp($a, $b);
}

/*
==============================================================================
		MAIN CODE
==============================================================================
*/


if($_GET['studentlist'] == 'false')
{
	echo '<div style="float:left; clear:left">
			<a href="courseLogg_ins.php?'.api_get_cidreq().'&studentlist=true">'.get_lang('StudentsTracking').'</a>&nbsp;|'.get_lang('CourseTracking') ;
}
else
{
	echo '<div style="float:left; clear:left">
			<a href="courseLogg_ins.php?'.api_get_cidreq().'&studentlist=true">'.get_lang('StudentsTracking').'</a>&nbsp; |
			<a href="courseLogg_ins.php?'.api_get_cidreq().'&studentlist=false">'.get_lang('CourseTracking').'</a>&nbsp;';
}
// Informe FD ampliado, enlaces a Seguimiento de Exámenes, Ejercicios y Notas Globales para todos los alumnos del curso
echo  ' |'.get_lang('SeguimientoExamenes').'&nbsp; 
	| <a href="courseLogg_ins_ejercicios.php?'.api_get_cidreq().'&studentlist=true">'.get_lang('SeguimientoEjercicios').'</a>&nbsp;
	| <a href="courseLogg_ins_notasglobales.php?'.api_get_cidreq().'&studentlist=true">'.get_lang('SeguimientoNotasGlobales').'</a>';		  	 		  

// Ampliamos mas el informe para mostrar conexiones del alumno (egarcia 23/09/09)

//echo ' | <a href="courseLogg_ins_conexiones.php?'.api_get_cidreq().'&studentlist=true">'.get_lang('SeguimientoConexiones').'</a>';
echo '</div>';	
echo '<div style="float:right; clear:right">';
echo '&nbsp;<a href="#" onclick="window.print()"><img align="absbottom" src="../img/printmgr.gif">&nbsp;'.get_lang('Print').'</a>';
// Comentado CSV
/*
if($_GET['studentlist'] == 'false'){	
	echo '<a href="'.api_get_self().'?'.api_get_cidreq().'&export=csv&studentlist=false"><img align="absbottom" src="../img/excel.gif">&nbsp;'.get_lang('ExportAsCSV').'</a></div>';
}
else{
	echo '<a href="'.api_get_self().'?'.api_get_cidreq().'&export=csv"><img align="absbottom" src="../img/excel.gif">&nbsp;'.get_lang('ExportAsCSV').'</a></div>';
}
*/
//echo ' <a href="'.api_get_self().'?'.api_get_cidreq().'&export=csv"><img align="absbottom" src="../img/excel.gif">&nbsp;'.'Exportar'.'</a>';
echo '</div>';
echo '<div class="clear"></div>';
	
	

	$tracking_column = isset($_GET['tracking_column']) ? $_GET['tracking_column'] : 0;
	$tracking_direction = isset($_GET['tracking_direction']) ? $_GET['tracking_direction'] : 'DESC';
	
	if(count($a_students)>0)
	{
		//Montamos cabecera de la tabla, con los exámenes dinámicamente según el número exámenes que tenga el curso	
		$course_code = $_course['id'];
		//echo "curso: ".$course_code;
		//Obtenemos el número total de exámenes para el curso tratado
		$total_examenes=Tracking_fd ::numero_total_examenes_curso($course_code);
		//die($total_examenes);
		//iniciamos el array para el CSV
		$csv_content[] = array('','','');

		$table = new SortableTable('tracking', 'count_student_in_course');		
		$table -> set_header(0, get_lang('LastName'), true, 'align="center"');
		$table -> set_header(1, get_lang('FirstName'), false, 'align="center"');
		
		//tabla resumen
		$table2 = new SortableTable('resumen');		
		$table2 -> set_header(0,'&nbsp;', true, 'align="center"');		
		
		//$sql_titulos_examenes = "SELECT q.title FROM ".$tbl_course_quiz." q, " .$tbl_learnpath_item. " lp WHERE q.active=0 and q.id=lp.path and item_type='quiz' order by q.id";			
				
		//títulos de exámenes ordenados según orden de id
		$sql_titulos_examenes = "SELECT q.title FROM ".$tbl_course_quiz." q, " .$tbl_course_exam. " ex WHERE q.active=1 and q.id=ex.id order by q.id";			
		//echo $sql_titulos_examenes;
		$rs = api_sql_query($sql_titulos_examenes, __FILE__, __LINE__);
		$i=2;	
		$j=1;
		while($row = Database :: fetch_array($rs))				
		{			
		  	$table -> set_header($i, $row['title'], false, 'align="center"');
			
			//tabla resumen
			$table2 -> set_header($j, $row['title'], false, 'align="center"');
			
			$i++;
			$j++;			
		}						
		//DETALLES
		$table -> set_header($i, get_lang('Details'),false);		
		
	    $all_datas = array();		
		
		// Rellenamos la tabla con los datos de alumnos y exámenes para el curso
		//foreach($a_students as $student_id => $student)
		while ($alumno = Database :: fetch_array($a_students))
		{
			$student_id=$alumno['user_id'];															
			$student_datas = UserManager :: get_user_info_by_id($student_id);									
			
			$row = array();
					
			$row[] = $student_datas['lastname'];
			$row[] = $student_datas['firstname'];				
			
			$sql_titulos_examenes = "SELECT q.* FROM ".$tbl_course_quiz." q, " .$tbl_course_exam. " ex WHERE q.active=1 and q.id=ex.id order by q.id";
			//echo $sql_titulos_examenes;
			$rs = api_sql_query($sql_titulos_examenes, __FILE__, __LINE__);		
			while($detalle = Database :: fetch_array($rs))				
			{			
				//$row[]=$detalle['id']."   alumno: ". $student_id. " curso: ". $course_code;							
				
				// Obtenemos nota del alumno en cada examen del curso
			    $nota = Tracking_fd ::nota_examen_alumno ($student_id, $course_code, $detalle['id']);		
				$row[]=$nota;							
			}				

			// Enlace detalle para un alumno dado	
			if($export_csv )
			{
				$csv_content[] = $row;
			}
						
			// Enlace detalle para un alumno dado	
			$row[] = '<a href="../auth/my_progress_details_course_INS.php?student='.$student_id.'&details=true&course='.$course_code.'&origin=tracking_course"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';		
			
			
			$all_datas[] = $row;		
	
		}				
		
		
		usort($all_datas, 'sort_users');
		$page = $table->get_pager()->getCurrentPageID();
		$all_datas = array_slice($all_datas, ($page-1)*$table -> per_page, $table -> per_page);
				
		
		foreach($all_datas as $row)
		{
			$table -> addRow($row,'align="right"');	
		}			
		
		
		$table -> setColAttributes(0,array('align'=>'left'));
		$table -> setColAttributes(1,array('align'=>'left'));

		$table -> display();
		
		// Rellenamos una fila más resumen con las medias de los exámenes, sacamos los exámenes ordenados por el quiz.id, igual que al montar la cabecera de 
		// la tabla para hacer corresponder cada resultado con su título en cabecera
						
		$sql_resumen_examenes = "SELECT q.id FROM ".$tbl_course_quiz." q, " .$tbl_course_exam. " ex WHERE q.active=1 and q.id=ex.id order by q.id";
		
		//echo $sql_resumen_examenes;
		$rs_resumen = api_sql_query($sql_resumen_examenes, __FILE__, __LINE__);			
		
		
		$row = array();					
		$row[] = get_lang('MoyenneExamenes');		
		while($row_resumen = Database :: fetch_array($rs_resumen))				
		{		
			$media_examen=Tracking_fd ::nota_media_examen ($course_code,$row_resumen['id']);		
			//echo "<p>media examen: ".$media_examen;
			$row[]=$media_examen;  		
		}				
		
		$table2 -> addRow($row,'align="right"');			
		echo "<br>";
		$table2 -> display();
		//----
		usort($csv_content, 'sort_users');
		$csv_content[0] = array ( get_lang('LastName'), get_lang('FirstName'), 'Notas Examenes');
		if($export_csv)
			{
				ob_end_clean();
				Export_FD :: export_table_xls($csv_content, 'seguimiento_examenes_' . $_cid);
			}
		
	}
	else
	{
		echo get_lang('NoUsersInCourseTracking');
	}	
	

?>
</table>

<?php
Display::display_footer();
?>
