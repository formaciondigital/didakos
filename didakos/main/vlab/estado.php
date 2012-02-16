<?php
include("../inc/global.inc.php");
include("funciones.php");
$id = $_GET['id'];
$datos = getPeticionInfo($id);
$info = $_GET['info'];
//print_r($datos);
$tabla2 = "";

switch($info)
 {
 
 case "estado":

					  if($datos["status"] == "ERROR")
					 {
					  $tabla2 .= '<img id="img_espera" src=img/sand_clock.jpg  border="0">';
					 }else{
							if($datos[0]["fields"]["state"] == "running")
							{
							  $tabla2 .= '<img id="img_espera" src=img/bombilla_on.gif  border="0">';
							}
					  }
					  break;
					  
 case "detener":

					  if($datos["status"] == "ERROR")
					 {
					  $tabla2 .= '';
					 }else{
							if($datos[0]["fields"]["state"] == "running")
							{
							  $tabla2 .= '<img src="img/Stop.gif" border="0"/>';
							}else{
							  $tabla2 .= '';
							}
					  }
					  break;
					  
  case "inicio":

					  if($datos["status"] == "ERROR")
					 {
					  $tabla2 .= '';
					 }else{
							if($datos[0]["fields"]["state"] == "running")
							{
							  $tabla2 .= $datos[0]["fields"]["creation_date"];
							}else{
							  $tabla2 .= '';
							}
					  }
					  break;
					  
   case "fin":

					  if($datos["status"] == "ERROR")
					 {
					  $tabla2 .= '';
					 }else{
							if($datos[0]["fields"]["state"] == "running")
							{
							  $tabla2 .= $datos[0]["fields"]["estimated_termination_date"];
							}else{
							  $tabla2 .= '';
							}
					  }
					  break;
					  
				
	case "conexion":

					 	if($datos["status"] == "ERROR")
					 {
					  $tabla2 .= "";
					 }else{
							if($datos[0]["fields"]["state"] == "running")
							{
							  $tabla2 .= '<a href="#" onClick="open_overlay();return false;">'.  get_lang('langShow').'</a><div id="overlay"></div><div id="media"><a href="javascript:close_overlay()">['.  get_lang('Close').']</a><br/><br/>&nbsp;&nbsp;<b>IP:</b> '.$datos[0]["fields"]["public_dns_name"].'<br/><br/><a href="certificado.php?id='.$id.'" target="_blank">'.  get_lang('langDownloadCert').'</a>';
							}else{
							  $tabla2 .= "";
							}
					  }
					  break;





					  
					  
					  
}				  
					  
echo $tabla2;
?>