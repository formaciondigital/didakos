<?php
// comprobacion de la existencia del archivo de configuracion de la plataforma
if(!file_exists("../main/inc/conf/configuration.php"))
{
    //die('no hay');
	header("Location: ../index.php");
}
// Si no se elige idioma o no existe el fichero de idiomas seleccionado, seleccionamos español
$access_lang = $_POST['access_lang'];
if (empty($access_lang) || !include ('../main/lang/'.$access_lang.'/acceso.inc.php')) {
	include ('../main/lang/spanish_fd/acceso.inc.php');
	$access_lang = 'spanish_fd';
}

switch ($access_lang) {
    case "catalan_fd":
       $logo_lang = "cat";
        break;
    case "english_fd":
       $logo_lang = "en";
        break;
    case "spanish_fd":
       $logo_lang = "es";
        break;
    case "french_fd":
       $logo_lang = "fr";
        break;
    case "brazilian_fd":
       $logo_lang = "br";
        break;
    default:
       $logo_lang = "default";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="estilos/estilos.css" />
		<title>Plataforma teleformación VERSION10</title>
		<script type="text/javascript">
			function cambiar_idioma(idioma) {
				document.idioma.access_lang.value = idioma;
				document.idioma.submit();
			}
		</script>
	</head>
	<body>
		<div id="contenedor">
			<div id="logo">
				<img src="img/logo_login_<?php echo $logo_lang ?>.png" alt="Logo" />
			</div>
			<div id="caja_login">
				<div id="titulo"><?php echo $Titulo ?></div>
				<div id="aclaracion"><?php echo $Aclaracion ?></div>
				<div id="campos">
					<form action="../index.php" method="post" name="acceso">
						<input type="hidden" name="access_lang" value="<?php echo $access_lang ?>">
						<?php echo $CamposUsuario ?>: <input type="text" name="login">
						<?php echo $CamposContrasena ?>: <input type="password" name="password">
						<input type="submit" value="<?php echo $CamposIr ?>">
					</form>
				</div>
				<div id="idiomas">
					<div><?php echo $Idiomas ?>:</div>
					<form action="index.php" method="post" name="idioma">
						<input type="hidden" name="access_lang">
						<input type="button" value="Català" onclick="cambiar_idioma('catalan_fd')">
						<input type="button" value="English" onclick="cambiar_idioma('english_fd')">
						<input type="button" value="Español" onclick="cambiar_idioma('spanish_fd')">
						<input type="button" value="Français" onclick="cambiar_idioma('french_fd')">
						<input type="button" value="Português (Brazil)" onclick="cambiar_idioma('brazilian_fd')">
					</form>
				</div>
				<div id="ayuda">
					<a href="http://cursos.formaciondigital.com/apps/faqs/faq.html"><?php echo $Ayuda ?></a>
				</div>
			</div>
			<div id="logo_fd">
				<a href="http://www.didakos.org" ><img src="img/logo_login_inf.png" alt="Formación Digital" /></a>
			</div>
		</div>
	</body>
</html>
