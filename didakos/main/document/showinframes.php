<?php // $Id: showinframes.php 15013 2008-04-22 17:47:13Z juliomontoya $ 
/*
============================================================================== 
	Dokeos - elearning and course management software
	
	Copyright (c) 2004-2008 Dokeos S.A.
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) Hugues Peeters
	Copyright (c) Roan Embrechts
	
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
*	This file will show documents in a separate frame.
*	We don't like frames, but it was the best of two bad things.
*
*	display html files within Dokeos - html files have the Dokeos header.
*
*	--- advantages ---
*	users "feel" like they are in Dokeos,
*	and they can use the navigation context provided by the header.
*
*	--- design ---
*	a file gets a parameter (an html file)
*	and shows
*	- dokeos header
*	- html file from parameter
*	- (removed) dokeos footer
*
*	@version 0.6
*	@author Roan Embrechts (roan.embrechts@vub.ac.be)
*	@package dokeos.document
==============================================================================
*/
	
/*
============================================================================== 
	   DOKEOS INIT 
============================================================================== 
*/ 
$language_file[] = 'document';
include('../inc/global.inc.php');

if (!empty($_GET['nopages']))
{
	$nopages=Security::remove_XSS($_GET['nopages']);
	if ($nopages==1)
	{		
		require_once(api_get_path(INCLUDE_PATH) . 'reduced_header.inc.php');
		Display::display_error_message(get_lang('FileNotFound'));
	}
	exit();	
}

$_SESSION['whereami'] = 'document/view';
	
$interbreadcrumb[]= array ('url'=>'./document.php', 'name'=> get_lang('Documents'));
$nameTools = get_lang('Documents');
$file = Security::remove_XSS(urldecode($_GET['file']));

$tool_name = "Documentos"; // title of the page (should come from the language file) 
Display::display_header($tool_name);

/*
============================================================================== 
		Main section
============================================================================== 
*/ 
header('Expires: Wed, 01 Jan 1990 00:00:00 GMT');
//header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Last-Modified: Wed, 01 Jan 2100 00:00:00 GMT');
		
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

$browser_display_title = "Dokeos Documents - " . $_GET['cidReq'] . " - " . $file;

//only admins get to see the "no frames" link in pageheader.php, so students get a header that's not so high
$frameheight = 130;
if($is_courseAdmin)
{
	$frameheight = 155;	
}
$file_root=$_course['path'].'/document'.str_replace('%2F', '/',$file);
$file_url_sys=api_get_path('SYS_COURSE_PATH').$file_root;
$file_url_web=api_get_path('WEB_COURSE_PATH').$file_root;


echo '<iframe name="iframe" border="0" scrolling="no" frameborder="no" width="100%" height="525" src="'.$file_url_web.'?'.api_get_cidreq().'&rand='.mt_rand(1,10000).'"><p>Su navegador no soporta frames</p></iframe>';		


Display::display_footer();
?>

