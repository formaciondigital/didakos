<?php 
/***********************************************************************************************************************************  
   	PÁGINA QUE CONTROLA EL PROCESO DE PETICIÓN DE CONFORMIDAD A UN ALUMNO
	PARA QUE PERMITA ACCEDER A SU MURO DE TWITTER

***********************************************************************************************************************************/
// including necessary libraries
ob_start();
$language_file[] = 'redes_sociales';
require ('../../inc/global.inc.php');
require ('funciones_twitter.php');

// Tomamos el user_id del alumno de la sesión
$user_id= $_SESSION["_user"]["user_id"];

// Guardamos tokens de Twitter del usuario en la sesión, para tenerlos disponibles
// Si la función devuelve true es que los tenemos disponibles en la sesion, si devuelve
// false es que no los tenemos. $_SESSION['toauth_at'] $_SESSION['toauth_ats']


if(!api_get_twitter_tokens($user_id)) 
	{	
		header("Location: permisos_twitter.php"); 
	}	

$tool_name = 'Twitter';
$interbreadcrumb[]= array ('url'=>'../index.php', 'name'=> get_lang("Redessociales"));
Display :: display_header($tool_name); 

?>
<script language="javascript">	
	function disableEnterKey() { 
		if (window.event.keyCode == 13) window.event.keyCode = 0; 
	}
	function Retwit(texto)
	{
		this.formulario_texto.texto_libre.value = ('RT @' + texto);	
	}
</script>

<style type="text/css">
BODY { 
	margin: 10 0 10 0px; 
	font-family:Verdana, Arial, Helvetica, sans-serif;
	font-size:12px;
	color: #4a4c4d;
	background:#ffffff;
} 

#cuerpo{ 
	background:#efefef;
	width: 50%;
	border-radius:2px;
	-moz-border-radius: 1px;
	-webkit-border-radius : 5px;
	float:left; 
} 

#lateral{ 
	
	background:#adccd8;
	width: 50%;
	border-radius:2px;
	-moz-border-radius: 1px;
	-webkit-border-radius : 5px;
	float:right; 

} 

p.panel {
	font-family:Verdana, Arial, Helvetica, sans-serif;
	font-size:12px;
	color: #4a4c4d;
}
#lateral_box {
	padding-left: 20px;
	padding-top: 10px;

}
#inferior_box {
	padding-left: 20px;
	padding-top: 10px;
	float:left;

}
#sp_ContenedorGestor{
	float:left;
	width: 100%;
	background: #dddddd;
}

#sp_ContenedorAlumno{
	float:left;
	width: 100%;
	padding-left: 5px;
}


#sp_foto{
	float:left;
	width: 20%;
	padding-top: 5px;

}
#sp_lineas{
	float:left;
	width: 80%;	
}

#linea1 {
	font-family:Verdana, Arial, Helvetica, sans-serif;
	font-size:12px;
	
	padding-bottom: 5px;
	color: #000000;

}
#linea2 {
	font-family:Verdana, Arial, Helvetica, sans-serif;
	font-size:12px;
	padding-top: 5px;
	padding-bottom: 5px;
	color: #6d6d6d;

}
#linea3 {

	font-family:Verdana, Arial, Helvetica, sans-serif;
	font-size:12px;
	padding-top: 5px;
	padding-bottom: 5px;
	color: #000000;
}
#linea {
	float:left;
	width: 100%;
	border-top: 1px dashed #ffffff;
}

#vermas {
	float:right;
	padding-right: 10px;
}
#inferior {
	padding-top: 10px;
	padding-bottom: 10px;
	text-align:center;
}

</style>

