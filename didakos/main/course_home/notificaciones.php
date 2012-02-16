<?php

$language_file = array("course_home", "redes_sociales");
include('../../main/inc/global.inc.php');
require ('notificaciones_functions.php');


$tipo = $_POST['tipo'];
$fecha = $_POST['fecha'];


switch ($tipo)
{
	case "dmail":
		$list = GetDmailList (api_get_user_id());
		$texto = get_lang("TehaenviadounDmail");
		$link = "../../main/dmail/lectura.php?dir=recibidos&id=";
		echo '<table class="notificaciones" >';
		if ( count($list)>0 )
		{
			foreach ($list as $element)
			{
				list ($nombre,$link_perfil) = GetNotificationUserData ($element['envia']);
				echo '<tr>';
				if ($element['fecha_envio']>=$fecha)
				{
					$class = "notificacion_resaltada";			//Es un elemento a resltar
				}
				else
				{ 
					$class = "notificacion_normal";				//Es un elemento ya visto o pendiente pero lo mostramos igualmente
				}
				echo '<td class="' . $class . '" width="90%" align="left"><a target="_blank" href="' . $link_perfil . '">' .$nombre . '</a> ' . $texto . '<br>' . getTime($element['fecha_envio']) .'</td>';
				echo '<td  valign="top" width="10%" align="right"><a target="_self" href="' . $link . $element['id'] .'">'. get_lang("Ir").' ></a></td>';
				echo '</tr>';
			}
		}
		else
		{
			echo '<tr><td>Sin notificaciones</td></tr>';
		}
		echo '</table>';
	break;
	case "anuncio":
		$list = GetAnnouncementList (api_get_user_id());
		$texto = get_lang("Haenviadoelanuncio");
		$link = "../../main/announcements/announcements.php?" .api_get_cidreq(). "#" ;
		echo '<table class="notificaciones" >';
		if ( count($list)>0 )
		{
			foreach ($list as $element)
			{
				list ($nombre,$link_perfil) = GetNotificationUserData ($element['user_id']);
				echo '<tr>';
				if ($element['fecha_envio']>=$fecha)
				{
					$class = "notificacion_resaltada";			//Es un elemento a resltar
				}
				else
				{ 
					$class = "notificacion_normal";				//Es un elemento ya visto o pendiente pero lo mostramos igualmente
				}
				echo '<td class="' . $class . '" width="90%" align="left"><a target="_blank" href="' . $link_perfil . '">' .$nombre . '</a> ' . $texto . ' "'. $element['title'] . '"<br>' . getTime($element['fecha_envio']) .'</td>';
				echo '<td  valign="top" width="10%" align="right"><a target="_self" href="' . $link . $element['id'] .'">'. get_lang("Ir").' ></a></td>';
				echo '</tr>';
			}
		}
		else
		{
			echo '<tr><td>Sin notificaciones</td></tr>';
		}
		echo '</table>';
	break;
	case "foro":
		$list = GetForumList (api_get_user_id());
		$texto = get_lang("HaComentadoForo");
		$link = "../../main/forum/viewthread.php?" . api_get_cidreq();
		echo '<table class="notificaciones" >';
		if ( count($list)>0 )
		{
			foreach ($list as $element)
			{
				list ($nombre,$link_perfil) = GetNotificationUserData ($element['user_id']);
				echo '<tr>';
				if ($element['fecha_envio']>=$fecha)
				{
					$class = "notificacion_resaltada";			//Es un elemento a resltar
				}
				else
				{ 
					$class = "notificacion_normal";				//Es un elemento ya visto o pendiente pero lo mostramos igualmente
				}
				echo '<td class="' . $class . '" width="90%" align="left"><a target="_blank" href="' . $link_perfil . '">' .$nombre . '</a> ' . $texto . ' "'. $element['title'] . '"<br>' . getTime($element['fecha_envio']) .'</td>';
				echo '<td  valign="top" width="10%" align="right"><a target="_self" href="' . $link . '&forum='.$element['forum_id']. '&thread=' .$element['thread_id'] .'">'. get_lang("Ir").' ></a></td>';
				echo '</tr>';
			}
		}
		else
		{
			echo '<tr><td>Sin notificaciones</td></tr>';
		}
		echo '</table>';
	break;
	case "enlace":
		$list = GetLinkList (api_get_user_id());
		$texto = get_lang("HaCompartidoEnlace");
		echo '<table class="notificaciones" >';
		if ( count($list)>0 )
		{
			foreach ($list as $element)
			{
				list ($nombre,$link_perfil) = GetNotificationUserData ($element['user_id']);
				echo '<tr>';
				if ($element['fecha_envio']>=$fecha)
				{
					$class = "notificacion_resaltada";			//Es un elemento a resltar
				}
				else
				{ 
					$class = "notificacion_normal";				//Es un elemento ya visto o pendiente pero lo mostramos igualmente
				}
				echo '<td class="' . $class . '" width="90%" align="left"><a target="_blank" href="' . $link_perfil . '">' .$nombre . '</a> ' . $texto . ' "'. $element['title'] . '"<br>' . getTime($element['fecha_envio']) .'</td>';
				echo '<td  valign="top" width="10%" align="right"><a target="_blank" href="' . $element['url'].'">'. get_lang("Ir").' ></a></td>';
				echo '</tr>';
			}
		}
		else
		{
			echo '<tr><td>Sin notificaciones</td></tr>';
		}
		echo '</table>';
	break;
	case "multimedia":
		$list = GetMultimediaList (api_get_user_id());
		$texto_M = get_lang("HaCompartidoMultimedia");
		$texto_C = get_lang("HaComentadoMultimedia");
		$link = "../../main/multimedia/index.php?id=";
		echo '<table class="notificaciones" >';
		if ( count($list)>0 )
		{
			foreach ($list as $element)
			{
				list ($nombre,$link_perfil) = GetNotificationUserData ($element['user_id']);
				echo '<tr>';
				if ($element['fecha_envio']>=$fecha)
				{
					$class = "notificacion_resaltada";			//Es un elemento a resltar
				}
				else
				{ 
					$class = "notificacion_normal";				//Es un elemento ya visto o pendiente pero lo mostramos igualmente
				}
				if ($element['comentario']==0)
				{
					$texto= $texto_M;
				}
				else
				{
					$texto= $texto_C;
				}
				echo '<td class="' . $class . '" width="90%" align="left"><a target="_blank" href="' . $link_perfil . '">' .$nombre . '</a> ' . $texto . '<br>' . getTime($element['fecha_envio']) .'</td>';
				echo '<td  valign="top" width="10%" align="right"><a target="_self" href="' . $link. $element['id'] .'">'. get_lang("Ir").' ></a></td>';
				echo '</tr>';
			}
		}
		else
		{
			echo '<tr><td>Sin notificaciones</td></tr>';
		}
		echo '</table>';
	break;
	case "encuesta":
		$list = GetSurveyList (api_get_user_id());
		$texto = get_lang("HaCreadoEncuesta");
		$link = "../../main/survey/survey_list.php";
		echo '<table class="notificaciones" >';
		if ( count($list)>0 )
		{
			foreach ($list as $element)
			{
				list ($nombre,$link_perfil) = GetNotificationUserData ($element['user_id']);
				echo '<tr>';
				if ($element['fecha_envio']>=$fecha)
				{
					$class = "notificacion_resaltada";			//Es un elemento a resltar
				}
				else
				{ 
					$class = "notificacion_normal";				//Es un elemento ya visto o pendiente pero lo mostramos igualmente
				}
				echo '<td class="' . $class . '" width="90%" align="left"><a target="_blank" href="' . $link_perfil . '">' .$nombre . '</a> ' . $texto . ' <br>' . getTime($element['fecha_envio']) .'</td>';
				echo '<td  valign="top" width="10%" align="right"><a target="_self" href="' . $link.'">'. get_lang("Ir").' ></a></td>';
				echo '</tr>';
			}
		}
		else
		{
			echo '<tr><td>Sin notificaciones</td></tr>';
		}
		echo '</table>';
	break;
}

?>



