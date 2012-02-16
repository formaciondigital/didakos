<?php //$Id: myStudents.php 15252 2008-05-08 21:51:28Z yannoo $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2006-2008 Dokeos SPRL
	Copyright (c) 2006-2008 Elixir Interactive http://www.elixir-interactive.com

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
 
 // name of the language file that needs to be included 
$language_file = array ('perfil');
 $cidReset=true;
 include ('../inc/global.inc.php');

 include_once(api_get_path(LIBRARY_PATH).'tracking.lib.php');
 include_once(api_get_path(LIBRARY_PATH).'export.lib.inc.php');
 include_once(api_get_path(LIBRARY_PATH).'usermanager.lib.php');
 include_once(api_get_path(LIBRARY_PATH).'course.lib.php');
 include_once('../newscorm/learnpath.class.php');

$this_section = "session_my_space";
$nameTools=get_lang("Perfil");
api_block_anonymous_users();
Display :: display_header($nameTools);
 
 /*
  * ======================================================================================
  * 	FUNCTIONS
  * ======================================================================================
  */
  
/*
 *===============================================================================
 *	MAIN CODE
 *===============================================================================  
 */
// Database Table Definitions
$tbl_user 					= Database :: get_main_table(TABLE_MAIN_USER);
$tbl_user_field 				= Database :: get_main_table(TABLE_MAIN_USER_FIELD);
$tbl_user_field_options 			= Database :: get_main_table(TABLE_MAIN_USER_FIELD_OPTIONS);
$tbl_user_field_values 				= Database :: get_main_table(TABLE_MAIN_USER_FIELD_VALUES);
$tbl_session_user 			= Database :: get_main_table(TABLE_MAIN_SESSION_USER);
$tbl_session 				= Database :: get_main_table(TABLE_MAIN_SESSION);
$tbl_session_course 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
$tbl_session_course_user 	= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
$tbl_course 				= Database :: get_main_table(TABLE_MAIN_COURSE);
$tbl_course_user 			= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$tbl_stats_exercices 		= Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$tbl_stats_exercices_attempts 		= Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
//$tbl_course_lp_view 		= Database :: get_course_table('lp_view');
//$tbl_course_lp_view_item = Database :: get_course_table('lp_item_view');
//$tbl_course_lp_item 		= Database :: get_course_table('lp_item');

$tbl_course_lp_view = 'lp_view';
$tbl_course_lp_view_item = 'lp_item_view';
$tbl_course_lp_item = 'lp_item';
$tbl_course_lp = 'lp';
$tbl_course_quiz = 'quiz';
$course_quiz_question = 'quiz_question';
$course_quiz_rel_question = 'quiz_rel_question';
$course_quiz_answer = 'quiz_answer';
$course_student_publication = Database::get_course_table(TABLE_STUDENT_PUBLICATION);


if(isset($_GET["user_id"]) && $_GET["user_id"]!="")
{
	$i_user_id=$_GET["user_id"];
}
else
{
	$i_user_id = $_user['user_id'];
}

if(!empty($_GET['student']))
{
	$student_id = intval($_GET['student']);	
	// infos about user
	$a_infosUser = UserManager::get_user_info_by_id($student_id);
	$a_infosUser['name'] = $a_infosUser['firstname'].' '.$a_infosUser['lastname'];
	
	echo '<div align="right">
		<a href="#" onclick="window.print()"><img align="absbottom" src="../img/printmgr.gif">&nbsp;'.get_lang('Print').'</a>';
	 echo '</div>'; 
	
	$avg_student_progress = $avg_student_score = $nb_courses = 0;
	$sql = 'SELECT course_code FROM '.$tbl_course_user.' WHERE user_id='.$a_infosUser['user_id'];
	$rs = api_sql_query($sql, __FILE__, __LINE__);
	$a_courses = array();
	while($row = Database :: fetch_array($rs))
	{
		$a_courses[$row['course_code']] = $row['course_code'];
	}
	
	// get the list of sessions where the user is subscribed as student
	$sql = 'SELECT DISTINCT course_code FROM '.Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER).' WHERE id_user='.intval($a_infosUser['user_id']);
	$rs = api_sql_query($sql, __FILE__, __LINE__);
	while($row = Database :: fetch_array($rs))
	{
		$a_courses[$row['course_code']] = $row['course_code'];
	}

	// Obtenemos el resto de datos nuevos.
	$sql = 'select uf.field_variable,uf.field_display_text, ufv.field_value 
	from  ' .$tbl_user_field. ' uf LEFT OUTER JOIN ' . $tbl_user_field_values . ' ufv on (uf.id =ufv.field_id) where ufv.user_id=' . $a_infosUser['user_id'];
	$rs = api_sql_query($sql, __FILE__, __LINE__);

	while($row = Database :: fetch_array($rs))
	{
		$a_extras [$row['field_variable']] = $row['field_value'];	
	}
	
