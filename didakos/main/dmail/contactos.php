<?php
include('../inc/global.inc.php'); 
require_once ('dmail_functions.inc.php');

$curso = $_SESSION['_course']['id'];
$usuarios = Usuariosagenda ($curso);
echo '<table class="agenda">
	<tr>
		<td width="10"></td>
		<td align="center">
		<input type="button" value="Aceptar" onclick="javascript:add_destinatarios();">
		<input type="button" value="Cancelar" onclick="javascript:cerraragenda();">
		</td>
		<td width="10"></td>
	</tr>
	<tr>
	<td width="10">
	<td>
		<p align="left"><b>Seleccionar:</b> <a onclick="javascript:sel_destinatarios(1);">Todos</a> , <a onclick="javascript:sel_destinatarios(0);">Ninguno</a></p>
	</td>
	<td width="10"></td>
	</tr>
</table>';

echo '<form name="formagenda">';
echo '<table class="agenda">
		<tr>
			<td width="10"></td>
			<td class="agenda"><b>Enviar</b></td>
			<td class="agenda"><b>Nombre</b></td>
			<td class="agenda"><b>Apellidos</b></td>
			<td class="agenda"><b>Status</b></td>
			<td width="10"></td>
		</tr>';

foreach($usuarios as $usuario)
{
		echo '<tr>' . 
			'<td width="10"></td>' .
			'<td class="agenda" width="10%"><input type="checkbox" name="chk" value="'. $usuario['user_id'] .'"><input type="hidden" name="datos_agenda_'. $usuario['user_id'] .'" value="'. $usuario['firstname'] . ' ' .  $usuario['lastname'] . '"></td>';
			if ($usuario['status']==5)
			{
			    echo '<td class="agenda" width="35%">' . utf8_encode($usuario['firstname']) . '</td>' .
			    '<td class="agenda" width="35%"> ' . utf8_encode($usuario['lastname']) . '</td>' .
			    '<td class="agenda" width="10%">Alumno</td>';
			}
			else
			{
			    if($usuario['status']==1)
			    {
			        echo '<td class="agenda" width="35%"><b>' . utf8_encode($usuario['firstname']) . '</b></td>' .
			        '<td class="agenda" width="35%"><b> ' . utf8_encode($usuario['lastname']) . '</b></td>' .
			        '<td class="agenda" width="10%"><b>Tutor</b></td>';    			
   			    }
   			}
    
		echo '<td width="10"></td>' .
			'</tr>';
}
echo "</form></table><br>";
?>
<input type="button" value="Aceptar" onclick="javascript:add_destinatarios();">
<input type="button" value="Cancelar" onclick="javascript:cerraragenda();">
