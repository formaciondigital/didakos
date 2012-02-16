<?php
// name of the language file that needs to be included
$language_file = array('registration','tracking','exercice');

require ('../inc/global.inc.php');
require_once (api_get_path(LIBRARY_PATH).'tracking.lib.php');
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once ('../newscorm/learnpath.class.php');

//Incluimos librería de utilidades generales
require_once (api_get_path(LIBRARY_PATH).'main_api.lib.php');
require_once (api_get_path(LIBRARY_PATH).'tracking.lib_fd.php');

//Incluimos la librería de validación de formularios
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

// Database table definitions
$tbl_course_lp_view 		= Database :: get_course_table('lp_view');
$tbl_course_lp_view_item 	= Database :: get_course_table('lp_item_view');
$tbl_course_lp_item 		= Database :: get_course_table('lp_item');

//** Eliminamos **//

//Pintamos la cabecera
$nameTools    = get_lang('MyProgress');
Display :: display_header($nameTools);

//Recuperamos los valores enviados a la página
$alumno=isset($_GET['student']) ? $_GET['student'] : $_POST['student'];
$curso=isset($_GET['course']) ? $_GET['course'] : $_POST['course'];
$seguimiento=isset($_GET['seguimiento']) ? $_GET['seguimiento'] : $_POST['seguimiento'];

//Información sobre el curso
$course        = Database::escape_string($curso);
$a_infosCours  = CourseManager::get_course_information($course);

//Recuperamos los datos
$sql = "SELECT iv.id as id_iv, iv.status as estado,  iv.score as progreso, iv.total_time as tiempo_sco, i.id as id_elemento, 
	i.title as titulo_sco,  i.item_type as tipo_elemento, iv.start_time as fecha_inicio_sco  
	FROM ".$a_infosCours['db_name'].".".$tbl_course_lp_item."
	 as i, ".$a_infosCours['db_name'].".".$tbl_course_lp_view_item."  as iv, ".$a_infosCours['db_name'].".".$tbl_course_lp_view."  as v 
	WHERE i.id = iv.lp_item_id AND iv.lp_view_id = v.id AND v.user_id =" .$alumno." AND i.item_type='sco' AND i.id = ".$seguimiento;
$result   = api_sql_query($sql, __FILE__, __LINE__);

while ($row = Database :: fetch_array($result)) {
	$id_iv		= $row['id_iv'];
}
//Elimiamos el seguimiento
//$sql = "DELETE FROM ".$a_infosCours['db_name'].".".$tbl_course_lp_view_item." WHERE id = ".$id_iv;
//Reseteamos los datos (si eliminamos la línea no aparecería en el listado hasta que vuelva a entrar el alumno)
$sql = "UPDATE ".$a_infosCours['db_name'].".".$tbl_course_lp_view_item." SET
	status = 'not attempted',
	score = 0,
	total_time = 0,
	start_time = 0,
	suspend_data = null,
	lesson_location = null,
	core_exit = 'none',
	max_score = ''
	WHERE id = ".$id_iv;

$result   = api_sql_query($sql, __FILE__, __LINE__);
?>
	<h2><?php echo $titulo_sco ?></h2>
	Eliminando...
	<script language="javascript">		
		alert('Seguimiento eliminado');
		location.href="my_progress_details_course_x15.php?student=<?php echo $alumno ?>&details=true&course=<?php echo $curso ?>&origin=tracking_course";
	</script>
<?php
//Pintamos el pie
Display :: display_footer();
?>
