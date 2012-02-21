<?php
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004-2008 Dokeos SPRL
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) Olivier Brouckaert
	Copyright (c) Roan Embrechts, Vrije Universiteit Brussel

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
* GOAL : Dokeos installation
* As seen from the user, the installation proceeds in 6 steps.
* The user is presented with several webpages where he/she has to make choices
* and/or fill in data.
*
* The aim is, as always, to have good default settings and suggestions.
*
* @todo	reduce high level of duplication in this code
* @todo (busy) organise code into functions
* @package dokeos.install
==============================================================================
*/

/*
==============================================================================
		INIT SECTION
==============================================================================
*/
session_start();
// Including necessary files
@include('../inc/installedVersion.inc.php');
require('../inc/lib/main_api.lib.php');

require('../lang/spanish_fd/trad4all.inc.php');
require('../lang/spanish_fd/install.inc.php');

if (!empty($_POST['language_list']))
{
	$search = array('../','\\0');
	$install_language = str_replace($search,'',urldecode($_POST['language_list']));
	if(!is_dir('../lang/'.$install_language)){$install_language = 'spanish_fd';}
	include_once("../lang/$install_language/trad4all.inc.php");
	include_once("../lang/$install_language/install.inc.php");
	api_session_register('install_language');
}
elseif ( isset($_SESSION['install_language']) && $_SESSION['install_language'] )
{
	$install_language = $_SESSION['install_language'];
	include_once("../lang/$install_language/trad4all.inc.php");
	include_once("../lang/$install_language/install.inc.php");
}

$charset = '';
//force ISO-8859-15 for European languages. Leave Apache determine the encoding for others (HTML declaring UTF-8)
/*$euro_langs = array('english','french','french_KM','french_corporate','french_org','dutch','spanish','german','italian','greek','danish','swedish','norwegian','polish','galician','catalan','czech','finnish');
if (isset($install_language))
{
	if(in_array($install_language,$euro_langs))
	{
		$charset = 'ISO-8859-15';
		header('Content-Type: text/html; charset='. $charset);
	}
}*/
$charset = 'UTF-8';
require_once('install_upgrade.lib.php'); //also defines constants
require_once('install_functions.inc.php');

// Some constants
define('DOKEOS_INSTALL',1);
define('MAX_COURSE_TRANSFER',100);
define('INSTALL_TYPE_UPDATE', 'update');
define('FORM_FIELD_DISPLAY_LENGTH', 40);
define('DATABASE_FORM_FIELD_DISPLAY_LENGTH', 25);
define('MAX_FORM_FIELD_LENGTH', 50);
define('DEFAULT_LANGUAGE', 'spanish_fd');

// setting the error reporting
error_reporting(E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR);

// overriding the timelimit (for large campusses that have to be migrated)
@set_time_limit(0);

//upgrading from any subversion of 1.6 is just like upgrading from 1.6.5 
$update_from_version_6=array('1.6','1.6.1','1.6.2','1.6.3','1.6.4','1.6.5');
//upgrading from any subversion of 1.8 avoids the additional step of upgrading from 1.6
$update_from_version_8=array('1.8','1.8.2','1.8.3','1.8.4');
$my_old_version = '';
$tmp_version = get_config_param('dokeos_version');
if(!empty($_POST['old_version']))
{
	$my_old_version = $_POST['old_version'];
}
elseif(!empty($dokeos_version)) //variable coming from installedVersion, normally
{
	$my_old_version = $dokeos_version;
}
elseif(!empty($tmp_version))
{
	$my_old_version = $tmp_version;
}
$new_version = '2.0';
$new_version_stable = true;
/*
==============================================================================
		STEP 1 : INITIALIZES FORM VARIABLES IF IT IS THE FIRST VISIT
==============================================================================
*/
$badUpdatePath=false;
$emptyUpdatePath=true;
$proposedUpdatePath = '';
if(!empty($_POST['updatePath']))
{
	$proposedUpdatePath = $_POST['updatePath'];	
}

