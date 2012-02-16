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

//Estados
$estados_scorm = array("not attempted"=>get_lang('Sin_intentar'),"incomplete"=>get_lang('Incompleto'),"completed"=>get_lang('Completado'),"passed"=>get_lang('Aprobado'),"failed"=>get_lang('Suspenso'),"browsed"=>get_lang('Visionado'));

//** Modificamos **//

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
	$indice         = $row['estado'];
	$estado		= $estados_scorm[$indice];
	$progreso       = $row['progreso'];
	$titulo_sco     = $row['titulo_sco'];
	$tiempo         = api_time_to_hms($row['tiempo_sco']);		
	if ($row['fecha_inicio_sco']<>0) {
		$fecha_inicio['d'] =  (int)format_locale_date('%d',$row['fecha_inicio_sco']);
		$fecha_inicio['m'] =  (int)format_locale_date('%m',$row['fecha_inicio_sco']);
		$fecha_inicio['a'] =  format_locale_date('%Y',$row['fecha_inicio_sco']);
	} else {
		$fecha_inicio['d'] =  (int)date("d");
		$fecha_inicio['m'] =  (int)date("m");
		$fecha_inicio['a'] =  date("Y");
	}
	$id_elemento    = $row['id_elemento'];
	$id_iv		= $row['id_iv'];
}

//Creamos el formulario
$form = new FormValidator('formulario');
//Fecha_inicio
$form->addElement('datepickerdate', 'fecha_inicio', '', array ('form_name' => $form->getAttribute('name')));
$form->addRule('fecha_inicio', get_lang('InvalidDate'), 'date');
//Estado
$form->addElement('select','estado','',$estados_scorm);
//Progreso
$form->addElement('text','progreso','',array('size' => '3'));
$form->applyFilter('progreso','trim');
//Tiempo
$form->addElement('text','tiempo','',array('size' => '10'));
$form->applyFilter('progreso','trim');
//Aceptar
$form->addElement('submit','guardar',get_lang('Ok'));
//Campos ocultos
$form->addElement('hidden','student',$alumno);
$form->addElement('hidden','course',$curso);
$form->addElement('hidden','seguimiento',$seguimiento);
//Renderer
$renderer =& $form->defaultRenderer();
$renderer->setElementTemplate('<td>{element}</td>');
//Asignamos por defecto los valores recuperamos
$valores_por_defecto=array(
	'fecha_inicio'=>array(
		'd'=>$fecha_inicio['d'],
		'F'=>$fecha_inicio['m'],
		'Y'=>$fecha_inicio['a']
	),
	'estado'=>$indice,
	'progreso'=>$progreso,
	'tiempo'=>$tiempo
);
$form->setDefaults($valores_por_defecto);

//Muestra errores de validación
if($form->validate()!=1):
?>
	<h2><?php echo $titulo_sco ?></h2>
	<table class="data_table" width="100%" style="text-align:center;">
		<tr>
			<th><?php echo get_lang('StartDate'); ?></th>
			<th><?php echo get_lang('langScormStatusColumn'); ?></th>
			<th><?php echo get_lang('Progress'); ?>(%)</th>
			<th><?php echo get_lang('Time'); ?></th>
			<th>&nbsp;</th>
		</tr>
		<tr>
			<?php $form->display(); ?>
		</tr>
	</table>
<?php
else:
	//Recogemos los datos
	$datos = $form->exportValues();
	//Transformamos fecha_inicio y tiempo
	list ($year, $month, $day) = explode("-", $datos['fecha_inicio']);
	$fecha_inicio_ms=mktime('0', '0', '0', $month, $day, $year);
	list ($hora, $min, $seg) = explode(":", $datos['tiempo']);
	$tiempo_ms=$hora*3600 + $min*60 + $seg;
	//Guardamos los datos
	$sql = "UPDATE ".$a_infosCours['db_name'].".".$tbl_course_lp_view_item." SET
		status = '".$datos['estado']."',
		score = ".$datos['progreso'].",
		total_time = ".$tiempo_ms.",
		start_time = ".$fecha_inicio_ms."
		WHERE id = ".$id_iv;

	$result   = api_sql_query($sql, __FILE__, __LINE__);
?>
	<h2><?php echo $titulo_sco ?></h2>
	Guardando...
	<script language="javascript">		
		alert('Seguimiento modificado');
		location.href="my_progress_details_course_x15.php?student=<?php echo $alumno ?>&details=true&course=<?php echo $curso ?>&origin=tracking_course";
	</script>
<?php
endif;
//Pintamos el pie
Display :: display_footer();
?>
