<?php
// name of the language file that needs to be included 
$language_file = array('resourcelinker','document');
include('../../../../../../inc/global.inc.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Audio Properties</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta content="noindex, nofollow" name="robots">
		<script src="../../dialog/common/fck_dialog_common.js" type="text/javascript"></script>
		<script src="fck_mp3.js" type="text/javascript"></script>
		<link href="../../dialog/common/fck_dialog_common.css" type="text/css" rel="stylesheet">
	</head>
	<body> <!--scroll="no" style="OVERFLOW: hidden"-->
		<div id="divInfo">
		  <div id="divExtra1"  style="DISPLAY: none">
			<table cellspacing="1" cellpadding="1" border="0" width="100%">
				<tr>
					<td>
						<table cellspacing="0" cellpadding="0" width="100%" border="0">
							<tr>
							<td valign="top" width="100%">								
								<span fckLang="DlgMP3URL">URL</span><br>
								<input id="mpUrl" onBlur="updatePreview();" style="WIDTH: 100%" type="text">
							</td>
							<td id="tdBrowse" valign="bottom" nowrap>
								<input type="button" fckLang="DlgMP3BtnBrowse" value="Browse Server" onClick="BrowseServer();" id="btnBrowse">
							</td>
						</tr>
						</table>
					</td>
				</tr>
			</table>
		  </div>
		  <?php

		  $sType = "MP3";
		  if(isset($_course["sysCode"]))
		  {
		 	 include(api_get_path(INCLUDE_PATH).'course_document.inc.php');
		  }
		  
		  ?>
		</div>
		<div id="divUpload" style="DISPLAY: none">
		
		<?php
		
			include_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
			$form = new FormValidator('frmUpload','POST','','UploadWindow','id="frmUpload" enctype="multipart/form-data" onSubmit="return CheckUpload();"');
			
			$form->addElement('html','<table cellspacing="1" cellpadding="1" border="0" width="90%" align="center">');
			
			$form->addElement('html','<tr><td>');
			$form->addElement('file','NewFile','','id="txtUploadFile" style="WIDTH: 100%" size="40"');
			$form->addElement('html','</td></tr>');
			
			$form->addElement('html','<tr><td>');
			$renderer = & $form->defaultRenderer();
			$renderer->setElementTemplate('<div style="margin-left:-4px;">{element} {label}</div>', 'autostart');
			$form->addElement('checkbox','autostart',get_lang('FckMp3Autostart'), '', 'id="autostart"');
			$form->addElement('html','</td></tr>');
			
			$form->addElement('html','<tr><td>');
			$form->addElement('submit','','Send it to the Server','id="btnUpload" fckLang="DlgLnkBtnUpload"');
			$form->addElement('html','</td></tr></table>');
			
			$form->addElement('html','<iframe name="UploadWindow" style="DISPLAY: none" src="../fckblank.html"></iframe>');
			
			$form->add_real_progress_bar('fckMP3','NewFile');
			
			$form->display();			
		?>		
		</div>
		<script language="javascript">window_onload();</script>
	</body>
</html>