if($_POST['step2_install'] || $_POST['step2_update_8'] || $_POST['step2_update_6'])
{
	if($_POST['step2_install'])
	{
		$installType='new';

		$_POST['step2']=1;
	}
	else
	{
		$installType='update';
		if($_POST['step2_update_8'])
		{
			$emptyUpdatePath = false;
			if(empty($_POST['updatePath']))
			{
				$proposedUpdatePath = $_SERVER['DOCUMENT_ROOT'];
			}
			else
			{
				$proposedUpdatePath = $_POST['updatePath'];
			}
			if(substr($proposedUpdatePath,-1) != '/')
			{
				$proposedUpdatePath.='/';
			}			
			if(file_exists($proposedUpdatePath))
			{
				if(in_array($my_old_version,$update_from_version_8))
				{
					$_POST['step2']=1;
				}
				else
				{
					$badUpdatePath=true;
				}
			}
			else
			{
				$badUpdatePath=true;
			}
		}
		else //step2_update_6, presumably
		{
			if(empty($_POST['updatePath']))
			{
				$_POST['step1']=1;
			}
			else
			{
				$emptyUpdatePath = false;
				if(substr($_POST['updatePath'],-1) != '/')
				{
					$_POST['updatePath'].='/';
				}
	
				if(file_exists($_POST['updatePath']))
				{
					//1.6.x
					$my_old_version = get_config_param('clarolineVersion',$_POST['updatePath']);
					if(in_array($my_old_version,$update_from_version_6))
					{
						$_POST['step2']=1;
						$proposedUpdatePath = $_POST['updatePath'];
					}
					else
					{
						$badUpdatePath=true;
					}
				}
				else
				{
					$badUpdatePath=true;
				}
			}
		}
	}
}
elseif($_POST['step1'])
{
	$_POST['updatePath']='';
	$installType='';
	$updateFromConfigFile='';
	unset($_GET['running']);
}
else
{
	$installType=$_GET['installType'];
	$updateFromConfigFile=$_GET['updateFromConfigFile'];
}

if($installType=='update' && in_array($my_old_version,$update_from_version_8))
{
	include_once('../inc/conf/configuration.php');
}

if(!isset($_GET['running']))
{
	$dbHostForm='localhost';
	$dbUsernameForm='root';
	$dbPassForm='';
 	$dbPrefixForm='bd_plataforma';
	$dbNameForm='_main';
	$dbStatsForm='_stats';
	$dbScormForm='';
	$dbUserForm='_user';

	// extract the path to append to the url if Dokeos is not installed on the web root directory
	$urlAppendPath=str_replace('/main/install/index.php','',api_get_self());
  	$urlForm='http://'.$_SERVER['HTTP_HOST'].$urlAppendPath.'/';
	$pathForm=str_replace('\\','/',realpath('../..')).'/';

	$emailForm=$_SERVER['SERVER_ADMIN'];
	$email_parts = explode('@',$emailForm);
	if($email_parts[1] == 'localhost')
	{
		$emailForm .= '.com';
	}
	$adminLastName='Apellidos';
	$adminFirstName='Nombre';
	$loginForm='admin';
	$passForm=api_generate_password();
        
        $gestorLastName='Apellidos';
	$gestorFirstName='Nombre';
	$gestorloginForm='gestor';
	$gestorpassForm=api_generate_password();
        $gestoremailForm = 'gestor@localhost.com';
        
        $ServicioTecnico = 'info@localhost.com';
        
        $SmtpFromEmail = "fromUser@localhost.com";
        $SmtpFromName = "FromName";
        $SmtpHost = "mail.localhost.com";
        $SmtpUser = "Usuario";
        $SmtpPass = "Clave";

	$campusForm='Mi Campus';
	$educationForm='Albert Einstein';
	$adminPhoneForm='954 00 11 22';
	$institutionForm='Mi Empresa';
	$institutionUrlForm='http://www.miempresa.com';

	$languageForm='spanish';

	$checkEmailByHashSent=0;
	$ShowEmailnotcheckedToStudent=1;
	$userMailCanBeEmpty=1;
	$allowSelfReg=0;
	$allowSelfRegProf=0;
	$enableTrackingForm=1;
	$singleDbForm=0;
	$encryptPassForm=0;
	$session_lifetime=360000;
}
else
{
	foreach($_POST as $key=>$val)
	{
		$magic_quotes_gpc=ini_get('magic_quotes_gpc')?true:false;

		if(is_string($val))
		{
			if($magic_quotes_gpc)
			{
				$val=stripslashes($val);
			}

			$val=trim($val);

			$_POST[$key]=$val;
		}
		elseif(is_array($val))
		{
			foreach($val as $key2=>$val2)
			{
				if($magic_quotes_gpc)
				{
					$val2=stripslashes($val2);
				}

				$val2=trim($val2);

				$_POST[$key][$key2]=$val2;
			}
		}

		$GLOBALS[$key]=$_POST[$key];
	}
}

