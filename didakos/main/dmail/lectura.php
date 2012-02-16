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
$htmlHeadXtra[] = '<script languaje="javascript"></script>';

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


//HAY QUE MARCAR COMO LEIDO EL CORREO QUE ACABAMOS DE ABRIR

MarcarLeido ($_GET['id'],$usuario);

?>
<div name="contenedor" class="contenedor">
	<div name="lateral" class="lateral">
		<table name="carpetas" class="carpetas">
			<tr>
			  <td class="titulo"><?=get_lang('Carpetas_per')?></td>
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
				echo '<tr><td class=""><br><img src="img/gestion_carpetas.gif"><a class="tool-icon" href="carpetas.php?action=listar"> '.get_lang('Gestionar_carpetas').'</a></td></tr>';

			?>
		</table>
	</div>

	<div name="central" class="central">
		<div name="anuncios" class="anuncios">
			<table name="anuncios" class="anuncios">
				<tr>
					<td>
					<?php
						if (isset ( $_GET['anuncio']))
						{
							echo $_GET['anuncio'];
						}
					?>
					</td>
				</tr>
			</table>
		</div>

		<div name="herramientas" class="herramientas">
			<table name="herramientas" class="herramientas">
				<tr>
					<td width="80%" align="left">
					<img src="img/respuesta.gif"><a class="tool-icon" href="redactar.php?action=respuesta&id=<?php echo $_GET['id'] . '&dir=' . $_GET['dir']; ?>" target="_self"><?=get_lang('Responder')?></a>
					| <img src="img/reenvio.gif"> <a class="tool-icon" href="redactar.php?action=reenvio&id=<?php echo $_GET['id'] . '&dir=' . $_GET['dir']; ?>" target="_self"><?=get_lang('Reenviar')?></a>
					<?php
						
					?>
					</td>
				</tr>
			</table>
		</div>

		<?
		$dmail = DmailRead ($_GET['id'],$usuario,$_GET['dir']);	
		foreach($dmail as $mail)
		{
		?>
		<div name="panel" class="panel">
			<table name="panel" class="panel">
				<tr>
					<td class="filacorreonoleida" width="10%">
					<b><?=get_lang('Remitente')?>:</b> 
					</td>
					<td class="filacorreonoleida" width="90%">
						<input name="remitente" type="text" size="50" readonly="readonly" value="<?php echo $mail['firstname1'] . ' ' . $mail['lastname1'];?>">
						<input name="id_remitente" type="hidden" size="50">
					</td>
				</tr>
				<tr>
					<td class="filacorreonoleida" width="10%">
						<b><?=get_lang('Destinatario')?>: </b>
					</td>
					<td class="filacorreonoleida" width="90%">
						<input name="destinatarios" type="text" size="50" readonly="readonly" value="<?php echo $mail['firstname2'] . ' ' . $mail['lastname2'];?>">
						<input name="id_destinatarios" type="hidden" size="50">
					</td>
				</tr>
				<tr>
					<td class="filacorreonoleida" width="10%">
					  <b><?=get_lang('Asunto')?>: </b>
					</td>
					<td class="filacorreonoleida" width="90%">
						<input name="Asunto" type="text" size="60" readonly="readonly" value="<?php echo $mail['asunto'];?>">
					</td>
				</tr>
				<tr>
					<td class="filacorreonoleida" width="10%">
						<b><?=get_lang('Adjunto')?>: </b>
					</td>
					<td class="filacorreonoleida" width="90%">
						<?php
							if ($mail['id_adjunto']!='')
							{
								//Aqui insertamos un enlace al archivo
								echo '<img src="img/adjunto.gif">  ';
								echo '<a class="tool-icon" href="descargar_archivo.php?id=' .  $mail['id_adjunto'] . '">' .  $mail['nombre'] . ' (' . round($mail['size']/1024,2) . ' Kb)</a>';
							}
							else
							{
							  echo get_lang('Sin_adjunto');
							}
						?>
					</td>
				</tr>
				<tr>
					<td class="filacorreonoleida" width="10%">
					  <b><?=get_lang('Contenido')?>: </b>
					</td>
					<td class="filacorreonoleida" width="90%">
					<textarea cols="80" id="editor_kama" name="editor_kama" rows="10"><? echo $mail['contenido']; ?></textarea>
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
			</table>
		</div>
		<?php   } ?>
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
