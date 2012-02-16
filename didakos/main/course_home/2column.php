<?php 
/*
	Nuevo modelo de visualización, debe sustituir a activity.php
	Es necesario tener activado el apartado de perfil y permitir imágenes de usuarios
	Necesita social_functions.php
	además de cambios en main/inc/lib/database.lib.php y add_course.lib.php
	para añadir una nueva tabla a cada curso "social_config" que guarda la configuración
	del panel por usuario.
*/

//Funciones necesarias (esta carga las de twitter y facebook)
require ('social_functions.php');
//Para las notificaciones
require ('notificaciones_functions.php');
include(dirname ( __FILE__ )."../main/inc/conf/configuration.php");
$db_prefix = $_configuration['db_prefix'];

// echo '<meta http-equiv="Refresh" content="60">';
//Recolectamos los datos del usuario.
list ($imagen, $nombre, $tipo_usuario) = GetUserData(api_get_user_id());
$web_code_path = api_get_path(WEB_CODE_PATH);
$tool_table = Database::get_course_table(TABLE_TOOL_LIST);

/*
==============================================================================
	ESTILOS PROPIOS
==============================================================================
*/
?>
<!-- include the Tools --> 
<!-- <script src="../../main/course_home/jquery.tools.min.js"></script> -->
<!-- tab pane styling --> 
<link rel="stylesheet" type="text/css" href="../../main/course_home/css/social.css" /> 


<?php
/*
==============================================================================
	JAVASCRIPT y AJAX
==============================================================================
*/
?>
<script type="text/javascript" src="../../main/course_home/jquery-1.2.6.min.js"></script>
<script language="javascript">
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

function mostrar_menu(id)
{
	if (document.all) {
		element = document.all["menu" + id];
		img= document.all["img" + id];
	}else {
		element = document.getElementById("menu" + id);
		img = document.getElementById("img" + id);
	}
	// Si el menu seleccionado no esta visible lo muestra y si esta visible lo oculta			
	if(element.style.display=='none')
	{
		element.style.display='';
		img.src='../../main/img/close.gif';
		
	}
	else
	{
		element.style.display='none';
		img.src='../../main/img/open.gif';
	}
}
function Validar()
{
	if (this.formulario_texto.texto.value=='')
	{
		alert ('<?php echo get_lang("InsertarComentario")?>');
		this.formulario_texto.texto.focus();
		return 0;
	}
	else
	{
		if (this.formulario_texto.url.value=='')
		{
			alert ('<?php echo get_lang("InsertarURL")?>');
			this.formulario_texto.url.focus();
			return 0;
		}
		else
		{
			var regex=/^(ht|f)tps?:\/\/\w+([\.\-\w]+)?\.([a-z]{2,3}|info|mobi|aero|asia|name)(:\d{2,5})?(\/)?((\/).+)?$/i;
			if (!regex.test(this.formulario_texto.url.value))
			{
				alert ('<?php echo get_lang("URLNoValida")?>');
				this.formulario_texto.url.focus();
				return 0;
			}
			else
			{
				this.formulario_texto.submit();
			}
		}			

	}
}

function limpiaurl (value)
{
	if (value=="http://*")
		{
			this.formulario_texto.url.value = '' ;
		}
}

function Notificaciones (tipo,fecha)
{
	// Jquery gracias a Rparadela :D

	var destino= $('#desplegable');
	// var boton = $('#block');

	// TODO Ver como podemos meter que el botón pulsado se quede en azulito.

	if (destino.css('display')=='none')
	{
		//hace la petición ajax y en el retorno baja el desplegable
		$.post("../../main/course_home/notificaciones.php",{tipo:tipo,fecha:fecha},function(datos){
			destino.html(datos).slideDown();
			//guarda el tipo que hemos abierto
			destino.attr('rel',tipo);
			// boton.css({'background':'yellow'});
		});
	}
	else
	{
		if (destino.attr('rel') == tipo)
		{
			// Si el tipo es el mismo cerramos
			destino.slideUp();
		}
		else
		{
			//Cerramos, cargamos por ajax y abrimos de nuevo
			destino.slideUp('fast',function(){
				$.post("../../main/course_home/notificaciones.php",{tipo:tipo,fecha:fecha},function(datos){
					destino.html(datos);
					destino.attr('rel',tipo);					
					destino.slideDown();
				});						
			});
		}
	}
}

