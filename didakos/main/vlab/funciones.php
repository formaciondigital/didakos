<?php
/*
==============================================================================
		FUNCTIONS
==============================================================================
*/
include("../inc/global.inc.php");
include_once(api_get_path(LIBRARY_PATH).'database.lib.php');
$t_practicas = Database::get_main_table(TABLE_MAIN_VLAB_PRACTICAS);
$t_peticiones = Database::get_main_table(TABLE_MAIN_VLAB_PETICIONES);

function insertaPractica($descripcion,$maquina,$id_curso){
  
 global $t_practicas;
  $maquina = htmlspecialchars($maquina);
  $id_curso = htmlspecialchars($id_curso);
  $descripcion = htmlspecialchars($descripcion);
  
  $sql = "insert into $t_practicas (id_curso,id_maquina,descripcion,activa,tiempo) values ('$id_curso','$maquina','$descripcion','S','060')";
  $res = api_sql_query($sql);
  
  return $res; 
}

function eliminaPractica($id_practica,$id_curso){
  
 global $t_practicas;
  $id_practica = htmlspecialchars($id_practica);
  $id_curso = htmlspecialchars($id_curso);
  
  $sql = "delete from  $t_practicas where id_practica = '$id_practica' and id_curso= '$id_curso'";
  $res = api_sql_query($sql);
  
  return $res; 
}

//comprueba en que estado (terminated,running..) se encuentra un peticion dada 
//y actualiza su valor en la tabla de peticiones
function  comprobarPeticion($id_peticion)
{
    global $t_peticiones;
    global $_configuration;
$estado = null;
$datos = realizaPeticion($_configuration['url_vlab_api']."info/".$id_peticion."/"); 
	
	  if($datos["status"] == "ERROR")
	  {
	    if($datos["reason"] == "PETICION_ID does not exist (perhaps still booting?)" ){
		  $estado = "pending";
		}else{
		  $estado = "error";
		}
	  }else{
	     $estado = $datos[0]["fields"]["state"];
	  }
//no actualizamos en caso de que la creacion de la maquina haya dado error de creacion
  $sql = "update $t_peticiones set status = '".$estado."' where id_peticion = '".$id_peticion."' and (status <> 'error' or status is null)";
  api_sql_query($sql);
  return $estado;
}




//devuelve la informacion de una peticion dada
function getPeticionInfo($id_peticion)
{
 global $_configuration;
$datos = realizaPeticion($_configuration['url_vlab_api']."info/".$id_peticion."/");
return $datos;
}





/*
//comprueba si una maquina tiene alguna peticion no finalizada
function ismaquinaOn($id_maquina)
{
$peticiones = GetPeticionesAbiertasXmaquina($id_maquina);
if(count($peticiones) > 0 ){
return true;
}else{
return false;
}
}*/


//comprueba si una practica tiene algun apeticion no finalizada
function isPracticaOn($id_practica)
{
$peticiones = GetPeticionesAbiertasXPractica($id_practica);
if(count($peticiones) > 0 ){
return true;
}else{
return false;
}
}







//miramos las peticiones que tenga pendiente el usuario en la tabla control_peticiones,solo devolveremos las que no esten terminadas
function GetPeticionesPendientes()
{
    global $t_peticiones;

	

$sql = "select id_peticion from  $t_peticiones  where  user_id = ".$_SESSION[_user][user_id]." and code = '".$_SESSION[_cid]."' and ((status <> 'terminated' and status <> 'error' and status <> 'shutting-down') or status is null)";

$datos = api_store_result(api_sql_query($sql));

return $datos;
}







//comprueba las peticiones no terminadas que tenga un maquina (solo mirando en la tabla)
function GetPeticionesXPractica($id_practica)
{
    global $t_peticiones;

$sql = "select id_peticion from  $t_peticiones  where  user_id = ".$_SESSION[_user][user_id]." and code = '".$_SESSION[_cid]."' and id_practica = ".$id_practica." and ((status <> 'terminated' and status <> 'error' and status <> 'shutting-down' and status <> 'shutting-down') or status is null) order by id_peticion DESC";

$datos = api_store_result(api_sql_query($sql));

return $datos ;
}



//funcion que obtienen una lista de peticiones abiertas (todas aquellas que no estan terminated) y devuelve su estado
function GetPeticionesAbiertas()
{
$maquinas = array();
//recibimos las peticiones pendientes de finalizar
$peticiones = GetPeticionesPendientes();
	foreach($peticiones as $v_peticion)
	{
	  //comprobamos que realmente esta abierta realizando un peticion a la api
	  if( comprobarPeticion($v_peticion[0]) != "terminated" and comprobarPeticion($v_peticion[0]) != "shutting-down"  ){
	    $maquinas[] = $v_peticion[0];
	  }
	}
 return $maquinas;
}



