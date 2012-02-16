<?php 

/*
============================================================================== 
	Página creada por Formación Digital
	 
 	PÁGINA QUE PRESENTA LOS LOGOS DE LAS DISTINTAS REDES SOCIALES EN INVESTIGACIÓN		
==============================================================================
*/
/*

CREATE TABLE `fd_xxxxxxxxxxxx_main`.`parametros_facebook` (
  `id` NUMERIC UNSIGNED NOT NULL,
  `user_id` NUMERIC  NOT NULL,
  `facebook_id` NUMERIC  NOT NULL,
  `acess_token` VARCHAR(255)  NOT NULL,
  `fecha_operacion` TIMESTAMP  NOT NULL,
  PRIMARY KEY (`id`)
)
ENGINE = MyISAM;

// Datos de la cuenta de facebook
// correo: version08.formaciondigital@gmail.com
// clave del correo: version08facebook
Aun no esta creada la aplicacion
y hay que modificar abajo el client_id y el secret... lo ideal es un archivo de config como el twitter.


*/
$language_file[] = 'redes_sociales';
//funciones necesarias
require ('funciones_facebook.php');
require ('../../inc/global.inc.php');
require ('config.php');
require ('../../inc/conf/configuration.php');

//obtenemos el user_id
$user_id= $_SESSION["_user"]["user_id"];

//Si no lo tenemos comenzamos el proceso de petición

if(!api_get_facebook_tokens($user_id)) 
{
	if ($_GET['code']=="")
	{
		// Aquí toda la parte del facebbok
		$url = "https://graph.facebook.com/oauth/authorize?client_id=".CONSUMER_KEY."&redirect_uri=" .$_configuration['root_web']. "main/social/facebook/index.php&scope=offline_access,publish_stream,read_stream";
		header("Location: $url"); 
	}
	else
	{

		$code = $_GET['code'];
		$url = "https://graph.facebook.com/oauth/access_token?client_id=".CONSUMER_KEY."&redirect_uri=".$_configuration['root_web']."main/social/facebook/index.php&scope=offline_access,publish_stream,read_stream&client_secret=".CONSUMER_SECRET."&code=$code";
		$ch = curl_init();
		$opts[CURLOPT_URL] = $url;
		if ($proxy!='') 
                {
		$opts[CURLOPT_PROXY] = $proxy;
		$opts[CURLOPT_PROXYUSERPWD] = $proxyuserpwd;
		}
		$opts[CURLOPT_RETURNTRANSFER] = true;
		curl_setopt_array($ch, $opts);
		$result = curl_exec($ch);
		curl_close($ch);
		//Obtenemos el access token y lo guardamos en BBDD
		$parametros_facebook= Database::get_main_table(TABLE_PARAMETROS_FACEBOOK); 
		$data1 = explode("=",$result);
		$data2 = explode("&",$data1[1]);
		$sql ="insert into ".$parametros_facebook." (user_id,access_token) values (".$user_id.",'". $data2[0] ."')";
		$res = api_sql_query($sql,__FILE__,__LINE__);		
	}
}
else
{
	/*
	$ch = curl_init();
	$opts[CURLOPT_URL] = "https://graph.facebook.com/home?access_token=". $_SESSION['foauth_at'];
	$opts[CURLOPT_PROXY] = '10.2.11.1:3128';
	$opts[CURLOPT_RETURNTRANSFER] = true;
	curl_setopt_array($ch, $opts);
	$result = curl_exec($ch);
	curl_close($ch);

	$user_info=@json_decode($result);
	$fb_id=$user_info->id; // returns user's facebook id
	$fb_name=$user_info->name; // returns user's facebook full name
	*/

	if ( isset($_GET['opcion'] ))
	{
		$opcion=$_GET['opcion'];
	}
	else
	{	
		if (isset($_POST['texto_libre']))
		{
			$texto=  substr(utf8_encode ($_POST['texto_libre']),0,140);
			$opcion = 1;
		}
	}

	$user_id= $_SESSION["_user"]["user_id"];

	if ($opcion==1) { // INFO: Texto libre
		publica_mensaje_facebook ($texto);
		$mensaje = get_lang("PublicadoFacebook");
		
	}
	if ($opcion==2) {  // INFO: Me gusta el curso	
		//Esto es como un enlace.
		$var1 = utf8_decode ("Formación digital");
		$var2 = "Estoy realizando el curso ". $_SESSION['_course']['name'];			
		$var3 = substr(api_get_path(WEB_PATH),0,strlen(api_get_path(WEB_PATH))-1 ) ;
		$var4 = api_get_path(WEB_PATH) . "main/social/img/fd.gif";
		$var5 = "";		
		publica_link_facebook ($var1,$var2,$var3,$var4,$var5);
		$mensaje = get_lang("PublicadoFacebook");
		
	}

	if ($opcion==4) { // Borrar TOKENS y Terminar		
		borrar_tokens_facebook($user_id);
		// Destruir la sesión
		$_SESSION['foauth_at']="";
		header("Location: ../index.php"); 
	}	

	


}
$tool_name = 'Facebook';
$interbreadcrumb[]= array ('url'=>'../index.php', 'name'=> get_lang("Redessociales"));
Display :: display_header($tool_name); 