// Esto es para que empiece la notificación oculta.
	$(document).ready(function(){
	$("#desplegable").hide();
	});

</script>

<?php 
/*
==============================================================================
		CONTENIDO
==============================================================================
*/
	
if(api_is_allowed_to_edit())
{
 	/*
	-----------------------------------------------------------
		HIDE
	-----------------------------------------------------------
	*/
	if(!empty($_GET['hide'])) // visibility 1 -> 0
	{
		api_sql_query("UPDATE $tool_table SET visibility=0 WHERE id='".$_GET["id"]."'",__FILE__,__LINE__);
		Display::display_confirmation_message(get_lang('ToolIsNowHidden'));
	}

  /*
	-----------------------------------------------------------
		REACTIVATE
	-----------------------------------------------------------
	*/
	elseif(!empty($_GET['restore'])) // visibility 0,2 -> 1
	{
		api_sql_query("UPDATE $tool_table SET visibility=1 WHERE id='".$_GET["id"]."'",__FILE__,__LINE__);
		Display::display_confirmation_message(get_lang('ToolIsNowVisible'));
	}
}

// work with data post askable by admin of course

if(api_is_platform_admin())
{
	// Show message to confirm that a tools must be hide from available tools
	// visibility 0,1->2
	if(!empty($_GET['askDelete']))
	{
		?>
			<div id="toolhide">
			<?php echo get_lang("DelLk")?>
			<br />&nbsp;&nbsp;&nbsp;
			<a href="<?php echo api_get_self()?>"><?php echo get_lang("No")?></a>&nbsp;|&nbsp;
			<a href="<?php echo api_get_self()?>?delete=yes&id=<?php echo $_GET["id"]?>"><?php echo get_lang("Yes")?></a>
			</div>
		<?php
	}

	/*
	 * Process hiding a tools from available tools.
	 */

	elseif(isset($_GET["delete"]) && $_GET["delete"])
	{
		api_sql_query("DELETE FROM $tool_table WHERE id='$id' AND added_tool=1",__FILE__,__LINE__);
	}
}

