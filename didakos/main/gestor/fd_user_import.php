<?php // $Id: user_import.php 14792 2008-04-08 20:57:53Z yannoo $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004-2008 Dokeos SPRL
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) Olivier Brouckaert
	Copyright (c) 2005 Bart Mollet <bart.mollet@hogent.be>

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact: Dokeos, rue du Corbeau, 108, B-1030 Brussels, Belgium, info@dokeos.com
==============================================================================
*/
/*
==============================================================================
		Página modificada por Formación Digital

Autor: Eduardo García
Página incial: user_import.php (1.8.5)
Página actual: fd_user_import.php (versión 0.2)
Descripción: Página que importa un archivo con datos de alumnos y matriculaciones para la plataforma
- Modificamos la pagina para que solo acepte CSV
- Modificamos ejemplo explicativo
- Añadimos opción de dar de baja. 06/05/10
		
==============================================================================
*/


/**
 * validate the imported data
 */
function validate_data($users,$status)
{
	global $defined_auth_sources;
	$errors = array ();
	$usernames = array ();
	foreach ($users as $index => $user)
	{ 
		//1. check if mandatory fields are set	
	    //si se trata de un alumno,requerimos el dni
            if($status == 5){
               $mandatory_fields = array ('LastName', 'FirstName','OfficialCode'); 
            }else{ //si es inspector o tutor no
               $mandatory_fields = array ('LastName', 'FirstName');
            }

		
		foreach ($mandatory_fields as $key => $field)
		{
			if (!isset ($user[$field]) || strlen($user[$field]) == 0)
			{
				$user['error'] = 'Dato obligatorio ('. $field . ')'; 
				$errors[] = $user;
			}
		}
		
		// chequeamos la validez del dni	
                // if (isset ($user['OfficialCode']) && $user['OfficialCode'] != "" )
		// if (isset ($user['OfficialCode']) && $user['OfficialCode'] != "" )
		// {
			//No usamos la revisión de si está repetido dado que le proceso
			//puede matricular si el alumno ya existe
			//UserManagerFD::is_dni_available($official_code)
				
			//normalizamos el formato
        		// $dni = preg_replace( '/[^0-9A-Z]/i', '', $user['OfficialCode'] );

			// Primero vamos a ver si se trata de un NIE
                    /*
				switch (substr ( $dni ,0, 1 )) {
    				case 'X':
					//Si se trata de un NIE eliminamos la letra
					$dni = substr ( $dni ,1);
					//Vamos a ver si falta alguna cifra, añadimos hasta 8
					while (strlen($dni)<=8):
						$dni = '0'.$dni;
					endwhile ;
				break;
    				case 'Y':
					//Si se trata de un NIE eliminamos la letra
					$dni = substr ( $dni ,1);
					//Vamos a ver si falta alguna cifra, añadimos hasta 7
					while (strlen($dni)<=7):
						$dni = '0'.$dni;
					endwhile ;
					$dni = '1'.$dni;
				break;
    				case 'Z':
					//Si se trata de un NIE eliminamos la letra
					$dni = substr ( $dni ,1);
					//Vamos a ver si falta alguna cifra, añadimos hasta 7
					while (strlen($dni)<=7):
						$dni = '0'.$dni;
					endwhile ;
					$dni = '2'.$dni;
				break;
				}
	
			//calculamos que letra corresponde al número del DNI o NIE
			$stack = 'TRWAGMYFPDXBNJZSQVHLCKE';
			$pos = substr($dni, 0, 8) % 23;
			*/
			//echo strtoupper( substr($dni, 8, 1) ) . ' ' . substr($stack, $pos, 1);
                    /*
			if (strtoupper( substr($dni, 8, 1) ) == substr($stack, $pos, 1))
			{    
				//es valido
			}
			else
			{
				$user['error'] = 'Dni no v&aacute;lido (No valida)' . $user['OfficialCode'];
				$errors[] = $user;
			}
*/
               //  }
		 // }
		
	}//fin foreach
	return $errors;
}

