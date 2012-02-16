<?php // $Id: template.php,v 1.2 2006/03/15 14:34:45 pcool Exp $
/*
==============================================================================
	Dokeos - elearning and course management software
	
	Copyright (c) 2004-2006 Dokeos S.A.
	Copyright (c) Sally "Example" Programmer (sally@somewhere.net)
	//add your name + the name of your organisation - if any - to this list
	
	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".
	
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	See the GNU General Public License for more details.
	
	Contact address: Dokeos, 44 rue des palais, B-1030 Brussels, Belgium
	Mail: info@dokeos.com
==============================================================================
*/
/**
==============================================================================
*	This file is a code template; 
*	copy the code and paste it in a new file to begin your own work.
*
*	@package dokeos.plugin
==============================================================================


documentacion relativa a la herramienta en:
http://wikitec.grupogdt.com/general/Multimedia

*/
	
/*
==============================================================================
		INIT SECTION
==============================================================================
*/ 
// global settings initialisation 
// also provides access to main, database and display API libraries
$language_file[] = 'document';
$language_file[] = 'slideshow';
$language_file[] = 'redes_sociales';
$language_file[] = 'multimedia';

include("../inc/global.inc.php"); 
include("funciones.php");

require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

$dbl_click_id = 0; // used to avoid double-click
$is_allowed_to_edit = api_is_allowed_to_edit();
$curdirpathurl = 'multimedia';
api_protect_course_script();
$web_code_path = api_get_path(WEB_CODE_PATH);


/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/ 
//the main_api.lib.php, database.lib.php and display.lib.php
//libraries are included by default

	
/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/ 

$tool_name = get_lang('Multimedia'); // title of the page (should come from the language file) 
Display::display_header($tool_name);
api_display_tool_title($tool_name);
$interbreadcrumb[]= array ('url'=>'', 'name'=> 'Multimedia');
$dir_array=explode("/",$curdirpath);
$array_len=count($dir_array);

	
/*
==============================================================================
		FUNCTIONS
==============================================================================
*/ 

/**
 * Get the users to display on the current page.
 * @see SortableTable#get_table_data($from)
 */


/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 
?>
<style> 
div.video{
	float:top;
	padding-top: 5px;
	padding-bottom: 5px;
	background:#ffffff;
	width: 100%;
}

div.player{
padding-left: 10px;
float:top;
}

div.comentario{
	padding-top: 5px;
	padding-bottom: 5px;
	background:#efefef;
	width: 50%;
	float:left; 
}

    div.sp_foto{
	    float:left;
	    width: 20%;
	    padding-top: 15px;
	    padding-bottom: 15px;
	
    }
    div.sp_lineas{
	    float:left;
	    width: 70%;
    }
    div.herramientas{
	    float:right;
    }
    div.linea1 {
	    font-family:Verdana, Arial, Helvetica, sans-serif;
	    font-size:12px;
	    padding-top: 5px;
	    padding-right: 5px;
	    padding-bottom: 5px;
	    color: #000000;
    }
    div.linea2 {
	    font-family:Verdana, Arial, Helvetica, sans-serif;
	    font-size:12px;
	    padding-top: 5px;
	    padding-right: 5px;
	    padding-bottom: 5px;
	    color: #6d6d6d;
    }
    div.linea3 {
	    font-family:Verdana, Arial, Helvetica, sans-serif;
	    font-size:12px;
	    padding-top: 5px;
	    padding-right: 5px;
	    padding-bottom: 5px;
	    color: #000000;
	    float:right;
    }
    div.linea {
	    float:left;
	    width: 90%;
	    border-top: 1px dashed #000000;
}

