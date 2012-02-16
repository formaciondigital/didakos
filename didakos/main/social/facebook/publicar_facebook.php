<?php 
//Para poder tirar de las variables de sesión.
include("../../inc/global.inc.php");
include("config.php");
include("funciones_facebook.php");  

switch ($_GET['tipo'])
{
	case "1":
		// Escribe facebook un link;
		// recibimos
		// var1 = titulo
		// var2 = texto
		// var3 = subtexto
		// var4 = enlace
		// var5 = imagen
		// Usamos funciones de facebook
		publica_link_facebook ($_GET['var1'],$_GET['var2'],$_GET['var3'],$_GET['var4'],$_GET['var5']);
		break;
	case "2":
		// escribe un texto recibido por post
		$mensaje = substr(utf8_encode($_GET['mensaje']),0,140); 
		publica_mensaje_facebook ($mensaje);
		break;
}



?>

