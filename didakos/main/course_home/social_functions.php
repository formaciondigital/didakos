<?php

/*
	Recibe un ID y devuelve datos del alumno (Imagen, nombre y status en el curso)
*/


require ('../../main/social/twitter/funciones_twitter.php');
require ('../../main/social/facebook/funciones_facebook.php');


//Variables globales
$tipos = "";
$elementos_ignorados=0;
$max_eventos=0;
$categorias_ignoradas=0;

function GetUserData ($user_id)
{
	require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');
	$user_data = UserManager::get_user_info_by_id($user_id);

	//	IMAGEN
	$image_path = UserManager::get_user_picture_path_by_id($user_data['user_id'],'web');
	$image_dir = $image_path['dir'];
	$image = $image_path['file'];
	$image_file = ($image != '' ? $image_dir.$image : api_get_path(WEB_CODE_PATH).'img/icon_user.png');
	$img_attributes = 'src="'.$image_file.'?rand='.time().'" '
		.'alt="'.$user_data['lastname'].' '.$user_data['firstname'].'" '
		.'style="float:left; padding:5px;"';

	$imagen = '<img '.$img_attributes.' height="60" border="0"/>';
	//	NOMBRE Y APELLIDOS
	$nombre = $user_data['lastname'].', '.$user_data['firstname'];
	
	//	TIPO DE USUARIO (Gestor, Tutor, Alumno)
	if ($user_data['status']=='1')
	{
		if(api_is_platform_admin())
		{$tipo_usuario = 'Gestor';}
		else
		{$tipo_usuario = 'Tutor';}
	}
	else
	{
		if ($user_data['status']=='5')
		{
			$tipo_usuario = 'Alumno';		
		}
		else
		{
			// 4, inspector. con recursos limitados.
			$tipo_usuario = 'Inspector';		
		}
	}

	return array ($imagen,$nombre,$tipo_usuario);
}

function GetTools()
{
	list ($imagen, $nombre, $tipo_usuario) = GetUserData (api_get_user_id());
	$web_code_path = api_get_path(WEB_CODE_PATH);
	$course_tool_table = Database::get_course_table(TABLE_TOOL_LIST);

	switch ($tipo_usuario)
	{
		case "Alumno":
		$result = api_sql_query("SELECT * FROM $course_tool_table WHERE visibility = '1' AND  category != 'admin' ORDER BY category,id",__FILE__,__LINE__);
		break;
		case "Tutor":
		$result = api_sql_query("SELECT * FROM $course_tool_table WHERE name not in ('course_setting','course_maintenance') ORDER BY category,id",__FILE__,__LINE__);
		break;
		case "Gestor":
		$result = api_sql_query("SELECT * FROM $course_tool_table ORDER BY category,id",__FILE__,__LINE__);
		break;
		case "Inspector":
		$result = api_sql_query("SELECT * FROM $course_tool_table WHERE name in ('learnpath','document','quiz','quiz_exam','link','student_publication','Dmail','forum','announcement','calendar_event','chat','tracking') ORDER BY category,id",__FILE__,__LINE__);
		break;
	}
	while ($temp_row = Database::fetch_array($result))
	{
		$all_tools_list[]=$temp_row;
	}

	return $all_tools_list;
}

