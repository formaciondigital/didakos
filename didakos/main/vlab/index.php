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
*	This file is a code template; 
*	copy the code and paste it in a new file to begin your own work.
*
*	@package dokeos.plugin
==============================================================================
*/
	
/*
==============================================================================
		INIT SECTION
==============================================================================
*/ 
// global settings initialisation 
// also provides access to main, database and display API libraries
$language_file[] = 'document';
$language_file[] = 'slideshow';
$language_file[] = 'vlab';
include("../inc/global.inc.php");
include("funciones.php");
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
$this_section=SECTION_COURSES;
$dbl_click_id = 0; // used to avoid double-click
$is_allowed_to_edit = api_is_allowed_to_edit();
$curdirpathurl = 'vlab';




$htmlHeadXtra[] = '
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript">

function ConsultaEstado(id){
    $("#capa_estado").load("estado.php?info=estado&id="+id);
	 $("#capa_detener").load("estado.php?info=detener&id="+id);
	  $("#capa_inicio").load("estado.php?info=inicio&id="+id);
	   $("#capa_fin").load("estado.php?info=fin&id="+id);
	    $("#datos_conexion").load("estado.php?info=conexion&id="+id);
}

//comprobamos el estado de la maquina
function CompruebaEstado(id){
id_peticion = id;
$("#img_espera").attr("src","img/bigrotation.gif");
ConsultaEstado(id_peticion);
}

//cada 20 seg comprueba el estado de la maquina
function ActualizaEstado(id){
variable = id;
setInterval("CompruebaEstado(variable)",30000);
}




</script>

<style type="text/css">
#overlay {
visibility:hidden;
display:none;
position:absolute;
top:0px;
left: 0px;
z-index: 9;
width: 100%;
height: 100%;
background-color: #000000;
opacity:0.65;
}
#media {
visibility:hidden;
position:absolute;
display:none;
left: 30%;
top:65px;
z-index: 10;
width: 600px;
height: 300px;
background-color: #fff;
border:1px solid #454545;
}
*:first-child+html body #overlay {
filter: alpha(opacity=65);
} * html #overlay {
filter: alpha(opacity=65);
}
</style>
<script language="javascript">
function open_overlay(mediatype, url) {
document.getElementById("overlay").style.display="block";
document.getElementById("media").style.display="block";
document.getElementById("overlay").style.visibility="visible";
document.getElementById("media").style.visibility="visible";
var _docHeight = document.body.offsetHeight;
document.getElementById("overlay").style.height=_docHeight;
}
function close_overlay() {
document.getElementById("overlay").style.visibility="hidden";
document.getElementById("media").style.visibility="hidden";
}