// Hemos recibido por POST
if ( isset($_POST['texto']) && isset($_POST['url']) )
{
	// Venimos por post de una compartición. analizamos el contenido de la URL.
	// Obtenemos todos los contenidos externos soportados por la plataforma.

	$multimedia_sources = Database :: get_course_table(TABLE_MULTIMEDIA_SOURCES);
	$multimedia = Database :: get_course_table(TABLE_MULTIMEDIA);
	$link_category = Database :: get_course_table(TABLE_LINK_CATEGORY);
	$item_property = Database :: get_course_table(TABLE_ITEM_PROPERTY);
	$link = Database :: get_course_table(TABLE_LINK);

	$sql = 'SELECT * FROM ' .$multimedia_sources. ' order by id';
	$res = api_sql_query($sql, __FILE__, __LINE__);
	
	while ($temp_row = Database::fetch_array($res))
	{
		if (stristr($_POST['url'],$temp_row['name']))
		{
			// Hemos localizado un elemento externo multimedia
			// Obtenemos el Id del video para insertarlo en el campo target
			// Dependiendo del tipo se obtiene de una forma u otra.
			// Aquí tendríamos que insertar un algoritmo para cada nuevo elemento soportado por la plataforma.

			switch($temp_row['name'])
			{
				case 'Youtube':
					// buscamos la cadena "watch?v=" hasta "&" sumamos 8 para eliminar el inicio de la cadena
					$inicio = strpos($_POST['url'],"watch?v=") + 8;
					// buscamos el primer &
					$fin = strpos($_POST['url'],"&");
					//Calculamos el tamaño
					$length= $fin - $inicio;
					//obtenemos el ID
					$target = substr($_POST['url'],$inicio,$length);
					// Procedemos a guardar datos.
					$sql = "INSERT into " . $multimedia . " (title,description,duration,source_id,target,date,user_id,orden) 
					values ('" . get_lang("CompartidoPor") . " " . $_user['firstName']. " " .$_user['lastName'] . "','". $_POST["texto"] ."',0,1,'". $target . "', now(),".api_get_user_id().",0)";
					$res = api_sql_query($sql, __FILE__, __LINE__);
				    $sql = "update " . $multimedia . " set orden=" . GetLastElementOrder() . " where orden=0";
                	$res = api_sql_query($sql, __FILE__, __LINE__);
					break;
				case 'Vimeo':
					// http://www.vimeo.com/23798312
					$inicio = strpos($_POST['url'],".com/") + 5;
					$target = substr($_POST['url'],$inicio);
					$sql = "INSERT into " . $multimedia . " (title,description,duration,source_id,target,date,user_id,orden) 
					values ('" . get_lang("CompartidoPor") . " " . $_user['firstName']. " " .$_user['lastName'] . "','". $_POST["texto"] ."',0,2,'". $target . "', now(),".api_get_user_id().",0)";
					$res = api_sql_query($sql, __FILE__, __LINE__);
				    $sql = "update " . $multimedia . " set orden=" . GetLastElementOrder() . " where orden=0";
                	$res = api_sql_query($sql, __FILE__, __LINE__);
					break;
				break;
				case 'Dailymotion':
					// video/ y -
					$inicio = strpos($_POST['url'],"video/") + 6;
					// buscamos el primer -
					if (strpos($_POST['url'],"_"))
					{
						//La cadena viene con mas mierda detras
						$fin = strpos($_POST['url'],"_");
						//Calculamos el tamaño
						$length= $fin - $inicio;
						$target = substr($_POST['url'],$inicio,$length);
					}
					else
					{
						// La cadena viene limpia
						$target = substr($_POST['url'],$inicio);
					}
					// Procedemos a guardar datos.
					$sql = "INSERT into " . $multimedia . " (title,description,duration,source_id,target,date,user_id,orden) 
					values ('Compartido por " . $_user['firstName']. " " .$_user['lastName'] . "','". $_POST["texto"] ."',0,3,'". $target . "', now(),".api_get_user_id().",0)";
					$res = api_sql_query($sql, __FILE__, __LINE__);
				    $sql = "update " . $multimedia . " set orden=" . GetLastElementOrder() . " where orden=0";
                	$res = api_sql_query($sql, __FILE__, __LINE__);					
					break;
				case 'Ivoox':
					// http://www.ivoox.com/un-ano-misterio-iker-jimenez-audios-mp3_rf_201637_1.html
					$inicio = strpos($_POST['url'],"rf_") + 3;
					$cadena = substr($_POST['url'],$inicio);
					$fin = strpos($cadena,"_");
					$target = substr($cadena,0,$fin);
					echo $inicio . " " . $fin . " " . $cadena . " " . $target;
					// Procedemos a guardar datos.
					$sql = "INSERT into " . $multimedia . " (title,description,duration,source_id,target,date,user_id,orden) 
					values ('Compartido por " . $_user['firstName']. " " .$_user['lastName'] . "','". $_POST["texto"] ."',0,4,'". $target . "', now(),".api_get_user_id().",0)";
					$res = api_sql_query($sql, __FILE__, __LINE__);
				    $sql = "update " . $multimedia . " set orden=" . GetLastElementOrder() . " where orden=0";
                	$res = api_sql_query($sql, __FILE__, __LINE__);					
					break;
			}		
			if ($res==1)
			{
				Display::display_confirmation_message(get_lang("ContenidoExternoCorrecto"));
			}
		}	
	}

if ($res!=1)
	{
		// No es un enlace externo valido.
		// Lo metemos como enlace
		// Miramos si la carpeta compartida existe, si no existe la creamos
		$sql = "select * from $link_category where category_title='".get_lang("CompartidaAlumnos")."'";
		$row = mysql_fetch_array(mysql_query($sql));	
		if ( is_numeric($row['id']))
		{
			$category_id = $row['id'];
		}
		else
		{
			//La insertamos y obtenemos el id insertado
			$sql = "Insert into " . $link_category . " (category_title,description,display_order) values ('" . get_lang("CompartidaAlumnos") . "','" . get_lang("CompartidaDesc") . "',0)";
			$res = api_sql_query($sql, __FILE__, __LINE__);
			$category_id =mysql_insert_id();
			// Obtenemos el último id de orden
			$sql = "select max(display_order) as display_order from " . $link_category;
			$res = api_sql_query($sql,__FILE__,__LINE__);
			$obj = Database::fetch_object($res);
			$display_order = ($obj->display_order)+1 ;
			//Updateamos el orden
			$sql = "update " . $link_category . "set display_order=" . $display_order. " where id=" . $category_id;
			$res = api_sql_query($sql, __FILE__, __LINE__);
		}
		
		// Ya tenemos el id categoria.
		// Buscamos el display order
		$sql = "select max(display_order) as display_order from $link where category_id=".$category_id;
		$res = api_sql_query($sql,__FILE__,__LINE__);
		$obj = Database::fetch_object($res);
		$display_order = ($obj->display_order)+1 ;
		// Insertamos el enlace
		$sql = "insert into " . $link . " (url,title,description,category_id,display_order) values ('".$_POST['url']."','" . $_POST['url'] . "','" .$_POST['texto']. " ." . get_lang("CompartidoPor") . " " . $_user['firstName'] . " " . $_user['lastName'] . "'," . $category_id . "," . $display_order . ")";
		$res = api_sql_query($sql, __FILE__, __LINE__);	
		// Insertamos en item_property.
		$sql = "insert into " . $item_property . " (tool,insert_user_id,insert_date,lastedit_date,ref,lastedit_type,lastedit_user_id,visibility) values ('link',".api_get_user_id().",now(),now(),".mysql_insert_id().",'LinkAdded',".api_get_user_id().",1)";
		$res = api_sql_query($sql, __FILE__, __LINE__);

		if ($res==1)
		
			{
				Display::display_confirmation_message(get_lang("ContenidoLinkCorrecto"));
			}

		// Insertamos el enlace con fecha ??????.

	}
// Algún error rrrrarrrrrrooooo rrrrarrrrrrooooo 
if ($res!=1)
	{
		Display::display_confirmation_message(get_lang("ErrorInsertar"));
	}
}

