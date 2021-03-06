<?php
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2008 Dokeos SPRL

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
*	@package dokeos.survey
* 	@author unknown, the initial survey that did not make it in 1.8 because of bad code
* 	@author Patrick Cool <patrick.cool@UGent.be>, Ghent University: cleanup, refactoring and rewriting large parts of the code
* 	@version $Id: reporting.php 15556 2008-06-11 20:53:01Z juliomontoya $
*
* 	@todo The question has to be more clearly indicated (same style as when filling the survey)
*/

// name of the language file that needs to be included
$language_file = 'survey';

// including the global dokeos file
require ('../inc/global.inc.php');
require_once('survey.lib.php');

// export
/**
 * @todo use export_table_csv($data, $filename = 'export')
 */
if ($_POST['export_report'])
{
	switch($_POST['export_format'])
	{
		case 'xls':
			$survey_data = survey_manager::get_survey($_GET['survey_id']);
			$filename = 'survey_results_'.$_GET['survey_id'].'.xls';
			$data = SurveyUtil::export_complete_report_xls($filename);
			exit;
			break;
		case 'csv':
		default:
			$survey_data = survey_manager::get_survey($_GET['survey_id']);
			$data = SurveyUtil::export_complete_report();
			//$filename = 'fileexport.csv';
			$filename = 'survey_results_'.$_GET['survey_id'].'.csv';

			header('Content-type: application/octet-stream');
			header('Content-Type: application/force-download');
			header('Content-length: '.$len);
			if (preg_match("/MSIE 5.5/", $_SERVER['HTTP_USER_AGENT']))
			{
				header('Content-Disposition: filename= '.$filename);
			}
			else
			{
				header('Content-Disposition: attachment; filename= '.$filename);
			}
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
			{
				header('Pragma: ');
				header('Cache-Control: ');
				header('Cache-Control: public'); // IE cannot download from sessions without a cache
			}
			header('Content-Description: '.$filename);
			header('Content-transfer-encoding: binary');

			echo $data;
			exit;
			break;
	}
}

// including additional libraries
//require_once (api_get_path(LIBRARY_PATH)."/survey.lib.php");
require_once (api_get_path(LIBRARY_PATH)."/course.lib.php");

// Checking the parameters
SurveyUtil::check_parameters();

/** @todo this has to be moved to a more appropriate place (after the display_header of the code)*/
if (!api_is_allowed_to_edit())
{
	Display :: display_header(get_lang('Survey'));
	Display :: display_error_message(get_lang('NotAllowed'), false);
	Display :: display_footer();
	exit;
}

// Database table definitions
$table_survey 					= Database :: get_course_table(TABLE_SURVEY);
$table_survey_question 			= Database :: get_course_table(TABLE_SURVEY_QUESTION);
$table_survey_question_option 	= Database :: get_course_table(TABLE_SURVEY_QUESTION_OPTION);
$table_course 					= Database :: get_main_table(TABLE_MAIN_COURSE);
$table_user 					= Database :: get_main_table(TABLE_MAIN_USER);
$user_info 						= Database :: get_main_table(TABLE_MAIN_SURVEY_REMINDER);

// getting the survey information
$survey_data = survey_manager::get_survey($_GET['survey_id']);
$urlname = substr(html_entity_decode($survey_data['title'],ENT_QUOTES,$charset), 0, 40);
if (strlen(strip_tags($survey_data['title'])) > 40)
{
	$urlname .= '...';
}

// breadcrumbs
$interbreadcrumb[] = array ("url" => "survey_list.php", "name" => get_lang('SurveyList'));
$interbreadcrumb[] = array ('url' => 'survey.php?survey_id='.$_GET['survey_id'], 'name' => $urlname);
if (!$_GET['action'] OR $_GET['action'] == 'overview')
{
	$tool_name = get_lang('Reporting');
}
else
{
	$interbreadcrumb[] = array ("url" => "reporting.php?survey_id=".$_GET['survey_id'], "name" => get_lang('Reporting'));
	switch ($_GET['action'])
	{
		case 'questionreport':
			$tool_name = get_lang('DetailedReportByQuestion');
			break;
		case 'userreport':
			$tool_name = get_lang('DetailedReportByUser');
			break;
		case 'comparativereport':
			$tool_name = get_lang('ComparativeReport');
			break;
		case 'completereport':
			$tool_name = get_lang('CompleteReport');
			break;
	}
}

// Displaying the header
Display::display_header($tool_name,'Survey');

// Action handling
SurveyUtil::handle_reporting_actions();

if (!$_GET['action'] OR $_GET['action'] == 'overview')
{
	$myweb_survey_id = Security::remove_XSS($_GET['survey_id']);
	echo '<b><a href="reporting.php?action=questionreport&amp;survey_id='.$myweb_survey_id.'">'.get_lang('DetailedReportByQuestion').'</a></b> <br />'.get_lang('DetailedReportByQuestionDetail').' <br /><br />';
	echo '<b><a href="reporting.php?action=userreport&amp;survey_id='.$myweb_survey_id.'">'.get_lang('DetailedReportByUser').'</a></b><br />'.get_lang('DetailedReportByUserDetail').'.<br /><br />';
	echo '<b><a href="reporting.php?action=comparativereport&amp;survey_id='.$myweb_survey_id.'">'.get_lang('ComparativeReport').'</a></b><br />'.get_lang('ComparativeReportDetail').'.<br /><br />';
	echo '<b><a href="reporting.php?action=completereport&amp;survey_id='.$myweb_survey_id.'">'.get_lang('CompleteReport').'</a></b><br />'.get_lang('CompleteReportDetail').'<br /><br />';
}

// Footer
Display :: display_footer();