div.c_foto{
	    float:left;
	    width: 20%;
	    padding-top: 15px;
	    padding-bottom: 15px;
	
    }
    div.c_lineas{
	    float:left;
	    width: 60%;
    }
    div.c_linea1 {
	    font-family:Verdana, Arial, Helvetica, sans-serif;
	    font-size:12px;
	    padding-top: 5px;
	    padding-right: 5px;
	    padding-bottom: 5px;
	    color: #000000;
    }
    div.c_linea2 {
	    font-family:Verdana, Arial, Helvetica, sans-serif;
	    font-size:12px;
	    padding-top: 5px;
	    padding-right: 5px;
	    padding-bottom: 5px;
	    color: #6d6d6d;
    }
    div.c_linea {
	    float:left;
	    width: 50%;
	    border-top: 1px dashed #000000;
}
</style>
<script language="javascript">
function Validar()
{
	texto = this.comentar.texto.value;
	if (texto=='')
	{
		alert ('<?php echo get_lang("ComentarioVacio")?>');
		this.comentar.texto.focus();
		return 0;
	}
	else
	{
		if (texto.length > 250)
		{
			alert ('<?php echo get_lang("ComentarioLargo")?>');
			this.comentar.texto.focus();
			return 0;
		}
		else
		{
			this.comentar.submit();
		}
	}
}
function creaAjax(){
         var objetoAjax=false;
         try {
          /*Para navegadores distintos a internet explorer*/
          objetoAjax = new ActiveXObject("Msxml2.XMLHTTP");
         } catch (e) {
          try {
                   /*Para explorer*/
                   objetoAjax = new ActiveXObject("Microsoft.XMLHTTP");
                   }
                   catch (E) {
                   objetoAjax = false;
          }
         }

         if (!objetoAjax && typeof XMLHttpRequest!='undefined') {
          objetoAjax = new XMLHttpRequest();
         }
         return objetoAjax;
}

function FAjax (red,tipo,var1,var2,var3,var4,var5)
{
	// Ajax para publicar en redes sociales.
	// red indica la red social, tipo indica el tipo de mensaje a publicar (Si hubiera)
	// var1 a var5 variables para poder pasar datos a la función 

        var ajax=creaAjax();
	switch(red)
	{
		case 'facebook':
		ajax.open("GET", "../../main/social/facebook/publicar_facebook.php?tipo="+tipo+"&var1="+var1+"&var2="+var2+"&var3="+var3+"&var4="+var4+"&var5="+var5);
		break;
		case 'twitter':
		ajax.open("GET", "../../main/social/twitter/publicar_twitter.php?tipo="+tipo+"&var1="+var1+"&var2="+var2+"&var3="+var3+"&var4="+var4+"&var5="+var5);
  		break;
	}

        ajax.onreadystatechange=function() {
               if (ajax.readyState==4) {
		alert ("Publicado correctamente");		
               }
        }         
	ajax.send(null);

}
</script>
<script src="flowplayer-3.2.6.min.js"></script>
<?php
if ($_GET['action'] == "delete")
	{
		$id = $_GET['id'];
		$res = delete_multimedia($id);
		if ($res)
		{
			Display::display_confirmation_message(get_lang("Eliminado"));	
		}
		else
		{
		  Display::display_warning_message(get_lang("EliminadoProblema"));
		}

	}

//Movemos en el orden
$multimedia_table = Database::get_course_table(TABLE_MULTIMEDIA);	
$orden = $_GET['orden'];

if ($_GET['action']== "moveup")
{
    $sql = "update " . $multimedia_table . " set orden = 0 where orden=" . $orden;
   	$res = api_sql_query($sql, __FILE__, __LINE__);
    $sql = "update " . $multimedia_table . " set orden = " . ($orden) ." where orden=" . ($orden-1);
   	$res = api_sql_query($sql, __FILE__, __LINE__);
   	$sql = "update " . $multimedia_table . " set orden = " . ($orden - 1). " where orden=0";
   	$res = api_sql_query($sql, __FILE__, __LINE__);
}
if ($_GET['action']== "movedown")
{
    $sql = "update " . $multimedia_table . " set orden = 0 where orden=" . $orden;
   	$res = api_sql_query($sql, __FILE__, __LINE__);
    $sql = "update " . $multimedia_table . " set orden = " . ($orden) ." where orden=" . ($orden+1);
   	$res = api_sql_query($sql, __FILE__, __LINE__);
   	$sql = "update " . $multimedia_table . " set orden = " . ($orden + 1). " where orden=0";
   	$res = api_sql_query($sql, __FILE__, __LINE__);
}

//recibimos por post un comentario
if (isset($_POST['texto']))
{
	// insertamos valores
	// Eliminamos comillas y html
	// Eliminamos comilla simple
	$texto = str_replace("\""," ", strip_tags ($_POST['texto']));
	$texto = str_replace("'"," ",$texto);

	$table_post = Database::get_course_table(TABLE_MULTIMEDIA_POST);
	$sql = "INSERT into " . $table_post . " (user_id,multimedia_id,post,date) 
	values (" . api_get_user_id() . "," . $_GET['id'] . ",'". $texto."',now())";
	$res = api_sql_query($sql, __FILE__, __LINE__);

	if ($res==1)
	{
		Display::display_confirmation_message(get_lang("ComentarioOk"));
	}
	else
	{
		Display::display_error_message(get_lang("ComentarioError"));
	}
}
?>