?>


<div class="cuerpo"> 
<div id="refresh" style="text-align:right;"><img style="cursor:pointer;" src="../../main/img/refresh.gif" border="0" onclick="javascript:location.reload();"></div>
	<div id="sp_ContenedorGestor">
	<form action="index.php" method="POST" id="formulario_texto" name="formulario_texto" onKeyPress="disableEnterKey()">	
	<table align="center">
		<tr><td><b><?php echo get_lang("QueCompartir"); ?></b></td></tr>
		<tr>
			<td>
				<input type="text" name="url" size="65" value="http://*" onclick="javascript:limpiaurl(this.value);">
			</td>
		</tr>
		<tr><td><b><?php echo get_lang("Comentario"); ?></b></td></tr>
		<tr>
			<td valign="top">
				<TEXTAREA COLS=50 ROWS=4 NAME="texto" ></TEXTAREA>
			</td>
			
		</tr>
		<tr>
			<td align="right">
			<INPUT TYPE="button" value="<?php echo get_lang("Compartir"); ?>" onclick="javascript:Validar();">
			</td>
		</tr>
	</table>
	</form>
	</div>
	<div class="panes">
		<!-- Y cada uno que sea de tipo "pan" es un panel -->
		<div class="pan">
		    <?php
			//cargamos las noticias del panel
			$items_curso = GetEventos ();
			$tokens_facebook = api_get_facebook_tokens(api_get_user_id());
			$tokens_twitter = api_get_twitter_tokens (api_get_user_id());
			//Cargamos la configuracion
			list ($eventos_por_tipo,$max_eventos,$orden,$post_orden,$social,$notificaciones,$visibility,$tipos) = GetConfig();
			$elementos_mostrados=0;

			foreach ($items_curso as $elemento)	
			{

			//Recolectamos los datos del usuario.
			//Si el usuario es de una red social no recibimos un numero, sino el nombre
				list ($imagen, $nombre, $tipo_usuario) = GetUserData($elemento[4]);
				$link_perfil = '../../main/user/perfil.php?'. api_get_cidreq(). '&student='.$elemento[4];

				$elementos_mostrados++;
				if ($tipo_usuario=='Gestor')
				{
					//Es un estilo con algo más de fuerza. En los estilos de 2column.php
					$estilo = 'sp_ContenedorGestor';
				}
				else
				{
					//Con el color por defecto.
					$estilo = 'sp_ContenedorAlumno';
				}

				echo 	'<div id="'.$estilo.'">';
							// Añadimos enlace al perfil del alumno en la imagen
				echo			'<div id="sp_foto"><a href="'.$link_perfil.'">' . $imagen . '</a></div>';
				echo			'<div id="sp_lineas">';
					echo			'<div id="linea1"><img src="'. $web_code_path . 'img/'. $tipos[$elemento[1]][2] . '">&nbsp;'. get_lang($tipos[$elemento[1]][1]) . ' | ' . '<a href="'.$link_perfil.'">'. $nombre . '</a></div>';
					echo			'<div id="linea2">' . $elemento[3] . '</div>';
					echo 			'<div id="linea3">' . getHace($elemento[2]);
								// La variable $tipos es una variable global
								if ($tipos[$elemento[1]][0]=="5")
								{
								// Es el Curso
								// Insertamos iconos de publicación social.
								// Hay que revisar que tengamos permisos.
									if ( $tokens_facebook && $elemento[4] == api_get_user_id())
									{			
										$var1 = utf8_decode ("Formación digital");
										$var2 = "Estoy realizando el curso ". $_SESSION['_course']['name'];			
										$var3 = substr(api_get_path(WEB_PATH),0,strlen(api_get_path(WEB_PATH))-1 ) ;
										$var4 = api_get_path(WEB_PATH) . "main/social/img/fd.gif";
										$var5 = "";
										echo ' | <a onclick="javascript:FAjax(\'facebook\',1,\''.$var1.'\',\''.$var2.'\',\''.$var3.'\',\''.$var4.'\',\''.$var5.'\');" href="#"><img src="'. $web_code_path . 'img/facebook.gif"></a>';
									}
									if ( $tokens_twitter && $elemento[4] == api_get_user_id())
									{
										$var1 = utf8_decode(substr("Estoy realizando el curso ". utf8_encode($_SESSION['_course']['name']) . " con @FormacionDgtal",0,140));
										$var2 = "";
										$var3 = "";
										$var4 = "";
										$var5 = "";
										echo ' | <a onclick="javascript:FAjax(\'twitter\',1,\''.$var1.'\',\''.$var2.'\',\''.$var3.'\',\''.$var4.'\',\''.$var5.'\');" href="#"><img src="'. $web_code_path . 'img/twitter.gif"></a>';
									}
								}
								if ($tipos[$elemento[1]][0]=="4")
								{
								$publicacion = $elemento[3];
								// Es un exámen o ejercicio
								// Insertamos iconos de publicación social
									if ( $tokens_facebook && $elemento[4] == api_get_user_id())
									{
										$var1 = utf8_decode ("Formación digital");
										$var2 = $elemento[3]. " del curso " . $_SESSION['_course']['name'];			
										$var3 = substr(api_get_path(WEB_PATH),0,strlen(api_get_path(WEB_PATH))-1 ) ;
										$var4 = api_get_path(WEB_PATH) . "main/social/img/fd.gif";
										echo ' | <a onclick="javascript:FAjax(\'facebook\',1,\''.$var1.'\',\''.$var2.'\',\''.$var3.'\',\''.$var4.'\',\''.$var5.'\');" href="#"><img src="'. $web_code_path . 'img/facebook.gif"></a>';
									}
									if ( $tokens_twitter && $elemento[4] == api_get_user_id())
									{
										$var1 = substr($elemento[3]. " en un curso impartido por @formacionDgtal",0,140);
										$var2 = "";
										$var3 = "";
										$var4 = "";
										$var5 = "";
										echo ' | <a onclick="javascript:FAjax(\'twitter\',1,\''.$var1.'\',\''.$var2.'\',\''.$var3.'\',\''.$var4.'\',\''.$var5.'\');" href="#"><img src="'. $web_code_path . 'img/twitter.gif"></a>';
									}
								}
								if ($elemento[5] != "")
								{
									$target="_self";
									  echo	' <div id="vermas"><a href="' . $elemento[5] . '" target="'.$target.'">'. get_lang('VerMas').'</a></div>';
								}
							echo	'</div>';
						echo	'</div>';
				echo	'</div>';
				echo 	'<div id="linea"></div>';
				if ($elementos_mostrados >= $max_eventos)
				{break;}
				}
			?>
		</div>
	</div>
