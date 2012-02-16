<?php // $Id: template.php,v 1.2 2006/03/15 14:34:45 pcool Exp $
//session_start ();
/*
==============================================================================
	Dokeos - elearning and course management software
	
	Copyright (c) 2004-2006 Dokeos S.A.
	Copyright (c) Sally "Example" Programmer (sally@somewhere.net)
	//add your name + the name of your organisation - if any - to this list
	
	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".
	
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	See the GNU General Public License for more details.
	
	Contact address: Dokeos, 44 rue des palais, B-1030 Brussels, Belgium
	Mail: info@dokeos.com
==============================================================================
*/
/**
==============================================================================
*	This file is a code template; 
*	copy the code and paste it in a new file to begin your own work.
*
*	@package dokeos.plugin
==============================================================================
*/
	 
/*
==============================================================================
		INIT SECTION
==============================================================================
*/ 
// global settings initialisation 
// also provides access to main, database and display API libraries

include("../inc/global.inc.php"); 
include(dirname(__FILE__)."/src/phpfreechat.class.php");


function limpiar_acentos($cadena){
$tofind = "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿ";
$replac = "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuy";
return(strtr($cadena,$tofind,$replac));
}


if (!isset($_SESSION["_cid"]) || !isset($_SESSION["_user"]["firstName"]))
{
header('Location: ../../index.php?loginFailed=1');
}

$canal = limpiar_acentos($_SESSION["_cid"]);


//obtenemos el username del usuario
$info_usuario = api_get_user_info($_SESSION['_user']['user_id']);
$usuario = $info_usuario['username'];


//session_start ();
//$params["serverid"] = md5(__FILE__); // calculate a unique id for this chat
$params["serverid"] = $canal;
$params["language"] = "es_ES"; /*22/12/2011 - modificado el idioma para ponerlo en espa�ol*/
$params["theme"] = "default";
$params["title"]  = get_lang("ChatDelCurso")." ". $canal . " ".limpiar_acentos($_SESSION["_course"]["name"]);
$params["nick"]  =  iconv("ISO-8859-1", "UTF-8",$usuario); 
$params["frozen_nick"]  = true;
$params["refresh_delay"]  = 1000;//1 seg
$params["timeout"]  = 120000;  //120 seg
$params["dyn_params"] = array("channels");
$params["channels"] = array($canal);
$params["max_channels"] = 100;
$params["max_msg"] = 0;
$params["isadmin"]  = false;
$params["debug"]  = false;
$params["container_type"] = "mysql";
$params["container_cfg_mysql_host"]  = $_configuration['db_host']	;
$params["container_cfg_mysql_port"]  = 3306;
$params["container_cfg_mysql_database"]  = $_configuration['main_database'];
$params["container_cfg_mysql_table"]  = "fchat";
$params["container_cfg_mysql_username"]  = $_configuration['db_user'];
$params["container_cfg_mysql_password"]  = $_configuration['db_password'];
$chat = new phpFreeChat( $params );
/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/ 
//the main_api.lib.php, database.lib.php and display.lib.php
//libraries are included by default




	
/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/ 

//	Optional extra http or html header
//	If you need to add some HTTP/HTML headers code 
//	like JavaScript functions, stylesheets, redirects, put them here.

 //$httpHeadXtra[] = "<link rel="'stylesheet'" title="'classic'" type="'text/css'" href="'style/content.css'" />"; 
// $httpHeadXtra[] = ""; 
//    ... 
// 
// $htmlHeadXtra[] = ""; 
// $htmlHeadXtra[] = ""; 
//    ... 

//$tool_name = "Example Plugin"; // title of the page (should come from the language file) 
Display::display_header($tool_name);

	
/*
==============================================================================
		FUNCTIONS
==============================================================================
*/ 

// put your functions here
// if the list gets large, divide them into different sections:
// display functions, tool logic functions, database functions	
// try to place your functions into an API library or separate functions file - it helps reuse
	
/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

// Put your main code here. Keep this section short,
// it's better to use functions for any non-trivial code

//api_display_tool_title($tool_name);
//session_start ();


?>
<script language="javascript">
window.onunload = desconectar;

function desconectar() {
pfc.connect_disconnect()
}
</script>
<br><br>
<center><div style="width:80%; text-align:left">
  <?php $chat->printChat(); ?>
  <?php if (isset($params["isadmin"]) && $params["isadmin"]) { ?>
    <p style="color:red;font-weight:bold;">Warning: because of "isadmin" parameter, everybody is admin. Please modify this script before using it on production servers !</p>
  <?php } ?>
</div>
</center>

<?
/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>