<div style="float:top">
	<?php
		if ($is_allowed_to_edit || $group_member_with_upload_rights)
		{
	?>
			<a href="upload.php?<?php echo api_get_cidreq();?>&path=<?php echo $curdirpathurl.$req_gid; ?>"><img src="../img/submit_file.gif" border="0" title="<?php echo get_lang('AnnadirContenidos');  ?>" alt="" /></a>
			<a href="upload.php?<?php echo api_get_cidreq();?>&path=<?php echo $curdirpathurl.$req_gid; ?>"><?php echo get_lang('AnnadirContenidos'); ?></a>&nbsp;
	<?php
		}
	?>
</div>
<div style="float:left">	
		<?php
			$multimedia = get_multimedia();

			if (count($multimedia)>0)
			{
			    $orden=1;
				foreach ($multimedia as $element)
				{	
				    
					if ($element[3]!=0)
					{
						$duracion =  '('. (floor($element['duration']/60)) .' '.get_lang('Min').'. '. ($element['duration']%60) .' '.get_lang('Seg').'.)';
					}
					else
					{
						$duracion = '';
					}
    					echo '<div class="video">';
						echo 	'<div class="sp_foto"><img src="' . $element['icon']. '" height="20"></div>';
						echo	'<div class="sp_lineas">';
						echo		'<div class="linea1"><a href="index.php?id=' . $element['id']. '" target="_self">' . $element['title'] . '</a>';
                        // Herramientas de tutor
						if ($is_allowed_to_edit || $group_member_with_upload_rights)
					    { 
    					    echo '<div class="herramientas">';
						    echo '<a href="edit.php?id='.$element['id'].'&source_id='.$element['source_id'].'"><img src="../img/edit.gif" border="0"></a> <a href="index.php?action=delete&id='.$element['id'].'" onclick="javascript:if(!confirm('."'".addslashes(htmlentities(get_lang("ConfirmYourChoice"),ENT_QUOTES,$charset))."'".')) return false;"><img src="../img/delete.gif" border="0"></a>';
						    if ($orden==1)
						        {
						            if (count($multimedia)>1)
						            {
						                echo '<a href="index.php?orden='.$element['orden'].'&action=movedown"><img src="../img/arrow_down_1.gif"></a>';
						            }
						        }
						    else
						    {
						        if ($orden==count($multimedia))
						        {
						            echo '<a href="index.php?orden='.$element['orden'].'&action=moveup"><img src="../img/arrow_up_1.gif"></a>';
						        }
						        else
						        {
						            echo '<a href="index.php?orden='.$element['orden'].'&action=movedown"><img src="../img/arrow_down_1.gif"></a><a href="index.php?orden='.$element['orden'].'&action=moveup"><img src="../img/arrow_up_1.gif"></a>';
						        }
						    }
						    $orden++;
                            echo '</div>';					
					    }
                        echo '</div>';					
						echo		'<div class="linea2">'. $element['description'] .'</div>';
						list ($imagen, $nombre, $tipo_usuario) = GetUserData($element['user_id']);
						echo 		'<div class="linea2">' . gethace($element['date']) . ', por: ' . $nombre . '</div>';
						echo 	'</div>';
						echo '<div class="linea"></div>';
						echo '</div>';

						
					
				}
				//echo '<tr><td colspan="4"><p style="text-align: center;font-family: Verdana;font-size: 11px;color: #404040;">'.get_lang('SiElElementoNoCarga').'.<br>'.get_lang('SiElProblemaPersiste').'.</p></td></tr>';
			}
			else
			{
				echo get_lang('NoHayElementos');
			}
		?>