</div> 
<div class="lateral"> 
	<div id="lateral_box" >
		<div id="lateral_cabecera">
			<div id="cabecera_izq"><img src="<?php echo api_get_path(WEB_CODE_PATH). 'img/scorm.gif'; ?>">&nbsp;<b><? echo get_lang("ContenidoMultimedia"); ?></b></div>
			<div id="cabecera_der"><a onClick="javascript:mostrar_menu('1');"><img style="cursor:pointer;" id="img1" src="<?php echo api_get_path(WEB_CODE_PATH). 'img/open.gif';?>"></a></div>
		</div>

		<?php 
		//Recogemos los Lp_items del curso y su progreso
		$items = GetLpItems();
		?>

		<div id="menu1" style="display:none;float:left;" >
			<table>
				<?php
				echo '<tr><td style="PADDING-LEFT: 20px;"><b>' . get_lang("Elemento") . '</b></td><td style="PADDING-LEFT: 10px;"><b>' . get_lang("Progreso") . '</b></td></tr>';
				if (count($items)>0)
				{
					foreach ($items as $elemento)	
					{
						echo '<tr><td style="PADDING-LEFT: 20px;"><a href="' . $web_code_path . 'newscorm/lp_controller.php?cidReq=' . api_get_course_id() . '&action=view&item_id=' . $elemento['id']. '&lp_id=' .$elemento['lp_id']. '" target="_self">' . $elemento['title'] . '</a></td><td style="PADDING-LEFT: 10px;">' . $elemento['score'] . '%</td></tr>';
					}
				}
				else
				{
					echo '<tr><td style="PADDING-LEFT: 20px;" colspan="2">'.get_lang("NoHayContenido").'</td></tr>';
				}
				?>				
			</table>
		</div>	
	</div>
	<div id="linea"></div>
	<div id="lateral_box">
		<div id="lateral_cabecera">
			<div id="cabecera_izq"><img src="<?php echo api_get_path(WEB_CODE_PATH). 'img/quiz.gif'; ?>">&nbsp;<b><? echo get_lang("Examenes"); ?></b></div>
			<div id="cabecera_der"><a onClick="javascript:mostrar_menu('2');"><img style="cursor:pointer;" id="img2" src="<?php echo api_get_path(WEB_CODE_PATH). 'img/open.gif';?>"></a></div>
		</div>
		<?php 
			$items = GetQuizs($db_prefix);
		?>
		<div id="menu2" style="display:none;float:left;" width="300">
			<table >
			<?php		
				echo '<tr><td style="PADDING-LEFT: 20px;"><b>' . get_lang("Examen") . '</b></td><td style="PADDING-LEFT: 10px;"><b>' . get_lang("Calificacion") . '</b></td></tr>';
				if (count($items)>0)
				{
					foreach ($items as $elemento)	
					{
						echo '<tr><td style="PADDING-LEFT: 20px;"><a href="' . $web_code_path . 'exam/exercice_submit.php?cidReq=' . api_get_course_id() . '&exerciseId=' . $elemento['id']. '" target="_self">' . $elemento['title'] . '</a></td><td style="PADDING-LEFT: 10px;">';
						if ($elemento['score']!= "")
						{echo $elemento['score']/10;}
						else
						{echo '-';}
						echo "</td></tr>";
					}
				}
				else
				{
					echo '<tr><td style="PADDING-LEFT: 20px;" colspan="2">'.get_lang("NoHayContenido").'</td></tr>';
				}
			?>	
			</table>
		</div>
	</div>
	<div id="linea"></div>
	<div id="lateral_box">
		<div id="lateral_cabecera">
			<div id="cabecera_izq"><img src="<?php echo api_get_path(WEB_CODE_PATH). 'img/acces_tool.gif'; ?>">&nbsp;<b><? echo get_lang("OtrasHerramientas"); ?></b><br></div>
			<div id="cabecera_der"><a  onClick="javascript:mostrar_menu('3');"><img style="cursor:pointer;" id="img3" src="<?php echo api_get_path(WEB_CODE_PATH). 'img/close.gif';?>"></a></div>
		</div>
		<div id="menu3">
		<?php
		$tools = GetTools();
		// Primero vamos a poner todas las herramientas normales.
		foreach ($tools as $elemento)	
			{
				if (($elemento['category']=='authoring' || $elemento['category']=='interaction') && ($elemento['name']!= 'learnpath'))
				{		
					echo '<div style="float:left;" >';
					//dependiendo de si es o no visible mostramos una u otra miagen
					if($elemento['visibility'] == '1')	
					{
						echo '<a class="Ntooltip" href="' . $web_code_path . $elemento['link'] . '" target="_self"><span>'. get_lang(ucfirst($elemento['name'])) .'</span><img  src="' . $web_code_path . 'img/' . $elemento['image'] . '"></a>';
					}
					else
					{
						echo '<a class="Ntooltip" href="' . $web_code_path . $elemento['link'] . '" target="_self"><span>'.get_lang(ucfirst($elemento['name'])) .'</span><img desc="' . get_lang(ucfirst($elemento['name'])) . '" src="' . $web_code_path . 'img/' . str_ireplace(".", "_na.", $elemento['image']) . '"></a>';
					}
					// Si es tutor o gestor mostramos el ojito para activar o desactivar herramientas
					if ( api_is_allowed_to_edit() )
					{
						if($elemento['visibility'] == '1')
						{
							echo '<a class="Ntooltip" href="index.php?cidreq='.api_get_course_id() . '&hide=yes&id='.$elemento['id'].'"><span>'.get_lang(ucfirst($elemento['name'])).'</span><img src="'.api_get_path(WEB_CODE_PATH).'img/visible.gif" alt="'.get_lang("Deactivate").'"/></a>';
						}
						else
						{	
							echo '<a class="Ntooltip" href="index.php?cidreq='.api_get_course_id() . '&restore=yes&id='.$elemento['id'].'"><span>'.get_lang(ucfirst($elemento['name'])) .'</span><img src="'.api_get_path(WEB_CODE_PATH).'img/invisible.gif" alt="'.get_lang("Activate").'"/></a>';
						}
					}
					echo '</div>';
				}		
			}
		?>
		</div>
	</div>	
	<div id="linea"></div>
	<div id="lateral_box">
		<div id="lateral_cabecera">
			<div id="cabecera_izq"><img src="<?php echo api_get_path(WEB_CODE_PATH). 'img/settings.gif'; ?>">&nbsp;<b><? echo get_lang("Configuracion"); ?></b></div>
			<div id="cabecera_der"><a onClick="javascript:mostrar_menu('4');"><img style="cursor:pointer;" id="img4" src="<?php echo api_get_path(WEB_CODE_PATH). 'img/open.gif';?>"></a></div>
		</div>		
		<div id="menu4" style="display:none;float:left;" >
			<table>
			<tr><td style="PADDING-LEFT: 20px;"><a target="_self" href="<?php echo $web_code_path . 'newscorm/lp_controller.php' ?>"> <? echo get_lang(ucfirst('learnpath')); ?></a></td></tr>
			<?php
			// La configuración del panel es solo administrable por el tutor o gestor
			if (api_is_allowed_to_edit())
			{?>
			<tr><td style="PADDING-LEFT: 20px;"><a target="_self" href="<?php echo $web_code_path . 'social/config.php'?>"> <? echo get_lang("ConfigurarPanel"); ?></a></td></tr>										
			<?php } ?>
			<tr><td style="PADDING-LEFT: 20px;"><a target="_self" href="<?php echo $web_code_path . 'servicio_tecnico/contacto.php' ?>"><? echo get_lang("ServicioTecnico"); ?></a></td></tr>
			<?php
			// Ahora vamos a poner todas las herramientas de administración.
			if ( api_is_allowed_to_edit() || $tipo_usuario=='Inspector')
			{
				foreach ($tools as $elemento)	
				{
					if ($elemento['category']=='admin' || $elemento['category']=='ninguna_categoría')
					{
						echo '<tr><td style="PADDING-LEFT: 20px;"><a href="' . $web_code_path . $elemento['link'] . '" target="_self">' . get_lang(ucfirst($elemento['name'])). '</a></td></tr>';
					}		
				}
			}

			?>	
			</table>
		</div>
	</div>