function GetConfig ()
{
	$config= Database::get_course_table(TABLE_SOCIAL_CONFIG);
	// Leemos la posible configuración
	$sql = "Select * from  $config ";
	$res = api_sql_query($sql,__FILE__,__LINE__);
	$post = Database::fetch_array($res);

	if (isset($post['id']))
	{
		//Tenemos configuracion
		$eventos_por_tipo = $post['elementos'];
		$max_eventos = $post['eventos'];
		if ($post['orden']=='GROUP')
		{
			// La agrupación no podemos insertarla en los SQL
			// Vamos a hacer una ordenación posterior una vez lleno todo el array.
			$orden = 'DESC';
			$post_orden = 'GROUP';
		}
		else
		{
			$orden = $post['orden'];
			$post_orden = '';
		}
		$social = $post['social'];
		$notificaciones = $post['notificaciones'];
		//El orden de guardado de la visibilidad es el mismo que el de las herramientas
		$visibility = preg_split("/,/",$post['visibility']);
	}
	else
	{
		//Configuracion por defecto
		$max_eventos = 50;
		$eventos_por_tipo = 4;
		$orden = 'DESC';
		$social = 1;
		$notificaciones = 1;
		//Uno por cada herramienta existente
		$visibility = preg_split("/,/",'1,1,1,1,1,1,1,1,1,1,1,1');
	}
	


	//Iniciamos tipos de eventos
	//Almacenamos en una variable global $tipos para poder acceder a ella desde 2column.php
	global $tipos;
	$tipo1 = array('0',get_lang('Anuncio'),'valves.gif',$visibility[0]);
	$tipo2 = array('1',get_lang('Blog'),'blog.gif',$visibility[1]);
	$tipo3 = array('2',get_lang('ComentarioBlog'),'blog.gif',$visibility[2]);
	$tipo4 = array('3',get_lang('Agenda'),'agenda.gif',$visibility[3]);
	$tipo5 = array('4',get_lang('Nota'),'quiz.gif',$visibility[4]);
	$tipo6 = array('5',get_lang('Curso'),'scorm.gif',$visibility[5]);
	$tipo7 = array('6',get_lang('Chat'),'chat.gif',$visibility[6]);
	$tipo8 = array('7',get_lang('Dmail'),'dropbox.gif',$visibility[7]);
	$tipo9 = array('8',get_lang('Foro'),'forum.gif',$visibility[8]);
	// $tipo10 = array('9',get_lang('Twitter'),'twitter.gif',1);
	// $tipo11 = array('10',get_lang('Facebook'),'facebook.gif',1);
	$tipo10 = array('9',get_lang('Multimedia'),'multimedia.gif',$visibility[9]);
	$tipo11 = array('10',get_lang('MultimediaComentario'),'multimedia.gif',$visibility[10]);
	$tipo12 = array('11',get_lang('Link'),'link.gif',$visibility[11]);
	$tipos = array($tipo1,$tipo2,$tipo3,$tipo4,$tipo5,$tipo6,$tipo7,$tipo8,$tipo9,$tipo10,$tipo11,$tipo12);

	//Devolvemos toda la configuracion
	return array($eventos_por_tipo,$max_eventos,$orden,$post_orden,$social,$notificaciones,$visibility,$tipos);
}


function GetEventos ()
{	
	// añadimos la variable $tipo para diferenciar contenidos del curso de contenidos.
	// es de tipo texto y tiene tres dos valores posibles "curso" o "social"

	
	//Función que devuelve todos los eventos teniendo en cuenta la configuración del usuario
	$announcement_table = Database::get_course_table(TABLE_ANNOUNCEMENT);
	$blog_post_table = Database::get_course_table(TABLE_BLOGS_POSTS);
	$blog_table = Database::get_course_table(TABLE_BLOGS);
	$blog_comment_table = Database::get_course_table(TABLE_BLOGS_COMMENTS);
	$agenda_table = Database::get_course_table(TABLE_AGENDA);
	$chat = Database::get_main_table(TABLE_FCHAT);
	$dmail_table = Database::get_course_table(TABLE_DMAIL);
	$forum_table = Database::get_course_table(TABLE_FORUM_POST);
	$exercice_table = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
	$quiz_table = Database::get_course_table(TABLE_QUIZ_TEST);
	$course_access_table= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_COURSE_ACCESS);

	$parametros_twitter= Database::get_course_table(TABLE_PARAMETROS_TWITTER); 
	$multimedia_table= Database::get_course_table(TABLE_MULTIMEDIA); 
	$multimedia_post= Database::get_course_table(TABLE_MULTIMEDIA_POST); 
	$link = Database :: get_course_table(TABLE_LINK);
	$link_category = Database :: get_course_table(TABLE_LINK_CATEGORY);
	$item_property = Database :: get_course_table(TABLE_ITEM_PROPERTY);

	//iniciamos variables.
	$indice = 1;
	$id_curso = api_get_course_id();
	$web_code_path = api_get_path(WEB_CODE_PATH);
	$and="";
	
