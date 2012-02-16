<?php 

/*
============================================================================== 
	Página creada por Formación Digital
	 
 	PÁGINA QUE PRESENTA LOS LOGOS DE LAS DISTINTAS REDES SOCIALES EN INVESTIGACIÓN		
==============================================================================
*/


// including necessary libraries
$language_file[] = 'redes_sociales';
require ('../inc/global.inc.php');
$tool_name = get_lang("Redessociales");
Display :: display_header($tool_name);  	
api_protect_course_script();
api_display_tool_title(get_lang("Redessociales"));
?>	
	<p><?php echo get_lang ("LasRedesSocialesSon"); ?></p>
	<p align="center">
            <?php
                $t_keys = Database::get_main_table(TABLE_REDES_SOCIALES);   
                $sql = "select * from $t_keys where name='twitter'";
                $result = api_sql_query($sql,__FILE__,__LINE__);
                $temp_row = Database::fetch_array($result);
                if ($temp_row['consumer_key'] != '' && $temp_row['consumer_secret'] != '')
                {
                    echo '<a href="../../main/social/twitter/index_twitter.php"><img src="../../main/social/img/twitter.jpg" align="top"></a>';
                }
                else
                {
                    echo '<a href="../../main/gestor/fd_redes_sociales.php"><img src="../../main/social/img/twitter_na.jpg" align="top"></a>';
                }
                
                echo " ";
                $t_keys = Database::get_main_table(TABLE_REDES_SOCIALES);   
                $sql = "select * from $t_keys where name='facebook'";
                $result = api_sql_query($sql,__FILE__,__LINE__);
                $temp_row = Database::fetch_array($result);
                if ($temp_row['consumer_key'] != '' && $temp_row['consumer_secret'] != '')
                {
                    echo '<a href="../../main/social/facebook/index.php"><img src="../../main/social/img/facebook.jpg" align="top"></a>';
                }
                else
                {
                    echo '<a href="../../main/gestor/fd_redes_sociales.php"><img src="../../main/social/img/facebook_na.jpg" align="top"></a>';
                }
            ?>

	</p>
	<p><?php echo get_lang ("NoUsoRedSocial"); ?></p>
	<p><?php echo get_lang ("RedesProximamente"); ?></p>
	<div><img src='img/linkedin_na.jpg' align='top'><img src='img/tuenti_na.jpg' align='top'><img src='img/flickr_na.jpg' align='top'><img src='img/myspace_na.jpg' align='top'></div>
	
<?php 
/*
==============================================================================
		FOOTER
==============================================================================
*/
Display::display_footer();
?>