</div>

<?php 

if ($notificaciones ==1)
{
	// Sistema de notificaciones, muestra el total de notificaciones nuevas desde la última visita.
	// Despliega una lista
	$trak_e_login = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_LOGIN);
	$sql = "select logout_date from ".$trak_e_login." where login_user_id= ". api_get_user_id() . " order by login_id DESC LIMIT 2";
	$result = api_sql_query($sql,__FILE__,__LINE__);
		while ($temp_row = Database::fetch_array($result))
		{
			//Al ser un bucle solo vamos a guardar el último registro que
			//según la ordenación es la última fecha de logut de la paltaforma ignorando
			//la fila actual, que es updateada por dokeos con cada acción.
			$lastlogoutdate = $temp_row['logout_date'];
			//En caso de ser el primer login cogerá la fecha de la última acción.
		}
	?>
	<div class="taskbar">
	   <div class="container" id="conteainer">
		<div class="block" onclick="javascript:Notificaciones('dmail','<?php echo $lastlogoutdate; ?>');" onMouseOver="this.style.backgroundColor='#b0e4ff';" onMouseOut="this.style.backgroundColor='';"><?php echo DmailCount(api_get_user_id(),$lastlogoutdate); ?>&nbsp;<img src="../../main/img/dropbox.gif"></div>
		<div class="block" onclick="javascript:Notificaciones('anuncio','<?php echo $lastlogoutdate; ?>');" onMouseOver="this.style.backgroundColor='#b0e4ff';" onMouseOut="this.style.backgroundColor='';"><?php echo AnnouncementCount(api_get_user_id(),$lastlogoutdate); ?>&nbsp;<img src="../../main/img/valves.gif"></div>
		<div class="block" onclick="javascript:Notificaciones('foro','<?php echo $lastlogoutdate; ?>');" onMouseOver="this.style.backgroundColor='#b0e4ff';" onMouseOut="this.style.backgroundColor='';"><?php echo ForumCount(api_get_user_id(),$lastlogoutdate); ?>&nbsp;<img src="../../main/img/forum.gif"></div>
		<div class="block" onclick="javascript:Notificaciones('enlace','<?php echo $lastlogoutdate; ?>');" onMouseOver="this.style.backgroundColor='#b0e4ff';" onMouseOut="this.style.backgroundColor='';"><?php echo LinkCount(api_get_user_id(),$lastlogoutdate); ?>&nbsp;<img src="../../main/img/link.gif"></div>
		<div class="block" onclick="javascript:Notificaciones('multimedia','<?php echo $lastlogoutdate; ?>');" onMouseOver="this.style.backgroundColor='#b0e4ff';" onMouseOut="this.style.backgroundColor='';"><?php echo MultimediaCount(api_get_user_id(),$lastlogoutdate); ?>&nbsp;<img src="../../main/img/multimedia.gif"></div>
		<div class="block" onclick="javascript:Notificaciones('encuesta','<?php echo $lastlogoutdate; ?>');" onMouseOver="this.style.backgroundColor='#b0e4ff';" onMouseOut="this.style.backgroundColor='';"><?php echo SurveyCount(api_get_user_id(),$lastlogoutdate); ?>&nbsp;<img src="../../main/img/survey.gif"></div>
	    </div>
	   <div class="desplegable" id="desplegable"></div>
	</div>
<?php
}
?>