// The Steps
$total_steps=7;
if (!$_POST)
{
	$current_step=1;
}
elseif (!empty($_POST['language_list']) or !empty($_POST['step1']) or ((!empty($_POST['step2_update_8']) or (!empty($_POST['step2_update_6'])))  && ($emptyUpdatePath or $badUpdatePath)))
{
	$current_step=2;
}
elseif (!empty($_POST['step2']) or (!empty($_POST['step2_update_8']) or (!empty($_POST['step2_update_6'])) ))
{
	$current_step=3;
}
elseif (!empty($_POST['step3']))
{
	$current_step=4;
}
elseif (!empty($_POST['step4']))
{
	$current_step=5;
}
elseif (!empty($_POST['step5']))
{
	$current_step=6;
}



?>
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>&mdash; <?php echo get_lang('DokeosInstallation').' &mdash; '.get_lang('Version_').' '.$new_version; ?></title>
	<style type="text/css" media="screen, projection">
		/*<![CDATA[*/
		@import "../css/public_admin/default.css";
		/*]]>*/
	</style>
	<script language="javascript">
		init_visibility=0;
		function show_hide_option()
		{
			if(init_visibility == 0)
			{
				document.getElementById('optional_param1').style.display = '';
				document.getElementById('optional_param2').style.display = '';
				if(document.getElementById('optional_param3'))
				{
					document.getElementById('optional_param3').style.display = '';
				}
				document.getElementById('optional_param4').style.display = '';
				document.getElementById('optional_param5').style.display = '';
				document.getElementById('optional_param6').style.display = '';
				init_visibility = 1;
			}
			else
			{
				document.getElementById('optional_param1').style.display = 'none';
				document.getElementById('optional_param2').style.display = 'none';
				if(document.getElementById('optional_param3'))
				{
					document.getElementById('optional_param3').style.display = 'none';
				}
				document.getElementById('optional_param4').style.display = 'none';
				document.getElementById('optional_param5').style.display = 'none';
				document.getElementById('optional_param6').style.display = 'none';
				init_visibility = 0;				
			}
		}
	</script>
<?php if(!empty($charset)){ ?>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset ?>" />
<?php } ?>
</head>
<body style="background-color: #ffffff;" dir="<?php echo $text_dir ?>">


<div id="header">
	<div id="header1"><div id="logosHeader1"></div></div>
	<div id="header2"><?php echo get_lang('DokeosInstallation').' &mdash; '.get_lang('Version_').' '.$new_version; ?><?php if($installType == 'new') echo ' &ndash; '.get_lang('NewInstallation'); else if($installType == 'update') echo ' &ndash; '.get_lang('UpdateFromDokeosVersion').implode('|',$update_from_version); ?></div>
	<div id="header3">&nbsp;</div>
</div>


<form style="padding: 0px; margin: 0px;" method="post" action="<?php echo api_get_self(); ?>?running=1&amp;installType=<?php echo $installType; ?>&amp;updateFromConfigFile=<?php echo urlencode($updateFromConfigFile); ?>">

