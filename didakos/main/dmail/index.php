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


// name of the language file that needs to be included
$language_file = 'dmail';

// global settings initialisation 
// also provides access to main, database and display API libraries
include('../inc/global.inc.php'); 
require_once(api_get_path(LIBRARY_PATH) . "security.lib.php");
include_once("../inc/lib/fckeditor/fckeditor.php") ;
api_protect_course_script();

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
 
function MarcaFavoritos(id_mail, nuevoestado,directorio){
	
        Imgresultado = document.getElementById(\'img\' + id_mail);
	Enlace = document.getElementById(\'a\' + id_mail);
        ajax=objetoAjax();
        ajax.open("GET", "favoritear.php?id=" + id_mail + "&nuevoestado=" + nuevoestado);
        ajax.onreadystatechange=function() {
               if (ajax.readyState==4) {
			if (nuevoestado==\'1\')
			{
				Imgresultado.src = "img/new.gif";
				Enlace.href = "javascript:MarcaFavoritos(\'" +id_mail + "\',\'0\');";
			}
			else
			{
				Imgresultado.src = "img/new_gris.gif";
				Enlace.href = "javascript:MarcaFavoritos(\'" +id_mail + "\',\'1\');";
			}
               }
        }
        ajax.send(null);

	if(directorio==\'destacados\')
	{
		window.location="index.php?dir=" + directorio;
	}
}

function Eliminar(directorio){

var id_mail="";

	for (var i=0;i < document.forms.listado.elements.length;i++)
	{
		var elemento = document.forms.listado.elements[i];
		if (elemento.type == "checkbox" && elemento.checked==true)
			{
				if (id_mail.length==0)
				{
					id_mail = elemento.id;
				}
				else
				{
					id_mail = id_mail + \',\' + elemento.id;
				}
		
			}
	}

	if ( id_mail.length==0 )
	{
		window.location="index.php?dir=" + directorio + "&anuncio=" + escape("'. get_lang('Selmensaje_eliminar') .'");
	}
	else
	{
		if (confirm("'. get_lang('SeguroEliminar') .'"))	
		{
			ajax=objetoAjax();
			ajax.open("GET", "eliminar.php?id=" + id_mail,false);
			ajax.onreadystatechange=function() {
			       if (ajax.readyState==4) {
					
			       }

			}
			ajax.send(null);
			window.location="index.php?dir=" + directorio + "&anuncio='. get_lang('DmailEliminados') .'";
		}
	}
}

function Recuperar(directorio){

var id_mail="";

	for (var i=0;i < document.forms.listado.elements.length;i++)
	{
		var elemento = document.forms.listado.elements[i];
		if (elemento.type == "checkbox" && elemento.checked==true)
			{
				if (id_mail.length==0)
				{
					id_mail = elemento.id;
				}
				else
				{
					id_mail = id_mail + \',\' + elemento.id;
				}
		
			}
	}

	if ( id_mail.length==0 )
	{
		window.location="index.php?dir=" + directorio + "&anuncio='. get_lang('Selmensaje_recuperar') .'";
	}
	else
	{
		if (confirm("'. get_lang('SeguroRecuperar') .'"))	
		{
			ajax=objetoAjax();
			ajax.open("GET", "recuperar.php?id=" + id_mail,false);
			ajax.onreadystatechange=function() {
			       if (ajax.readyState==4) {
					
			       }

			}
			ajax.send(null);
			window.location="index.php?dir=" + directorio + "&anuncio=' . get_lang('DmailRecuperados') .'";
		}
	}
}

function mover(carpeta_origen)
{
var id_mail="";
for (var i=0;i < document.forms.listado.elements.length;i++)
{
	var elemento = document.forms.listado.elements[i];
	if (elemento.type == "checkbox" && elemento.checked==true)
		{
			if (id_mail.length==0)
			{
				id_mail = elemento.id;
			}
			else
			{
				id_mail = id_mail + \',\' + elemento.id;
			}
		
		}	
}

	if (id_mail.length==0)
	{
		window.location="index.php?dir=" + carpeta_origen + "&anuncio='. get_lang('Selmensaje_mover') .'";	
	}
	else
	{		
		window.location="mover.php?id=" + id_mail + "&carpeta_origen=" + carpeta_origen;
	}

}

function leido(carpeta_origen,estado)
{
var id_mail="";
for (var i=0;i < document.forms.listado.elements.length;i++)
{
	var elemento = document.forms.listado.elements[i];
	if (elemento.type == "checkbox" && elemento.checked==true)
		{
			if (id_mail.length==0)
			{
				id_mail = elemento.id;
			}
			else
			{
				id_mail = id_mail + \',\' + elemento.id;
			}
		
		}	
}

	if (id_mail.length==0)
	{
		window.location="index.php?dir=" + carpeta_origen + "&anuncio='. get_lang('Selmensaje_leido') .'";	
	}
	else
	{		
		if (confirm("'. get_lang('SeguroLeido') .'"))	
		{
			ajax=objetoAjax();
			ajax.open("GET", "leido.php?id=" + id_mail + "&estado=" + estado,false);
			ajax.onreadystatechange=function() {
			       if (ajax.readyState==4) {
					
			       }

			}
			ajax.send(null);
			window.location="index.php?dir=" + carpeta_origen + "&anuncio=' . get_lang('DmailLeidos') .'";
		}
	}

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

api_display_tool_title($tool_name);

//examino la página a mostrar y el inicio del registro a mostrar
$TAMANO_PAGINA = 10;
$pagina = $_GET["pagina"];
if (!$pagina) {
    $inicio = 0;
    $pagina=1;
}
else {
    $inicio = ($pagina - 1) * $TAMANO_PAGINA;
} 

// examinamos si se ha realizado alguna busqueda
if (isset($_POST['buscar']))
{
	$buscar = $_POST['buscar'];
	$anuncio = get_lang('ShowingResults') . " '$buscar'";/*"Mostrando resultados de la b&uacute;squeda '$buscar'";*/
	$TAMANO_PAGINA = 30;
	$inicio=0;
	$pagina=1;
}

$total_dmail = CuentaDmailCarpeta ($usuario,$_GET['dir'],$buscar);
$total_paginas = ceil($total_dmail / $TAMANO_PAGINA);
if ($total_paginas==0)
$total_paginas = 1;

//examinamos el orden

$orden = $_GET["orden"];
if (!$orden) {
	$orden = "fecha_envio";
}



// deberiamos contemplar la direccion ASC y DESC   
$direccion = $_GET['direccion'];
if (!$direccion)
{
	$direccion = 'DESC';
}


?>
<div name="contenedor" class="contenedor" >
	<div name="lateral" class="lateral">
		<table name="carpetas" class="carpetas">
			<tr>
				<td class="titulo">
				  <?php 
				    echo get_lang('Carpetas_loc'); 
				  ?>
				</td>
			</tr>
			<tr>
			<?php 
				$total= CuentaDmailCarpeta ($usuario,1);
				if ($_GET['dir']=='recibidos')
				{					
				  echo '<td bgcolor="#fdf2a5" class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=recibidos" target="_self">'. get_lang('Recibidos') .'</a> (' . $total . ')</td>';
				}
				else
				{
				  echo '<td class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=recibidos" target="_self">'. get_lang('Recibidos') .' </a> (' . $total . ')</td>';
				}
			?>
			</tr>
			<tr>
			<?php 
				$total= CuentaDmailCarpeta ($usuario,4);
				if ($_GET['dir']=='destacados')
				{
				  echo '<td bgcolor="#fdf2a5" class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=destacados" target="_self">'. get_lang('Destacados') .'</a> (' . $total . ')</td>';
				}
				else
				{
				  echo '<td class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=destacados" target="_self">'. get_lang('Destacados') .'</a> (' . $total . ')</td>';
				}
			?>
			</tr>
			<tr>
			<?php 
				$total= CuentaDmailCarpeta ($usuario,2);
				if ($_GET['dir']=='enviados')
				{
				  echo '<td bgcolor="#fdf2a5" class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=enviados" target="_self">'. get_lang('Enviados') .'</a> (' . $total . ')</td>';
				}
				else
				{
				  echo '<td class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=enviados" target="_self">'. get_lang('Enviados') .'</a> (' . $total . ')</td>';
				}
			?>
			</tr>
			<tr>
			<?php 
				$total= CuentaDmailCarpeta ($usuario,3);
				if ($_GET['dir']=='borradores')
				{
				  echo '<td bgcolor="#fdf2a5" class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=borradores" target="_self">'. get_lang('Borradores') .'</a> (' . $total . ')</td>';
				}
				else
				{
				  echo '<td class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=borradores" target="_self">'. get_lang('Borradores') .'</a> (' . $total . ')</td>';
				}
			?>
			</tr>
			<tr>
			<?php 
				$total= CuentaDmailCarpeta ($usuario,0);
				if ($_GET['dir']=='eliminados')
				{
				  echo '<td bgcolor="#fdf2a5" class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=eliminados" target="_self">'. get_lang('Eliminados') .'</a> (' . $total . ')</td>';
				}
				else
				{
				  echo '<td class=""><img src="img/folder.gif"> <a class="tool-icon" href="index.php?dir=eliminados" target="_self">'. get_lang('Eliminados') .'</a> (' . $total . ')</td>';
				}
			?>
			</tr>
			<tr>
				<td class="titulo"><?php echo get_lang('Carpetas_per');?></td>
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
				  echo '<tr><td class=""><br><img src="img/gestion_carpetas.gif"><a class="tool-icon" href="carpetas.php?action=listar">'. get_lang('Gestionar_carpetas') .'</a></td></tr>';

			?>
		</table>
	</div>

	<div name="central" class="central">
		<div name="anuncios" class="anuncios">
			<table name="anuncios" class="anuncios">
				<tr>
					<td>
					<?php
						if (isset ( $_GET['anuncio']) || $anuncio != "" )
						{
						  echo str_replace("\\'","'",$_GET['anuncio']) . str_replace("\\'","'",$anuncio);
						}
					?>
					</td>
				</tr>
			</table>
		</div>

		<div name="herramientas" class="herramientas">
			<table name="herramientas" class="herramientas">
				<tr>
					<td width="60%" align="left">
					<?php
						switch ($_GET['dir'])
						{
						case 'recibidos':
						echo 	'<img src="img/redactar.gif"><a class="tool-icon" href="redactar.php?action=redactar">'. get_lang('Redactar') .'</a> | '.
						'<img src="img/eliminar.gif"><a class="tool-icon" href="javascript:Eliminar(\'' . $_GET['dir'] . '\');" >' . get_lang('Eliminar') .'</a> | ' .
						'<img src="img/mover.gif"><a class="tool-icon" href="javascript:mover(\'' . $_GET['dir'] . '\');">'. get_lang('Mover') .'</a>  | ' .
						'<img src="img/leido.gif"><a class="tool-icon" href="javascript:leido(\'' . $_GET['dir'] . '\',1);">'. get_lang('Markasreaded') .'</a> | ' .
						'<img src="img/noleido.gif"><a class="tool-icon" href="javascript:leido(\'' . $_GET['dir'] . '\',0);">'. get_lang('Markasunreaded') .'</a>';
						break;
						case 'enviados':
						echo 	'<img src="img/redactar.gif"><a class="tool-icon" href="redactar.php?action=redactar">'. get_lang('Redactar') .'</a> | '.
						'<img src="img/eliminar.gif"><a class="tool-icon" href="javascript:Eliminar(\'' . $_GET['dir'] . '\');" >'. get_lang('Eliminar') .'</a>';
						break;
						case 'destacados':
						echo 	'<img src="img/redactar.gif"><a class="tool-icon" href="redactar.php?action=redactar">'. get_lang('Redactar') .'</a> | '.
						'<img src="img/eliminar.gif"><a class="tool-icon" href="javascript:Eliminar(\'' . $_GET['dir'] . '\');" >'. get_lang('Eliminar') .'</a>';
						break;
						case 'borradores':
						echo 	'<img src="img/redactar.gif"><a class="tool-icon" href="redactar.php?action=redactar">'. get_lang('Redactar') .'</a> | '.
						'<img src="img/eliminar.gif"><a class="tool-icon" href="javascript:Eliminar(\'' . $_GET['dir'] . '\');" >'. get_lang('Eliminar') .'</a> | ';
						break;
						case 'eliminados':
						echo 	'<img src="img/redactar.gif"><a class="tool-icon" href="redactar.php?action=redactar">'. get_lang('Redactar') .'</a> | '.
						'<img src="img/recuperar.gif"><a class="tool-icon" href="javascript:Recuperar(\'' . $_GET['dir'] . '\');" >'. get_lang('Recuperar') .'</a> | ';
						break;
						default:
						echo 	'<img src="img/redactar.gif"><a class="tool-icon" href="redactar.php?action=redactar">'. get_lang('Redactar') .'</a> | '.
						'<img src="img/eliminar.gif"><a class="tool-icon" href="javascript:Eliminar(\'' . $_GET['dir'] . '\');" >'. get_lang('Eliminar') .'</a> | ' .
						'<img src="img/mover.gif"><a class="tool-icon" href="javascript:mover(\'' . $_GET['dir'] . '\');">'. get_lang('Mover') .'</a>';
						break;
						}
					?>
					</td>
					<td width="40%" align="right"> 
					<?php
					echo	'<form name="busqueda" method="POST"><input type="text" name="buscar" size="20">&nbsp;<input type="submit" value="'. get_lang('Buscar') .'">';
				  echo 	'&nbsp;'. get_lang('Paginas') .':&nbsp;';

					if ($total_paginas > 1)
					{
	    					for ($i=1;$i<=$total_paginas;$i++)
						{
					      		if ($pagina == $i)
							{
							  	//si muestro el índice de la página actual, no coloco enlace
							  	echo $pagina . " ";
							}
					       		else
							{
						  		//si el índice no corresponde con la página mostrada actualmente, coloco el enlace para ir a esa página
		  						echo '<a class="tool-icon" href="index.php?dir='. $_GET['dir'] . '&pagina=' . $i . '&orden=' . $orden . '">' . $i . '</a> ';
							}
	    					}
					} 
					else
					{
						echo "1";
					}
					echo '</form>';
					?>
					</td>
				</tr>
			</table>
		</div>

		<div name="panel" class="panel">
			<table name="panel" class="panel">
			<?php
	
				//Cabecera
				switch ($_GET['dir'])
				{
					case 'enviados':
						echo '<tr>'.
						'<td class="filacorreocabecera" width="2%"></td>' .
						'<td class="filacorreocabecera" width="2%"></td>';
						if ( ($orden == 'firstname') && ($direccion == 'DESC'))
							{
								echo '<td class="filacorreocabecera" width="30%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=firstname&direccion=ASC">Destinatario</a></b> <img src="img/up.gif"></td>';
							}
						else
							{
							if ($orden == 'firstname' && $direccion == 'ASC')
								{
									echo '<td class="filacorreocabecera" width="30%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=firstname&direccion=DESC">'. get_lang('Destinatario') .'</a></b> <img src="img/down.gif"></td>';
								}
							else
								{
									echo '<td class="filacorreocabecera" width="30%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=firstname&direccion=ASC">'. get_lang('Destinatario') .'</a></b></td>';
								}
							}		
						if ( ($orden == 'asunto') && ($direccion == 'DESC'))
							{
								echo '<td class="filacorreocabecera" width="40%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=asunto&direccion=ASC">Asunto</a></b> <img src="img/up.gif"></td>';
							}
						else
							{
							if ($orden == 'asunto' && $direccion == 'ASC')
								{
									echo '<td class="filacorreocabecera" width="40%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=asunto&direccion=DESC">'. get_lang('Asunto') .'</a></b> <img src="img/down.gif"></td>';
								}
							else
								{
									echo '<td class="filacorreocabecera" width="40%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=asunto&direccion=ASC">'. get_lang('Asunto') .'</a></b></td>';
								}
							}
						if ( ($orden == 'fecha_envio') && ($direccion == 'DESC'))
							{
								echo '<td class="filacorreocabecera" width="26%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=fecha_envio&direccion=ASC">'. get_lang('Fecha') .'</a></b> <img src="img/up.gif"></td>';
							}
						else
							{
							if ($orden == 'fecha_envio' && $direccion == 'ASC')
								{
									echo '<td class="filacorreocabecera" width="26%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=fecha_envio&direccion=DESC">'. get_lang('Fecha') .'</a></b> <img src="img/down.gif"></td>';
								}
							else
								{
									echo '<td class="filacorreocabecera" width="26%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=fecha_envio&direccion=ASC">'. get_lang('Fecha') .'</a></b></td>';
								}
							}
						
						echo '</tr>';
					break;
					case 'borradores';
						echo '<tr>' .
						'<td class="filacorreocabecera" width="2%"></td>' .
						'<td class="filacorreocabecera" width="2%"></td>';
						if ( ($orden == 'asunto') && ($direccion == 'DESC'))
							{
							  echo '<td class="filacorreocabecera" width="40%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=asunto&direccion=ASC">'. get_lang('Asunto') .'</a></b> <img src="img/up.gif"></td>';
							}
						else
							{
							if ($orden == 'asunto' && $direccion == 'ASC')
								{
								  echo '<td class="filacorreocabecera" width="60%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=asunto&direccion=DESC">'. get_lang('Asunto') .'</a></b> <img src="img/down.gif"></td>';
								}
							else
								{
								  echo '<td class="filacorreocabecera" width="60%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=asunto&direccion=ASC">'. get_lang('Asunto') .'</a></b></td>';
								}
							}
						if ( ($orden == 'fecha_envio') && ($direccion == 'DESC'))
							{
							  echo '<td class="filacorreocabecera" width="36%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=fecha_envio&direccion=ASC">'. get_lang('Fecha') .'</a></b> <img src="img/up.gif"></td>';
							}
						else
							{
							if ($orden == 'fecha_envio' && $direccion == 'ASC')
								{
								  echo '<td class="filacorreocabecera" width="36%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=fecha_envio&direccion=DESC">'. get_lang('Fecha') .'</a></b> <img src="img/down.gif"></td>';
								}
							else
								{
								  echo '<td class="filacorreocabecera" width="36%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=fecha_envio&direccion=ASC">'. get_lang('Fecha') .'</a></b></td>';
								}
							}
						echo '</tr>';
					break;
					case 'eliminados';
						echo '<tr>' .
						'<td class="filacorreocabecera" width="2%"></td>';
						if ( ($orden == 'firstname1') && ($direccion == 'DESC'))
							{
							  echo '<td class="filacorreocabecera" width="20%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=firstname1&direccion=ASC">'. get_lang('Remitente') .'</a></b> <img src="img/up.gif"></td>';
							}
						else
							{
							if ($orden == 'firstname1' && $direccion == 'ASC')
								{
								  echo '<td class="filacorreocabecera" width="20%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=firstname1&direccion=DESC">'. get_lang('Remitente') .'</a></b> <img src="img/down.gif"></td>';
								}
							else
								{
								  echo '<td class="filacorreocabecera" width="20%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=firstname1&direccion=ASC">'. get_lang('Remitente') .'</a></b></td>';
								}
							}	
						if ( ($orden == 'firstname2') && ($direccion == 'DESC'))
							{
							  echo '<td class="filacorreocabecera" width="20%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=firstname2&direccion=ASC">'. get_lang('Destinatario') .'</a></b> <img src="img/up.gif"></td>';
							}
						else
							{
							if ($orden == 'firstname2' && $direccion == 'ASC')
								{
								  echo '<td class="filacorreocabecera" width="20%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=firstname2&direccion=DESC">'. get_lang('Destinatario') .'</a></b> <img src="img/down.gif"></td>';
								}
							else
								{
								  echo '<td class="filacorreocabecera" width="20%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=firstname2&direccion=ASC">'. get_lang('Destinatario') .'</a></b></td>';
								}
							}	
						if ( ($orden == 'asunto') && ($direccion == 'DESC'))
							{
							  echo '<td class="filacorreocabecera" width="35%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=asunto&direccion=ASC">'. get_lang('Asunto') .'</a></b> <img src="img/up.gif"></td>';
							}
						else
							{
							if ($orden == 'asunto' && $direccion == 'ASC')
								{
								  echo '<td class="filacorreocabecera" width="35%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=asunto&direccion=DESC">'. get_lang('Asunto') .'</a></b> <img src="img/down.gif"></td>';
								}
							else
								{
								  echo '<td class="filacorreocabecera" width="35%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=asunto&direccion=ASC">'. get_lang('Asunto') .'</a></b></td>';
								}
							}
						if ( ($orden == 'fecha_envio') && ($direccion == 'DESC'))
							{
							  echo '<td class="filacorreocabecera" width="25%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=fecha_envio&direccion=ASC">'. get_lang('Fecha') .'</a></b> <img src="img/up.gif"></td>';
							}
						else
							{
							if ($orden == 'fecha_envio' && $direccion == 'ASC')
								{
								  echo '<td class="filacorreocabecera" width="25%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=fecha_envio&direccion=DESC">'. get_lang('Fecha') .'</a></b> <img src="img/down.gif"></td>';
								}
							else
								{
								  echo '<td class="filacorreocabecera" width="25%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=fecha_envio&direccion=ASC">'. get_lang('Fecha') .'</a></b></td>';
								}
							}
						
						echo '</tr>';
					break;
					default:
						echo '<tr>' .
						'<td class="filacorreocabecera" width="2%"></td>' .
						'<td class="filacorreocabecera" width="2%"></td>';
						if ( ($orden == 'firstname') && ($direccion == 'DESC'))
							{
							  echo '<td class="filacorreocabecera" width="30%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=firstname&direccion=ASC">'. get_lang('Remitente') .'</a></b> <img src="img/up.gif"></td>';
							}
						else
							{
							if ($orden == 'firstname' && $direccion == 'ASC')
								{
								  echo '<td class="filacorreocabecera" width="30%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=firstname&direccion=DESC">'. get_lang('Remitente') .'</a></b> <img src="img/down.gif"></td>';
								}
							else
								{
								  echo '<td class="filacorreocabecera" width="30%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=firstname&direccion=ASC">'. get_lang('Remitente') .'</a></b></td>';
								}
							}		
						if ( ($orden == 'asunto') && ($direccion == 'DESC'))
							{
							  echo '<td class="filacorreocabecera" width="40%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=asunto&direccion=ASC">'. get_lang('Asunto') .'</a></b> <img src="img/up.gif"></td>';
							}
						else
							{
							if ($orden == 'asunto' && $direccion == 'ASC')
								{
								  echo '<td class="filacorreocabecera" width="40%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=asunto&direccion=DESC">'. get_lang('Asunto') .'</a></b> <img src="img/down.gif"></td>';
								}
							else
								{
								  echo '<td class="filacorreocabecera" width="40%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=asunto&direccion=ASC">'. get_lang('Asunto') .'</a></b></td>';
								}
							}
						if ( ($orden == 'fecha_envio') && ($direccion == 'DESC'))
							{
							  echo '<td class="filacorreocabecera" width="26%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=fecha_envio&direccion=ASC">'. get_lang('Fecha') .'</a></b> <img src="img/up.gif"></td>';
							}
						else
							{
							if ($orden == 'fecha_envio' && $direccion == 'ASC')
								{
								  echo '<td class="filacorreocabecera" width="26%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=fecha_envio&direccion=DESC">'. get_lang('Fecha') .'</a></b> <img src="img/down.gif"></td>';
								}
							else
								{
								  echo '<td class="filacorreocabecera" width="26%"><b><a href="index.php?dir='. $_GET['dir'] .'&pagina=' . $_GET['pagina'] .'&orden=fecha_envio&direccion=ASC">'. get_lang('Fecha') .'</a></b></td>';
								}
							}		
						echo '</tr>';
					break;
				}

				//listado de mensajes
				$mails = array ();
				$mails = CrearListadoDmail ($usuario,$_GET['dir'],$inicio,$TAMANO_PAGINA,$orden,$direccion,$buscar);

				echo '<form name="listado">';

				if (count($mails) ==0)
				{
				  echo '<tr><td colspan="5" align="center">'. get_lang('Sinmensajes') .'</td></tr>';
				}
				foreach($mails as $mail)
				{

					if ( $mail['leido'] == 0 && $_GET['dir']!='enviados')
					{
						$estilo = 'filacorreonoleida';
					}
					else
					{
						$estilo = 'filacorreoleida';
					}

					$img = 'img' . $mail['id_mail'];
					$a = 'a' . $mail['id_mail'];
					$check = $mail['id_mail'];

					if ( $mail['importante'] == 1) 
					{
						$imagen='img/new.gif';
						$enlace="javascript:MarcaFavoritos('" . $mail['id_mail'] . "','0','" . $_GET['dir'] . "');";
					}
					else
					{
						$imagen='img/new_gris.gif';
						$enlace="javascript:MarcaFavoritos('" . $mail['id_mail'] . "','1','" . $_GET['dir'] . "');";
					}


					echo '<tr onmouseover="this.style.backgroundColor = \'#fdf2a5\'" onmouseout="this.style.backgroundColor = \'#ffffff\'">' . 
					'<td class="' . $estilo. '"><input id="' . $check . '" name="chk" type="checkbox" value="' . $check . '"></td>';

					//dependiendo de la carpeta hay que mostrar unas cosas u otras

					switch ($_GET['dir'])
					{
						case 'enviados':
							//sin estrella
							echo '<td class="' . $estilo . '">&nbsp</td>';
							//destinatario
							echo '<td style="cursor:pointer" onclick="location=\'lectura.php?id=' . $mail['id_mail'] . '&dir=' . $_GET['dir'] .'\'" class="' . $estilo . '">' . $mail['firstname'] . ' ' . $mail['lastname'] . '</td>';
							//asunto
							echo '<td style="cursor:pointer" onclick="location=\'lectura.php?id=' . $mail['id_mail'] . '&dir=' . $_GET['dir'] .'\'" class="' . $estilo . '">'; 
							if ($mail['id_adjunto'] != "")
							{
								echo '<img src="img/adjunto.gif">  ';
							}
							if ($mail['asunto'] != "")
							{
								echo $mail['asunto'];
							}
							else
							{
							  echo get_lang('Sinasunto');
							}
							echo '</td>';
							//fecha envio
							echo '<td style="cursor:pointer" onclick="location=\'lectura.php?id=' . $mail['id_mail'] . '&dir=' . $_GET['dir'] .'\'" class="' . $estilo . '">'. $mail['fecha_envio'] .'</td></tr>';
						break;
						case 'borradores':
							//con estrella
							//echo '<td class="' . $estilo . '"><a href="' . $enlace . '" id = "'. $a .'"><img id="' .$img . '" src="' . $imagen.'"></a></td>';
							//sin estrella
							echo '<td class="' . $estilo . '">&nbsp</td>';
							//no se ponen ni remitente ni destinatario
							//asunto
							echo '<td style="cursor:pointer" onclick="location=\'lectura.php?id=' . $mail['id_mail'] . '&dir=' . $_GET['dir'] .'\'" class="' . $estilo . '">'; 
							if ($mail['id_adjunto'] != "")
							{
								echo '<img src="img/adjunto.gif">  ';
							}
							if ($mail['asunto'] != "")
							{
								echo $mail['asunto'];
							}
							else
							{
							  echo get_lang('Sinasunto');
							}
							echo '</td>';
							//fecha envio
							echo '<td style="cursor:pointer" onclick="location=\'lectura.php?id=' . $mail['id_mail'] . '&dir=' . $_GET['dir'] .'\'" class="' . $estilo . '">'. $mail['fecha_envio'] .'</td></tr>';
						break;
						case 'eliminados':
							//remitente
							echo '<td style="cursor:pointer" onclick="location=\'lectura.php?id=' . $mail['id_mail'] . '&dir=' . $_GET['dir'] .'\'" class="' . $estilo . '">' . $mail['firstname1'] . ' ' . $mail['lastname1'] . '</td>';
							//destinatario
							echo '<td style="cursor:pointer" onclick="location=\'lectura.php?id=' . $mail['id_mail'] . '&dir=' . $_GET['dir'] .'\'" class="' . $estilo . '">' . $mail['firstname2'] . ' ' . $mail['lastname2'] . '</td>';
							//asunto
							echo '<td style="cursor:pointer" onclick="location=\'lectura.php?id=' . $mail['id_mail'] . '&dir=' . $_GET['dir'] .'\'" class="' . $estilo . '">'; 
							if ($mail['id_adjunto'] != "")
							{
								echo '<img src="img/adjunto.gif">  ';
							}
							if ($mail['asunto'] != "")
							{
								echo $mail['asunto'];
							}
							else
							{
							  echo get_lang('Sinasunto');
							}
							echo '</td>';
							//fecha envio
							echo '<td style="cursor:pointer" onclick="location=\'lectura.php?id=' . $mail['id_mail'] . '&dir=' . $_GET['dir'] .'\'" class="' . $estilo . '">'. $mail['fecha_envio'] .'</td></tr>';
						break;
						default:
							//con estrella
							echo '<td class="' . $estilo . '"><a href="' . $enlace . '" id = "'. $a .'"><img id="' .$img . '" src="' . $imagen.'"></a></td>';
							//remitente
							echo '<td style="cursor:pointer" onclick="location=\'lectura.php?id=' . $mail['id_mail'] . '&dir=' . $_GET['dir'] .'\'" class="' . $estilo . '">' . $mail['firstname'] . ' ' . $mail['lastname'] . '</td>';
							//asunto
							echo '<td style="cursor:pointer" onclick="location=\'lectura.php?id=' . $mail['id_mail'] . '&dir=' . $_GET['dir'] .'\'" class="' . $estilo . '">'; 
							if ($mail['id_adjunto'] != "")
							{
								echo '<img src="img/adjunto.gif">  ';
							}
							if ($mail['asunto'] != "")
							{
								echo $mail['asunto'];
							}
							else
							{
							  echo get_lang('Sinasunto');
							}
							echo '</td>';
							//fecha envio
							echo '<td style="cursor:pointer" onclick="location=\'lectura.php?id=' . $mail['id_mail'] . '&dir=' . $_GET['dir'] .'\'" class="' . $estilo . '">'. $mail['fecha_envio'] .'</td></tr>';
						break;
					}
				}
				  echo '<tr><td colspan="5" align="center" class="filacorreocabecera"><center>'. get_lang('Pagina') .' ' . $pagina . ' '. get_lang('De') .' ' . $total_paginas . '</center></td></tr>';

				echo '</form>';
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

