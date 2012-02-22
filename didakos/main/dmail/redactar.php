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

Herramienta DMAIL
egarcia@grupogdt.com
Agosto -2009
http://www.formaciondigital.com

Herramienta que permite enviar correos internos en la plataforma.
Los correos se almacenan en la BBDD_main.

==============================================================================
*/
	
/*
==============================================================================
		INIT SECTION
==============================================================================
*/ 

$language_file = 'dmail';

// global settings initialisation 
// also provides access to main, database and display API libraries
include('../inc/global.inc.php'); 

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/ 
//the main_api.lib.php, database.lib.php and display.lib.php
//libraries are included by default

$libpath = api_get_path(LIBRARY_PATH);
require_once ('dmail_functions.inc.php');
	
/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/ 

//	Optional extra http or html header
//	If you need to add some HTTP/HTML headers code 
//	like JavaScript functions, stylesheets, redirects, put them here.


// $httpHeadXtra[] = "";
// $httpHeadXtra[] = ""; 


// hoja de estilos propia
$htmlHeadXtra[] = "<link rel='stylesheet' type='text/css' href='estilos.css' />"; 
$htmlHeadXtra[] = "<script type='text/javascript' src='ckeditor/ckeditor.js'></script>";
$htmlHeadXtra[] = '<script languaje="javascript">
function objetoAjax(){
        var xmlhttp=false;
        try {
               xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
               try {
                  xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
               } catch (E) {
                       xmlhttp = false;
               }
        }
 
        if (!xmlhttp && typeof XMLHttpRequest!=\'undefined\') {

               xmlhttp = new XMLHttpRequest();
        }
        return xmlhttp;
}
 
