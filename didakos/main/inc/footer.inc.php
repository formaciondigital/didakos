<?php
/**
==============================================================================
*	This script displays the footer that is below (almost)
*	every Dokeos web page.
*
*	@package dokeos.include
==============================================================================
*/

/**** display of tool_navigation_menu according to admin setting *****/
if(api_get_setting('show_navigation_menu') != 'false')
{

   $course_id = api_get_course_id();
   if ( !empty($course_id) && ($course_id != -1) )
   {
   		if( api_get_setting('show_navigation_menu') != 'icons')
		{
	    	echo '</div> <!-- end #center -->';
    		echo '</div> <!-- end #centerwrap -->';
		}
      	require_once(api_get_path(INCLUDE_PATH)."tool_navigation_menu.inc.php");
      	show_navigation_menu();
   }
}
/***********************************************************************/

?>
 <div class="clear">&nbsp;</div> <!-- 'clearing' div to make sure that footer stays below the main and right column sections -->
</div> <!-- end of #main" started at the end of banner.inc.php -->

<div id="footer"> <!-- start of #footer section -->
<div id="bottom_corner"></div> 
 <div class="copyright" style=" padding-top:3px;">
  <?php global $_configuration; ?>
  <?php echo api_get_setting('Institution');?>
 </div>
<?php
/*
-----------------------------------------------------------------------------
	Plugins for footer section
-----------------------------------------------------------------------------
*/
api_plugin('footer');
?>
  <?php
  
  if(isset($_SESSION['_user']))//el alumno se ha logado en la plataforma
  {
    //redirijimos a la pagina del servicio tecnico dentro de la plataforma
     echo  "<a href=\"".$_SESSION['checkDokeosURL']."main/servicio_tecnico/contacto.php\">".get_lang('ServTecnico')."</a>";
  }
  else
  {//el alumnos no esta logado
  
		    if (get_setting('show_administrator_data')=="true")
			{
			/*echo get_lang("Manager") */  echo Display::encrypted_mailto_link(get_setting('emailAdministrator')."?subject=Incidencia de Acceso en  ".$_SESSION["checkDokeosURL"],get_setting('administratorName')." ".get_setting('administratorSurname'));
			}
	
   }
	
  ?>
</div> <!-- end of #footer -->
</div> <!-- end of #outerframe opened in header.inc.php -->
</body>
</html>
