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

$is_allowedToTrack = $is_courseAdmin || $is_platformAdmin || $is_courseCoach || $is_sessionAdmin;

if(!$is_allowedToTrack)
{
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
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

//Incluimos librería de utilidades generales
require_once (api_get_path(LIBRARY_PATH).'tracking.lib_fd.php');
//Incluimos librería de exportación a CSV, XLS,... de FD
require_once (api_get_path(LIBRARY_PATH).'export.lib.inc_FD.php');


$export_csv = isset($_GET['export']) && $_GET['export'] == 'csv' ? true : false;
if($export_csv)
{
	ob_start();
}
$csv_content = array();
$csv_content[] =  array('','','Fecha de login','Fecha de logut');
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
$table_user		= Database::get_main_table(TABLE_MAIN_USER);

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

// tablas de conexiones
$tbl_login = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_LOGIN);
$tbl_course_access = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_COURSE_ACCESS);

$view = (isset($_REQUEST['view'])?$_REQUEST['view']:'');

$nameTools = get_lang('Tracking');

Display::display_header($nameTools, "Tracking");
include(api_get_path(LIBRARY_PATH)."statsUtils.lib.inc.php");
include("../resourcelinker/resourcelinker.inc.php");


// No usamos la lista de alumnos devuelta por la clase CourseManager de Dokeos, sino una función propia de tracking.lib_fd.php donde excluimos alumnos demos y teletutores,
// para que sólo aparezcan alumnos reales en el seguimiento FD 
//$a_students = CourseManager :: get_student_list_from_course_code($_course['id'], true, (empty($_SESSION['id_session'])?null:$_SESSION['id_session']));
//$nbStudents = count($a_students);
//echo $nbStudents;

//Podemos recibir un user_id.

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


//********************************************************************************************


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
			<a href="courseLog_FD.php?'.api_get_cidreq().'&studentlist=true">'.get_lang('StudentsTracking').'</a>&nbsp;|'.get_lang('CourseTracking') ;
}
else
{
	echo '<div style="float:left; clear:left">
			<a href="courseLog_FD.php?'.api_get_cidreq().'&studentlist=true">'.get_lang('StudentsTracking').'</a>&nbsp; |
			<a href="courseLog_FD.php?'.api_get_cidreq().'&studentlist=false">'.get_lang('CourseTracking').'</a>&nbsp;';
}
// Informe FD ampliado, enlaces a Seguimiento de Exámenes, Ejercicios y Notas Globales para todos los alumnos del curso
echo ' | <a href="courseLog_FD_examenes.php?'.api_get_cidreq().'&studentlist=true">'.get_lang('SeguimientoExamenes').'</a>&nbsp;';
echo ' | <a href="courseLog_FD_ejercicios.php?'.api_get_cidreq().'&studentlist=true">'.get_lang('SeguimientoEjercicios').'</a>&nbsp;';
echo ' | <a href="courseLog_FD_notasglobales.php?'.api_get_cidreq().'&studentlist=true">'.get_lang('SeguimientoNotasGlobales').'</a>';
// Ampliamos mas el informe para mostrar conexiones del alumno (egarcia 23/09/09)
echo ' | '.get_lang('SeguimientoConexiones');

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
echo '<a href="'.api_get_self().'?'.api_get_cidreq().'&export=csv"><img align="absbottom" src="../img/excel.gif">&nbsp;'.get_lang('ExportAsCSV').'</a>';

echo '</div>';
echo '<div class="clear"></div>';
	
	

	$tracking_column = isset($_GET['tracking_column']) ? $_GET['tracking_column'] : 0;
	$tracking_direction = isset($_GET['tracking_direction']) ? $_GET['tracking_direction'] : 'DESC';
	

	//Montamos cabecera de la tabla, con los exámenes dinámicamente según el número exámenes que tenga el curso	
	$course_code = $_course['id'];
	//echo "curso: ".$course_code;

	//die($total_examenes);
	$table = new SortableTable('tracking', 'count_student_in_course');		
	$table -> set_header(0, get_lang('LastName'), true, 'align="center"');
	$table -> set_header(1, get_lang('FirstName'), false, 'align="center"');										
	$table -> set_header(2, get_lang('UltimoAccesoPlataforma'),false);
	$table -> set_header(3, get_lang('UltimoAccesoCurso'),false);

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
		
		//añadimos al csv el alumno
		$csv_content[] = $row;
			
		// Obtenemos los datos de las conexiones del alumno
		$enlace1 = '';
		$last_connection_date = Tracking :: get_last_connection_date($student_id);
		if ( $last_connection_date != '' )
		{
		$enlace1= ' <a href="courseLog_FD_conexiones2.php?user_id=' . $student_id . '&tipo=plataforma"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';
		}

		$row[] = $last_connection_date . $enlace1;

		$enlace2 = '';
		$last_connection_date_on_the_course = Tracking :: get_last_connection_date_on_the_course($student_id, $course_code);			
		if ( $last_connection_date_on_the_course != '' )
		{
			$enlace2 = ' <a href="courseLog_FD_conexiones2.php?user_id=' . $student_id . '&tipo=curso"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';
		}
		$row[] = $last_connection_date_on_the_course . $enlace2;
	
		$all_datas[] = $row;	
		// Si el alumno tiene conexiones en la tabla aparece un enlace para expandir esta información
		
		
		//para el csv,por cada alumno obtenemos sus conexiones
           if($export_csv)
			{
			   
                  $conexiones = Tracking_fd :: get_conexiones_curso($_course['id'], $student_id);
                 foreach($conexiones as $conexion)
				{
					$row = array();
					$row[] =  "";
					$row[] =  "";
					$row[] = format_locale_date(get_lang('DateFormatLongWithoutDay'), strtotime($conexion['login_course_date'])) . ' ' . substr($conexion['login_course_date'],11) ;
					$row[] = format_locale_date(get_lang('DateFormatLongWithoutDay'), strtotime($conexion['logout_course_date'])) . ' ' . substr($conexion['logout_course_date'],11) ;
					$all_datas[] = $row;
					//añadimos al CSV
					$csv_content[] = $row;
				}
			}	
	}				

	usort($all_datas, 'sort_users');
	$page = $table->get_pager()->getCurrentPageID();
	$all_datas = array_slice($all_datas, ($page-1)*$table -> per_page, $table -> per_page);
		

	foreach($all_datas as $row)
	{
		$table -> addRow($row,'align="right"');	
	}		

            if($export_csv)
			{
			   
                             /*echo "<pre>";
                             print_r($csv_content);
                             echo "</pre>";
                             die();*/
				ob_end_clean();
				Export_FD :: export_table_xls($csv_content, 'seguimiento_conexiones_' . $_cid);
				
			}	


	$table -> setColAttributes(0,array('align'=>'left'));
	$table -> setColAttributes(1,array('align'=>'left'));

	$table -> display();				

?>
</table>

<?php
Display::display_footer();
?>