<?php


	$opcion=$_GET['opcion'];
	if ($opcion==1) { // INFO: Texto libre

		if ((isset($_POST['texto_libre'])) && (!empty($_POST['texto_libre'])) ) {				
			escribe_tweet($_POST['texto_libre']);
			$mensaje = get_lang("PublicadoTwitter");						
		}
	}
	if ($opcion==2) {  // INFO: Me gusta el curso			
		
		$curso=elimina_acentos($_SESSION['_course']['name']);			
		$mensaje=get_lang("MeGustaElCurso"). " " . $curso;
		$mensaje.=" de ".$_SESSION['checkDokeosURL'];	
		escribe_tweet($mensaje);				
		$mensaje = get_lang("PublicadoTwitter");	
	}

	if ($opcion==4) { // Borrar TOKENS y Terminar		
		borrar_tokens($user_id);
		header("Location: ../index.php"); 
	}	

	if ($mensaje)
	{
		Display::display_normal_message($mensaje);
	}

?>

<div id="cuerpo"> 
	<div id="sp_ContenedorAlumno">  
	<br>
    	<?php echo get_lang("EscribaAqui"); ?>:<br/>
	<form action="index_twitter.php?opcion=1" method="POST" id="formulario_texto" name="formulario_texto" onKeyPress="disableEnterKey()">	
		<TEXTAREA COLS=50 ROWS=4 NAME="texto_libre"></TEXTAREA><br/>
		<INPUT TYPE="submit" value="<?php echo get_lang("Enviar"); ?>"><INPUT TYPE="Reset" value="<?php echo get_lang("Limpiar"); ?>">
	</form>
	</div>
	<br>
<?php

if (api_get_twitter_tokens($user_id))
	{
	/*
		Eliminamos la opción de ver los elementos de la red social.

		$r=lee_ultimos_tweets();
		for ($i=1;$i<=10;$i++)  
		{
			//formateamos la fecha del twitter			
			$created_at = new DateTime ($r[$i-1]['created_at']);
			//Hay que mirar como hacerlo referente al horario del usuario
			$created_at->modify('+2 hour');
			$created_at = $created_at->format("Y-m-d H:i:s"); 
			$imagen = '<img src='. $r[$i-1]['user']['profile_image_url'].' height="60" border="0"/>';	
			echo '<div id="sp_ContenedorAlumno">';
			echo '<div id="sp_foto">' . $imagen . '</div>';
			echo	'<div id="sp_lineas">';
			echo		'<div id="linea1">&nbsp;<b>'. $r[$i-1]['user']['screen_name'] . '</b></div>';
			echo		'<div id="linea2">' . lookurl(utf8_decode($r[$i-1]['text'])) . '</div>';
			echo		'<div id="linea3">' . getHace($created_at);
			echo			'<div id="'. get_lang("VerMas").'"><a href="#" onclick="javascript:Retwit(\''. $r[$i-1]['user']['screen_name'] . ': ' . str_replace('"','',utf8_decode($r[$i-1]['text'])) . '\') "><img src="../../img/retwit.png"></a>&nbsp;<a href="http://www.twitter.com/'. $r[$i-1]['user']['screen_name'] . '" target="_blank">'. get_lang('VerMas').'</a></div>';
			echo		'</div>';
			echo	'</div>';
			echo '</div>';
			echo '<div id="linea"></div>';	  	
		}
	*/
	}	

?>
</div> 
<div id="lateral"> 
	<div id="lateral_box"><?php echo get_lang("LaPlataforma"); ?>:<br><br>
		<a href="index_twitter.php?opcion=2"><?php echo get_lang("MeGustaCurso"); ?></a><br>
		<a href="http://twitter.com/formacion_dgtal" target="_blank"><?php echo get_lang("SeguirFD"); ?></a><br>
		<a href="index_twitter.php?opcion=4" target="_blank"><?php echo get_lang("EliminarTwitter"); ?></a><br>	
	</div>
	<br><br>
</div>

<?php

