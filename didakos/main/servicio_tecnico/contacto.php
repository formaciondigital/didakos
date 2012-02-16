<?php 

/*
==============================================================================
		Página creada por Formación Digital

Autor: Eduardo García
Página incial: -
Página actual: contacto.php
Descripción: Página que muestra datos del alumno y una caja de texto para enviar una 
incidencia tecnica por email. Si los datos no se encuentran en las variables de sesión muestra
unas cajas de texto para completarlas por parte del alumno.
		
==============================================================================
*/

// name of the language file that needs to be included
$language_file = 'serv_tecnico';

// including necessary libraries
require ('../inc/global.inc.php');
require ('../inc/conf/mail.conf.php');

$libpath = api_get_path(LIBRARY_PATH);
require_once ($libpath.'formvalidator/FormValidator.class.php');
$main_user_table = Database :: get_main_table(TABLE_MAIN_USER);
$interbreadcrumb[] = array ("url" => 'contacto.php');
$tool_name = get_lang('ServTecnico');

Display :: display_header($tool_name);

if (isset ($_GET['incidencia']) && $_GET['incidencia']!="" )
	{
	//hemos recibido el texto de incidencia
	
	$mail = new PHPMailer();
	$mail->IsSMTP();   
	$mail->Host  = $platform_email['SMTP_HOST']  ;  // specify main and backup server
	$mail->SMTPAuth = true;
	$mail->Username = $platform_email['SMTP_USER'];
	$mail->Password = $platform_email['SMTP_PASS'] ;

	$mail->IsHTML(true);
	$mail->From = $platform_email['SMTP_FROM_EMAIL'];
	$mail->FromName = get_lang('Incidencia'); /*"Incidencia";*/
	$mail->Subject = "Incidencia - " . $_SESSION["checkDokeosURL"];

	$plataforma = $_SESSION["checkDokeosURL"];
	$alumno = $_GET['alumno'];
	
	if  (isset ($_GET['curso'])) 
	{$curso = $_GET['curso'];}
	else
	{$curso = $_SESSION["_course"]["id"] . ' ' . $_SESSION["_course"]["name"];}
	
	if  (isset ($_GET['email']))
	{$email = $_GET['email'];}
	else
	{$email = $_SESSION["email"];}

	if  (isset ($_GET['phone']))
	{$phone = $_GET['phone'];}
	else
	{$phone = $_GET["phone"]["id"];}
	
	$body = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body><br>';
	$body = $body . '<b>'. get_lang('Plataforma') .':</b> ' . $plataforma . '<br>';
	$body = $body . '<b>'. get_lang('Curso') .':.</b> ' . $curso . '<br>';
	$body = $body . '<b>'. get_lang('Alumno') .':</b> ' . $alumno . '<br>';
	$body = $body . '<b>'. get_lang('Email') .':</b> ' . $email . '<br>';
	$body = $body . '<b>'. get_lang('Telefono') .':</b> ' . $phone . '<br>';
	$body = $body . '<b>'. get_lang('Incidencia') .':</b> <br>';
	$body = $body . $_GET['incidencia']. '</body></html>';

	$mail->Body    = $body;


	//Esta es la dirección a la que llegan los correos
	$mail->AddAddress($platform_email['SERVICIO_TECNICO']);

	if (!$mail->Send())
	{
	//echo "ERROR: mail not sent to ".$recipient_name." (".$recipient_email.") because of ".$mail->ErrorInfo."<br>";
	$error="true";
	}
	// Clear all addresses
	$mail->ClearAddresses();

	echo '<p align="left">'. get_lang('ConsultaEnviada').'</p>' ;
	

	}
else
	{
	echo '<p align="left">'. get_lang('InfServTecnico') .'</p>';

	$sql = "select firstname,lastname,username,password,email,phone from $main_user_table where user_id=" . $_SESSION["_user"]["user_id"];
	$result = api_sql_query($sql, __FILE__, __LINE__);
	$res = mysql_fetch_array($result);

	echo '<form name="contacto" method="get" action="contacto.php">';
	echo "<b>". get_lang('Plataforma'). "</b> - " . $_SESSION["checkDokeosURL"]. '<br>';

	if (!$_SESSION["_course"]["id"])
		{
		echo '<b>'. get_lang('Curso') .'</b> - <input name="curso" type="text" />* '. get_lang('RelleneDatoMasInfo') .'<br>';
		}
		else
		{
		echo '<b>'. get_lang('Curso') .'</b> - ' . $_SESSION["_course"]["id"] . ' / ' . $_SESSION["_course"]["name"] . '<br>';
		}


	echo '<b>'. get_lang('Alumno') .'</b> - ' . $res["firstname"] . ' ' . $res["lastname"]. '<br>';
	echo '<input name="alumno" type="hidden" value="' . $res["firstname"] . ' ' . $res["lastname"].'"/>';

	if (!$res["email"])
		{
		echo '<b>'. get_lang('Email') .'</b> - <input name="email" type="text" />* '. get_lang('RelleneDatoContactar') .'<br>';
		}
		else
		{
		echo '<b>'. get_lang('Email') .'</b> - ' . $res["email"] . '<input name="email" type="hidden" value="' . $res["email"] . '"/> <br>';
		}

	if (!$res["phone"])
		{
		echo '<b>'. get_lang('Telefono') .'</b> - <input name="phone" type="text" />* '. get_lang('RelleneDatoContactar') .'<br>';
		}
	else
		{
		echo '<b>'. get_lang('Telefono') .'</b> - ' . $res["phone"]. '<input name="phone" type="hidden" value=' . $res["phone"] . '/> <br>';
		}

	echo '<br><b>'. get_lang('InserteConsulta') .'</b><br><textarea COLS="80" ROWS="20" name="incidencia"/></textarea>';
	echo '<div align="left" ><input type="submit" name="Enviar" value="'. get_lang('Enviar') .'" /></div>';
	echo '</form>';
}
/*
==============================================================================
		FOOTER
==============================================================================
*/
Display::display_footer();
?>
