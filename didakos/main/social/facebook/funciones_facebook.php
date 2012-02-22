<?php

function borrar_tokens_facebook ($user_id)
{	
    global $proxy;
    global $proxyuserpwd;
    global $proxyauth;
    
	//Borramos de la plataforma
	$parametros_facebook= Database::get_main_table(TABLE_PARAMETROS_FACEBOOK); 
	$sql= "delete from ". $parametros_facebook . " where user_id=".$user_id;
	api_sql_query($sql,__FILE__,__LINE__);
	//Borramos de facebook
	$ch = curl_init();
	$opts[CURLOPT_URL] = "https://api.facebook.com/method/auth.revokeAuthorization?access_token=". $_SESSION['foauth_at'] . "&format=json";

	if (isset($_SESSION['proxy']))
	  {
	    $opts[CURLOPT_PROXY] = $_SESSION['proxy'];
	    $opts[CURLOPT_PROXYUSERPWD] = $_SESSION['proxyuserpwd'];
	    $opts[CURLOPT_PROXYAUTH] =$_SESSION['proxyauth'];
	  }
	
	$opts[CURLOPT_RETURNTRANSFER] = true;
	curl_setopt_array($ch, $opts);
	$result = curl_exec($ch);

	unset($_SESSION['foauth_at']);
}

function publica_link_facebook ($titulo,$mensaje,$link,$imagen,$texto)
{
    global $proxy;
    global $proxyuserpwd;
    global $proxyauth;
    
	//Esto publica en formato enlace de facebook
	//$texto por ahora no lo recibimos, no lo veo necesario
	$url = 'https://graph.facebook.com/me/feed';
	$arpost = array(
	'link' => $link,
	'message' => utf8_encode($mensaje),
    	'name' => utf8_encode($titulo),
    	'caption' => '',
    	'description' => '',
	'picture' => $imagen,
    	'access_token' => $_SESSION['foauth_at']);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arpost));
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	if (isset($_SESSION['proxy']))
	  {
	      curl_setopt($ch,CURLOPT_PROXY,$_SESSION['proxy']);
	      curl_setopt($ch,CURLOPT_PROXYUSERPWD,$_SESSION['proxyuserpwd']);	
	      curl_setopt($ch,CURLOPT_PROXYAUTH,$_SESSION['proxyauth']);
	  }                       
	curl_exec($ch);
	curl_close($ch);
}

function publica_mensaje_facebook ($mensaje)
{
    global $proxy;
    global $proxyuserpwd;
    global $proxyauth;
    
	//Esto publica solo un mensaje
	$url = 'https://graph.facebook.com/me/feed';
	$arpost = array(
	'message' => $mensaje,
    	'access_token' => $_SESSION['foauth_at']);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arpost));
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	if (isset($_SESSION['proxy']))
	  {
	      curl_setopt($ch,CURLOPT_PROXY,$_SESSION['proxy']);
	      curl_setopt($ch,CURLOPT_PROXYUSERPWD,$_SESSION['proxyuserpwd']);	
	      curl_setopt($ch,CURLOPT_PROXYAUTH,$_SESSION['proxyauth']);
	  }
	curl_exec($ch);
	curl_close($ch);
}

function lee_ultimos_post($numero)
{		
    global $proxy;
    global $proxyuserpwd;
    global $proxyauth;
    
	if(!empty($_SESSION['foauth_at'])) 
	{
		$objecto = new stdClass();
		$contador=0;

		$ch = curl_init();
		$opts[CURLOPT_URL] = "https://graph.facebook.com/me/home?access_token=". $_SESSION['foauth_at'];
		if (isset($_SESSION['proxy']))
		  {
		    $opts[CURLOPT_PROXY] = $_SESSION['proxy'];
		    $opts[CURLOPT_PROXYUSERPWD] = $_SESSION['proxyuserpwd'];
		    $opts[CURLOPT_PROXYAUTH] =$_SESSION['proxyauth'];
		  }
		$opts[CURLOPT_RETURNTRANSFER] = true;
		curl_setopt_array($ch, $opts);
		$result = curl_exec($ch);
		curl_close($ch);
		$wall_info=@json_decode($result);
		$objeto = $wall_info->data;

		// print_r ($objeto);
		// Vamos a obtener solo las últimos $numero
		foreach ($objeto as $post)
		{
			switch ($post->type)
			{
				case "status":
				$evento[] = $post->from->name;
				$evento[] = "";
				$evento[] = $post->message;
				$evento[] = $post->created_time;
				$evento[] = $post->from->id;
				$evento[] = $post->actions[0]->link;
				$contador= $contador + 1;
				$lista_eventos[] = $evento;
				unset($evento);	
				break;

				case "link":
				$evento[] = $post->from->name;
				$evento[] = $post->name;
				$evento[] = $post->message . " " .$post->description;
				$evento[] = $post->created_time;
				$evento[] = $post->from->id;
				$contador= $contador + 1;
				$evento[] = $post->actions[0]->link;
				$lista_eventos[] = $evento;
				unset($evento);	
				break;
				case "video":
				$evento[] = $post->from->name;
				$evento[] = $post->name;
				$evento[] = $post->message . " " .$post->description;
				$evento[] = $post->created_time;
				$evento[] = $post->from->id;
				$evento[] = $post->actions[0]->link;
				$contador= $contador + 1;
				$lista_eventos[] = $evento;
				unset($evento);	
				break;
				case "photo":
				$evento[] = $post->from->name;
				$evento[] = $post->name;
				$evento[] = $post->message . " [Imagen adjunta]";
				$evento[] = $post->created_time;
				$evento[] = $post->from->id;
				$evento[] = $post->actions[0]->link;
				$contador= $contador + 1;
				$lista_eventos[] = $evento;
				unset($evento);	
				break;
			}

			if ($contador== $numero)
			{
				break;
			}
		}
		return $lista_eventos;
	}
		
}



?>