/**
 * Save the imported data
 */
function save_data($users,$status)
{
        $resultado = "<b>Resultado de la importacion:</b><br><br><br><br>";
	$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	
	foreach ($users as $index => $user)
	{
		//$user = complete_missing_data($user); lo eliminamos ya que no es necesario
		
		$lastname = $user['LastName'];
		$firstname = $user['FirstName'];
		$official_code = $user['OfficialCode'];
		$email = $user['Email'];
		$phone = $user['PhoneNumber'];
		//$status = 5; //alumno
		$platform_admin = 0; //no administrador
		$send_mail = 0; //no se envia email
		$hr_dept_id =0; //no es responsable de recursos humanos
		$expiration_date='0000-00-00 00:00:00'; //la cuenta no expira nunca
		$active =1; // cuenta activa
		$auth_source = "platform";
		$user_id=0;
                $usuario = $user['usuario'];
                $clave = $user['clave'];



                // DEPENDIENDO DEL STATUS,ESTAMOS INSERTANDO UN ALUMNO UN TUTOR O UN INSPECTOR
                switch ($status){

                   //ALUMNO
                    case 5:

                        //SI EL ALUMNO TRAE LAS CLAVES VACIAS ES QUE LAS TENEMOS QUE GENERAR NOSOSTROS
                        if ($usuario == "" ||  $clave == ""){
                             $user_id = UserManagerFD::create_alumno($firstname,$lastname,$status,$email,$official_code,api_get_setting('platformLanguage'),$phone,$auth_source,$expiration_date,$active, $hr_dept_id);
                        }else{
                         //SI EL ALUMNO TIENE USUARIO Y CLAVE RELLENA USAMOS EL METODO DE CREACION DE USUARIO QUE TRAIA DOKEOS QUE PERMITE ESTABLECERLAS NOSOTROS
                             $user_id = UserManagerFD::create_user($firstname,$lastname,$status,$email,$usuario, $clave, $official_code,api_get_setting('PlatformLanguage'), $phone,'', $auth_source,$expiration_date,$active, $hr_dept_id);
                        }
                        break;
                    //TELETUTOR
                    case 1:

		                     $user_id = UserManagerFD::create_teletutor($firstname,$lastname,$status,$email,$official_code,api_get_setting('platformLanguage'),$phone,'',$auth_source,$expiration_date,$active, $hr_dept_id);
                         break;
                    //INSPECTOR (el inspector no es mas que un usuario de RRHH,status = 4)
                    case 4:
							 //usamos el usuario como dni
							 $official_code = $usuario;
                             $user_id = UserManagerFD::create_user($firstname,$lastname,$status,$email,$usuario, $clave, $official_code,api_get_setting('PlatformLanguage'), $phone,'', $auth_source,$expiration_date,$active, $hr_dept_id);
                          break;

                }


                $resultado .= "<b>".$official_code."</b> - ".$firstname.",".$lastname;
                if ( $user_id != 0){

                     $resultado .= " - <i>Usuario creado</i><br>";
                     
                }else{

                     $resultado .= " - <i>Ya existia el usuario</i><br>";
                }
               
                    
		
                            //$user_id = UserManagerFD::create_alumno($firstname,$lastname,$status,$email,$official_code,api_get_setting('platformLanguage'),$phone,$auth_source,$expiration_date,$active, $hr_dept_id);

                            //esta era la antigua llamada a la función.
                            //$user_id = UserManager :: create_user($user['FirstName'], $user['LastName'], $user['Status'], $user['Email'], $user['UserName'], $user['Password'], $user['OfficialCode'], api_get_setting('PlatformLanguage'), $user['PhoneNumber'], '', $user['AuthSource']);

                            //si el user_id = 0 es porque el dni ya existe, debemos obtener el user_id directamente
                            if($user_id==0)
                            {
                            $usuario = UserManagerFD::get_user_info_by_dni($official_code);
                            $user_id = $usuario['user_id'];

                            // Aqui tenemos la opción de hacer update sobre ciertos datos del alumno (nombre, apellidos, telefono, email)

                            $usuario = UserManagerFD::update_user_csv ($user_id, $firstname, $lastname, $email,  $phone);

                            }

                           

                                    foreach ($user['Courses'] as $index => $course)
                                    {

                                            $resul = CourseManagerFD::subscribe_user($user_id,$course);

                                            if($resul){
                                                $resultado .= "<i>Matriculado en el curso </i>".$course."<br>";
                                            }

                                    }

                                    foreach ($user['Baja'] as $index => $course)
                                    {

                                            $resul = CourseManagerFD::unsubscribe_user($user_id,$course);

                                    }
                           
		/*
		if ($sendMail)
		{
			$emailto = '"'.$user['FirstName'].' '.$user['LastName'].'" <'.$user['Email'].'>';
			$emailsubject = '['.api_get_setting('siteName').'] '.get_lang('YourReg').' '.api_get_setting('siteName');
			$emailbody = get_lang('Dear').$user['FirstName'].' '.$user['LastName'].",\n\n".get_lang('YouAreReg')." ".api_get_setting('siteName')." ".get_lang('Settings')." $user[UserName]\n".get_lang('Pass')." : $user[Password]\n\n".get_lang('Address')." ".api_get_setting('siteName')." ".get_lang('Is')." : ".api_get_path('WEB_PATH')." \n\n".get_lang('Problem')."\n\n".get_lang('Formula').",\n\n".api_get_setting('administratorName')." ".api_get_setting('administratorSurname')."\n".get_lang('Manager')." ".api_get_setting('siteName')."\nT. ".api_get_setting('administratorTelephone')."\n".get_lang('Email')." : ".api_get_setting('emailAdministrator')."";
			$emailheaders = 'From: '.api_get_setting('administratorName').' '.api_get_setting('administratorSurname').' <'.api_get_setting('emailAdministrator').">\n";
			$emailheaders .= 'Reply-To: '.api_get_setting('emailAdministrator');
			@ api_send_mail($emailto, $emailsubject, $emailbody, $emailheaders);
		}
		*/

	}
        return $resultado."<br>";
}
/**
 * Read the CSV-file 
 * @param string $file Path to the CSV-file
 * @return array All userinformation read from the file
 */