</div>
<div class="player">
<?php 
	if (isset($_GET['id']) && $_GET['action']!='delete')
	{
		// Enlace directo a un video desde el exterior
		// antes usabamos un iframe. se conserva en iframe.php
		// Ahora lo integramos en la página por la nueva modificación a la herramienta que
		// añade comentarios
		// echo '<iframe name="player" frameborder="0" scrolling="no" src="iframe.php?id='.$_GET['id'].'" height="500" width="600" marginheight="0" marginwidth="5">';
		$element = get_multimediabyid ($_GET["id"]);
		if ($element[4]==5)
		{
			//Es interno
			echo str_replace($element[13],"http://" . $_SERVER["SERVER_NAME"] . "/courses/" . $_SESSION['_course']['id'] . "/multimedia".$element[5],$element[12]);
		}
		else
		{
			//Es externo
			echo str_replace($element[13],$element[5],$element[12]);
			//componemos lo enlaces
			$url = str_replace($element[13],$element[5],$element[14]);			

			if ($element[4] <= 3)
			{
				// ¿Por qué lo de menor de 3? Pues porque solo los elementos 1,2 y 3 permiten un acceso directo por un ID, el Iboox lleva una url con un html.
				if ( api_get_facebook_tokens(api_get_user_id()))
				{
					$var1 = utf8_decode ("Formación digital");
					$var2 = utf8_decode (get_lang("InteresanteVideo") . " " . $_SESSION['_course']['name']);			
					$var3 = $url ;
					$var4 = api_get_path(WEB_PATH) . "main/social/img/fd.gif";
					$var5 = "";
					$icon = '&nbsp;<a onclick="javascript:FAjax(\'facebook\',1,\''.$var1.'\',\''.$var2.'\',\''.$var3.'\',\''.$var4.'\',\''.$var5.'\');" href="#"><img src="'. $web_code_path . 'img/facebook.gif"></a>';
				}
				if ( api_get_twitter_tokens(api_get_user_id()))
				{
					$var1 = substr(get_lang("InteresanteVideo") . " " . $_SESSION['_course']['name'] . " " . $url,0,140);
					$var2 = "";
					$var3 = "";
					$var4 = "";
					$var5 = "";
					$icon .='&nbsp;<a onclick="javascript:FAjax(\'twitter\',1,\''.$var1.'\',\''.$var2.'\',\''.$var3.'\',\''.$var4.'\',\''.$var5.'\');" href="#"><img src="'. $web_code_path . 'img/twitter.gif"></a>';
				}
			}

		}
		//Aquí debe ir la posibilidad de compartirlos por las redes sociales.
			if ($icon!="")
			{
				echo '<p style="text-align: left;font-family: Verdana;font-size: 11px;color: #404040;">' . get_lang("Compartir") . ': ' . $icon . '</p>';
			}
	}
	else
	{
		echo '<p style="text-align: center;font-family: Verdana;font-size: 11px;color: #404040;">'.get_lang('SeleccioneUnElemento').'</p>';
	}
?>
<br><br>
    
		<?php
			if (isset($_GET['id']) && !isset($_GET['action']))
			{
				$post = get_multimedia_post($_GET['id']);
				//Ponemos la caja de inserción de comentarios.
				list ($imagen, $nombre, $tipo_usuario) = GetUserData(api_get_user_id());
				echo '<form name="comentar" id="comentar" method="post"><div class="comentario">';
				echo 	'<div class="c_foto"><a href="'.$link_perfil.'">' . $imagen . '</a></div>';
				echo	'<div class="c_lineas">';
				echo		'<div class="c_linea1"><img src="'. $web_code_path . 'img/forum.gif">&nbsp;'. get_lang("MultimediaPost") . ' | ' . '<a href="'.$link_perfil.'">'. $nombre . '</a></div>';
				echo		'<div class="c_linea2"><TEXTAREA COLS=40 ROWS=5 NAME="texto"></TEXTAREA><br><INPUT TYPE="button" value="' . get_lang("Compartir") .'" onclick="javascript:Validar();"></div>';
				echo 	'</div>';
				echo '</div></form>';
				echo '<div class="c_linea"></div>';
				if (count($post)>0)
				{
					$c=0;
					foreach ($post as $element)
					{
						$c = $c +1;
						list ($imagen, $nombre, $tipo_usuario) = GetUserData($element['user_id']);
						$link_perfil = '../../main/user/perfil.php?'. api_get_cidreq(). '&student='.$element['user_id'];
						echo '<div class="comentario">';
						echo 	'<div class="c_foto"><a href="'.$link_perfil.'">' . $imagen . '</a></div>';
						echo	'<div class="c_lineas">';
						echo		'<div class="c_linea1"><img src="'. $web_code_path . 'img/forum.gif">&nbsp;'. get_lang("MultimediaPost") . ' | ' . '<a href="'.$link_perfil.'">'. $nombre . '</a></div>';
						echo		'<div class="c_linea2">' . $element['post'] . '</div>';
						echo 		'<div class="c_linea3">' . getHace($element['date']) .'</div>';
						echo 	'</div>';
						echo '</div>';
						if (count($post)!=$c)
						{
							// Para que no ponga la línea en el último comentario
							echo '<div class="c_linea"></div>';
						}
					}
				}
			}	
		?>
	
</div>
<br clear="all"/> 
<?php
/*
==============================================================================
		FOOTER 
==============================================================================

*/ 
Display::display_footer();
?>
