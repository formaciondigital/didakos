<?php
/***********************************************************************************************************************************   
		FUNCIONES PARA INTERACTUAR CON RED SOCIAL TWITTER MEDIANTE OAUTH

***********************************************************************************************************************************/

include("../../inc/global.inc.php");

$t_keys = Database::get_main_table(TABLE_REDES_SOCIALES);   
$sql = "select * from $t_keys where name='twitter'";
$result = api_sql_query($sql,__FILE__,__LINE__);
$temp_row = Database::fetch_array($result);
define('CONSUMER_KEY', $temp_row['consumer_key']);
define('CONSUMER_SECRET', $temp_row['consumer_secret']);



require('tOAuth/tOAuth.class.php');

/*
Consulta si tenemos los tokens de Twitter del usuario indicado y si los tenemos, los guarda en la sesión
*/
/*
Añadida a main_api.lib.php
function obtener_tokens($user_id)
{	
	$parametros_twitter= Database::get_main_table(TABLE_PARAMETROS_TWITTER); 
	$sql="SELECT * FROM ". $parametros_twitter ." where user_id=".$user_id;
	$res = api_sql_query($sql,__FILE__,__LINE__);
	$row = Database::fetch_array($res);

	if (!isset($row['user_id'])) 
	{  
		// No Existe el alumno en la tabla
		// No tenemos los tokens del alumno
		return "false";
	}
	else  
	{ 
		// Sí tenemos los tokens del alumno, los guardamos en la sesión
		$_SESSION['toauth_at']=$row['toauth_at'];
		$_SESSION['toauth_ats']=$row['toauth_ats'];
		return "true";		
	}
}		
*/
	
/*
Elimina los tokens de Twitter que tenemos almacenados en la bd del usuario indicado y también los elimina de la sesión
*/
function borrar_tokens($user_id)
{	
	$parametros_twitter= Database::get_main_table(TABLE_PARAMETROS_TWITTER); 

	$sql= "delete from ". $parametros_twitter . " where user_id=".$user_id;
	api_sql_query($sql,__FILE__,__LINE__);
	
	unset($_SESSION['toauth_at']);
	unset($_SESSION['toauth_ats']);			
}	
	

/*
Escribe un tweet con el mensaje pasado en el muro de Twitter del usuario indicado, (si tenemos sus tokens de permiso en 
la sesión). Coge los primeros 140 caracteres del mensaje pasado (de momento, por la limitación de Twitter, hasta que 
aumente la longitud permitida de los tweets) 
*/
function escribe_tweet($mensaje)
{		
	if(!empty($_SESSION['toauth_at']) && !empty($_SESSION['toauth_ats'])) {
		$connection = new tOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['toauth_at'], $_SESSION['toauth_ats']);
		
		$mensaje=utf8_encode(substr($mensaje,0,140));
		$r = $connection->post('statuses/update', array('status'=>$mensaje));
		if(isset($r['error'])) {
			echo "mensaje: ".$mensaje;
			echo '<pre>'.print_r($r, true).'</pre>';
			die('<b>Error en escribe_tweet:</b> '.$r['error']);
		}	
	}			
}	

/*
Obtiene el último tweet del muro de Twitter del alumno indicado, (si tenemos sus tokens de permiso en 
la sesión) 
*/
function lee_ultimo_tweet()
{		
	if(!empty($_SESSION['toauth_at']) && !empty($_SESSION['toauth_ats'])) {
		$connection = new tOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['toauth_at'], $_SESSION['toauth_ats']);
		
		$r = $connection->get('account/verify_credentials');	
		  
		if(isset($r['error'])) {
			die('<b>Error en lee_ultimo_tweet:</b> '.$r['error']);			
		}else {
			return $r;
		}		
	}			
}


/*
Obtiene los últimos 20 tweets del muro de Twitter del usuario indicado, (si tenemos sus tokens de permiso en 
la sesión) 
*/
function lee_ultimos_tweets()
{		
	if(!empty($_SESSION['toauth_at']) && !empty($_SESSION['toauth_ats'])) {
		$connection = new tOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['toauth_at'], $_SESSION['toauth_ats']);
		
		$r = $connection->get('statuses/home_timeline');			
		  
		if(isset($r['error'])) {			
			die('<b>Error en lee_ultimos2tweets:</b> '.$r['error']);			
		}else {
			return $r;
		}		
	}			
}


function elimina_acentos($cadena){
	$tofind = "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ";
	$replac = "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn";
	//return(strtr($cadena,$tofind,$replac));
	return utf8_encode((strtr($cadena,utf8_decode($tofind),$replac)));
}

function test (){
	echo "test";
}

?>

