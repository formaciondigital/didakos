<?php

/*
 * ==============================================================================
	SEGUIMIENTO AMPLIADO FD SOBRE ALUMNOS - NOTAS GLOBALES PARA UN CURSO
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
//tabla de ejercicios/exámenes
$tbl_course_quiz = Database :: get_course_table('quiz');
$tbl_course_user_fd = Database :: get_main_table(TABLE_MAIN_COURSE_USER_FD);

$view = (isset($_REQUEST['view'])?$_REQUEST['view']:'');

$nameTools = get_lang('Tracking');

Display::display_header($nameTools, "Tracking");
include(api_get_path(LIBRARY_PATH)."statsUtils.lib.inc.php");
include("../resourcelinker/resourcelinker.inc.php");


// No usamos la lista de alumnos devuelta por la clase CourseManager de Dokeos, sino una función propia de tracking.lib_fd.php donde excluimos y teletutores,
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

/////// Depuración FD
/*
$id_user_session=$_SESSION[_user][user_id];
if ($id_user_session!=180)   exit(); 
*/
///////


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
echo  ' | <a href="courseLogg_ins_examenes.php?'.api_get_cidreq().'&studentlist=true">'.get_lang('SeguimientoExamenes').'</a>&nbsp; 
 	| <a href="courseLogg_ins_ejercicios.php?'.api_get_cidreq().'&studentlist=true">'.get_lang('SeguimientoEjercicios').'</a>&nbsp; 
	| '.get_lang('SeguimientoNotasGlobales');		  	

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

//echo '<a href="'.api_get_self().'?'.api_get_cidreq().'&export=csv"> <img align="absbottom" src="../img/excel.gif">&nbsp;'.'Exportar'.'</a>';

echo '</div>';
echo '<div class="clear"></div>';
	
	

	$tracking_column = isset($_GET['tracking_column']) ? $_GET['tracking_column'] : 0;
	$tracking_direction = isset($_GET['tracking_direction']) ? $_GET['tracking_direction'] : 'DESC';
	
	if(count($a_students)>0)	
	{
		//Array de contenido csv
		/*
		$csv_content[] = array( 									
								get_lang('LastName'),
								get_lang('FirstName'),
								get_lang('Nota Global')
							);								   
		*/					
		
		$csv_content[] = array('','','');

		
		//Montamos cabecera de la tabla, con los exámenes dinámicamente según el número exámenes que tenga el curso	
		$course_code = $_course['id'];
		//echo "curso: ".$course_code;
		
		//die($total_examenes);
		$table = new SortableTable('tracking', 'count_student_in_course');		
		$table -> set_header(0, get_lang('LastName'), true, 'align="center"');
		$table -> set_header(1, get_lang('FirstName'), false, 'align="center"');										
		//Nota global
		$table -> set_header(2, 'Nota Global',false);	
		//Enlace detalle	del alumno
		$table -> set_header(3, get_lang('Details'),false);		
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
					
			// Obtenemos nota global para cada alumno en el curso
			$nota_global=" - ";		
			$sql_nota ="SELECT nota_global FROM  ".$tbl_course_user_fd." WHERE course_code='".$course_code."' and user_id=".$student_id;
			
			$result_nota = api_sql_query($sql_nota, __FILE__, __LINE__);
			if ($row_nota = Database :: fetch_array($result_nota)) {	
				$nota_global=$row_nota['nota_global'];
			}			
			$row[] = $nota_global;
			$csv_content[] = $row;	
			
			// Enlace detalle para un alumno dado	
			$row[] = '<a href="../auth/my_progress_details_course_INS.php?student='.$student_id.'&details=true&course='.$course_code.'&origin=tracking_course"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';		
			
			$all_datas[] = $row;							
		}				
		
		usort($csv_content, 'sort_users');
		$csv_content[0] = array( 									
								get_lang('LastName'),
								get_lang('FirstName'),
								get_lang('Nota Global')
							);
		
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
		
	}
	else
	{
		echo get_lang('NoUsersInCourseTracking');
	}	
	
	// Envío de fichero csv
	//var_dump($csv_content);	
	
	if($export_csv)
	{
		ob_end_clean();
		
		//var_dump($csv_content);	
		
		//Formato CSV
		//Export_FD :: export_table_csv($csv_content, 'informe_notas_globales_csv');
		
		//Formato XLS
		Export_FD :: export_table_xls($csv_content, 'informe_notas_globales_excel_'. $_cid);
	}
	

?>
</table>

<?php
Display::display_footer();
?>
