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
$usuario = Database::escape_string(api_get_user_id());

// ESto hay que meterlo aqui pq al haber una redireccion debe salir antes de meterle nada.

if (isset($_POST['mover']))
{
	MueveDmail ($_POST['id'],$_POST['carpeta_origen'],$_POST['carpeta_destino'],$usuario);
	
	switch ($_POST['carpeta_destino'])
	{
		case '1':
		$carpeta_destino = 'recibidos';
		break;
		default:
		$carpeta_destino = $_POST['carpeta_destino'];
		break;
	}

	header ("Location:index.php?dir=" . $carpeta_destino . "&anuncio=" . get_lang('DmailMovidoCorrecto'));

}
else
{
  $anuncio = get_lang('Selcarpeta_mover');/*"Seleccione la carpeta a la que desea mover los mensaje seleccionados";*/
	switch ($_GET['carpeta_origen'])
	{
		case 'recibidos':
		$carpeta_origen = 1;
		break;
		default:
		$carpeta_origen = $_GET['carpeta_origen'];
		break;
	}
}	
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
$htmlHeadXtra[] = '<script languaje="javascript"></script>';

$tool_name = "D-mail"; // title of the page (should come from the language file) 
Display::display_header($tool_name);



	
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

?>
<div name="contenedor" class="contenedor">
	<div name="lateral" class="lateral">
		<table name="carpetas" class="carpetas">
			<tr>
			  <td class="titulo"><?php echo get_lang('Carpetas_loc');?></td>
			</tr>
			<tr>
			<?php $total= CuentaDmailCarpeta ($usuario,1);?>
  <td class=""><img src="../img/folder.gif"> <a class="tool-icon" href="index.php?dir=recibidos" target="_self"><?php echo get_lang('Recibidos');?></a> (<?php echo $total; ?>)</td>
			</tr>
			<tr>
			<?php $total= CuentaDmailCarpeta ($usuario,4);?>
<td class=""><img src="../img/folder.gif"> <a class="tool-icon" href="index.php?dir=destacados" target="_self"><?php echo get_lang('Destacados');?></a> (<?php echo $total; ?>)</td>
			</tr>
			<tr>
			<?php $total= CuentaDmailCarpeta ($usuario,2);?>
<td class=""><img src="../img/folder.gif"> <a class="tool-icon" href="index.php?dir=enviados" target="_self"><?php echo get_lang('Enviados');?></a> (<?php echo $total; ?>)</td>
			</tr>
			<tr>
			<?php $total= CuentaDmailCarpeta ($usuario,3);?>
			<td class=""><img src="../img/folder.gif"> <a class="tool-icon" href="index.php?dir=borradores" target="_self"><?php echo get_lang('Borradores')?></a> (<?php echo $total; ?>)</td>
			</tr>
			<tr>
			<?php $total= CuentaDmailCarpeta ($usuario,0);?>
			<td class=""><img src="../img/folder.gif"> <a class="tool-icon" href="index.php?dir=eliminados" target="_self"><?php echo get_lang('Eliminados')?></a> (<?php echo $total; ?>)</td>
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
					echo '<tr><td class=""><img src="../img/folder.gif"> <a class="tool-icon" href="index.php?dir=' . $carpeta['id_carpeta'] .'" target="_self">' . $carpeta['nombre'] .'</a> ('.$total .')</td></tr>';
				}
			echo '<tr><td class=""><br><img src="img/gestion_carpetas.gif"><a class="tool-icon" href="carpetas.php?action=listar">'. get_lang('Gestionar_carpetas') . '</a></td></tr>';

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
					<td></td>
				</tr>
			</table>
		</div>

		<div name="panel" class="panel">
			<table name="panel" class="panel">
			<form name="borrar" action="mover.php" method="post">
				<tr>
				  <td class="filacorreonoleida" width="20%"><b><?php echo get_lang('Selcarpeta_destino');?>: </b></td>
					<td class="filacorreonoleida" width="80%">
					<select name="carpeta_destino" size="1">

					<?php
						if ($carpeta_origen!=1)
						{
							echo '<option value="1">'. get_lang('Recibidos').'</option>';
						}
							

						$carpetas = Carpetaspersonales ($usuario);
						foreach($carpetas as $carpeta)
							{
								echo '<option value="'. $carpeta['id_carpeta'] .'">'. $carpeta['nombre'] . '</option>';
							}
					?>
					</select>
					<input type="hidden" name="id" value="<?php echo $_GET['id'] ?>">
					<input type="hidden" name="carpeta_origen" value="<?php echo $carpeta_origen ?>">
					<input type="submit" value="<?php echo get_lang('Mover');?>" name ="mover">
					</td>
				</tr>
			</form>
				<tr>
					<td class="filacorreonoleida" colspan="2"><b><?php echo get_lang('DmailAMover');?></td>
				</tr>
			<?php
				$listado = ListaMover ($_GET['id'],$usuario);
				foreach($listado as $dmail)
				{
					echo '<tr>' .
					'<td colspan="2" class="filacorreoleida">Dmail (' . $dmail['asunto']. ', ' . $dmail['fecha_envio'] . ')</td>' .
					'</tr>';
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
