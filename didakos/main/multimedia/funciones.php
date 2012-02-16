<?php
/*
Funciones para le herramienta multimedia
*/
function get_multimedia()
{
	$multimedia_table = Database :: get_course_table(TABLE_MULTIMEDIA);
	$multimedia_sources_table = Database :: get_course_table(TABLE_MULTIMEDIA_SOURCES);
	$multimedia = array ();

	$sql = "SELECT
		m.*,s.icon
             FROM
                 $multimedia_table m, $multimedia_sources_table s where m.source_id=s.id";
	$sql .= " ORDER BY orden";

	$result = api_sql_query($sql,__FILE__,__LINE__);
	while ($temp_row = Database::fetch_array($result))
	{
		$multimedia[] = $temp_row;
	}
	return $multimedia;
}

function get_multimedia_post($multimedia_id)
{
	$multimedia_post_table = Database :: get_course_table(TABLE_MULTIMEDIA_POST);
	$multimedia_post = array ();
	$sql = "SELECT * FROM $multimedia_post_table where multimedia_id=" . $multimedia_id;
	$sql .= " ORDER BY date DESC";

	$result = api_sql_query($sql,__FILE__,__LINE__);
	while ($temp_row = Database::fetch_array($result))
	{
		$multimedia_post[] = $temp_row;
	}
	return $multimedia_post;
}

function get_multimediabyid($id)
{
	$multimedia_table = Database :: get_course_table(TABLE_MULTIMEDIA);
	$sources_table = Database :: get_course_table(TABLE_MULTIMEDIA_SOURCES);

	$sql = "SELECT
		*
             FROM
                 $multimedia_table m, $sources_table s  where m.id=" . $id . " and m.source_id=s.id" ;
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$element = Database::fetch_row($res);
	return $element;
}

function delete_multimedia ($id)
{
	$multimedia_table = Database::get_course_table(TABLE_MULTIMEDIA);
	$multimedia_table_post = Database::get_course_table(TABLE_MULTIMEDIA_POST);

	//Sacamos el path del archivo
	$sql = "select target,source_id from $multimedia_table where id= $id";
       	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_array($res);
	//borramos archivo

	if ($obj[1]==5)
	{
		$path = "../../courses/" . $_SESSION['_course']['id'] . "/multimedia" . $obj["target"];
		unlink($path);
	}
	//borramos fila
       	$sql = "delete from $multimedia_table where id= $id";
       	api_sql_query($sql, __FILE__, __LINE__);
	// Borramos los comentarios
	$sql = "delete from $multimedia_table_post where multimedia_id= $id";
       	api_sql_query($sql, __FILE__, __LINE__);
	return true;
}

function GetUserData ($user_id)
{
	require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');
	$user_data = UserManager::get_user_info_by_id($user_id);

	//	IMAGEN
	$image_path = UserManager::get_user_picture_path_by_id($user_data['user_id'],'web');
	$image_dir = $image_path['dir'];
	$image = $image_path['file'];
	$image_file = ($image != '' ? $image_dir.$image : api_get_path(WEB_CODE_PATH).'img/unknown.jpg');
	$img_attributes = 'src="'.$image_file.'?rand='.time().'" '
		.'alt="'.$user_data['lastname'].' '.$user_data['firstname'].'" '
		.'style="float:left; padding:5px;"';

	$imagen = '<img '.$img_attributes.' height="60" border="0"/>';
	//	NOMBRE Y APELLIDOS
	$nombre = $user_data['lastname'].', '.$user_data['firstname'];
	
	//	TIPO DE USUARIO (Gestor, Tutor, Alumno)
	if ($user_data['status']=='1')
	{
		if(api_is_platform_admin())
		{$tipo_usuario = 'Gestor';}
		else
		{$tipo_usuario = 'Tutor';}
	}
	else
	{
		if ($user_data['status']=='5')
		{
			$tipo_usuario = 'Alumno';		
		}
		else
		{
			// 4, inspector. con recursos limitados.
			$tipo_usuario = 'Inspector';		
		}
	}

	return array ($imagen,$nombre,$tipo_usuario);
}

function getHace($fecha)
{
	$cacho = explode(" ",$fecha);
	$fecha = explode("-",$cacho[0]);
	$tiempo = explode(":",$cacho[1]);

	if ( count($cacho)==1 )
	{
		$fecha1 = mktime (0,0,0,$fecha[1],$fecha[2],$fecha[0]);
	}
	else
	{
		$fecha1 = mktime ($tiempo[0],$tiempo[1],$tiempo[2],$fecha[1],$fecha[2],$fecha[0]);
	}
	$fecha2= time();
	$dateDiff = $fecha2 - $fecha1;
	$fullDays = floor($dateDiff/(60*60*24));

	if ($fullDays < 1)
	{
			$fullDays = floor($dateDiff/(60*60));
			if ($fullDays < 1) 
			{
				$fullDays = floor($dateDiff/(60));
				if ($fullDays <= 1)
				{
				  return get_lang("HaceUnosInstantes");
				}
				else
				{				
				  return get_lang("Hace")." ".$fullDays ." ".get_lang("Minutos");		
				}
			}
			else
			{
				if ($fullDays==1)
				{
				  return get_lang("Hace")." ". $fullDays ." ".get_lang("Hora");
				}	
				else
				{
				  return get_lang("Hace")." ". $fullDays ." ".get_lang("Horas");
				}
			}
	}
	else
	{
		if ($fullDays > 30)
		{
			return get_lang("HaceMuchoTiempo");
		}
		else
		{
			if ($fullDays==1)
			{
			  return get_lang("Hace")." ". $fullDays ." ".get_lang("Dia");
			}	
			else
			{
			  return get_lang("Hace")." ". $fullDays ." ".get_lang("Dias");
			}
		}
	}

	
}

function GetLastElementOrder()
{
		$table_document = Database::get_course_table(TABLE_MULTIMEDIA);
        //Obtenemos ahora el orden
	    $sql = "select max(orden) as orden from " . $table_document;
    	$res = api_sql_query($sql, __FILE__, __LINE__);
	    $element = Database::fetch_row($res);
	    //Actualizamos el orden
	    return $element[0]+1;
}	    

?>