//funcion que obtienen una lista de peticiones abiertas (todas aquellas que no estan terminated) y devuelve su estado
/*function GetPeticionesAbiertasXmaquina($id_maquina)
{
$peticion_comprobada = array();
//recibimos las peticiones pendientes de finalizar
$peticiones = GetPeticionesXmaquina($id_maquina);
	foreach($peticiones as $v_peticion)
	{
	  //comprobamos que realmente esta abierta realizando un peticion a la api
	  if( comprobarPeticion($v_peticion[0]) != "terminated" ){
	    $peticion_comprobada[] = $v_peticion[0];
	  }
	}
 return $peticion_comprobada;
}*/



//funcion que obtienen una lista de peticiones abiertas (todas aquellas que no estan terminated o shutting-down) y devuelve su estado
function GetPeticionesAbiertasXPractica($id_practica)
{
$peticion_comprobada = array();
//recibimos las peticiones pendientes de finalizar
$peticiones = GetPeticionesXPractica($id_practica);
	foreach($peticiones as $v_peticion)
	{
	  //comprobamos que realmente esta abierta realizando un peticion a la api
	  if( comprobarPeticion($v_peticion[0]) != "terminated" and comprobarPeticion($v_peticion[0]) != "shutting-down" ){
	    $peticion_comprobada[] = $v_peticion[0];
	  }
	}
 return $peticion_comprobada;
}



 
/* 
//devuelve la lista de maquinas disponibles y filtramos para mostrar solo las del curso actual 
function GetMachineList ($curso)
{

	//$handle = fopen("http://10.1.20.12:8000/vlab/api/list/","r");
	$json = realizaPeticion($_configuration['url_vlab_api']."list/");

	//Recibimos un Array con todas las máquinas disponibles, tenemos que quedarnos solo con las que
	//correspondan a las del curso en el que estamos. El curso son 5 caracteres numéricos (00000 - 99999)
	//en las plartaformas ocasionalmente para para indicar las ediciones a esta numeración se le añade EDXX
	//pasando a ser algo aprecido a 10100ED4. Lo recibimos ya sin este añadido.

	//creamos un array para almacenar las maquinas virtuales
	$machinelist = array();

	foreach ($json as $machine)
	{
		if ( substr($machine['fields']['name'],3,5) == $curso )
		{
			//si la maquina corresponde a nuestro curso, solo recogemos name y description 
			$machinelist[] = $machine['fields'];
		}
	}

	return $machinelist;
	//devuelve un array con datos de todas las máquinas que son válidas para este curso
}*/



function GetPracticasList ($curso)
{
    global $t_practicas;
   
	$sql = "SELECT id_maquina as name,descripcion as description,id_practica,tiempo FROM $t_practicas where id_curso = '".$curso."' and activa = 'S'";
    return api_store_result(api_sql_query($sql));
	
}


function GetDatosPractica ($id_practica,$id_curso)
{
    global $t_practicas;
   
	$sql = "SELECT * FROM $t_practicas where id_curso = '".$id_curso."' and id_practica = ".$id_practica;
    return api_store_result(api_sql_query($sql));
	
}






/*********************************************************
//CONEXION CON LA API
**********************************************************/




function init_curl() {  
    $ch = curl_init(); 
	global $_configuration;
    //Iniciamos y pasamos todos los parámetros 
    curl_setopt($ch, CURLOPT_VERBOSE, TRUE); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 200 ); 
    curl_setopt($ch, CURLOPT_HEADER, 0);
		
		//if is set ssl certificate path in configuration file
		if($_configuration['path_certificate'] != '')
		{			
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0 ); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);         
			curl_setopt($ch, CURLOPT_SSLVERSION,3);

			curl_setopt($ch, CURLOPT_SSLCERT, $_configuration['path_certificate'] ); 
		}
		
    return $ch;
    }

function open_url($url,$ch){
    
    curl_setopt($ch, CURLOPT_URL, $url ); 
    //recogemos el resultado          
    $data  = curl_exec( $ch ); 
    //recogemos el error 
    $error = curl_errno( $ch ) . " " . curl_error($ch); 
    //cerramos 
 
    if (curl_errno( $ch ) == 0) 
    { 
        // No hay errores 
       //echo "No hay errores";
        curl_close( $ch );  
        return $data; 
    } 
    else 
    { 
        //Hay error 
       
        curl_close( $ch );  
        die("error:".$error); 
		
    } 
} 


function realizaPeticion($url)
{
$ch=init_curl(); 
$handle = open_url($url,$ch); 
$json = json_decode($handle,true);

return $json;
}

?>