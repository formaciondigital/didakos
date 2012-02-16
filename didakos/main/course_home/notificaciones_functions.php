<?php

/*
Funciones para las notificaciones
*/





function DmailCount($user_id,$fecha)
{
	$dmail_table = Database::get_course_table(TABLE_DMAIL);
	$sql = "select count(*) as total from " . $dmail_table . " where recibe=". $user_id ." and borrado=0 and leido=0 and fecha_envio>'" . $fecha . "'";
	$result =mysql_query($sql); 
	if ($row = mysql_fetch_array($result)) 
	{
		return $row['total'];
	}
	else
	{
		return 0;
	}
}


function AnnouncementCount ($user_id,$fecha)
{
	$item_property = Database :: get_course_table(TABLE_ITEM_PROPERTY);
	//anuncios... En announcement no se guarda hora, mejor miramos en la item_properties.
	$sql = "select count(*) as total from " .$item_property. " where tool='announcement' and lastedit_type='AnnouncementAdded' and visibility=1 and insert_date>'" . $fecha . "'";
	$result =mysql_query($sql); 
	if ($row = mysql_fetch_array($result)) 
	{
		return $row['total'];
	}
	else
	{
		return 0;
	}
}

function ForumCount($user_id,$fecha)
{
	$forum_post = Database :: get_course_table(TABLE_FORUM_POST);
	$sql = "select count(*) as total from " .$forum_post. " where visible=1 and post_date>'" . $fecha . "'";
	$result =mysql_query($sql); 
	if ($row = mysql_fetch_array($result)) 
	{
		return $row['total'];
	}
	else
	{
		return 0;
	}
}

function LinkCount($user_id,$fecha)
{
	$item_property = Database :: get_course_table(TABLE_ITEM_PROPERTY);
	$sql = "select count(*) as total from " .$item_property. " where tool='link' and lastedit_type='LinkAdded' and visibility=1 and insert_date>'" . $fecha . "'";
	$result =mysql_query($sql); 
	if ($row = mysql_fetch_array($result)) 
	{
		return $row['total'];
	}
	else
	{
		return 0;
	}
}

function MultimediaCount($user_id,$fecha)
{
	$multimedia= Database::get_course_table(TABLE_MULTIMEDIA); 
	$multimedia_post= Database::get_course_table(TABLE_MULTIMEDIA_POST); 
	$c=0;

	$sql = "select count(*) as total from " .$multimedia. " where date>'" . $fecha . "'";
	$result =mysql_query($sql); 
	if ($row = mysql_fetch_array($result)) 
	{
		$c = $row['total'];
	}
	else
	{
		$c = 0;
	}
	$sql = "Select count(*) as total from $multimedia m, $multimedia_post p where m.user_id= " . $user_id .
	" and m.id = p.multimedia_id and p.date>'" . $fecha . "'";
	$result =mysql_query($sql); 
	if ($row = mysql_fetch_array($result)) 
	{
		$c += $row['total'];
	}
	else
	{
		$c += 0;
	}

	return $c;
}

function SurveyCount($user_id,$fecha)
{
	$survey= Database::get_course_table(TABLE_SURVEY);
	$sql = "select count(*) as total from " .$survey. " where creation_date>'" . $fecha . "'";
	$result =mysql_query($sql); 
	if ($row = mysql_fetch_array($result)) 
	{
		return $row['total'];
	}
	else
	{
		return 0;
	}
}


function GetDmailList ($user_id)
{
	$dmail_table = Database::get_course_table(TABLE_DMAIL);
	$elemento= array();
	$lista= array();
	$sql = "select id_mail,asunto,envia,recibe,fecha_envio,fecha_lectura,id_carpeta,borrado,leido,importante,contenido,id_adjunto from "
	 . $dmail_table . " where recibe=". $user_id ." and borrado=0 and id_carpeta=1 and leido=0 order by fecha_envio desc LIMIT 5";
	$result = api_sql_query($sql,__FILE__,__LINE__);
	while ($temp_row = Database::fetch_array($result))
	{
		$elemento['id']= $temp_row['id_mail'];
		$elemento['asunto']= $temp_row['asunto'];
		$elemento['envia']= $temp_row['envia'];
		$elemento['fecha_envio']= $temp_row['fecha_envio'];
		$lista [] = $elemento;
	}	
	return $lista;
}

function GetAnnouncementList ($user_id)
{
	$item_property = Database :: get_course_table(TABLE_ITEM_PROPERTY);
	$anuncios_table = Database :: get_course_table(TABLE_ANNOUNCEMENT);
	$elemento= array();
	$lista= array();

	$sql = "Select a.id,a.title,i.insert_date,i.insert_user_id from " . $anuncios_table . "a," .$item_property. " i 
	where a.id=i.ref and i.tool='announcement' and i.lastedit_type='AnnouncementAdded' and i.visibility=1 and end_date>=now() order by i.insert_date desc LIMIT 5";
	$result = api_sql_query($sql,__FILE__,__LINE__);
	while ($temp_row = Database::fetch_array($result))
	{
		$elemento['id']= $temp_row['id'];
		$elemento['title']= $temp_row['title'];
		$elemento['user_id']= $temp_row['insert_user_id'];
		$elemento['fecha_envio']= $temp_row['insert_date'];
		$lista [] = $elemento;
	}	
	return $lista;
}