if ($mensaje)
	{
	Display::display_normal_message($mensaje);
	}
	
?>

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

<div id="cuerpo">   
	<div id="sp_ContenedorAlumno">
	<br>
    	<?php echo get_lang("EscribaAqui"); ?><br/>
	<form action="index.php" method="POST" id="formulario_texto" name="formulario_texto" onKeyPress="disableEnterKey()">	
		<TEXTAREA COLS=50 ROWS=4 NAME="texto_libre"></TEXTAREA><br/>
		<INPUT TYPE="submit" value="Enviar"><INPUT TYPE="Reset">
	</form>
	</div>
	<br>
<?php

if (api_get_facebook_tokens($user_id))
	{
		/*
		Eliminamos la lectura del muro de facebook

		$r=lee_ultimos_post(10);
		foreach ( $r as $post)
		{
			//formateamos la fecha
			$created_at = new DateTime ($post[3]);
			//Hay que mirar como hacerlo referente al horario del usuario
			$created_at->modify('+2 hour');
			$created_at = $created_at->format("Y-m-d H:i:s"); 
			$imagen = '<img src="http://graph.facebook.com/' . $post[4] . '/picture" height="60" border="0"/>';	
			echo '<div id="sp_ContenedorAlumno">';
			echo '<div id="sp_foto">' . $imagen . '</div>';
			echo	'<div id="sp_lineas">';
			echo		'<div id="linea1">&nbsp;<b>'. utf8_decode($post[0]) . '</b></div>';
					if ( $post[1]!="") 
					{
			echo		'<div id="linea2">' . lookurl(utf8_decode($post[1] . "<br> " . substr($post[2],0,150) . ' ...')) . '</div>';
					} 
					else 
					{
			echo		'<div id="linea2">' . lookurl(utf8_decode(substr($post[2],0,150) . ' ...')) . '</div>';
					}
			echo		'<div id="linea3">' . getHace($created_at);
			echo			'<div id="vermas"><a href="'. $post[5] . '" target="_blank">'. get_lang('VerMas').'</a></div>';
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
	<div id="lateral_box"><?php echo get_lang("LaPlataformaFacebook"); ?>:<br><br>
	<a href="http://www.facebook.com/formaciondigital" target="_blank"><?php echo get_lang("PaginaFd"); ?></a><br>
	<a href="index.php?opcion=2" target="_self"><?php echo get_lang("MeGustaCurso"); ?></a><br>
	<a href="index.php?opcion=4" target="_blank"><?php echo get_lang("EliminarFacebook"); ?></a><br><br>
	<iframe src="http://www.facebook.com/plugins/like.php?href=www.formaciondigital.com&amp;layout=button_count&amp;show_faces=true&amp;width=450&amp;action=like&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:21px;" allowTransparency="true"></iframe>
	<br><br>
	<iframe src="http://www.facebook.com/plugins/recommendations.php?site=http%3A%2F%2Fformaciondigital.com&amp;width=300&amp;height=300&amp;header=true&amp;colorscheme=light" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:300px; height:300px;" allowTransparency="true"></iframe>
	<br><br>
	<div id="fb-root"></div>
	<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>
	<fb:comments href="http://www.formaciondigital.com/"></fb:comments>
	</div>
</div>


<?php

// $wall_info = lee_ultimos_post(5);
// print_r ($wall_info);

// https://graph.facebook.com/me/feed?access_token=...

   
/*
==============================================================================
		FOOTER
==============================================================================
*/

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

Display::display_footer();

?>