list ($eventos_por_tipo,$max_eventos,$orden,$post_orden,$social,$visibility,$tipos) = GetConfig();

//Vamos a ir seleccionando todos los eventos de las diferentes tablas.
//Acumulamos en un array tipo [x][id, id_tipo,fecha, texto, user_id, enlace]

	//Nombre ########## Anuncios.
	//Privacidad ###### Todos son visibles.
	//Descripcion ##### Muestra los anuncios publicados en la tabla announcements
		if ($tipos[0][3]!='0')
		{
			$sql = "Select title,content,end_date from " . $announcement_table . " where end_date>=now() order by end_date ". $orden . " LIMIT " . $eventos_por_tipo  ;
			$res = api_sql_query($sql,__FILE__,__LINE__);
			while ($post = Database::fetch_array($res))
			{
				$evento[]=$indice;
				$evento[]=0;
				$evento[]=$post["end_date"];
				$evento[]=  substr($post["title"] . '. ' . $post["content"],0,200) . ' ...';
				$evento[]=2;
				$evento[]= $web_code_path."/announcements/announcements.php?cidReq=" . $id_curso;
				$indice = $indice + 1;		
				$lista_eventos[] = $evento;
				unset($evento);
			}
		}

	//Nombre ########## Blog.
	//Privacidad ###### Todos son visibles.
	//Descripcion ##### Muestra los temas publicados en los blogs visibles del curso
		if ($tipos[1][3]!='0')
		{
			$sql = "Select bp.title,bp.full_text,bp.date_creation,bp.blog_id,bp.author_id,b.visibility from " . $blog_post_table . "bp," . $blog_table ." b where bp.blog_id=b.blog_id and b.visibility=1 order by date_creation  ". $orden . " LIMIT " . $eventos_por_tipo  ;
			$res = api_sql_query($sql,__FILE__,__LINE__);
			while ($post = Database::fetch_array($res))
			{
				$evento[]=$indice;
				$evento[]=1;
				$evento[]=$post["date_creation"];
				$evento[]=substr($post["title"] . '. ' . $post["full_text"],0,200) . ' ...';
				$evento[]=$post["author_id"];
				$evento[]= $web_code_path."blog/blog.php?blog_id=" . $post["blog_id"] . "&cidReq=" .  $id_curso;
				$indice = $indice + 1;		
				$lista_eventos[] = $evento;
				unset($evento);
			}
		}

	//Nombre ########## Comentarios Blog.
	//Privacidad ###### Todos son visibles.
	//Descripcion ##### Muestra los comentarios publicados en los blogs visibles del curso
		if ($tipos[2][3]!='0')
		{
			$sql = "Select bc.title,bc.comment,bc.author_id,bc.date_creation,bc.blog_id,bc.post_id from " . $blog_comment_table . " bc, " . $blog_table . " b where b.blog_id =bc.blog_id and b.visibility=1 order by date_creation ". $orden . " LIMIT " . $eventos_por_tipo  ;
			$res = api_sql_query($sql,__FILE__,__LINE__);
			while ($post = Database::fetch_array($res))
			{
				$evento[]=$indice;
				$evento[]=2;
				$evento[]=$post["date_creation"];
				$evento[]=substr($post["title"] . '. ' . $post["comment"],0,200) . ' ...';
				$evento[]=$post["author_id"];
				$evento[]=$web_code_path."blog/blog.php?action=view_post&blog_id=" . $post["blog_id"] .  "&post_id=" . $post["post_id"];
				$indice = $indice + 1;		
				$lista_eventos[] = $evento;
				unset($evento);
			}
		}

	//Nombre ########## Agenda.
	//Privacidad ###### Todos son visibles.
	//Descripcion ##### Muestra los temas publicados en la agenda.
		if ($tipos[3][3]!='0')
		{
			$sql = "Select title,content,start_date,end_date from " . $agenda_table . " where end_date>=now() order by start_date ". $orden . " LIMIT " . $eventos_por_tipo  ;
			$res = api_sql_query($sql,__FILE__,__LINE__);
			while ($post = Database::fetch_array($res))
			{
				$evento[]=$indice;
				$evento[]=3;
				$evento[]=$post["start_date"];
				$evento[]=substr($post["title"] . '. ' . $post["content"],0,200) . ' ...';
				$evento[]=2; //deberia ser el tutor del curso
				$evento[]= $web_code_path."calendar/agenda.php?cidReq=" . $id_curso;
				$indice = $indice + 1;		
				$lista_eventos[] = $evento;
				unset($evento);
			}
		}

	//Nombre ########## Ejercicios y exámenes.
	//Privacidad ###### Los alumnos solo van a ver sus notas, los tutores y gestores ven todas las notas.
	//Descripcion ##### Por simplificar vamos a considerar exámen a aquel que aparezca como activo y ejercicio a aquel que aparezca no activo.
	//################# El enlace va a ser diferente para alumno y tutor. 
		if ($tipos[4][3]!='0')
		{
			if (!api_is_allowed_to_edit() && !api_is_platform_admin())
			{
				$and = " and e.exe_user_id=" .	api_get_user_id() . " ";
			}

			$sql = "Select e.exe_id,e.exe_user_id,e.exe_date,e.exe_cours_id,e.exe_result,e.exe_exo_id,q.id,q.title,q.active from " . $exercice_table . " e, " . $quiz_table . " q where e.exe_cours_id='" . api_get_course_id() . "' and e.exe_exo_id=q.id ".  $and . "order by e.exe_date " . $orden . " LIMIT " . $eventos_por_tipo  ;

			$res = api_sql_query($sql,__FILE__,__LINE__);
			while ($post = Database::fetch_array($res))
			{
				$evento[]=$indice;
				$evento[]=4;
				$evento[]=$post["exe_date"];
		
				if ($post["active"]==0)
				{
					$evento[]= get_lang("HaObtenidoUn") . ' ' . $post["exe_result"]/10 . ' ' . get_lang("EnElExamen") . ' ' . $post["title"];
				}
				else
				{

					$evento[]= get_lang("HaObtenidoUn") . ' ' . $post["exe_result"]/10 . ' ' . get_lang("EnElEjercicio") . ' ' . $post["title"];
				}
				$evento[]=$post["exe_user_id"];
				if (api_is_platform_admin() || api_is_allowed_to_edit())
				{
					$evento[]= $web_code_path."auth/my_progress_details_course.php?student=" . $post["exe_user_id"]. "&details=true&course=" . $id_curso;
				}
				else
				{
					$evento[]= $web_code_path."auth/my_progress_details_course.php?details=true&course=" . $id_curso;
				}
				$indice = $indice + 1;		
				$lista_eventos[] = $evento;
				unset($evento);
			}
		}

	//Nombre ########## Acceso al curso.
	//Privacidad ###### Los alumnos solo van a ver sus accesos, los tutores y gestores ven todos los accesos.
	//Descripcion ##### Muestra los últimos accesos al curso.
		if ($tipos[5][3]!='0')
		{
			$and="";
			if (!api_is_allowed_to_edit() && !api_is_platform_admin())
			{
				$and = " and user_id=" . api_get_user_id() . " ";
			}
			$sql = "Select user_id,login_course_date from " . $course_access_table . " where user_id not in (0,1) and course_code='" . api_get_course_id() . "'" . $and . " order by login_course_date ". $orden . " LIMIT " . $eventos_por_tipo  ;

			$res = api_sql_query($sql,__FILE__,__LINE__);
			while ($post = Database::fetch_array($res))
			{
				$evento[]=$indice;
				$evento[]=5;
				$evento[]=$post["login_course_date"];
				$evento[]= get_lang('ConectadoCurso');/*"Se ha conectado al curso";*/
				$evento[]=$post["user_id"];
				$evento[]= "";
				$indice = $indice + 1;		
				$lista_eventos[] = $evento;
				unset($evento);
			}
		}
	//Nombre ########## Chat.
	//Privacidad ###### Todos ven los accesos.
	//Descripcion ##### Muestra los últimos accesos y desconexiones al chat)
		if ($tipos[6][3]!='0')
		{

		    // Primero revisamos que la tabla del chat haya sido ya creada para evitar errores. No podemos esperar que $res devuelva un error
		    // Porque la paltaforma pinta el error en pantalla directamente.

		    $sql = "select count(*) as total from information_schema.tables where table_schema='" .Database::get_main_database() . "' and table_name='fchat'";
		    $rs = mysql_query($sql,$conn);
		    $row = mysql_fetch_array($rs);
		    
		    if ($row["total"]==1)
		    {
		    
		      $sql = "select leafvalue,timestamp from $chat where server='" . api_get_course_id() . "' and (leafvalue like '%quit%' or leafvalue like '%rejoint%' or leafvalue like '%joins%' or leafvalue like '%se une a%' or leafvalue like '%desconectado%') and subgroup='ch_" . api_get_course_id() ."' order by timestamp " . $orden . " LIMIT " . $eventos_por_tipo ;
		      $res = api_sql_query($sql,__FILE__,__LINE__);

			  while ($post = Database::fetch_array($res))
			  {
				  $datos =  explode("\t", $post["leafvalue"]);
				  // Si es una conexión o una desconexión.
				  if ( count($datos)==5)
				  {
					  $evento[]=$indice;
					  $evento[]=6;
					  $evento[]= date('Y-m-d H:i:s',$datos[1]);
					  if (strstr($datos[4],'rejoint') || strstr($datos[4],'se une a') || strstr($datos[4],'joins'))
					  {
						  $evento[]= get_lang("ConectadoChat"); //'Se ha conectado al chat';
					  }
					  else
					  {
						  if (strstr($datos[4],'quit') || strstr($datos[4],'desconectado'))
						  {
							  $evento[]= get_lang("DesconectadoChat"); //'Se ha conectado al chat';
						  }
					  }
					  $evento[]= GetUserIdByUsername ($datos[2]) ; //username... hay que sacar el id_usuario
					  $evento[]= $web_code_path."fchat/index.php";
					  $indice = $indice + 1;		
					  $lista_eventos[] = $evento;
					  unset($evento);	
				  }
			  }
		    }
		}

	//Nombre ########## Dmail.
	//Privacidad ###### Solo se ven los correos recibidos.
	//Descripcion ##### Muestra los últimos correos no leidos recibidos.
		if ($tipos[7][3]!='0')
		{
			$sql = "Select fecha_envio,asunto,envia from " . $dmail_table . " where recibe=". api_get_user_id() . " and borrado=0 and id_carpeta=1  order by fecha_envio ". $orden . " LIMIT " . $eventos_por_tipo  ;
			$res = api_sql_query($sql,__FILE__,__LINE__);
			while ($post = Database::fetch_array($res))
			{
				$evento[]=$indice;
				$evento[]=7;
				$evento[]=$post["fecha_envio"];
				//Vamos a ver quien es el que lo ha enviado
				$user_data = UserManager::get_user_info_by_id($post["envia"]);
				$evento[]=substr(get_lang("CorreoDe") . ": " .$user_data['firstname'] . ' ' . $user_data['lastname'] . ': ' . $post["asunto"],0,200) . ' ...';
				$evento[]=api_get_user_id();
				$evento[]= $web_code_path."dmail/index.php?dir=recibidos";
				$indice = $indice + 1;		
				$lista_eventos[] = $evento;
				unset($evento);
			}
		}

	//Nombre ########## Foro.
	//Privacidad ###### Visible por todos.
	//Descripcion ##### Muestra los últimos  Temas abiertos y sus respuestas en los foros.

		if ($tipos[8][3]!='0')
		{
			$sql = "Select post_id,post_title,post_text,thread_id,forum_id,poster_id,post_date from " . $forum_table . " order by post_date ". $orden . " LIMIT " . $eventos_por_tipo  ;
			$res = api_sql_query($sql,__FILE__,__LINE__);
			while ($post = Database::fetch_array($res))
			{
				$evento[]=$indice;
				$evento[]=8;
				$evento[]=$post["post_date"];
				$evento[]=strip_tags(substr($post["post_title"] . '. ' . $post["post_text"],0,200)) . ' ...';
				$evento[]=$post["poster_id"];
				$evento[]= $web_code_path."forum/viewthread.php?cidReq=" . $id_curso . "&forum=" . $post["forum_id"] . "&thread=" . $post["thread_id"];
				$indice = $indice + 1;		
				$lista_eventos[] = $evento;
				unset($evento);
			}
		}

	//Nombre ########## Multimedia.
	//Privacidad ###### Visible por todos.
	//Descripcion ##### Muestra los últimos  elementos añadidos.
		if ($tipos[9][3]!='0')
		{
			$sql = "Select * from " . $multimedia_table . " where date is not null order by date ". $orden . " LIMIT " . $eventos_por_tipo ;
			$res = api_sql_query($sql,__FILE__,__LINE__);
			while ($post = Database::fetch_array($res))
			{
				$evento[]=$indice;
				$evento[]=9;
				$evento[]=$post["date"];
				$evento[]=substr(get_lang("CompartidoMultimedia"). ". ".$post["description"],0,200) . ' ...';
				$evento[]=$post["user_id"];
				$evento[]= $web_code_path."multimedia/index.php?cidReq=" . $id_curso . "&id=" .  $post["id"];
				$indice = $indice + 1;		
				$lista_eventos[] = $evento;
				unset($evento);
			}
		}

	//Nombre ########## Comentario Multimedia.
	//Privacidad ###### Visible por el alumno
	//Descripcion ##### Muestra los últimos comentario sobre elementos multimedia propios del alumno.

		if ($tipos[10][3]!='0')
		{
			$sql = "Select m.id,p.date,p.user_id from $multimedia_table m, $multimedia_post p where m.user_id= " . api_get_user_id() .
			" and m.id = p.multimedia_id order by p.date ". $orden . " LIMIT " . $eventos_por_tipo ;
			$res = api_sql_query($sql,__FILE__,__LINE__);
			while ($post = Database::fetch_array($res))
			{
				$evento[]= $indice;
				$evento[]= 10;
				$evento[]= $post["date"];
				$evento[]= get_lang("NewMultimediaComentario");
				$evento[]= $post["user_id"];
				$evento[]= $web_code_path."multimedia/index.php?cidReq=" . $id_curso . "&id=" .  $post["id"];
				$indice = $indice + 1;		
				$lista_eventos[] = $evento;
				unset($evento);
			}
		}

	//Nombre ########## Enlaces.
	//Privacidad ###### Visible por todos.
	//Descripcion ##### Muestra los últimos enlaces añadidos.

		if ($tipos[11][3]!='0')
		{
			$sql = "SELECT distinct(l.id),l.title,l.description,i.insert_user_id,i.visibility,i.insert_date FROM 
			$link l,$link_category c,$item_property i where ((l.category_id= c.id) or (l.category_id=0)) and i.tool='link' and l.id=i.ref and visibility=1 and insert_user_id>1 order by i.insert_date " .$orden . " LIMIT " . $eventos_por_tipo ;

			$res = api_sql_query($sql,__FILE__,__LINE__);
			while ($post = Database::fetch_array($res))
			{
				$evento[]=$indice;
				$evento[]=11;
				$evento[]=$post["insert_date"];
				$evento[]=substr(get_lang("CompartidoEnlace"). ". ". $post["description"],0,200) . ' ...';
				$evento[]=$post["insert_user_id"];
				$evento[]= $web_code_path."link/link.php?cidReq=" . $id_curso . "&urlview=1";
				$indice = $indice + 1;		
				$lista_eventos[] = $evento;
				unset($evento);
			}
		}