function MostrarAgenda(datos){
	
        divResultado = document.getElementById(\'miagenda\');
        ajax=objetoAjax();
        ajax.open("GET", datos);
        ajax.onreadystatechange=function() {
               if (ajax.readyState==4) {
                       divResultado.innerHTML = ajax.responseText
               }
        }
        ajax.send(null)
	divResultado.style.display=\'\';
}


function cerraragenda() {
div = document.getElementById(\'miagenda\');
div.style.display=\'none\';
}

function add_destinatarios()
{

check=document.formagenda.chk;
var cont=0;

for (i=0;i<check.length;++i)
{
	if (check[i].checked)
	{
		id_usuario = check[i].value;
		id_usuario = id_usuario.toString();
		datos_usuario = eval("document.formagenda." + "datos_agenda_" + id_usuario + ".value");
		datos_usuario = datos_usuario.toString();
		if (cont==0)
		{
			document.enviar.destinatarios.value= datos_usuario;
			document.enviar.id_destinatarios.value= id_usuario;

		}
		else
		{
			document.enviar.destinatarios.value= document.enviar.destinatarios.value + "," + datos_usuario;
			document.enviar.id_destinatarios.value= document.enviar.id_destinatarios.value + "," + id_usuario;
		}
		cont=cont +1;
	}
}
cerraragenda();
}

function sel_destinatarios(tipo)
{
	check=document.formagenda.chk;
	var cont=0;
	for (i=0;i<check.length;++i)
	{
		if (tipo==1)
		{check[i].checked=true;}
		else
		{check[i].checked=false;}
	}
}

function LimpiaDestinatarios()
{
	document.enviar.destinatarios.value= "";
	document.enviar.id_destinatarios.value= "";

}
</script>';



$tool_name = "D-mail"; // title of the page (should come from the language file) 
Display::display_header($tool_name);

$usuario = Database::escape_string(api_get_user_id());
	
/*
==============================================================================
		FUNCTIONS
==============================================================================
*/ 

// put your functions here
// if the list gets large, divide them into different sections:
// display functions, tool logic functions, database functions	
// try to place your functions into an API library or separate functions file - it helps reuse

	
/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

// Put your main code here. Keep this section short,
// it's better to use functions for any non-trivial code
if (isset($_POST['enviar']))
{
	//se ha pulsado el botón de enviar
	if ( isset($_POST['asunto']) && isset($_POST['destinatarios']) && isset($_POST['id_destinatarios']))
	{

		if (isset($_FILES['adjunto']))
		{
			//hemos añadido un fichero
			$data = file_get_contents ($_FILES['adjunto']['tmp_name']);
                	$data = mysql_real_escape_string($data);
			//otros datos
		 	$size = $_FILES["adjunto"]["size"];
			$tipo    = $_FILES["adjunto"]["type"];
			$nombre  = $_FILES["adjunto"]["name"];
		}

		// Si recibimos por post el id_adjunto lo mandamos, si no deberia coger un null
		if ( $_POST['id_adjunto'] != "")
		{
			EnviaDmail ($usuario, $_POST['id_destinatarios'],$_POST['asunto'],$_POST['editor_kama'],$data,$size,$tipo,$nombre,$_POST['id_adjunto']);
		}
		else
		{
			EnviaDmail ($usuario, $_POST['id_destinatarios'],$_POST['asunto'],$_POST['editor_kama'],$data,$size,$tipo,$nombre,'null');
		}
		$anuncio = get_lang('DmailEnviadoCorrecto');
 	}
}
else
{
	if (isset($_POST['guardar'])  && isset($_POST['asunto']))
	{
		GuardaBorrador ($usuario,$_POST['asunto'],$_POST['editor_kama'] );
		$anuncio = get_lang('DmailGuardadoCorrecto');
	}
}





api_display_tool_title($tool_name);



?>
<div name="contenedor" class="contenedor">
	<div name="lateral" class="lateral">
		<table name="carpetas" class="carpetas">
			<tr>
				<td class="titulo"><?php echo get_lang('Carpetas_loc');?></td>
			</tr>
			<tr>
			<?php 
				$total= CuentaDmailCarpeta ($usuario,1);
				if ($_GET['dir']=='recibidos')
				{					
				  echo '<td bgcolor="#fdf2a5" class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=recibidos" target="_self">'.get_lang('Recibidos').'</a> (' . $total . ')</td>';
				}
				else
				{
					echo '<td class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=recibidos" target="_self">'.get_lang('Recibidos').'</a> (' . $total . ')</td>';
				}
			?>
			</tr>
			<tr>
			<?php 
				$total= CuentaDmailCarpeta ($usuario,4);
				if ($_GET['dir']=='destacados')
				{
				  echo '<td bgcolor="#fdf2a5" class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=destacados" target="_self">'.get_lang('Destacados').'</a> (' . $total . ')</td>';
				}
				else
				{
				  echo '<td class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=destacados" target="_self">'.get_lang('Destacados').'</a> (' . $total . ')</td>';

				}
			?>
			</tr>
			<tr>
			<?php 
				$total= CuentaDmailCarpeta ($usuario,2);
				if ($_GET['dir']=='enviados')
				{
				  echo '<td bgcolor="#fdf2a5" class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=enviados" target="_self">'.get_lang('Enviados').'</a> (' . $total . ')</td>';
				}
				else
				{
				  echo '<td class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=enviados" target="_self">'.get_lang('Enviados').'</a> (' . $total . ')</td>';
				}
			?>
			</tr>
			<tr>
			<?php 
				$total= CuentaDmailCarpeta ($usuario,3);
				if ($_GET['dir']=='borradores')
				{
				  echo '<td bgcolor="#fdf2a5" class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=borradores" target="_self">'.get_lang('Borradores').'</a> (' . $total . ')</td>';
				}
				else
				{
				  echo '<td class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=borradores" target="_self">'.get_lang('Borradores').'</a> (' . $total . ')</td>';
				}
			?>
			</tr>
			<tr>
			<?php 
				$total= CuentaDmailCarpeta ($usuario,0);
				if ($_GET['dir']=='eliminados')
				{
				  echo '<td bgcolor="#fdf2a5" class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=eliminados" target="_self">'.get_lang('Eliminados').'</a> (' . $total . ')</td>';
				}
				else
				{
				  echo '<td class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=eliminados" target="_self">'.get_lang('Eliminados').'</a> (' . $total . ')</td>';
				}
			?>
			</tr>
			<tr>
			  <td class="titulo"><?php echo get_lang('Carpetas_per')?></td>
			</tr>
			<?php 
				//Listamos aquí todas las carpetas personales 
				$carpetas = Carpetaspersonales ($usuario);
				foreach($carpetas as $carpeta)
				{
					$total= CuentaDmailCarpeta ($usuario,$carpeta['id_carpeta']);
					if ($_GET['dir'] == $carpeta['id_carpeta'])
					{				
						echo '<tr><td bgcolor="#fdf2a5" class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=' . $carpeta['id_carpeta'] .'" target="_self">' . $carpeta['nombre'] .'</a> (' . $total . ')</td></tr>';					
					}
					else
					{
						echo '<tr><td class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=' . $carpeta['id_carpeta'] .'" target="_self">' . $carpeta['nombre'] .'</a> (' . $total . ')</td></tr>';
					}
				}
				echo '<tr><td class=""><br><img src="img/gestion_carpetas.gif"><a class="tool-icon" href="carpetas.php?action=listar"> '. get_lang('Gestionar_carpetas') . '</a></td></tr>';

			?>
		</table>
	</div>

	<div name="central" class="central">
		<div name="anuncios" class="anuncios">
			<table name="anuncios" class="anuncios">
				<tr>
					<td>
					<?php
						if ($anuncio != '')
						{
							echo $anuncio;
						}
						
					?>
					</td>
				</tr>
			</table>
		</div>

		<div name="herramientas" class="herramientas">
			<table name="herramientas" class="herramientas">
				<tr>
					<?php
					
					?>
				</tr>
			</table>
		</div>

		<div name="panel" class="panel">
			<table name="panel" class="panel">
			<?php
			if (isset ( $_GET['action']))
				{
					switch  ($_GET['action'])
					{
						case 'list':
						switch ($_GET['dir'])
						{
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
							$id_carpeta=$_GET['dir'];
							break;
						}
						
						//Cabecera
						echo '<tr><td class="filacorreocabecera" width="2%"></td><td class="filacorreocabecera" width="2%"></td><td class="filacorreocabecera" width="30%"><b>'. get_lang('Remitente').'</b></td><td class="filacorreocabecera" width="50%"><b>'.get_lang('Asunto').'</b></td><td class="filacorreocabecera" width="16%"><b>'.get_lang('Fecha').'</b></td></tr>';
						//listado de mensajes
						$mails = array ();
						$mails = Dmail::_CreateListado ($usuario,$id_carpeta,null,null);
						foreach($mails as $mail)
						{

							if ( $mail['leido'] == 0 )
							{
								$estilo = 'filacorreonoleida';
							}
							else
							{
								$estilo = 'filacorreoleida';
							}
							if ( $mail['importante'] == 1) 
							{
								$imagen='../img/new.gif';
								$enlace='';
							}
							else
							{
								$imagen='../img/new_gris.gif';
								$enlace='';
							}

							echo '<tr><td class="' . $estilo. '"><input type="checkbox"></td>';
							echo '<td class="' . $estilo . '"><img src="' . $imagen.'"></td>';
							echo '<td class="' . $estilo . '"><a class="tool-icon" href="">' . $mail['firstname'] . ' ' . $mail['lastname'] . '</a></td><td class="' . $estilo . '"><a class="tool-icon" href="">' . $mail['asunto'] . '</a></td><td class="' . $estilo . '"><a class="tool-icon" href="">'. $mail['fecha_envio'] .'</a></td></tr>';		
						}
						break;
						case 'redactar':
						?>
						<div id="alerts"><noscript><p></p></noscript></div>
						<form name="enviar" ENCTYPE="multipart/form-data" action="redactar.php?action=redactar" method="post">
						<tr>
							<td class="filacorreonoleida" width="10%">
								<b><?php echo get_lang('Destinatario')?>:</b> 
							</td>
							<td class="filacorreonoleida" width="90%">
								<input name="destinatarios" type="text" size="50" readonly="readonly">
								<input name="id_destinatarios" type="hidden" size="50">
								&nbsp; <img src="img/lp_dokeos_chapter_add.png"> 
								<a class="tool-icon" href="javascript:MostrarAgenda('contactos.php');"><?php echo get_lang('Contactos')?></a>
								&nbsp; <img src="img/agenda_delete.png"> 
								<a class="tool-icon" href="javascript:LimpiaDestinatarios();"><?php echo get_lang('Limpiar')?></a>
							</td>
						</tr>
						<tr>
							<td class="filacorreonoleida" width="10%">
							  <b><?php echo get_lang('Asunto')?>: </b>
							</td>
							<td class="filacorreonoleida" width="90%">
								<input name="asunto" type="text" size="50">
							</td>
						</tr>
						<tr>
							<td class="filacorreonoleida" width="10%">
							  <b><?php echo get_lang('Adjunto')?>: </b>
							</td>
							<td class="filacorreonoleida" width="90%">
								<input name="adjunto" type="file" value="<?php echo get_lang('Adjuntar');?>">
							</td>
						</tr>
						<tr>
							<td width="100%" colspan="2" class="filacorreonoleida">						
							<textarea cols="80" id="editor_kama" name="editor_kama" rows="10"></textarea>
							<script type="text/javascript">
							//<![CDATA[
								CKEDITOR.replace( 'editor_kama',
									{
										skin : 'kama'
									});

							//]]>
							</script>
							</td>
						</tr>
						<tr>
							<td width="100%" colspan="2" class="filacorreonoleida">		
								<input type="submit" name="enviar" value="<?php echo get_lang('Enviar')?>" >
								<input type="submit" name="guardar" value="<?php echo get_lang('GuardarBorrador')?>">
							</td>
						</form>
						<?php
						break;
						case 'respuesta':

						$dmail = DmailRead ($_GET['id'],$usuario,$_GET['dir']);	
						foreach($dmail as $mail)
						{
						?>
						<div id="alerts"><noscript><p></p></noscript></div>
						<form name="enviar" ENCTYPE="multipart/form-data" action="redactar.php?action=redactar" method="post">
						<tr>
							<td class="filacorreonoleida" width="10%">
						  <b><?php echo get_lang('Destinatario');?>:</b> 
							</td>
							<td class="filacorreonoleida" width="90%">
								<input name="destinatarios" type="text" size="50" readonly="readonly" value="<?php echo $mail['firstname1'] . ' ' . $mail['lastname1'];?>">
								<input name="id_destinatarios" type="hidden" size="50" value="<?php echo $mail['envia']; ?>">
								
							</td>
						</tr>
						<tr>
							<td class="filacorreonoleida" width="10%">
								<b><?php echo get_lang('Asunto');?>: </b>
							</td>
							<td class="filacorreonoleida" width="90%">
								<input name="asunto" type="text" size="50" value="<?php echo 'Re: ' . $mail['asunto'];?>">
							</td>
						</tr>
						<tr>
							<td class="filacorreonoleida" width="10%">
						  <b><?php echo get_lang('Adjunto');?>: </b>
							</td>
							<td class="filacorreonoleida" width="90%">
								<?php
									if ($mail['id_adjunto']!='')
									{
										//Aqui insertamos un enlace al archivo
										echo '<img src="img/adjunto.gif">  ';
										echo '<a class="tool-icon" href="descargar_archivo.php?id=' .  $mail['id_adjunto'] . '">' .  $mail['nombre'] . ' (' . round($mail['size']/1024,2) . ' Kb)</a>';
										echo '<input type="hidden" name="id_adjunto" value="'.$mail['id_adjunto'].'">';
									}
									else
									{ ?>
									  <input name="adjunto" type="file" value="<?php echo get_lang('Adjuntar');?>">
									  <?
									}
								?>
							</td>
						</tr>
						<tr>
							<td width="100%" colspan="2" class="filacorreonoleida">						
							<?php
							$respuesta = '<br><br>______________________________________________________________<br><br>' .
							'De: ' . $mail['firstname1'] . ' ' . $mail['lastname1'] . '<br>' .
							'A: ' . $mail['firstname2'] . ' ' . $mail['lastname2'] . '<br>' .
							  get_lang('Fecha_envio').': ' . $mail['fecha_envio'] . '<br>' . 
							  get_lang('Asunto'). ': ' . $mail['asunto'] . '<br>';
							if ($mail['id_adjunto']!='')
							{
							  $respuesta = $respuesta . get_lang('Adjunto').': ' . $mail['nombre'] . '<br>';
							} 
							$respuesta = $respuesta . get_lang('Contenido') .': <br>'. $mail['contenido'];
							?>				
							<textarea cols="80" id="editor_kama" name="editor_kama" rows="10"><?php echo $respuesta; ?></textarea>
							<script type="text/javascript">
							//<![CDATA[
								CKEDITOR.replace( 'editor_kama',
									{
										skin : 'kama'
									});

							//]]>
							</script>
							</td>
						</tr>
						<tr>
							<td width="100%" colspan="2" class="filacorreonoleida">		
								<input type="submit" name="enviar" value="<?php echo get_lang('Enviar')?>" >
								<input type="submit" name="guardar" value="<?php echo get_lang('GuardarBorrador');?>">
							</td>
						</form>
						<?php
						}
						break;
						case 'reenvio':
						$dmail = DmailRead ($_GET['id'],$usuario,$_GET['dir']);	
						foreach($dmail as $mail)
						{
						?>
						<div id="alerts"><noscript><p></p></noscript></div>
						<form name="enviar" ENCTYPE="multipart/form-data" action="redactar.php?action=redactar" method="post">
						<tr>
							<td class="filacorreonoleida" width="10%">
						  <b><?php echo get_lang('Destinatario');?>:</b> 
							</td>
							<td class="filacorreonoleida" width="90%">
								<input name="destinatarios" type="text" size="50" readonly="readonly">
								<input name="id_destinatarios" type="hidden" size="50">
								&nbsp; <img src="../img/lp_dokeos_chapter_add.png"> 
								<a class="tool-icon" href="javascript:MostrarAgenda('contactos.php');"><?php echo get_lang('Contactos')?></a>
								&nbsp; <img src="img/agenda_delete.png"> 
								<a class="tool-icon" href="javascript:LimpiaDestinatarios();"><?php echo get_lang('Limpiar')?></a>
							</td>
						</tr>
						<tr>
							<td class="filacorreonoleida" width="10%">
								<b><?php echo get_lang('Asunto');?>: </b>
							</td>
							<td class="filacorreonoleida" width="90%">
								<input name="asunto" type="text" size="50" value="<?php echo $mail['asunto'];?>">
							</td>
						</tr>
						<tr>
							<td class="filacorreonoleida" width="10%">
								<b><?php echo get_lang('Adjunto');?>: </b>
							</td>
							<td class="filacorreonoleida" width="90%">
								<?php
									if ($mail['id_adjunto']!='')
									{
										//Aqui insertamos un enlace al archivo
										echo '<img src="img/adjunto.gif">  ';
										echo '<a class="tool-icon" href="descargar_archivo.php?id=' .  $mail['id_adjunto'] . '">' .  $mail['nombre'] . ' (' . round($mail['size']/1024,2) . ' Kb)</a>';
										echo '<input type="hidden" name="id_adjunto" value="'.$mail['id_adjunto'].'">';
									}
									else
									{ ?>
									  <input name="adjunto" type="file" value="<?php echo get_lang('Adjuntar');?>">
									  <?
									}
								?>
							</td>
						</tr>
						<tr>
							<td width="100%" colspan="2" class="filacorreonoleida">						
							<textarea cols="80" id="editor_kama" name="editor_kama" rows="10"><?php echo $mail['contenido'] ?></textarea>
							<script type="text/javascript">
							//<![CDATA[
								CKEDITOR.replace( 'editor_kama',
									{
										skin : 'kama'
									});

							//]]>
							</script>			
							</td>
						</tr>
						<tr>
							<td width="100%" colspan="2" class="filacorreonoleida">		
								<input type="submit" name="enviar" value="<?php echo get_lang('Enviar');?>" >
								<input type="submit" name="guardar" value="<?php echo get_lang('GuardarBorrador');?>">
							</td>
						</form>
						<?php
						}
						break;
					}
				}
				?>
			</table>
		</div>
	</div> 
</div>
<div id="miagenda" class="agenda" style="display:none;"></div>
<?php
/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>