$(document).ready(function(){
      /**/
	  $(\'#form_practica\').css(\'display\', \'none\');
      $(\'#img_formulario\').click(function(){
				$(\'#form_practica\').slideToggle(300);
			});


});

</script>';


/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/ 
//the main_api.lib.php, database.lib.php and display.lib.php
//libraries are included by default

	
/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/ 


$tool_name = "Vlab"; // title of the page (should come from the language file) 
Display::display_header($tool_name);
api_display_tool_title($tool_name);
$interbreadcrumb[]= array ('url'=>'', 'name'=> 'Vlab');
$dir_array=explode("/",$curdirpath);
$array_len=count($dir_array);
$is_allowed_to_edit  = api_is_allowed_to_edit();
$id_curso=$_SESSION[_cid];



//Estas 3 funcines pintan una tabla, esta dividida en tres partes,
// ## FdTablaCabecera: Pinta el inicio de la tabla y la primera fila como cabecera. Recibe un array de una dimensión con tantos valores como columnas deseamos. También puede recibir contenido extra para la tabla, el TR y el TH (html)
// ## FdTablaFila: Pinta las filas recibiendo un array. También puede recibir contenido extra para la tabla, el TD (html)
// ## FdTablaFila: Pinta el fron de tabla
// *****************************************************************************************
function FdTablaCabecera($columnas,$extraTabla=null, $extraTR=null, $extraTH=null)
{
	$tabla = '<table ' . $extraTabla .  '><thead><tr ' . $extraTR .'>';
	for ($i = 0; $i < count($columnas); $i++)
		{
		$tabla .= '<th ' . $extraTH . '><b>' . $columnas[$i] . '</b></th>'; 
		}
	$tabla .= '</tr></thead>';
	echo $tabla;
}

//esta función pinta todas las filas de una tabla, recibe un array.

function FdTablaFila($columnas, $extraTD=null)
{

	if (count($columnas)>0)
	{
		foreach ($columnas as $columna)
			{
				$tabla .= '<tr>';
				//$tabla .= '<td ' . $extraTD . '>' . $columna['name'] . '</td>'; 
				$tabla .= '<td ' . $extraTD . '>' . utf8_decode($columna['description']) . '</td>'; 
				// GetPETICION_ID($columna['name']); 
				// Generamos la cadena de petición
				// si el usuario tiene peticiones de maquinas activas no le permitimos que solicite nuevas maquinas 
				$tabla .= '<td ' . $extraTD . '><div name="status" align="center"><a href="conecta.php?uid='.$columna['id_practica'].'"><img src="img/server.gif" border="0"></a></div></td>'; 
                                 if(api_is_allowed_to_edit())
                                {
                                $tabla .= '<td ' . $extraTD . '><div name="status" align="center"><a href="index.php?action=delete&uid='.$columna['id_practica'].'"><img src="../img/delete.gif" border="0"></a></div></td>';
                                }
				$tabla .= '</tr>';
			}
	}
	else
	{
		$tabla .= '<tr>';
		$tabla .= '<td colspan="4"' . $extraTD . '>No hay m&aacute;quinas virtuales disponibles</td>'; 
		$tabla .= '</tr>';
	}
	return $tabla;
}




function FdTablaFilaPendiente($columnas, $extraTD=null)
{
	if (count($columnas)>0)
	{
		foreach ($columnas as $columna)
			{
				

				$tabla .= '<tr>';
				//$tabla .= '<td ' . $extraTD . '>' . $columna['name'] . '</td>'; 
				$tabla .= '<td ' . $extraTD . '>' . utf8_decode($columna['description']) . '</td>'; 
				if(isPracticaOn($columna['id_practica']))
				{
				//si la maquina esta encendida es que tienen una peticion activa,obtenemos el id de esa peticion
				$peticiones = GetPeticionesAbiertasXPractica($columna['id_practica']);
                $ultima_peticion = $peticiones[0];
				//obtenemos los datos de esta peticion para mostrar la fecha de inicio y de fin previstas
				$info = getPeticionInfo($ultima_peticion);
				//print_r($info);
			     $tabla .= "<script language=\"javascript\">ActualizaEstado('".$ultima_peticion."');</script>";
					 if($info["status"] == "ERROR")
					 {
					  $tabla .= '<td ' . $extraTD . '><div id="capa_estado" align="center"><img id="img_espera" src=img/sand_clock.jpg  border="0"></div></td>';
					  $tabla .= '<td ' . $extraTD . '><a href = "detener.php?uid='.$columna['id_practica'].'"><div id="capa_detener" align="center"></div></a/</td>';	
					  $tabla .= '<td ' . $extraTD . '><div id="capa_inicio" align="center"></div></td>'; 
				          $tabla .= '<td ' . $extraTD . '><div id="capa_fin" align="center"></div></td>'; 
					  $tabla .= '<td ' . $extraTD . '><div id="datos_conexion" align="center"></div></td>'; 
 

			
					 }else{
					  $tabla .= '<td ' . $extraTD . '><div id="capa_estado" align="center"><img id="img_espera" src=img/bombilla_on.gif border="0"></div></td>';
					  $tabla .= '<td ' . $extraTD . '><a href = "detener.php?uid='.$columna['id_practica'].'">  <div id="capa_detener" align="center"><img src="img/Stop.gif" border="0"/></div></a></td>';
					  $tabla .= '<td ' . $extraTD . '><div id="capa_inicio" align="center">'.$info[0]["fields"]["creation_date"].'</div></td>'; 
				          $tabla .= '<td ' . $extraTD . '><div id="capa_fin" align="center">'.$info[0]["fields"]["estimated_termination_date"].'</div></td>'; 
					  $tabla .= '<td  align="center"' . $extraTD . '><div id="datos_conexion" ><a href="#" onClick="open_overlay();return false;">'.get_lang('langShow').'</a></div><div id="overlay"></div>';
					  $tabla .= '<div id="media"><a href="javascript:close_overlay()">['.get_lang('Close').']</a><br/><br/>&nbsp;&nbsp;<b>IP:</b> '.$info[0]["fields"]["public_dns_name"]; 
					  $tabla .= '<br/><br/><a href="certificado.php?id='.$ultima_peticion.'" target="_blank">'.get_lang('Download').' '.get_lang('langCertificate').'</a>';
					  $tabla .= '</div></td>';
 
				     }

				}else{
				  $tabla .= '<td align="center"><img  src="img/server_dis.gif" border="0"></td>';
				  $tabla .= '<td ' . $extraTD . '></td>';
				  $tabla .= '<td ' . $extraTD . '></td>'; 
				  $tabla .= '<td ' . $extraTD . '></td>'; 
				  $tabla .= '<td ' . $extraTD . '></td>'; 

 
				}
				$tabla .= '</tr>';
			}
	}
	else
	{
		$tabla .= '<tr>';
		$tabla .= '<td colspan="4"' . $extraTD . '>'.get_lang('langNoMachinesAval').'</td>'; 
		$tabla .= '</tr>';
	}
	return $tabla;
}


function FdTablaFin()
{
	return '</table>';
}



/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 




?>


<div style="width:194px; float: left"><img src="img/vlab.jpg" width="194" height="142" border="0"></div>
<div style="  margin-left: 220px;  border: 1px solid #97979B; padding:10px; ">
<?php 
echo "<p>".get_lang('langhead')."</p>";
echo "<p>1. ".get_lang('landStep1')."<br />";
echo "<p>2. ".get_lang('landStep2')."<img src=\"img/server.gif\" width=\"24\" height=\"24\" /></p>";
echo "<p>3. ".get_lang('landStep3')."<img src=\"img/sand_clock.jpg\" width=\"32\" height=\"32\" /></p>";
echo "<p>4. ".get_lang('landStep4')."<img src=\"img/bombilla_on.gif\" width=\"24\" height=\"24\" /><br></p>";
echo "<p>5. ".get_lang('landStep5')."<img src=\"img/Stop.gif\" width=\"24\" height=\"24\" /></p>";
echo "<p>6. ".get_lang('landStep6')."<img src=\"img/server_dis.gif\" width=\"32\" height=\"32\" /></p>";
?>
</div>
<div>
                <?php                
                //creacion y eliminacion de precticas solo los usuarios con permisos
                if(api_is_allowed_to_edit())
                {

                 echo  '<span id="img_formulario" style="cursor:pointer; font-weight:bold">'.Display::return_icon('valves_add.gif').' '.get_lang('Add').'</span>';
			     echo  "<div id='form_practica'>";
				 
                                    
				    $form = new FormValidator('crea_practica');

					$form->add_textfield( 'descripcion', 'Descripcion',true,array('size'=>'100','maxlength'=>250));
					$form->add_textfield( 'id_maquina', 'Virtual ami',true,array('size'=>'20','maxlength'=>10));

					$form->addElement('submit', null, get_lang('Ok'));
					            if( $form->validate())
								{
                                                                    $datos = $form->exportValues();
                                                                    
                                                                     $ok = insertaPractica($datos['descripcion'],$datos['id_maquina'],$id_curso);
                                                                    
                                                                     if(!$ok){
                                                                             Display::display_error_message('Error al insertar: '.mysql_error());
                                                                     }else{

                                                                             Display::display_confirmation_message('Practica insertada');
                                                                     }
					
					
								}else{
									$form->display();
						          }
                                                          
                                    if($_GET['action'] == 'delete' && $_GET['uid'] != ''){
                                            $res_del = eliminaPractica($_GET['uid'], $id_curso);
                                            if(!$res_del){
                                                     Display::display_error_message('Error al eliminar: '.mysql_error());
                                             }else{

                                                     Display::display_confirmation_message('Practica eliminada');
                                             }
                                     }
                                     
                               echo "</div>";
                                          
                         }  
			?>
                           
</div>
<div style="padding: 10px 0px 10px 0px;"><!-- capa lista de practicas -->
		<?php 
		                    $practicaslist = GetPracticasList ($id_curso);

                                    //maquinas abiertas para ese usuario
                                    $peticiones = GetPeticionesAbiertas();
                                    $registros = count($peticiones);

		//comprobamos si el usuario tienen maquinas abiertas (corriendo,pendiente de comienzo....)
			if($registros > 0){
			//si tienen maquinas abiertas le mostramos solo la informacion
			 $columnas = array (get_lang('langDescription'),get_lang('langState'),get_lang('langStop'),get_lang('StartDate'),get_lang('EndDate'),get_lang('langConnectData'));
			 echo FdTablaCabecera($columnas, 'class="data_table" align="center" cellpadding="4" border="0" cellspacing="0" width="200" cellpadding="4"','bgcolor="DOKEOSLIGHTGREY" align="center"');
                         echo FdTablaFilaPendiente($practicaslist);
			}else{
			//si el usuario no tienen maquinas abiertas le permitimos que conecta a una,si no le mostramos la lista pero sin la posiblidad de iniciar maquinas
			 
                            if(api_is_allowed_to_edit())
                            {
                               $columnas = array (get_lang('langDescription'),get_lang('langConnect'),get_lang('langDelete'));
                            }else{
                                $columnas = array (get_lang('langDescription'),get_lang('langConnect'));
                            }
 			 echo FdTablaCabecera($columnas, 'class="data_table" align="center" cellpadding="4" border="0" cellspacing="0" width="200" cellpadding="4"','bgcolor="DOKEOSLIGHTGREY" align="center"');
   		         echo FdTablaFila($practicaslist);
 			}
			echo FdTablaFin();

		?>
     <div/> <!--fin capa lista de practicas -->	
  </div>
</div>
<?php 
echo get_lang('langNote')."</p><br />";
echo get_lang('langSend')."</p><br />";
echo get_lang('langDataWatch')."</p><br />";
?>
<br />

<?php
/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>