/*

// Todo este código es de cuando se mezclaban los feeds de las redes sociales con el muro del alumno
// La variable $tipo_evento se pasaba a la función
if ($tipo_evento=='social')
{
	//Nombre ########## Twitter.
	//Privacidad ###### Solo ve los de su propio twitter.
	//Descripcion ##### Muestra los ultimos mensajes de su twitter
	// En ambos casos se ha impuesto un límite de 10 entradas.
	// Podría ser configurable.

		$user_id= $_SESSION["_user"]["user_id"];
		if (api_get_twitter_tokens($user_id))
		{
			$r=lee_ultimos_tweets();
			for ($i=1;$i<=10;$i++)  {
				$evento[]=$indice;
				$evento[]=9;
				//formateamos la fecha del twitter			
				$created_at = new DateTime ($r[$i-1]['created_at']);
				//Hay que mirar como hacerlo referente al horario del usuario
				$created_at->modify('+2 hour');
				$created_at = $created_at->format("Y-m-d H:i:s"); 
				$evento[]= $created_at;
				$evento[]= lookurl(utf8_decode($r[$i-1]['text']));
				$evento[]=utf8_decode($r[$i-1]['user']['name']) ."|". $r[$i-1]['user']['profile_image_url'];
				$evento[]= "http://www.twitter.com/" . $r[$i-1]['user']['screen_name'];
				$indice = $indice + 1;		
				$lista_eventos[] = $evento;
				unset($evento);				  	
			}
		}

	//Nombre ########## Facebook.
	//Privacidad ###### Solo ve los de su propio facebook.
	//Descripcion ##### Muestra los ultimos mensajes de facebook.
		if ( (api_get_facebook_tokens($user_id)))
		{
			$r= lee_ultimos_post(10);
			foreach ( $r as $post)
			{
				$evento[]=$indice;
				$evento[]=10;
				//formateamos la fecha del twitter			
				$created_at = new DateTime ($post[3]);
				//Hay que mirar como hacerlo referente al horario del usuario
				$created_at->modify('+2 hour');
				$created_at = $created_at->format("Y-m-d H:i:s"); 
				$evento[]= $created_at;
				if ($post[1]=="")
				{
					$evento[]=utf8_decode(substr($post[2],0,150) . ' ...');			
				}
				else
				{
					$evento[]=utf8_decode($post[1] . ".<br> " . substr($post[2],0,150) . ' ...');
				}
				$evento[]=utf8_decode($post[0]) . "|" . "http://graph.facebook.com/" . $post[4] . "/picture";
				$evento[]= $post[5];
				$indice = $indice + 1;		
				$lista_eventos[] = $evento;
				unset($evento);	
			}
		}
}
*/
	//Ordenamos por fecha (de mas nuevo a mas antiguo)

	if ($post_orden == 'GROUP')
	{
		// el método de ordenación es por grupo por lo que no hacemos ordenación alguna
		// el orden es el mismo que el de "llenado"
	}
	else
	{
		if ($orden=='DESC')
		{
			usort($lista_eventos, 'sortbydateDESC'); 
		}
		else
		{
			usort($lista_eventos, 'sortbydateASC'); 	
		}
	}

	return $lista_eventos;

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