function parse_csv_data($file)
{
	$users = Import :: csv_to_array($file);
	foreach ($users as $index => $user)
	{
		if (isset ($user['Courses']))
		{
			$user['Courses'] = explode('|', trim($user['Courses']));
		}

		if (isset ($user['Baja']))
		{
			$user['Baja'] = explode('|', trim($user['Baja']));
		}

		$users[$index] = $user;
	}
	return $users;
}


// name of the language file that needs to be included
$language_file = array ('admin', 'registration');

$cidReset = true;
require ('../inc/global.inc.php');
$this_section = SECTION_PLATFORM_ADMIN;
api_protect_gestor_script();
require_once (api_get_path(LIBRARY_PATH).'fileManage.lib.php');
require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.fd.php');
require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once(api_get_path(LIBRARY_PATH).'course.lib.php');
require_once(api_get_path(LIBRARY_PATH).'course.lib.fd.php');
require_once (api_get_path(LIBRARY_PATH).'import.lib.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
$formSent = 0;
$errorMsg = '';
$defined_auth_sources[] = PLATFORM_AUTH_SOURCE;
if (is_array($extAuthSource))
{
	$defined_auth_sources = array_merge($defined_auth_sources, array_keys($extAuthSource));
}

$tool_name = get_lang('ImportUserListXMLCSV');
$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));