<div id="installation_steps">
	
	<ol>
		<li <?php step_active('1'); ?>><?php echo get_lang('InstallationLanguage'); ?></li>
		<li <?php step_active('2'); ?>><?php echo get_lang('Requirements'); ?></li>
		<li <?php step_active('3'); ?>><?php echo get_lang('Licence'); ?></li>
		<li <?php step_active('4'); ?>><?php echo get_lang('DBSetting'); ?></li>
		<li <?php step_active('5'); ?>><?php echo get_lang('CfgSetting'); ?></li>
		<li <?php step_active('6'); ?>><?php echo get_lang('PrintOverview'); ?></li>
		<li <?php step_active('7'); ?>><?php echo get_lang('Installing'); ?></li>
	</ol>
</div>

<table cellpadding="6" cellspacing="0" border="0" width="75%" align="center">
<tr>
  <td>
	<input type="hidden" name="updatePath"           value="<?php if(!$badUpdatePath) echo   ($proposedUpdatePath); ?>" />
	<input type="hidden" name="urlAppendPath"        value="<?php echo   ($urlAppendPath); ?>" />
	<input type="hidden" name="pathForm"             value="<?php echo   ($pathForm); ?>" />
	<input type="hidden" name="urlForm"              value="<?php echo   ($urlForm); ?>" />
	<input type="hidden" name="dbHostForm"           value="<?php echo   ($dbHostForm); ?>" />
	<input type="hidden" name="dbUsernameForm"       value="<?php echo   ($dbUsernameForm); ?>" />
	<input type="hidden" name="dbPassForm"           value="<?php echo   ($dbPassForm); ?>" />
	<input type="hidden" name="singleDbForm"         value="<?php echo   ($singleDbForm); ?>" />
	<input type="hidden" name="dbPrefixForm"         value="<?php echo   ($dbPrefixForm); ?>" />
	<input type="hidden" name="dbNameForm"           value="<?php echo   ($dbNameForm); ?>" />
<?php
	if($installType == 'update' OR $singleDbForm == 0)
	{
?>
	<input type="hidden" name="dbStatsForm"          value="<?php echo   ($dbStatsForm); ?>" />
	<input type="hidden" name="dbScormForm"          value="<?php echo   ($dbScormForm); ?>" />
	<input type="hidden" name="dbUserForm"           value="<?php echo   ($dbUserForm); ?>" />
<?php
	}
	else
	{
?>
	<input type="hidden" name="dbStatsForm"          value="<?php echo   ($dbNameForm); ?>" />
	<input type="hidden" name="dbUserForm"           value="<?php echo   ($dbNameForm); ?>" />
<?php
	}