function sortbydateDESC($a, $b) { 
   if($a[2] > $b[2]) 
      return -1; 
   if($a[2] < $b[2]) 
      return 1; 
   return 0; 
} 

function sortbydateASC($a, $b) { 
   if($a[2] < $b[2]) 
      return -1; 
   if($a[2] > $b[2]) 
      return 1; 
   return 0; 
} 

function GetLpItems ()
{

$lp_item = Database::get_course_table(TABLE_LP_ITEM);
$lp_item_view = Database::get_course_table(TABLE_LP_ITEM_VIEW);
$lp_view = Database::get_course_table(TABLE_LP_VIEW);
$user = Database::get_main_table(TABLE_MAIN_USER);
$course = Database::get_main_table(TABLE_MAIN_COURSE);
$course_rel_user_fd = Database::get_main_table(TABLE_MAIN_COURSE_USER_FD);

$sql = "select lp_id,id,title,item_type from " .$lp_item. " where item_type in ('sco','asset','document') order by display_order";
$result = api_sql_query($sql,__FILE__,__LINE__);
while ($temp_row = Database::fetch_array($result))
	{
		$sql = "select truncate (iv.score,0) as score, iv.status as status
		from " . $lp_view . "v," . $lp_item_view . " iv, " .$user. " u 
		where iv.lp_view_id = v.id and 
		v.user_id = u.user_id and 
		iv.lp_item_id = " . $temp_row['id']. "
		and u.user_id=" . api_get_user_id();	
		$res = api_sql_query($sql,__FILE__,__LINE__);
		$obj = Database::fetch_object($res);
                
                if (($temp_row["item_type"]=='asset' || $temp_row["item_type"]=='document') &&  $temp_row["status"]=='completed')
                {
                    $obj->score='100';
                }
                
		if ($obj->score=="") {$obj->score='0';}
                // if (($obj->item_type=="asset" || $obj->item_type=="document") && 
		$temp_row['score']= $obj->score;
		$scorm_line []= $temp_row;
	}
	return $scorm_line;
}

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