set_time_limit(0);
Display :: display_header($tool_name);
if ($_POST['formSent'] AND $_FILES['import_file']['size'] !== 0)
{
	/*$file_type = $_POST['file_type'];
	//if ($file_type == 'csv')
	{
		$users = parse_csv_data($_FILES['import_file']['tmp_name']);
	}
	else
	{
		$users = parse_xml_data($_FILES['import_file']['tmp_name']);
	}*/
	$users = parse_csv_data($_FILES['import_file']['tmp_name']);
	$errors = validate_data($users,$_POST['status']);

        /*echo "<pre>";
        echo $_POST['status'];
        print_r($users);
        echo "</pre>";*/
    

	if (count($errors) == 0)
	{

		Display::display_confirmation_message(save_data($users,$_POST['status']),false);
		//header('Location: fd_lista_alumnos.php?action=show_message&message='.urlencode(get_lang('FileImported')));
		//exit ();
	}
}

//api_display_tool_title($tool_name);

if($_FILES['import_file']['size'] == 0 AND $_POST)
{
	Display::display_error_message(get_lang('ThisFieldIsRequired'));
}
	//borrar
	//print_r($users,true);
	
if (count($errors) != 0)
{
	$error_message = '<ul>';
	foreach ($errors as $index => $error_user)
	{
		$error_message .= '<li><b>'.$error_user['error'].'</b>: ';
		$error_message .= $error_user['FirstName'].' '.$error_user['LastName'];
		$error_message .= '</li>';
	}
	$error_message .= '</ul>';
	Display :: display_error_message($error_message, false);
}





$form = new FormValidator('user_import');
$form->addElement('hidden', 'formSent');
$form->addElement('file', 'import_file', get_lang("UbicacionFicheroCSV"));
$form->addRule('import_file', get_lang('ThisFieldIsRequired'), 'required');
$allowed_file_types = array ('csv');
$form->addRule('file', get_lang('InvalidExtension').' ('.implode(',', $allowed_file_types).')', 'filetype', $allowed_file_types);
//$form->addElement('radio', 'file_type', null, 'CSV (<a href="exemple.csv" target="_blank">'.get_lang('ExampleCSVFile').'</a>)', 'csv');

$form->addElement('radio','status', get_lang("TipoDeUsuario"),get_lang("Alumno"),5);
$form->addElement('radio','status','',get_lang("Teletutor"),1);
$form->addElement('radio','status','',get_lang("Inspector"),4);

$form->addElement('submit', 'submit', get_lang('Ok'));
$defaults['formSent'] = 1;
$defaults['file_type'] = 'csv';
$defaults['status'] = 5;
$form->setDefaults($defaults);
$form->display();

?>
<p><?php echo get_lang("ImportarUsuarioTexto1"); ?></p>
<blockquote>
<pre>
<table>
<tr>
<td><b>LastName</b></td>
<td>;</td>
<td><b>FirstName</b></td>
<td>;</td>
<td>Email</td>
<td>;</td>
<td><b>OfficialCode</b></td>
<td>;</td>
<td>Phone</td>
<td>;</td>
<td>Courses</td>
<td>;</td>
<td>Baja</td>
<td>;</td>
<td>usuario</td>
<td>;</td>
<td>clave</td>
</tr>
<tr>
<td><b>García García</b></td>
<td>;</td>
<td><b>Demófilo</b></td>
<td>;</td>
<td>Demofilo@demo.com</td>
<td>;</td>
<td><b>11111111H</b></td>
<td>;</td>
<td>123456789</td>
<td>;</td>
<td>10100ED1|10101ED1</td>
<td>;</td>
<td>10103ED2|10104ED4</td>
<td>;</td>
<td>Dgarcia1</td>
<td>;</td>
<td>Asdf</td>
</tr>
</table>
</pre>
</blockquote>
<p><?php echo get_lang("ImportarUsuarioTexto2"); ?></p>

<?php


/*
==============================================================================
		FOOTER
==============================================================================
*/
Display :: display_footer();
?>