// OPCIONES COMENTADAS
// Anotaciones en Tweet --> Probar
// Borrar tokens del alumno
/*	
	echo "<br />\n<img src='img/lp_quiz.gif' align='top'> <a href=\"indexbd_twitter.php?opcion=6\">Ejemplo de anotaciones en TWEET</a><br />\n";				
	
	if ($opcion==6) {  // INFO: Anotaciones, probar, según API de Twitter aún no están disponibles	
		// EJemplo de formato JSON para especificar una anotación en el tweet
		//[{'movie': {title:'Avatar', 'url':'http://www.rottentomatoes.com/m/avatar/', 'image':'...', 'text': 'Avatar'}}]
		$annotations = array(array('webpage' => array('title' => 'Avatar','url' => 'http://www.abc.es','text' => 'ej de enlace')));
		//echo json_encode($annotations); die();
		// POST new status with annotations json_encoded.
		$r = $connection->post('statuses/update', array( 'status' => 'Hello mundo --','annotations' => json_encode($annotations)));
							
		if(isset($r['error'])) die('<b>Error en Anotaciones:</b> '.$r['error']);
	}	
	
	echo "<br /><br />\n<img src='img/lp_quiz.gif' align='top'> <a href=\"indexbd_twitter.php?opcion=4\">BORRAR TOKENS DEL ALUMNO - NO SE PODRÁ ACCEDER A SU MURO.</a><br />\n";

*/

/*
==============================================================================
		FOOTER
==============================================================================
*/
ob_end_flush();
Display::display_footer();

function lookurl($texto)
{
	//recibe un texto y devuelve el mismo texto con los enlaces en html
	//primero buscamos la url http://**** 
	$inicio = strpos($texto,"http://");

	if (!is_numeric($inicio))
	{
		return $texto;
	}
	else
	{
		//Obtenemos la cadena desde el enlace en adelante
		$cadena = substr($texto,$inicio);		
		//Obtenemos el enlace
		$final = strpos($cadena," ");
		if (!is_numeric($final))
		{
			//Es porque el enlace está al final del texto
			$final=strlen($texto);
		} 
		$cadena = substr($cadena,0,$final);
		$texto = str_replace($cadena,'<a href="'.$cadena.'" target="_blank">'.$cadena.'</a>',$texto);
		return $texto;
	}
}

function getHace($fecha)
{
	$cacho = explode(" ",$fecha);
	$fecha = explode("-",$cacho[0]);
	$tiempo = explode(":",$cacho[1]);

	if ( count($cacho)==1 )
	{
		$fecha1 = mktime (0,0,0,$fecha[1],$fecha[2],$fecha[0]);
	}
	else
	{
		$fecha1 = mktime ($tiempo[0],$tiempo[1],$tiempo[2],$fecha[1],$fecha[2],$fecha[0]);
	}
	$fecha2= time();
	$dateDiff = $fecha2 - $fecha1;
	$fullDays = floor($dateDiff/(60*60*24));

	if ($fullDays < 1)
	{
			$fullDays = floor($dateDiff/(60*60));
			if ($fullDays < 1) 
			{
				$fullDays = floor($dateDiff/(60));
				if ($fullDays <= 1)
				{
				  return get_lang("HaceUnosInstantes");
				}
				else
				{				
				  return get_lang("Hace")." ".$fullDays ." ".get_lang("Minutos");		
				}
			}
			else
			{
				if ($fullDays==1)
				{
				  return get_lang("Hace")." ". $fullDays ." ".get_lang("Hora");
				}	
				else
				{
				  return get_lang("Hace")." ". $fullDays ." ".get_lang("Horas");
				}
			}
	}
	else
	{
		if ($fullDays > 30)
		{
			return get_lang("HaceMuchoTiempo");
		}
		else
		{
			if ($fullDays==1)
			{
			  return get_lang("Hace")." ". $fullDays ." ".get_lang("Dia");
			}	
			else
			{
			  return get_lang("Hace")." ". $fullDays ." ".get_lang("Dias");
			}
		}
	}

	
}

?>
