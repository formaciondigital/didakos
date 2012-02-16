<?php
include('../inc/global.inc.php'); 
require_once ('dmail_functions.inc.php');

//recogemos el id
$id_adjunto = $_GET['id'];
$usuario = Database::escape_string(api_get_user_id());

//revisamos que se tenga realmente acceso a dicho archivo (seguridad por aquello de venir por get)

if (RevisarPermisoArchivo ($id_adjunto, $usuario) > 0)
	{
		//descargamos
		$adjunto = LeerAdjunto ($id_adjunto);	
		header("Content-type: $adjunto->tipo");
		header("Content-length: $adjunto->size");
		header("Content-Disposition: attachment;  filename=$adjunto->nombre"); 
		echo $adjunto->archivo;
	}
?>
