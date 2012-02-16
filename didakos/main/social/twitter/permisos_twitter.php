<?php
ob_start();
//session_start();
require_once('tOAuth/tOAuth.class.php');
$language_file[] = 'redes_sociales';
require ('../../inc/global.inc.php');
require ('funciones_twitter.php');
$tool_name = get_lang("Redessociales") .' - Twitter';
Display :: display_header($tool_name); 

//ruta del callback
$args = array ('oauth_callback'=> $_configuration['root_web'] . 'main/social/twitter/permisos_twitter.php');
print_r ($args);
die();
// Tomamos el user_id del alumno de la sesión
$user_id= $_SESSION["_user"]["user_id"];
$parametros_twitter= Database::get_main_table(TABLE_PARAMETROS_TWITTER); 

if(empty($_SESSION['toauth_at']) || empty($_SESSION['toauth_ats'])) 
	{
	if(!empty($_GET['oauth_token']) && $_SESSION['toauth_state'] == 'start') 
		{

		$connection = new tOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['toauth_rt'], $_SESSION['toauth_rts']);
		$a = $connection->authenticate(false,null,null,$args);
		print_r ($a);
		
		
		$_SESSION['toauth_at'] = $a['oauth_token'];
		$_SESSION['toauth_ats'] = $a['oauth_token_secret'];
		$_SESSION['toauth_state'] = 'returned';

		// Guardamos sus tokens en la bd
		$sql="SELECT * FROM " . $parametros_twitter . " where user_id=".$user_id;
		$res = api_sql_query($sql,__FILE__,__LINE__);
		$row = Database::fetch_array($res);
		if (!isset($row['user_id'])) 
			{  
				$sql ="insert into ". $parametros_twitter ." (user_id,toauth_at,toauth_ats,fecha_operacion) values (".$user_id.",'".$_SESSION['toauth_at']."','".$_SESSION['toauth_ats']."',now())";
				$res = api_sql_query($sql,__FILE__,__LINE__);
			}
	}else {
		$connection = new tOAuth(CONSUMER_KEY, CONSUMER_SECRET);
		$a = $connection->authenticate(true,null,null,$args);
		$_SESSION['toauth_rt'] = $a['oauth_token'];
		$_SESSION['toauth_rts'] = $a['oauth_token_secret'];
		$_SESSION['toauth_state'] = 'start';
		
		echo "<div>".get_lang("TwitterNecesita")."</div>
		<br><center><div><a href=\"{$a['request_link']}\"><img src='../img/twitter.jpg' align='top'></a></div></center>";
	}
}

if(!empty($_SESSION['toauth_at']) && !empty($_SESSION['toauth_ats'])) {
	// 	Ya tenemos los tokens del alumno, se redirige a página para consultar/escribir en muro de Twitter del alumno
		header('Location: index_twitter.php'); 
	//	die();
	//echo 'Permisos obtenidos. Pulse <a href="index_twitter.php" target="_self">aquí</a> para regresar a las opciones relativas a twitter';	
}
ob_end_flush();
?>