?>
	<input type="hidden" name="enableTrackingForm"   value="<?php echo   ($enableTrackingForm); ?>" />
	<input type="hidden" name="allowSelfReg"         value="<?php echo   ($allowSelfReg); ?>" />
	<input type="hidden" name="allowSelfRegProf"     value="<?php echo   ($allowSelfRegProf); ?>" />
	<input type="hidden" name="emailForm"            value="<?php echo   ($emailForm); ?>" />
	<input type="hidden" name="adminLastName"        value="<?php echo   ($adminLastName); ?>" />
	<input type="hidden" name="adminFirstName"       value="<?php echo   ($adminFirstName); ?>" />
	<input type="hidden" name="adminPhoneForm"       value="<?php echo   ($adminPhoneForm); ?>" />
	<input type="hidden" name="loginForm"            value="<?php echo   ($loginForm); ?>" />
	<input type="hidden" name="passForm"             value="<?php echo   ($passForm); ?>" />
        
        <input type="hidden" name="gestoremailForm"       value="<?php echo   ($gestoremailForm); ?>" />
	<input type="hidden" name="gestorLastName"        value="<?php echo   ($gestorLastName); ?>" />
	<input type="hidden" name="gestorFirstName"       value="<?php echo   ($gestorFirstName); ?>" />
	<input type="hidden" name="gestorloginForm"       value="<?php echo   ($gestorloginForm); ?>" />
	<input type="hidden" name="gestorpassForm"        value="<?php echo   ($gestorpassForm); ?>" />

        <input type="hidden" name="SmtpFromEmail"       value="<?php echo   ($SmtpFromEmail); ?>" />
	<input type="hidden" name="SmtpFromName"        value="<?php echo   ($SmtpFromName); ?>" />
	<input type="hidden" name="SmtpHost"            value="<?php echo   ($SmtpHost); ?>" />
	<input type="hidden" name="SmtpUser"            value="<?php echo   ($SmtpUser); ?>" />
	<input type="hidden" name="SmtpPass"            value="<?php echo   ($SmtpPass); ?>" />
        
        <input type="hidden" name="ServicioTecnico"            value="<?php echo   ($ServicioTecnico); ?>" />

	<input type="hidden" name="languageForm"         value="<?php echo   ($languageForm); ?>" />
	<input type="hidden" name="campusForm"           value="<?php echo   ($campusForm); ?>" />
	<input type="hidden" name="educationForm"        value="<?php echo   ($educationForm); ?>" />
	<input type="hidden" name="institutionForm"      value="<?php echo   ($institutionForm); ?>" />
	<input type="hidden" name="institutionUrlForm"   value="<?php echo stristr($institutionUrlForm,'http://')?  ($institutionUrlForm):'http://'.  ($institutionUrlForm); ?>" />
	<input type="hidden" name="checkEmailByHashSent" value="<?php echo   ($checkEmailByHashSent); ?>" />
	<input type="hidden" name="ShowEmailnotcheckedToStudent" value="<?php echo   ($ShowEmailnotcheckedToStudent); ?>" />
	<input type="hidden" name="userMailCanBeEmpty"   value="<?php echo   ($userMailCanBeEmpty); ?>" />
	<input type="hidden" name="encryptPassForm"      value="<?php echo   ($encryptPassForm); ?>" />
	<input type="hidden" name="session_lifetime"  value="<?php echo   ($session_lifetime); ?>" />
	<input type="hidden" name="old_version"  value="<?php echo   ($my_old_version); ?>" />
	<input type="hidden" name="new_version"  value="<?php echo   ($new_version); ?>" />




<?php

