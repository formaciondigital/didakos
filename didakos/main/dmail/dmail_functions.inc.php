<?php

$t_dmail_user = Database::get_main_table(TABLE_MAIN_USER);
$t_dmail_matriculaciones = Database::get_main_table(TABLE_MAIN_MATRICULACIONES);
$t_dmail_course_rel_user = Database::get_main_table(TABLE_MAIN_COURSE_USER);

$t_dmail_main = Database::get_course_table(TABLE_DMAIL);
$t_dmail_carpetas = Database::get_course_table(TABLE_DMAIL_CARPETAS);
$t_dmail_adjuntos = Database::get_course_table(TABLE_DMAIL_ADJUNTOS);


	function Existecarpeta ($usuario,$carpeta)
	{
		global $t_dmail_carpetas;
		$listado = array ();
		$sql = "SELECT count(*) as existe from $t_dmail_carpetas where propietario=$usuario and nombre='$carpeta'";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$obj = Database::fetch_object($res);
		return $obj->existe;
	}

	function CuentaDmailCarpeta ($usuario,$id_carpeta,$buscar)
	{
		if ($buscar!=null)
		{ $buscar = " and asunto like '%$buscar%'";}
		global $t_dmail_main;
		switch ($id_carpeta)
		{
		case '0':
			// Por exigencias del diseño de la aplicación solo se pueden ver los correos eliminados en los que somos receptores.
			$sql = "select count(*) as total from $t_dmail_main where (recibe=$usuario) and borrado=true and id_carpeta=1 $buscar";
		break;
		case '1':
			$sql = "select count(*) as total from $t_dmail_main where recibe=$usuario and id_carpeta=$id_carpeta and borrado=false $buscar";
		break;
		case '2':
			$sql = "select count(*) as total from $t_dmail_main where envia=$usuario and id_carpeta=$id_carpeta and borrado=false $buscar";
		break;
		case '3':
			$sql = "select count(*) as total  from $t_dmail_main where envia=$usuario and id_carpeta=$id_carpeta and borrado=false $buscar";
		break;
		case '4':
			$sql = "select count(*) as total  from $t_dmail_main where recibe=$usuario and importante=1 and borrado=false $buscar";
		break;
		case 'eliminados':
			$sql = "select count(*) as total from $t_dmail_main where (recibe=$usuario) and borrado=true and id_carpeta=1 $buscar";
		break;
		case 'recibidos':
			$sql = "select count(*) as total from $t_dmail_main where recibe=$usuario and id_carpeta=1 and borrado=false $buscar";
		break;
		case 'enviados':
			$sql = "select count(*) as total from $t_dmail_main where envia=$usuario and id_carpeta=2 and borrado=false $buscar";
		break;
		case 'borradores':
			$sql = "select count(*) as total  from $t_dmail_main where envia=$usuario and id_carpeta=3 and borrado=false $buscar";
		break;
		case 'destacados':
			$sql = "select count(*) as total  from $t_dmail_main where recibe=$usuario and importante=1 and borrado=false $buscar";
		break;
		default:
			$sql = "select count(*) as total from $t_dmail_main where recibe=$usuario and id_carpeta=$id_carpeta and borrado=false $buscar";
		break;
		}

		$res = api_sql_query($sql, __FILE__, __LINE__);
		$obj = Database::fetch_object($res);
		return $obj->total;
	}
	
	function  Crearcarpeta ($usuario,$nombre,$bloqueo)
	{
		global $t_dmail_carpetas;
		$sql = "insert into $t_dmail_carpetas (propietario,nombre,bloqueada) values ($usuario,'$nombre',$bloqueo)";
		api_sql_query($sql,__FILE__,__LINE__);
	}

	function Carpetaspersonales ($usuario)
	{
		global $t_dmail_carpetas;
		$listado = array ();
		$sql = "SELECT id_carpeta,nombre,propietario,bloqueada from $t_dmail_carpetas where propietario=$usuario and bloqueada=0 order by nombre,id_carpeta";
		$res = api_sql_query($sql, __FILE__, __LINE__);

			while ($carpeta = Database::fetch_array($res))
			{
				$listado [] = $carpeta;
			}
		return $listado;
	}

	function Nombrecarpeta ($usuario,$id_carpeta)
	{
		global $t_dmail_carpetas;
		$listado = array ();
		$sql = "SELECT nombre as nombre from $t_dmail_carpetas where propietario=$usuario and bloqueada=0 and id_carpeta=$id_carpeta";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$obj = Database::fetch_object($res);
		return $obj->nombre;
	}

	function Modificarcarpeta  ($id_carpeta,$usuario,$nombre)
	{
		global $t_dmail_carpetas;
		$sql = "update $t_dmail_carpetas set nombre='$nombre' where propietario=$usuario and id_carpeta=$id_carpeta";	
		api_sql_query($sql,__FILE__,__LINE__);
	}

	function Eliminarcarpeta  ($usuario, $id_carpeta)
	{
		global $t_dmail_carpetas;
		global $t_dmail_main;

		//Movemos los mensajes de la carpeta a Recibidos
		$sql = "update $t_dmail_main set id_carpeta=1 where id_carpeta=$id_carpeta";
		api_sql_query($sql,__FILE__,__LINE__);

		//Eliminamos carpeta
		$sql = "delete from $t_dmail_carpetas where propietario=$usuario and id_carpeta=$id_carpeta";
		api_sql_query($sql,__FILE__,__LINE__);
	}

	function Usuariosagenda ($curso)
	{
		global $t_dmail_user;
		global $t_dmail_matriculaciones;
		global $t_dmail_course_rel_user;

		$listado = array ();

        //Añadimos el o los tutores
		$sql = "Select u.user_id,u.firstname,u.lastname,u.status from $t_dmail_user u, $t_dmail_course_rel_user cru where u.user_id = cru.user_id and cru.course_code='$curso' and u.status=1 and u.user_id not in (1,2)";
		$res = api_sql_query($sql, __FILE__, __LINE__);
			
			while ($usuario = Database::fetch_array($res))
			{
				$listado [] = $usuario;
			}
			
		//Añadimos los alumnos
		$sql = "SELECT u.user_id,u.firstname,u.lastname,u.status from $t_dmail_user u, $t_dmail_matriculaciones m where u.user_id=m.user_id and u.status=5 and u.user_id not in (1,2) and m.course_code='$curso' order by u.lastname,u.firstname";
		$res = api_sql_query($sql, __FILE__, __LINE__);
			while ($usuario = Database::fetch_array($res))
			{
				$listado [] = $usuario;
			}
		

		return $listado;
	}

	function EnviaDmail ($emisor,$destinatarios,$asunto,$contenido,$data,$size,$tipo,$nombre,$id_adjunto=null)
	{
		global $t_dmail_main;
		global $t_dmail_adjuntos;

		$usuarios = explode(",",$destinatarios);
		$date = getdate();
		$fecha = $date['year'] . '-' . $date['mon'] . '-' . $date['mday'] . ' ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];

		if ($data!='')
		{
			//insertamos el archivo adjunto en la tabla correspondiente
			$sql = "insert into $t_dmail_adjuntos (archivo,size,tipo,nombre) values ('$data',$size,'$tipo','$nombre')";
			api_sql_query($sql,__FILE__,__LINE__);
			//obtenemos el id_adjunto recien importado
			$sql = "select max(id_adjunto) as id_adjunto from $t_dmail_adjuntos";
			$res = api_sql_query($sql, __FILE__, __LINE__);
			$obj = Database::fetch_object($res);
			$id_adjunto = $obj->id_adjunto;
		}
		
		foreach ($usuarios as $usuario)
		{
			//insertamos en la carpeta de recibidos del receptor
			$sql = "insert into $t_dmail_main (asunto,envia,recibe,fecha_envio,id_carpeta,contenido,leido,id_adjunto) values ('". str_replace("'","''",$asunto) . "', $emisor, $usuario,'$fecha',1,'". str_replace("'","''",$contenido) . "',0,$id_adjunto)";
			api_sql_query($sql,__FILE__,__LINE__);
			//insertamos en la carpeta de enviados del emisor
			$sql = "insert into $t_dmail_main (asunto,envia,recibe,fecha_envio,id_carpeta,contenido,leido,id_adjunto) values ('". str_replace("'","''",$asunto) . "', $emisor, $usuario,'$fecha',2,'". str_replace("'","''",$contenido) . "',1,$id_adjunto)";			
			api_sql_query($sql,__FILE__,__LINE__);
		}
	}

	function GuardaBorrador ($emisor,$asunto,$contenido)
	{
		global $t_dmail_main;
		$date = getdate();
		$fecha = $date['year'] . '-' . $date['mon'] . '-' . $date['mday'] . ' ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
		$sql = "insert into $t_dmail_main (asunto,envia,recibe,fecha_envio,id_carpeta,contenido) values ('". str_replace("'","''",$asunto) . "', $emisor, 0,'$fecha',3,'". str_replace("'","''",$contenido) . "')";
		api_sql_query($sql,__FILE__,__LINE__);
	}

	function ActualizaFavorito ($dmail,$nuevoestado)
	{
		global $t_dmail_main;
		$sql = "update $t_dmail_main set importante=" . $nuevoestado ." where id_mail=" . $dmail;
		api_sql_query($sql,__FILE__,__LINE__);
	}

	function CrearListadoDmail ($usuario, $carpeta, $inicio=0, $mailsporpagina=10, $orden, $direccion, $buscar)
	{
		//if ($orden=="" )
		//	{$orden='fecha_envio desc';}
		if ($buscar!=null)
			{ $buscar = " and dm.asunto like '%$buscar%'";}

		global $t_dmail_main;
		global $t_dmail_user;
		switch ($carpeta)
			{
				case 'eliminados':
				$id_carpeta=0;
				break;
				case 'recibidos':
				$id_carpeta=1;
				break;
				case 'enviados':
				$id_carpeta=2;
				break;
				case 'borradores':
				$id_carpeta=3;
				break;			
				case 'destacados':
				$id_carpeta=4;
				break;
				default:
				$id_carpeta=$carpeta;
				break;
			}

		$listado = array ();

		switch ($id_carpeta)
		{
		case 0:
			// Por exigencias del diseño de la aplicación solo se pueden ver los correos eliminados en los que somos receptores.
			$sql = "select u.firstname as firstname1,u.lastname as lastname1, u2.firstname as firstname2,u2.lastname as lastname2,dm.* from $t_dmail_main dm,$t_dmail_user u,$t_dmail_user u2 
			where id_carpeta=1 and dm.recibe=$usuario and dm.borrado=true and dm.envia=u.user_id and dm.recibe=u2.user_id $buscar order by $orden $direccion LIMIT " . $inicio . "," . $mailsporpagina; 
			break;
		case 1:
			$sql = "select u.firstname,u.lastname,dm.* from $t_dmail_main dm,$t_dmail_user u " .
			"where recibe=$usuario and id_carpeta=$id_carpeta and borrado=0 and dm.envia=u.user_id $buscar order by $orden $direccion LIMIT " . $inicio . "," . $mailsporpagina;
			break;
		case 2:
			$sql = "select u.firstname,u.lastname,dm.* from $t_dmail_main dm,$t_dmail_user u " .
			"where envia=$usuario and id_carpeta=$id_carpeta and borrado=0 and dm.recibe=u.user_id $buscar order by $orden $direccion LIMIT " . $inicio . "," . $mailsporpagina;
			break;
		case 3:
			$sql = "select * from $t_dmail_main where id_carpeta=$id_carpeta and envia=$usuario and borrado=0 $buscar order by $orden $direccion LIMIT " . $inicio . "," . $mailsporpagina;
			break;
		case 4:
			$sql = "select u.firstname,u.lastname,dm.* from $t_dmail_main dm,$t_dmail_user u " .
			"where recibe=$usuario and importante=1 and borrado=0 and dm.envia=u.user_id $buscar order by $orden $direccion LIMIT " . $inicio . "," . $mailsporpagina;
			break;
		default:
			$sql = "select u.firstname,u.lastname,dm.* from $t_dmail_main dm,$t_dmail_user u " .
			"where recibe=$usuario and id_carpeta=$id_carpeta and borrado=0 and dm.envia=u.user_id $buscar order by $orden $direccion LIMIT " . $inicio . "," . $mailsporpagina;
		}
		
		$res = api_sql_query($sql, __FILE__, __LINE__);

		while ($mail = Database::fetch_array($res))
		{
			$listado [] = $mail;
		}
		return $listado;
	}

	function EliminarDmail ($id_mail)
	{
		global $t_dmail_main;
		$sql = "update $t_dmail_main set borrado=true where id_mail in ($id_mail)";	
		api_sql_query($sql,__FILE__,__LINE__);
	}
	
	function RecuperarDmail ($id_mail)
	{
		global $t_dmail_main;
		$sql = "update $t_dmail_main set borrado=false where id_mail in ($id_mail)";	
		api_sql_query($sql,__FILE__,__LINE__);
	}

	function MueveDmail ($id_mail,$carpeta_origen,$carpeta_destino,$usuario)
	{
		global $t_dmail_main;
		global $t_dmail_user;
		$sql = "update $t_dmail_main dm,$t_dmail_user u set id_carpeta=$carpeta_destino where id_mail in ($id_mail) and id_carpeta=$carpeta_origen and u.user_id=$usuario";	
		api_sql_query($sql,__FILE__,__LINE__);
	}

	function ListaMover ($id_mail,$usuario)
	{
		global $t_dmail_main;
		$listado = array ();
		$sql = "select * from $t_dmail_main where id_mail in ($id_mail) and (envia=$usuario or recibe=$usuario) order by id_mail";
		 $res = api_sql_query($sql, __FILE__, __LINE__);
			while ($dmail = Database::fetch_array($res))
			{
				$listado [] = $dmail;
			}
		return $listado;
	}

	function DmailRead ($id_mail,$usuario,$carpeta)	
	{
		global $t_dmail_main;
		global $t_dmail_user;
		global $t_dmail_adjuntos;

		$listado = array ();
		switch ($carpeta)
			{
				case 'borradores':
					//Los borradores no llevan adjuntos y no tienen destinatario
					$sql = "select dm.envia, u.firstname as firstname1 ,u.lastname as lastname1, dm.* from $t_dmail_main dm, $t_dmail_user u " .
					"where envia=$usuario and id_mail=$id_mail and dm.envia=u.user_id ";
				break;
				default:
					$sql = "select dm.envia, u.firstname as firstname1 ,u.lastname as lastname1, dm.recibe, u2.firstname as firstname2,u2.lastname as lastname2,dm.*, da.size, da.nombre, da.tipo from ($t_dmail_main dm,$t_dmail_user u, $t_dmail_user u2 )" .
					"left join $t_dmail_adjuntos da on (dm.id_adjunto=da.id_adjunto) where (envia=$usuario or recibe=$usuario) and id_mail=$id_mail and dm.envia=u.user_id and dm.recibe=u2.user_id";
				break;
			}
		
		$res = api_sql_query($sql, __FILE__, __LINE__);
		while ($dmail = Database::fetch_array($res))
			{
				$listado [] = $dmail;
			}
		return $listado;	
	}

	function MarcarLeido ($id_mail,$usuario)
	{
		global $t_dmail_main;
		$date = getdate();
		$fecha = $date['year'] . '-' . $date['mon'] . '-' . $date['mday'] . ' ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
		$sql = "update $t_dmail_main set leido=true, fecha_lectura='$fecha' where id_mail=$id_mail and leido=false and (recibe=$usuario or recibe=0)"; 
		api_sql_query($sql,__FILE__,__LINE__);
	}

	function RevisarPermisoArchivo ($id_adjunto,$usuario)
	{
		global $t_dmail_main;

		$sql = "select count(*) as total from $t_dmail_main where (envia=$usuario or recibe=$usuario) and id_adjunto=$id_adjunto";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$obj = Database::fetch_object($res);
		return $obj->total;
	}

	function LeerAdjunto ($id_adjunto)
	{
		global $t_dmail_adjuntos;

		$sql = "select * from $t_dmail_adjuntos where id_adjunto=$id_adjunto";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$obj = Database::fetch_object($res);
		return $obj;
	}

	function LeidoDmail ($id_mail, $estado)
	{
		global $t_dmail_main;
		if ($estado==1)
		{
		    $date = getdate();
		    $fecha = $date['year'] . '-' . $date['mon'] . '-' . $date['mday'] . ' ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
		    $sql ="Update $t_dmail_main set leido=1, fecha_lectura='$fecha' where id_mail in ($id_mail)";
		}
		else
		{
		    $sql ="Update $t_dmail_main set leido=0, fecha_lectura='NULL' where id_mail in ($id_mail)";
		}
		api_sql_query($sql,__FILE__,__LINE__);
	}
?>