function GetQuizs ($db)
{
$db = $db.api_get_course_id();
$lp_item = Database::get_course_table(TABLE_LP_ITEM);
$table_exam = Database::get_course_table('quiz_exam', $db);
$table_quiz = Database::get_course_table('quiz', $db);
$exercices = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);

$sql = "select q.id,q.title from $table_quiz q , $table_exam qe where q.id=qe.id order by q.id";
$result = api_sql_query($sql,__FILE__,__LINE__);
	while ($temp_row = Database::fetch_array($result))
	{
		$sql = "select exe_result as score from ". $exercices ." where exe_cours_id='" . api_get_course_id() . "' and exe_user_id=" . api_get_user_id(). " and exe_exo_id= " . $temp_row['id'] . " order by exe_id desc";
		$res = api_sql_query($sql,__FILE__,__LINE__);
		$obj = Database::fetch_object($res);
		$temp_row['score']= $obj->score;
		$scorm_line []= $temp_row;
	}
	return $scorm_line;
}

function GetUserIdByUsername ($Username)
{
	$user = Database::get_main_table(TABLE_MAIN_USER);
	$sql = "select user_id from " . $user . " where username='". $Username . "'";
	$result =mysql_query($sql); 
	if ($row = mysql_fetch_array($result)) 
	{
		return ($row['user_id']);
	}
}

function GetLastElementOrder()
{
		$table_document = Database::get_course_table(TABLE_MULTIMEDIA);
        //Obtenemos ahora el orden
	    $sql = "select max(orden) as orden from " . $table_document;
    	$res = api_sql_query($sql, __FILE__, __LINE__);
	    $element = Database::fetch_row($res);
	    //Actualizamos el orden
	    return $element[0]+1;
}	

?>