?>
	<table class="data_table" width="50%" align="center">
		<tr>
			<td class="border" width="100%">
				<table width="100%" border="0" >
					<tr>
						
							<?php							
								$image_array=UserManager::get_user_picture_path_by_id($a_infosUser['user_id'],'web',false, true);																					
								echo '<td class="borderRight" width="5%" valign="top">';
								echo '<img src="'.$image_array['dir'].$image_array['file'].'" border="1">';
								echo '<br><br>';
							?>			
								<?php
									if(!empty($a_extras['perfil_twitter']))
									{
										echo '<a target="_blank" href="'.$a_extras['perfil_twitter'].'"><img src="twitter.png"></a>&nbsp;';
									}
									if(!empty($a_extras['perfil_facebook']))
									{
										echo '<a target="_blank" href="'.$a_extras['perfil_facebook'].'"><img src="facebook.png"></a>&nbsp;';
									}
									if(!empty($a_extras['perfil_google']))
									{
										echo '<a target="_blank" href="'.$a_extras['perfil_google'].'"><img src="google-plus.jpg"></a>&nbsp;';
									}
									if(!empty($a_extras['perfil_linkedin']))
									{
										echo '<a target="_blank" href="'.$a_extras['perfil_linkedin'].'"><img src="linkedin.png"></a>&nbsp;';
									}
								echo '</td>';
								?>
							
						<td class="none" width="40%" valign="top">
							<table width="100%">
								<tr>
									<th>
										<?php echo get_lang('FichaAlumno'); ?>
									</th>
								</tr>
								<tr>
									<td class="none">
										<?php 
											echo get_lang('Name').' : ';
											echo $a_infosUser['name']; 
										?>
									</td>
								</tr>
								<tr>
									<td class="none">
										<?php
											echo get_lang('Email').' : ';
											if(!empty($a_infosUser['email']))
											{
												echo '<a href="mailto:'.$a_infosUser['email'].'">'.$a_infosUser['email'].'</a>';
											}
											else
											{
												echo get_lang('NoIndicado');
											}
										?>
									</td>
								</tr>
								<tr>
									<td class="none">
										<?php
											echo get_lang('Web').' : ';
											if(!empty($a_extras['web']))
											{
												echo '<a target="_blank" href="'.$a_extras['web'].'">'.$a_extras['web'].'</a>&nbsp;';
											}
											else
											{
												echo get_lang('NoIndicado');
											}
										?>
									</td>
								</tr>
								<tr>
									<td class="none">
										<?php
											echo get_lang('Blog').' : ';
											if(!empty($a_extras['blog']))
											{
												echo '<a target="_blank" href="'.$a_extras['blog'].'">'.$a_extras['blog'].'</a>&nbsp;';
											}
											else
											{
												echo get_lang('NoIndicado');
											}
										?>
									</td>
								</tr>
	<?php
		//Datos protegidos
		if (api_is_allowed_to_edit())
		{
	?>
								<tr>
									<td class="none">
										<?php
											echo get_lang('F_nacimiento').' : ';
											if(!empty($a_extras['f_nacimiento']) && $a_extras['f_nacimiento']!='1900-01-01')
											{
												echo $a_extras['f_nacimiento'];
											}
											else
											{
												echo get_lang('NoIndicado');
											}
										?>
									</td>
								</tr>
								<tr>
									<td class="none">
										<?php
											echo get_lang('Sexo').' : ';
											if(!empty($a_extras['sexo']))
											{
												echo $a_extras['sexo'];
											}
											else
											{
												echo get_lang('NoIndicado');
											}
										?>
									</td>
								</tr>
								<tr>
									<td class="none">
										<?php
											echo get_lang('Direccion').' : ';
											if(!empty($a_extras['direccion']))
											{
												echo $a_extras['direccion'];
											}
											else
											{
												echo get_lang('NoIndicado');
											}
										?>
									</td>
								</tr>
								<tr>
									<td class="none">
										<?php
											echo get_lang('Provincia').' : ';
											if(!empty($a_extras['provincia']))
											{
												echo $a_extras['provincia'];
											}
											else
											{
												echo get_lang('NoIndicado');
											}
										?>
									</td>
								</tr>
								<tr>
									<td class="none">
										<?php
											echo get_lang('Localidad').' : ';
											if(!empty($a_extras['localidad']))
											{
												echo $a_extras['localidad'];
											}
											else
											{
												echo get_lang('NoIndicado');
											}
										?>
									</td>
								</tr>
								<tr>
									<td class="none">
										<?php
											echo get_lang('Categoria').' : ';
											if(!empty($a_extras['categoria']))
											{
												echo $a_extras['categoria'];
											}
											else
											{
												echo get_lang('NoIndicado');
											}
										?>
									</td>
								</tr>
								<tr>
									<td class="none">
										<?php
											echo get_lang('Contrato').' : ';
											if(!empty($a_extras['contrato']))
											{
												echo $a_extras['contrato'];
											}
											else
											{
												echo get_lang('NoIndicado');
											}
										?>
									</td>
								</tr>
								<tr>
									<td class="none">
										<?php
											echo get_lang('Formacion').' : ';
											if(!empty($a_extras['formacion']))
											{
												echo $a_extras['formacion'];
											}
											else
											{
												echo get_lang('NoIndicado');
											}
										?>
									</td>
								</tr>
<?php } //Fin de protección de datos ?>
								<tr>
									<td class="none">
										<?php
											echo get_lang('Experiencia').' : ';
											if(!empty($a_extras['experiencia']))
											{
												echo $a_extras['experiencia'];
											}
											else
											{
												echo get_lang('NoIndicado');
											}
										?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

<?php
}
/*
==============================================================================
		FOOTER
==============================================================================
*/
Display::display_footer();
?>
