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
include_once("../inc/lib/fckeditor/fckeditor.php") ;

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

if (isset($_POST['crear']))
{
	//Estamos creando una carpeta. revisamos el nombre de la carpeta
	if ($_POST['nombre'] == '')
	{
	  $anuncio = get_lang('Nombre_carpeta_no_nulo');
	} 
	else
	{
		 // Revisamos que la carpeta no exista ya para este usuario
		$existe = Existecarpeta ($usuario,$_POST['nombre']);	
	
		if ($existe==1)
		{
		  $anuncio = get_lang('Ya_existe_carpeta');
		}
		else
		{
			// Creamos la carpeta
		  $anuncio = get_lang('La_carpeta'). ' <b>' . $_POST['nombre'] . ' </b>'. get_lang('CreadaCorrecta');
			Crearcarpeta  ($usuario,$_POST['nombre'],'false');
		}
	}
}
else
{
	if (isset($_POST['modificar']))
		{
			if ($_POST['nombre'] == '')
			{
			  $anuncio = get_lang('Nombre_carpeta_no_nulo');
			} 
			else
			{
				 // Revisamos que la carpeta no exista ya para este usuario
				$existe = Existecarpeta ($usuario,$_POST['nombre']);	

				if ($existe==1)
				{
				  $anuncio = get_lang('Ya_existe_carpeta');
				}
				else
				{
					// Modificamos la carpeta
				  $anuncio = get_lang('La_carpeta') .' <b>' . $_POST['nombre'] . '</b> '.get_lang('CarpetaModificadaCorrecta');// ha sido modificada correctamente, los mensajes que conten&iacute;a han sido movidos a Recibidos';
					Modificarcarpeta  ($_POST['id_carpeta'], $usuario,$_POST['nombre']);
				}
			}
		}
}

if ( $_GET['action']=='eliminar' && isset($_GET['id']) )
{
	Eliminarcarpeta ($usuario,$_GET['id']);
	$anuncio = get_lang("CarpetaEliminadaCorrecta");
}

// Put your main code here. Keep this section short,
// it's better to use functions for any non-trivial code

api_display_tool_title($tool_name);

?>
<div name="contenedor" class="contenedor">
	<div name="lateral" class="lateral">
		<table name="carpetas" class="carpetas">
			<tr>
				<td class="titulo">Carpetas Locales</td>
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
			  <td class="titulo"><?=get_lang('Carpetas_per')?></td>
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
				echo '<tr><td class=""><br><img src="img/gestion_carpetas.gif"><a class="tool-icon" href="carpetas.php?action=listar">'.get_lang('Gestionar_carpetas').'</a></td></tr>';

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
						if ($_GET['action']=='crear' || $_GET['action']=='modificar')
						{
							echo '<td width="100%" align="left">' .						
							  '<a class="tool-icon" href="carpetas.php?action=listar">'. get_lang('Volverlistado').'</a>' .
							'</td>';
						}
						else
						{
							echo '<td width="100%" align="left">' .						
							  '<img src="img/carpeta_add.gif"> <a class="tool-icon" href="carpetas.php?action=crear">'.get_lang('Crearcarpeta').'</a>' .
							'</td>';
						}
					?>
				</tr>
			</table>
		</div>

		<div name="panel" class="panel">
			<table name="panel" class="panel">
			<?php
				switch ( $_GET['action'])
				{
					case 'crear':
					//Formulario de creación de carpetas
						?>
						<form name="crear" action="carpetas.php" method="post">
						<tr>
						  <td class="filacorreonoleida" width="20%"><b><?php echo get_lang('Nombre_carpeta')?>: </b></td>
							<td class="filacorreonoleida" width="80%">
							<input name="nombre" type="text" size="30">
							<input type="submit" value="<?php echo get_lang('Guardar')?>" name ="crear">
							</td>
						</tr>
						<?php
					break;
					case 'modificar':
						$carpeta = Nombrecarpeta ($usuario,$_GET['id']);

						?>
						<form name="crear" action="carpetas.php" method="post">
						<tr>
						  <td class="filacorreonoleida" width="20%"><b><?=get_lang('Nombre_actual')?>:</b></td>
							<td class="filacorreonoleida" width="20%"><?php echo $carpeta ?></td>
							<td class="filacorreonoleida" width="20%"><b><?php echo get_lang('Nombre_nuevo')?>:</b></td>
							<td class="filacorreonoleida" width="20%"><input name="nombre" type="text" size="30" value="">
							<input name="id_carpeta" type="hidden" value="<?php echo $_GET['id'] ?>"></td>
							<td class="filacorreonoleida" width="10%"><input type="submit" value="<?=get_lang('Guardar')?>" name ="modificar"></td>
						</tr>
						<?php
					break;	
					case 'listar':
						$carpetas = Carpetaspersonales ($usuario);
						echo '<form name="listado" action = "carpetas.php" method="post">';
						foreach($carpetas as $carpeta)
						{
							echo '<tr>' .
							'<td class="filacorreoleida" width="60%">' . $carpeta['nombre'] . '</td>' .
							'<td class="filacorreoleida" width="20%"> <a href="?action=modificar&id=' . $carpeta['id_carpeta'] . '"><img alt="Editar" src="img/carpeta_edit.gif"></a></td>' .
							'<td class="filacorreoleida" width="20%"> <a href="?action=eliminar&id=' . $carpeta['id_carpeta'] . '"><img alt="Eliminar" src="img/carpeta_delete.gif"></a></td>' .
							'</tr>';
						}
						echo '</form>';
					break;
					default:
						$carpetas = Carpetaspersonales ($usuario);
						echo '<form name="listado" action = "carpetas.php" method="post">';
						foreach($carpetas as $carpeta)
						{
							echo '<tr>' .
							'<td class="filacorreoleida" width="60%">' . $carpeta['nombre'] . '</td>' .
							'<td class="filacorreoleida" width="20%"> <a href="?action=modificar&id=' . $carpeta['id_carpeta'] . '"><img alt="Editar" src="img/carpeta_edit.gif"></a></td>' .
							'<td class="filacorreoleida" width="20%"> <a href="?action=eliminar&id=' . $carpeta['id_carpeta'] . '"><img alt="Eliminar" src="img/carpeta_delete.gif"></a></td>' .
							'</tr>';
						}
						echo '</form>';
					break;
				}
			?>
			</table>
		</div>
	</div> 
</div>
<?php
/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>
