<?php
/*
 * ==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004 Dokeos SPRL
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)

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


//27-10-2010 - cambio fd - se le permiten ver este informe solo a los responsables de recursos humanos
$is_rrhh = false;
if($_user['status']==DRH){
   $is_allowedToTrack =  true;
}


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
//Incluimos librer?a de exportaci?n a CSV, XLS,... de FD
require_once (api_get_path(LIBRARY_PATH).'export.lib.inc_FD.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

//Incluimos librer?a de utilidades generales
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

$tbl_learnpath_main = Database::get_course_table(TABLE_LP_MAIN);
$tbl_learnpath_item = Database::get_course_table(TABLE_LP_ITEM);
$tbl_learnpath_view = Database::get_course_table(TABLE_LP_VIEW);
$tbl_learnpath_item_view = Database::get_course_table(TABLE_LP_ITEM_VIEW);

$view = (isset($_REQUEST['view'])?$_REQUEST['view']:'');

$nameTools = get_lang('Tracking');

Display::display_header($nameTools);
include(api_get_path(LIBRARY_PATH)."statsUtils.lib.inc.php");
include("../resourcelinker/resourcelinker.inc.php");

// No usamos la lista de alumnos devuelta por la clase CourseManager de Dokeos, sino una funci?n propia de tracking.lib_fd.php donde excluimos alumnos demos y teletutores,
// para que s?lo aparezcan alumnos reales en el seguimiento FD 
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


echo '<div class="clear"></div>';
if($_GET['studentlist'] == 'false')
{
	echo'<br /><br />';
	
	
	
	echo "<div class='admin_section'>
				<h4>
					<img src='../img/acces_tool.gif' align='absbottom'>&nbsp;".get_lang('ToolsMostUsed')."
				</h4>
			<table class='data_table'>";
			 
	$sql = "SELECT `access_tool`, COUNT(DISTINCT `access_user_id`),count( `access_tool` ) as count_access_tool
            FROM $TABLETRACK_ACCESS_2
            WHERE `access_tool` IS NOT NULL
                AND `access_cours_code` = '$_cid'
            GROUP BY `access_tool`
			ORDER BY count_access_tool DESC"; 
			
			
	
	$rs = api_sql_query($sql, __FILE__, __LINE__);
	/*
	 if($export_csv){
    	$temp=array(get_lang('ToolsMostUsed'),'');
    	$csv_content[] = $temp;
    }
	*/
	
	while ($row = Database::fetch_array($rs))
	{
		echo '	<tr>
					<td>'.get_lang(ucfirst($row['access_tool'])).'</td>
					<td align="right">'.$row['count_access_tool'].' '.get_lang('Clicks').'</td>
				</tr>';
		/*		
		if($export_csv){
			$temp=array(get_lang(ucfirst($row['access_tool'])),$row['count_access_tool'].' '.get_lang('Clicks'));
			$csv_content[] = $temp;
		}
		*/
	}
	
	echo '</table></div>';
	
	echo '<div class="clear"></div>';
	
	
	/***************************
	 * DOCUMENTS
	 ***************************/
	 
	 echo "<div class='admin_section'>
				<h4>
					<img src='../img/documents.gif' align='absbottom'>&nbsp;".get_lang('DocumentsMostDownloaded')."
				</h4>
			<table class='data_table'>";
			
	$sql = "SELECT `down_doc_path`, COUNT(DISTINCT `down_user_id`), COUNT(`down_doc_path`) as count_down
            FROM $TABLETRACK_DOWNLOADS
            WHERE `down_cours_id` = '$_cid'
            GROUP BY `down_doc_path`
			ORDER BY count_down DESC";
    $rs = api_sql_query($sql, __FILE__, __LINE__);
    /*
    if($export_csv){
    	$temp=array(get_lang('DocumentsMostDownloaded'),'');
    	$csv_content[] = array('','');
    	$csv_content[] = $temp;
    }
    */
    if(Database::num_rows($rs)>0)
    {
	    while($row = Database::fetch_array($rs))
	    {
	    	echo '	<tr>
						<td>'.$row['down_doc_path'].'</td>
						<td align="right">'.$row['count_down'].' '.get_lang('Clicks').'</td>
					</tr>';
			/*		
			if($export_csv){
				$temp=array($row['down_doc_path'],$row['count_down'].' '.get_lang('Clicks'));
				$csv_content[] = $temp;
			}
			*/
	    }
    }
    else
    {
    	echo '<tr><td>'.get_lang('NoDocumentDownloaded').'</td></tr>';
		/*
    	if($export_csv){
    		$temp=array(get_lang('NoDocumentDownloaded'),'');
			$csv_content[] = $temp;
    	}
		*/
    }
	echo '</table></div>';
	
	echo '<div class="clear"></div>';
	
	
	/***************************
	 * LINKS
	 ***************************/
	 
	 echo "<div class='admin_section'>
				<h4>
					<img src='../img/link.gif' align='absbottom'>&nbsp;".get_lang('LinksMostClicked')."
				</h4>
			<table class='data_table'>";
			
	$sql = "SELECT `cl`.`title`, `cl`.`url`,count(DISTINCT `sl`.`links_user_id`), count(`cl`.`title`) as count_visits
            FROM $TABLETRACK_LINKS AS sl, $TABLECOURSE_LINKS AS cl
            WHERE `sl`.`links_link_id` = `cl`.`id`
                AND `sl`.`links_cours_id` = '$_cid'
            GROUP BY `cl`.`title`, `cl`.`url`
			ORDER BY count_visits DESC";
    $rs = api_sql_query($sql, __FILE__, __LINE__);
   
    /*
    if($export_csv){
    	$temp=array(get_lang('LinksMostClicked'),'');
    	$csv_content[] = array('','');
    	$csv_content[] = $temp;
    }
    */
    if(Database::num_rows($rs)>0)
    {
	    while($row = Database::fetch_array($rs))
	    {
	    	echo '	<tr>
						<td>'.$row['title'].'</td>
						<td align="right">'.$row['count_visits'].' '.get_lang('Clicks').'</td>
					</tr>';
			/*		
			if($export_csv){
				$temp=array($row['title'],$row['count_visits'].' '.get_lang('Clicks'));
				$csv_content[] = $temp;
			}
			*/
	    }
    }
    else
    {
    	echo '<tr><td>'.get_lang('NoLinkVisited').'</td></tr>';
		/*
    	if($export_csv){
    		$temp=array(get_lang('NoLinkVisited'),'');
			$csv_content[] = $temp;
    	}
		*/
    }
	echo '</table></div>';
	
	
	echo '<div class="clear"></div>';	
	
	// send the csv file if asked
	/*
	if($export_csv)
	{
		ob_end_clean();
		Export :: export_table_csv($csv_content, 'reporting_course_tracking');
	}
	*/
	
}
// else display student list with all the informations
else {
	
	//Comentado Formulario que muestra recordatorio de avisos de usuarios inactivos seg?n n?mero de d?as
	/*
	// BEGIN : form to remind inactives susers
	$form = new FormValidator('reminder_form','get',api_get_path(REL_CODE_PATH).'announcements/announcements.php');
	
		$renderer = $form->defaultRenderer();
	$renderer->setElementTemplate('<span>{label} {element}</span>&nbsp;<input type="submit" value="'.get_lang('Ok').'"','since');
	
	$options = array(
				2 => '2 '.get_lang('Days'),
				3 => '3 '.get_lang('Days'),
				4 => '4 '.get_lang('Days'),
				5 => '5 '.get_lang('Days'),
				6 => '6 '.get_lang('Days'),
				7 => '7 '.get_lang('Days'),
				15 => '15 '.get_lang('Days'),
				30 => '30 '.get_lang('Days')
				);
	
	$el = $form -> addElement('select','since','<img width="22" align="middle" src="'.api_get_path(WEB_IMG_PATH).'messagebox_warning.gif" border="0" />'.get_lang('RemindInactivesLearnersSince'),$options);
	$el -> setSelected(7);
	
	$form -> addElement('hidden','action','add');
	$form -> addElement('hidden','remindallinactives','true');
	
	$form -> display();
	// END : form to remind inactives susers
	*/

	$tracking_column = isset($_GET['tracking_column']) ? $_GET['tracking_column'] : 0;
	$tracking_direction = isset($_GET['tracking_direction']) ? $_GET['tracking_direction'] : 'DESC';
	
	if(count($a_students)>0)
	{
		$table = new SortableTable('tracking', 'count_student_in_course');
		//Comentado NIF en informe FD
		//$table -> set_header(0, get_lang('OfficialCode'), false, 'align="center"');
		$table -> set_header(0, get_lang('LastName'), true, 'align="center"');
		$table -> set_header(1, get_lang('FirstName'), false, 'align="center"');
		//TIEMPO DE PERMANENCIA DEL ALUMNO EN EL CURSO
		//$table -> set_header(3, get_lang('Time'),false);
		$table -> set_header(2, 'Tiempo de permanencia en el curso',false);
		//MENSAJES EN EL FORO
		$table -> set_header(3, 'Mensajes en el Foros',false);
		//MENSAJES EN EL BUZ?N
		$table -> set_header(4, 'Mensajes en el Buzón',false);
		//MEDIA EN LOS EX?MENES
		$table -> set_header(5, 'Media en los exámenes',false);
		//MEDIA EN EL SEGUIMIENTO
		$table -> set_header(6, 'Progreso Medio de Seguimiento en Curso (%)',false);
		//MEDIA EN LOS EJERCICIOS
		$table -> set_header(7, 'Media en los ejercicios',false);		
		//PRIMER ACCESO AL CURSO
		$table -> set_header(8, get_lang('FirstLogin'), false, 'align="center"');
		//?LTIMO ACCESO AL CURSO
		$table -> set_header(9, get_lang('LatestLogin'), false, 'align="center"');
		//DETALLES
		$table -> set_header(10, get_lang('Details'),false);
		
		/*$table -> set_header(4, get_lang('Progress'),false);
		$table -> set_header(5, get_lang('Score'),false);	
		$table -> set_header(6, get_lang('Student_publication'),false);
		$table -> set_header(7, get_lang('Messages'),false);
		$table -> set_header(8, get_lang('FirstLogin'), false, 'align="center"');
		$table -> set_header(9, get_lang('LatestLogin'), false, 'align="center"');
		$table -> set_header(10, get_lang('Details'),false);*/

		//iniciamos el array para el CSV
		$csv_content[] = array('','','','','','','','','','');			
		
	    	$all_datas = array();
	    	$course_code = $_course['id'];
		
		//Obtenemos el n?mero total de ex?menes para el curso tratado
		$total_examenes=Tracking_fd ::numero_total_examenes_o_ejercicios_curso($course_code,0);
		//Obtenemos el n?mero total de ejercicios para el curso tratado
		$total_ejercicios=Tracking_fd ::numero_total_examenes_o_ejercicios_curso($course_code,1);
		//Obtenemos el n?mero total de scos para el curso tratado
		$total_scos=Tracking_fd ::numero_scos_curso($course_code);

		
		//foreach($a_students as $student_id => $student)
		while ($alumno = Database :: fetch_array($a_students))
		{
			$student_id=$alumno['user_id'];		
			$student_datas = UserManager :: get_user_info_by_id($student_id);
			
			$avg_time_spent = $avg_student_score = $avg_student_progress = $total_assignments = $total_messages = 0 ;
			
			// Variable para mensajes en buz?n
			$messages_in_course=0;

			// Variable para mensajes en buz?n
			$messages_in_dropbox=0;
			
			// Variable para la media de los ex?menes
			$media_examenes=0;
			
			// Variable para la media de los ejercicios
			$media_ejercicios=0;
			
			// Variable para la media del progreso de los scos
			$progreso_scos=0;

			$nb_courses_student = 0;
			$avg_time_spent = Tracking :: get_time_spent_on_the_course($student_id, $course_code);
			
			// N?mero de mensajes en foros				
			$messages_in_course = Tracking :: count_student_messages ($student_id, $course_code);
			// N?mero de mensajes en buz?n								
			$messages_in_dropbox = Tracking_fd ::count_dropbox_file ($student_id, $course_code);
				
			// Nota media de los ex?menes para el alumno y curso tratado
			$media_examenes = Tracking_fd ::media_examenes_alumno_curso ($student_id, $course_code, $total_examenes);
				
			// Nota media de las notas de los ?ltimos intentos de los ejercicios para el alumno y curso tratado
			$media_ejercicios = Tracking_fd ::media_ejercicios_alumno_curso ($student_id, $course_code, $total_ejercicios);
				
			// Progreso medio en los scos para el alumno y curso tratado
			$progreso_scos = Tracking_fd ::progreso_scos_curso ($student_id, $course_code, $total_scos);
			
			
			/*$avg_student_score = Tracking :: get_avg_student_score($student_id, $course_code);
			$avg_student_progress = Tracking :: get_avg_student_progress($student_id, $course_code);
			$total_assignments = Tracking :: count_student_assignments($student_id, $course_code);
			$total_messages = Tracking :: count_student_messages($student_id, $course_code);*/
			
			$row = array();
			//comentado NIF del alumno para informe FD
			//$row[] = $student_datas['official_code'];
			
			$row[] = $student_datas['lastname'];
			$row[] = $student_datas['firstname'];
			$row[] = api_time_to_hms($avg_time_spent);
			$row[] = $messages_in_course;
			$row[] = $messages_in_dropbox;
			$row[] = $media_examenes;			
			$row[] = $progreso_scos." %";
			$row[] = $media_ejercicios;
			
			
			/*$row[] = $avg_student_progress.' %';
			$row[] = $avg_student_score.' %';		
			$row[] = $total_assignments;
			$row[] = $total_messages;*/
			
			$row[] = Tracking :: get_first_connection_date_on_the_course($student_id, $course_code);
			$row[] = Tracking :: get_last_connection_date_on_the_course($student_id, $course_code);
			
			
			if($export_csv)
			{
				//$row[8] = strip_tags($row[8]);
				$csv_content[] = $row;
			}
			
			/*$row[] = '<a href="../mySpace/myStudents.php?student='.$student_id.'&details=true&course='.$course_code.'&origin=tracking_course"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';*/
			
			$row[] = '<a href="../auth/my_progress_details_course.php?student='.$student_id.'&details=true&course='.$course_code.'&origin=tracking_course"><img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" /></a>';
			
			
			
			$all_datas[] = $row;		
	
		}
		
		usort($all_datas, 'sort_users');
		$page = $table->get_pager()->getCurrentPageID();
		$all_datas = array_slice($all_datas, ($page-1)*$table -> per_page, $table -> per_page);
		
		
		usort($csv_content, 'sort_users');
		$csv_content[0] = array ( get_lang('LastName'), get_lang('FirstName'), get_lang('Time'),
						'Mensajes en el Foros',	'Mensajes en Buzon',	'Media en examenes',
						'Seguimiento en Curso (%)', 'Media en ejercicios',
						get_lang('FirstLogin'),get_lang('LatestLogin'));

		foreach($all_datas as $row)
		{
			$table -> addRow($row,'align="right"');	
		}
		//Comentado NIF informe FD
		//$table -> setColAttributes(0,array('align'=>'left'));
		$table -> setColAttributes(0,array('align'=>'left'));
		$table -> setColAttributes(1,array('align'=>'left'));
		$table -> setColAttributes(7,array('align'=>'right'));
		$table -> setColAttributes(8,array('align'=>'center'));
		$table -> setColAttributes(9,array('align'=>'center'));
		$table -> display();
		
	}
	else
	{
		echo get_lang('NoUsersInCourseTracking');
	}
	


	if($export_csv)
	{
		ob_end_clean();
		Export_FD :: export_table_xls($csv_content, 'seguimiento_alumnos_' . $_cid);
	}
	
}
?>
</table>

<?php
Display::display_footer();
?>