if($_POST['step2'])
{
	//STEP 3 : LICENSE
	display_license_agreement();
}
elseif($_POST['step3'])
{
	//STEP 4 : MYSQL DATABASE SETTINGS
	display_database_settings_form($installType, $dbHostForm, $dbUsernameForm, $dbPassForm, $dbPrefixForm, $enableTrackingForm, $singleDbForm, $dbNameForm, $dbStatsForm, $dbScormForm, $dbUserForm);
}
elseif($_POST['step4'])
{
	//STEP 5 : CONFIGURATION SETTINGS
	//if update, try getting settings from the database...
	if($installType == 'update')
	{
		$db_name = $dbNameForm;
		$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'platformLanguage');
		if(!empty($tmp)) $languageForm = $tmp;
		$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'emailAdministrator');
		if(!empty($tmp)) $emailForm = $tmp;
		$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'administratorName');
		if(!empty($tmp)) $adminFirstName = $tmp;
		$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'administratorSurname');
		if(!empty($tmp)) $adminLastName = $tmp;
		$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'administratorTelephone');
		if(!empty($tmp)) $adminPhoneForm = $tmp;
		$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'siteName');
		if(!empty($tmp)) $campusForm = $tmp;
		$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'Institution');
		if(!empty($tmp)) $institutionForm = $tmp;
		$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'InstitutionUrl');
		if(!empty($tmp)) $institutionUrlForm = $tmp;
		if(in_array($my_old_version,$update_from_version_6))
		{   //for version 1.6
			$urlForm = get_config_param('rootWeb');
			$encryptPassForm = get_config_param('userPasswordCrypted');
			$allowSelfReg = get_config_param('allowSelfReg');
			$allowSelfRegProf = get_config_param('allowSelfRegProf');
		}
		else
		{   //for version 1.8
			$urlForm = $_configuration['root_web'];
			$encryptPassForm = get_config_param('userPasswordCrypted');
			$allowSelfReg = false;
			$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'allow_registration');
			if(!empty($tmp)) $allowSelfReg = $tmp;
			$allowSelfRegProf = false;
			$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'allow_registration_as_teacher');
			if(!empty($tmp)) $allowSelfRegProf = $tmp;
		}
	}
         
	display_configuration_settings_form($installType, 
                $urlForm, 
                $languageForm, 
                $emailForm, 
                $adminFirstName, 
                $adminLastName, 
                $adminPhoneForm, 
                $campusForm, 
                $institutionForm, 
                $institutionUrlForm, 
                $encryptPassForm, 
                $allowSelfReg, 
                $allowSelfRegProf, 
                $loginForm, 
                $passForm,
                $gestorLastName,
                $gestorFirstName,
                $gestorloginForm,
                $gestorpassForm,
                $gestoremailForm,
                $SmtpFromEmail,
                $SmtpFromName,
                $SmtpHost,
                $SmtpUser,
                $SmtpPass,
                $ServicioTecnico 
 );
}
elseif($_POST['step5'])
{
	//STEP 6 : LAST CHECK BEFORE INSTALL
?>

	<h2><?php echo display_step_sequence().get_lang('LastCheck'); ?></h2>

	<?php echo get_lang('HereAreTheValuesYouEntered');?>
	<br />
	<b><?php echo get_lang('PrintThisPageToRememberPassAndOthers');?></b>

	<blockquote>

	<?php echo get_lang('MainLang').' : '.$languageForm; ?><br /><br />
        <?php echo "<br/><i>".  get_lang('DBHost')."</i>"; ?><br />
	<?php echo get_lang('DBHost').' : '.$dbHostForm; ?><br />
	<?php echo get_lang('DBLogin').' : '.$dbUsernameForm; ?><br />
	<?php echo get_lang('DBPassword').' : '.$dbPassForm; ?><br />
	<?php if(!empty($dbPrefixForm)) echo get_lang('DbPrefixForm').' : '.$dbPrefixForm.'<br />'; ?>
        
        <?php echo "<br/><i>".  get_lang('CfgSetting')."</i>"; ?><br />
        <?php echo get_lang('CampusName').' : '.$campusForm; ?><br />
	<?php echo get_lang('InstituteShortName').' : '.$institutionForm; ?><br />
	<?php echo get_lang('InstituteURL').' : '.$institutionUrlForm; ?><br />
	<?php echo get_lang('DokeosURL').' : '.$urlForm; ?><br />

     
 
     
	<?php // echo get_lang('EncryptUserPass').' : '.($encryptPassForm?$langYes:$langNo); ?>
        <?php echo "<br/><i>".  get_lang('langPlatformAdmin')."</i>"; ?><br />
	<?php echo get_lang('AdminEmail').' : '.$emailForm; ?><br />
	<?php echo get_lang('AdminLastName').' : '.$adminLastName; ?><br />
	<?php echo get_lang('AdminFirstName').' : '.$adminFirstName; ?><br />
	<?php echo get_lang('AdminPhone').' : '.$adminPhoneForm; ?><br />
        
        <?php if($installType == 'new'): ?>
	<?php echo get_lang('AdminLogin').' : <b>'.$loginForm; ?></b><br />
	<?php echo get_lang('AdminPass').' : <b>'.$passForm; ?></b><br /><br />
	<?php else: ?>
	<br />
	<?php endif; ?>
        
        
        
                <?php echo "<br/><i>".  get_lang('gestor')."</i>"; ?><br />
	<?php echo get_lang('AdminEmail').' : '.$gestoremailForm; ?><br />
	<?php echo get_lang('AdminLastName').' : '.$gestorLastName; ?><br />
	<?php echo get_lang('AdminFirstName').' : '.$gestorFirstName; ?><br />
           
        <?php if($installType == 'new'): ?>
	<?php echo get_lang('AdminLogin').' : <b>'.$gestorloginForm; ?></b><br />
	<?php echo get_lang('AdminPass').' : <b>'.$gestorpassForm; ?></b><br /><br />
	<?php else: ?>
	<br />
	<?php endif; ?>
        
        
           
        <?php echo "<br/><i>".  get_lang('Email')."</i>"; ?><br />
	<?php echo get_lang('SmtpFromEmail').' : '.$SmtpFromEmail; ?><br />
	<?php echo get_lang('SmtpFromName').' : '.$SmtpFromName; ?><br />
	<?php echo get_lang('langSmtpHost').' : '.$SmtpHost; ?><br />
        <?php echo get_lang('langSmtpUser').' : '.$SmtpUser; ?><br />
        <?php echo get_lang('langSmtpPass').' : '.$SmtpPass; ?><br />


	</blockquote>

	<?php if($installType == 'new'): ?>
	<div style="background-color:#FFFFFF">
	<p align="center"><b><font color="red">
	<?php // echo get_lang('Warning');?> <br />
	<?php // echo get_lang('TheInstallScriptWillEraseAllTables');?>
	</font></b></p>
	</div>
	<?php endif; ?>

	<table width="100%">
	<tr>
	  <td><input type="submit" name="step4" value="&lt; <?php echo get_lang('Previous'); ?>" /></td>
	  <td align="right"><input type="submit" name="step6" value="<?php echo get_lang('InstallDokeos'); ?> &gt;" onclick="javascript:if(this.value == '<?php echo get_lang('PleaseWait');?>...') return false; else this.value='<?php echo get_lang('PleaseWait');?>...';" /></td>
	</tr>
	</table>

<?php
}
elseif($_POST['step6'])
{
    /*echo "<pre>";
    print_r($_REQUEST);
    echo "</pre>";
    die("instalando");*/
	//STEP 6 : INSTALLATION PROCESS
	if($installType == 'update')
	{
		if(empty($my_old_version)){$my_old_version='1.8.3';} //we guess
		$_configuration['main_database'] = $dbNameForm;
		//$urlAppendPath = get_config_param('urlAppend');
		switch($my_old_version)
		{
			case '1.6':
			case '1.6.0':
			case '1.6.1':
			case '1.6.2':
			case '1.6.3':
			case '1.6.4':
			case '1.6.5':
				include('update-db-1.6.x-1.8.0.inc.php');
				include('update-files-1.6.x-1.8.0.inc.php');
				//intentionally no break to continue processing
			case '1.8':
			case '1.8.0':
				include('update-db-1.8.0-1.8.2.inc.php');
				//intentionally no break to continue processing
			case '1.8.2':
				include('update-db-1.8.2-1.8.3.inc.php');
				//intentionally no break to continue processing
			case '1.8.3':
				include('update-db-1.8.3-1.8.4.inc.php');
				include('update-files-1.8.3-1.8.4.inc.php');
			case '1.8.4':
			default:
				include('update-db-1.8.4-1.8.5.inc.php');
				include('update-files-1.8.4-1.8.5.inc.php');
				break;
		}
	}
	else
	{
           
            
		include('install_db.inc.php');
		include('install_files.inc.php');
	}

	display_after_install_message($installType, $nbr_courses);
}
elseif($_POST['step1'] || $badUpdatePath)
{
	//STEP 1 : REQUIREMENTS
	//make sure that proposed path is set, shouldn't be necessary but...
	if(empty($proposedUpdatePath)){$proposedUpdatePath = $_POST['updatePath'];}
	display_requirements($installType, $badUpdatePath, $proposedUpdatePath, $update_from_version_8, $update_from_version_6);
}
else
{
	//start screen
	display_language_selection();
}
?>

  </td>
</tr>
</table>



</form>
<br style="clear:both;" />

</body>
</html>
