<?php
// name of the language file that needs to be included
$language_file = array('registration','tracking','exercice');

$cidReset = true;

require ('../inc/global.inc.php');
require_once (api_get_path(LIBRARY_PATH).'tracking.lib.php');
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once ('../newscorm/learnpath.class.php');

$nameTools=get_lang('MyProgress');

$this_section = 'session_my_progress';

api_block_anonymous_users();

Display :: display_header($nameTools);

// Database table definitions
$tbl_course 				= Database :: get_main_table(TABLE_MAIN_COURSE);
$tbl_user 					= Database :: get_main_table(TABLE_MAIN_USER);
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


// get course list
$sql = 'SELECT course_code FROM '.$tbl_course_user.' WHERE user_id='.$_user['user_id'];
$rs = api_sql_query($sql, __FILE__, __LINE__);
$Courses = array();
while($row = Database :: fetch_array($rs))
{
	$Courses[$row['course_code']] = CourseManager::get_course_information($row['course_code']);
}

// Comentado seguimiento de sesiones para informe FD
// get the list of sessions where the user is subscribed as student
/*
$sql = 'SELECT DISTINCT course_code FROM '.Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER).' WHERE id_user='.intval($_user['user_id']);
$rs = api_sql_query($sql, __FILE__, __LINE__);
while($row = Database :: fetch_array($rs))
{
	$Courses[$row['course_code']] = CourseManager::get_course_information($row['course_code']);
}
*/
api_display_tool_title($nameTools);

$now=date('Y-m-d');

?>

<table class="data_table" width="100%">
<tr class="tableName">
	<td colspan="6">
		<strong><?php echo get_lang('MyCourses'); ?></strong>
	</td>
</tr>
<tr>
  <th><?php echo get_lang('Course'); ?></th>
<!--  Se comenta para el Informe de FD  -->
 <!--  
  <th><?php //echo get_lang('Time'); ?></th>  
  <th><?php //echo get_lang('Progress'); ?></th>
  <th><?php //echo get_lang('Score'); ?></th>
  <th><?php //echo get_lang('LastConnexion'); ?></th>
  -->
   <th><?php echo get_lang('Details'); ?></th>
</tr>

<?php

$i = 0;
$totalWeighting = 0;
$totalScore = 0;
$totalItem = 0;
$totalProgress = 0;

foreach($Courses as $enreg)
{
	$weighting = 0;

	$lastConnexion = Tracking :: get_last_connection_date_on_the_course($_user['user_id'],$enreg['code']);
	$progress = Tracking :: get_avg_student_progress($_user['user_id'], $enreg['code']);
	$time = api_time_to_hms(Tracking :: get_time_spent_on_the_course($_user['user_id'], $enreg['code']));
	$pourcentageScore = Tracking :: get_avg_student_score($_user['user_id'], $enreg['code']);

?>

<tr class='<?php echo $i?'row_odd':'row_even'; ?>'>
  	<td>
		<?php echo html_entity_decode($enreg['title'],ENT_QUOTES,$charset); ?>
  	</td>
<!-- 
  	<td align='center'>
		<?php //echo $time; ?>
  	</td>

  	<td align='center'>
  		<?php //echo $progress.'%'; ?>
  	</td>

  	<td align='center'>
		<?php //echo $pourcentageScore.'%'; ?>
  	</td>

  	<td align='center'>
		<?php //echo $lastConnexion; ?>
  	</td>
 -->
  	<td align='center'>
	
		<!-- <a href="<?php echo $SERVER['PHP_SELF']; ?>?course=<?php echo $enreg['code']; ?>"> <?php echo '<img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" />';?> </a> -->
		
		<a href="<?php echo "my_progress_details_course.php" ?>?course=<?php echo $enreg['code']; ?>"> <?php echo '<img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" />';?> </a>
  	</td>
</tr>

<?php



	$i=$i ? 0 : 1;
}
?>
</table>

<br/><br/>


<?php
Display :: display_footer();
?>
