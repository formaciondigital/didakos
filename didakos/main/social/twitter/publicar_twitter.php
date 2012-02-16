<?php 
//Para poder tirar de las variables de sesión.
include("../../inc/global.inc.php");
//Para las funciones.
include("funciones_twitter.php");  

switch ($_GET['tipo'])
{
	case "1":
		// escribe_tweet("Estoy realizando el curso");
		// atención con las codificaciones.
		// recibimos
		// var1 = texto
		$mensaje = $_GET['var1'];  
		escribe_tweet ($mensaje);
		break;
	case "2":
		// escribe un texto recibido por post
		$mensaje = substr($_GET['mensaje'],0,140); 
		escribe_tweet ($mensaje);
		break;
}	



?>

