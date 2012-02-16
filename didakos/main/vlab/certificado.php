<?php
include("funciones.php");
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"certificado.pem\"");
$datos = getPeticionInfo($_GET['id']);
if($datos["status"] == "ERROR"){
die(get_lang('langWrongMachineId'));
}else{
//echo $datos[0]["fields"]["key"];
echo($datos[1]["fields"]["key"]);
}
?>