function GetForumList ($user_id)
{
	$forum_post = Database :: get_course_table(TABLE_FORUM_POST);
	$elemento= array();
	$lista= array();

	$sql = "select post_id,post_title,poster_id,post_date,forum_id,thread_id from " .$forum_post. " where visible=1 order by post_date desc LIMIT 5";
	$result = api_sql_query($sql,__FILE__,__LINE__);
	while ($temp_row = Database::fetch_array($result))
	{
		$elemento['id']= $temp_row['post_id'];
		$elemento['title']= $temp_row['post_title'];
		$elemento['user_id']= $temp_row['poster_id'];
		$elemento['fecha_envio']= $temp_row['post_date'];
		$elemento['forum_id']= $temp_row['forum_id'];
		$elemento['thread_id']= $temp_row['thread_id'];
		$lista [] = $elemento;
	}	
	return $lista;
}

function GetLinkList ($user_id)
{
	$item_property = Database :: get_course_table(TABLE_ITEM_PROPERTY);
	$link_table = Database :: get_course_table(TABLE_LINK);
	$elemento= array();
	$lista= array();
	
	$sql = "Select l.id,l.title,l.url,i.insert_date,i.insert_user_id from " . $link_table . "l," .$item_property. " i 
	where l.id=i.ref and i.tool='link' and i.lastedit_type='LinkAdded' and i.visibility=1 and insert_user_id>1 order by i.insert_date desc LIMIT 5";
		
	$result = api_sql_query($sql,__FILE__,__LINE__);
	while ($temp_row = Database::fetch_array($result))
	{
		$elemento['id']= $temp_row['id'];
		$elemento['title']= $temp_row['title'];
		$elemento['user_id']= $temp_row['insert_user_id'];
		$elemento['fecha_envio']= $temp_row['insert_date'];
		$elemento['url']= $temp_row['url'];
		$lista [] = $elemento;
	}	
	return $lista;
}

function GetMultimediaList ($user_id)
{
	$multimedia= Database::get_course_table(TABLE_MULTIMEDIA); 
	$multimedia_post= Database::get_course_table(TABLE_MULTIMEDIA_POST); 
	$elemento= array();
	$lista= array();

	// Vamos a coger 5 elementos y 5 comentarios a ordenarlos por fecha mezclándolos y a quedarnos solo los 5 primeros.
	$sql = "select id,title,user_id,date from " .$multimedia. " order by date DESC LIMIT 5";
	$result = api_sql_query($sql,__FILE__,__LINE__);
	while ($temp_row = Database::fetch_array($result))
	{
		$elemento['id']= $temp_row['id'];
		$elemento['title']= $temp_row['title'];
		$elemento['user_id']= $temp_row['user_id'];
		$elemento['fecha_envio']= $temp_row['date'];
		//No es un comentario
		$elemento['comentario']= 0;
		$lista [] = $elemento;
	}	


	$sql = "Select m.id,m.title,p.user_id,p.date from $multimedia m, $multimedia_post p where m.user_id= " . $user_id .
	" and m.id = p.multimedia_id order by p.date DESC LIMIT 5";
	$result = api_sql_query($sql,__FILE__,__LINE__);
	while ($temp_row = Database::fetch_array($result))
	{
		$elemento['id']= $temp_row['id'];
		$elemento['title']= $temp_row['title'];
		$elemento['user_id']= $temp_row['user_id'];
		$elemento['fecha_envio']= $temp_row['date'];
		//Es un comentario
		$elemento['comentario']= 1;
		$lista [] = $elemento;
	}	

	usort($lista, 'dateSortDESC'); 


	if (count($lista)>5)
	{
		//recortamos
		array_splice($lista, 5);
	}

	return $lista;
}

function GetSurveyList ($user_id)
{
	$survey= Database::get_course_table(TABLE_SURVEY);
	$elemento= array();
	$lista= array();

	$sql = "select * from " .$survey. "  where avail_from<=now() and avail_till>=now() order by creation_date DESC LIMIT 5";
		
	$result = api_sql_query($sql,__FILE__,__LINE__);
	while ($temp_row = Database::fetch_array($result))
	{
		$elemento['id']= $temp_row['survey_id'];
		$elemento['title']= $temp_row['title'];
		$elemento['user_id']= $temp_row['author'];
		$elemento['fecha_envio']= $temp_row['creation_date'];
		$lista [] = $elemento;
	}	
	return $lista;
}

function GetNotificationUserData ($user_id)
{
	require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');
	$user_data = UserManager::get_user_info_by_id($user_id);
	$nombre = $user_data['firstname'] . ' ' . $user_data['lastname'];
	$link_perfil = '../../main/user/perfil.php?student='.$user_id;
	return array ($nombre,$link_perfil);
}

function dateSortDESC($a, $b) { 
   if($a['fecha_envio'] > $b['fecha_envio']) 
      return -1; 
   if($a['fecha_envio'] < $b['fecha_envio']) 
      return 1; 
   return 0; 
} 

function getTime($fecha)
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

