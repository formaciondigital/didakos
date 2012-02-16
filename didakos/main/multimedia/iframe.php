<?php

$language_file[] = 'multimedia';

include("../inc/global.inc.php"); 
api_protect_course_script();
$web_code_path = api_get_path(WEB_CODE_PATH);

?>
<script src="flowplayer-3.2.6.min.js"></script>
<script language="javascript">
function creaAjax(){
         var objetoAjax=false;
         try {
          /*Para navegadores distintos a internet explorer*/
          objetoAjax = new ActiveXObject("Msxml2.XMLHTTP");
         } catch (e) {
          try {
                   /*Para explorer*/
                   objetoAjax = new ActiveXObject("Microsoft.XMLHTTP");
                   }
                   catch (E) {
                   objetoAjax = false;
          }
         }

         if (!objetoAjax && typeof XMLHttpRequest!='undefined') {
          objetoAjax = new XMLHttpRequest();
         }
         return objetoAjax;
}

function FAjax (red,tipo,var1,var2,var3,var4,var5)
{
	// Ajax para publicar en redes sociales.
	// red indica la red social, tipo indica el tipo de mensaje a publicar (Si hubiera)
	// var1 a var5 variables para poder pasar datos a la función 

        var ajax=creaAjax();
	switch(red)
	{
		case 'facebook':
		ajax.open("GET", "../social/facebook/publicar_facebook.php?tipo="+tipo+"&var1="+var1+"&var2="+var2+"&var3="+var3+"&var4="+var4+"&var5="+var5);
		break;
		case 'twitter':
		ajax.open("GET", "../social/twitter/publicar_twitter.php?tipo="+tipo+"&var1="+var1+"&var2="+var2+"&var3="+var3+"&var4="+var4+"&var5="+var5);
  		break;
	}

        ajax.onreadystatechange=function() {
               if (ajax.readyState==4) {
		alert ("Publicado correctamente");		
               }
        }         
	ajax.send(null);

}
</script>

<?php
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

if ( isset($_GET["id"]))
{
	$element = get_multimediabyid ($_GET["id"]);
	if ($element[4]==5)
	{
		//Es interno
		echo str_replace($element[10],"http://" . $_SERVER["SERVER_NAME"] . "/courses/" . $_SESSION['_course']['id'] . "/multimedia".$element[5],$element[10]);
		echo '<script language="JavaScript">flowplayer("player", "flowplayer-3.2.7.swf");</script>';
	}
	else
	{
		//Es externo
		echo str_replace($element[12],$element[5],$element[11]);
		//componemos lo enlaces
		$url = str_replace($element[12],$element[5],$element[11]);			

		if ($element[4] <= 3)
		{
			// ¿Por qué lo de menor de 3? Pues porque solo los elementos 1,2 y 3 permiten un acceso directo por un ID, el Iboox lleva una url con un html.
			if ( api_get_facebook_tokens(api_get_user_id()))
			{
				$var1 = utf8_decode ("Formación digital");
				$var2 = get_lang("InteresanteVideo") . " " . $_SESSION['_course']['name'];			
				$var3 = $url ;
				$var4 = api_get_path(WEB_PATH) . "main/social/img/fd.gif";
				$var5 = "";
				$icon = '&nbsp;<a onclick="javascript:FAjax(\'facebook\',1,\''.$var1.'\',\''.$var2.'\',\''.$var3.'\',\''.$var4.'\',\''.$var5.'\');" href="#"><img src="'. $web_code_path . 'img/facebook.gif"></a>';
			}
			if ( api_get_twitter_tokens(api_get_user_id()))
			{
				$var1 = utf8_decode(substr(get_lang("InteresanteVideo") . " " . utf8_encode($_SESSION['_course']['name']) . " " . $url,0,140));
				$var2 = "";
				$var3 = "";
				$var4 = "";
				$var5 = "";
				$icon .='&nbsp;<a onclick="javascript:FAjax(\'twitter\',1,\''.$var1.'\',\''.$var2.'\',\''.$var3.'\',\''.$var4.'\',\''.$var5.'\');" href="#"><img src="'. $web_code_path . 'img/twitter.gif"></a>';
			}
		}

	}

	//Aquí debe ir la posibilidad de compartirlos por las redes sociales.
	echo '<table><tr>
	<td><p style="text-align: center;font-family: Verdana;font-size: 11px;color: #404040;">'.get_lang('SiElElementoNoCarga').'.<br>'.get_lang('SiElProblemaPersiste').'.</p></td>
	<td>';
	if ($icon!="")
	{
		echo '<p style="text-align: center;font-family: Verdana;font-size: 11px;color: #404040;">' . get_lang("Compartir") . ": " . $icon . '</p>';
	}
	echo '</p></td>';


}
else
{
	echo '<p style="text-align: center;font-family: Verdana;font-size: 11px;color: #404040;">'.get_lang('SeleccioneUnElemento').'</p>';
}
?>
