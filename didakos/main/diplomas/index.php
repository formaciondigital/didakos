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
==============================================================================
*/
	
/*
==============================================================================
		INIT SECTION
==============================================================================
*/ 
// name of the language file that needs to be included
$language_file = array('diplomas');
// including necessary libraries
require ('../inc/global.inc.php');
include(dirname ( __FILE__ )."../inc/conf/configuration.php");

$libpath = api_get_path(LIBRARY_PATH);
// section for the tabs
api_protect_course_script();
// Database table definitions
$t_design = Database::get_main_table(TABLE_DIPLOMAS_DESIGN); 
$t_diplomas_course = Database :: get_main_table(TABLE_DIPLOMAS_COURSES);
$t_course = Database :: get_main_table(TABLE_MAIN_COURSE);
$t_config = Database::get_main_table(TABLE_DIPLOMAS_CONFIG);

$tool_name = get_lang('diplomas');

$course_code=$_SESSION["_course"]["official_code"];
$user_id=$_SESSION["_user"]["user_id"];

/*
==============================================================================
		HEADER
==============================================================================
*/
$htmlHeadXtra[] = "<link rel='stylesheet' type='text/css' href='estilos.css' />"; 

Display::display_header($tool_name);

function getGlobalScore ($course_code,$user_id)
{
    $t_matriculaciones = Database::get_main_table(TABLE_MAIN_MATRICULACIONES);
    $sql = "select nota_global as global from $t_matriculaciones where course_code='" .$course_code. "' and user_id=$user_id";
    $res = api_sql_query($sql, __FILE__, __LINE__);
    $obj = Database::fetch_object($res);
    if ( is_numeric($obj->global))
        {
            // global is a 0-10 value
            return ($obj->global*10);
        }
    else
        {
            return 0;
        }
}

function getQuizScore ($db,$user_id,$course_code)
{
    $table_exam = Database::get_course_table('quiz_exam', $db);
    $table_track = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);

    $sql = "select count(*) as total from $table_exam";
    $res = api_sql_query($sql, __FILE__, __LINE__);
    $obj = Database::fetch_object($res);
    
    if ( is_numeric($obj->total))
        {
            $elementos = $obj->total;
            $score = 0;

            $sql2 = "select sum(exe_result) as score from $table_track t2 where t2.exe_id in 
            (select max(exe_id) as id from $table_track t, $table_exam e where e.id=t.exe_exo_id and t.exe_cours_id='$course_code' and t.exe_user_id=$user_id group by exe_exo_id)";
            $res2 = api_sql_query($sql2, __FILE__, __LINE__);
            $obj2 = Database::fetch_object($res2);        
            
            $score = round($obj2->score / $elementos,2);
        }
        else
        {$score= 0;}    
    return $score;
}

function getScoScore ($db,$user_id)
{
    $table_lp = Database::get_course_table('lp', $db);
    $table_i = Database::get_course_table('lp_item', $db);
    $table_v = Database::get_course_table('lp_view', $db);
    $table_iv = Database::get_course_table('lp_item_view', $db);
        
    $sql = "select count(*) as total from $table_i where item_type='sco'";
    $res = api_sql_query($sql, __FILE__, __LINE__);
    $obj = Database::fetch_object($res);

    if ( is_numeric($obj->total))
        {
            $elementos = $obj->total;
            $score = 0;
            $sql = "select i.lp_id,iv.id,i.item_type,iv.status,iv.score,i.path,i.title from " . $table_i . " i," . $table_v . " v," . $table_iv . " iv " .
            "where i.id=iv.lp_item_id and iv.lp_view_id = v.id and v.lp_id=i.lp_id " .
            "and v.user_id=" . $user_id . " and item_type='sco' order by display_order";
            $res2 = api_sql_query($sql, __FILE__, __LINE__);
            if(Database::num_rows($res2)>0)
            {
                while ($lp_item = Database::fetch_array($res2))
                    {
                        $score = $score + $lp_item['score'];                           
                    }
                    $score = $score / $elementos;
            } 
            else
            {
                $score= 0;
            }    
            
        }
    else
        {
            $score = 0;
        }
    return (round($score,2));
}
/*
===========================================================================
                BODY
===========================================================================
*/

// First of all, security control. ¿This option is active for this curse?
$sql = "select count(id) as total from $t_diplomas_course where course_code='" . $course_code . "'";
$res = api_sql_query($sql, __FILE__, __LINE__);
$obj = Database::fetch_object($res);
if ($obj->total ==1)
{
    // Ok active
    echo get_lang("optionactive");
    echo "<br><br>";
    // Get tool configuration
    $sql = "select * from $t_config order by id";
$result = api_sql_query($sql,__FILE__,__LINE__);
    while ($temp_row = Database::fetch_array($result))
    {
            // Extract all values into variables for easy use
            switch ($temp_row["property"])
            {
                case "GLOBALSCORE":
                    $gs_value = $temp_row["value"];
                    $gs_status = ($temp_row["status"]==0 ? "0" : "1");
                    $gs_valid = false;
                    break;
                case "QUIZSCOREAVERAGE":
                    $qs_value = $temp_row["value"];
                    $qs_status = ($temp_row["status"]==0 ? "0" : "1");
                    $qs_valid = false;
                    break;
                case "SCOSCOREAVERAGE":
                    $ss_value = $temp_row["value"];
                    $ss_status = ($temp_row["status"]==0 ? "0" : "1");
                    $ss_valid = false;
                    break;
                default:
            }
    }
    
    if ($gs_status==1)
    {
        // active
        ?>
        <table class="panel">
            <tr>
                <td class="cabecera" colspan="3">
                    <?php echo get_lang("globalscore") ?>
                </td>
            </tr>
            <tr>
                <td class="cabecera">
                    <?php echo get_lang("score") ?>
                </td>
                <td class="cabecera">
                    <?php echo get_lang("actual") ?>
                </td>
                <td class="cabecera">
                    <?php echo get_lang("estado") ?>
                </td>
            </tr>
            <tr>
                <td class="fila">
                    <?php echo $gs_value; ?>
                </td>
                <td class="fila">
                    <?php 
                    $gs_actual = getGlobalScore($course_code, $user_id);
                    echo round($gs_actual,2);
                    ?>
                </td>
                <td class="fila">
                    <?php
                    if ($gs_actual >= $gs_value)
                    {
                        echo '<img src="../img/message_confirmation.png">';
                        $gs_valid = true;
                    }
                    else
                    {
                        echo '<img src="../img/message_error.png">';
                        $gs_valid = false;
                    }
                    ?>
                </td>
            </tr>
        </table><br><br>
        <?php        
    }
    
    if ($qs_status==1)
    {
        // active
        ?>
        <table class="panel">
            <tr>
                <td class="cabecera" colspan="3">
                    <?php echo get_lang("quizscore") ?>
                </td>
            </tr>
            <tr>
                <td class="cabecera">
                    <?php echo get_lang("score") ?>
                </td>
                <td class="cabecera">
                    <?php echo get_lang("actual") ?>
                </td>
                <td class="cabecera">
                    <?php echo get_lang("estado") ?>
                </td>
            </tr>
            <tr>
                <td class="fila">
                    <?php echo $qs_value; ?>
                </td>
                <td class="fila">
                    <?php 
                    $db_prefix = $_configuration['db_prefix'];
                    $qs_actual = getQuizScore($db_prefix.$course_code, $user_id,$course_code);
                    echo $qs_actual;
                    ?>
                </td>
                <td class="fila">
                    <?php
                    if ($qs_actual >= $qs_value)
                    {
                        echo '<img src="../img/message_confirmation.png">';
                        $qs_valid = true;
                    }
                    else
                    {
                        echo '<img src="../img/message_error.png">';
                        $qs_valid = false;
                    }
                    ?>
                </td>
            </tr>
        </table><br><br>
        <?php        
    }
    
    if ($ss_status==1)
    {
        // active
        ?>
        <table class="panel">
            <tr>
                <td class="cabecera" colspan="3">
                    <?php echo get_lang("scoscore") ?>
                </td>
            </tr>
            <tr>
                <td class="cabecera">
                    <?php echo get_lang("score") ?>
                </td>
                <td class="cabecera">
                    <?php echo get_lang("actual") ?>
                </td>
                <td class="cabecera">
                    <?php echo get_lang("estado") ?>
                </td>
            </tr>
            <tr>
                <td class="fila">
                    <?php echo $ss_value . " %" ?>
                </td>
                <td class="fila">
                    <?php 
                    $db_prefix = $_configuration['db_prefix'];
                    $ss_actual = getScoScore($db_prefix.$course_code, $user_id);
                    echo $ss_actual . " %";
                    ?>
                </td>
                <td class="fila">
                   <?php
                    if ($ss_actual >= $ss_value)
                    {
                        echo '<img src="../img/message_confirmation.png">';
                        $ss_valid = true;
                    }
                    else
                    {
                        echo '<img src="../img/message_error.png">';
                        $ss_valid = false;
                    }
                    ?> 
                </td>
            </tr>
        </table><br><br>
        <?php        
    }
    if ((($gs_status==1 && $gs_valid==true) || ($gs_status==0)) && (($qs_status==1 && $qs_valid==true) || ($qs_status==0))  && (($ss_status==1 && $ss_valid==true) || ($ss_status==0)))
    {
        // Small security
        $crypt = base64_encode($user_id . $course_code);
        $enlace = get_lang("criteriapassed").'<br><br><img src="../img/pdficon_large.gif"><br><a href="pdf.php?crypt='.$crypt.'" target="_blank">'.get_lang("descarga").'</a>';
    }
    else
    {
        $enlace = get_lang("nocriteriapassed");
    }
    
    ?> 
    <table class="panel">
            <tr>
                <td class="cabecera">
                    <?php echo get_lang("download") ?>
                </td>
            </tr>
            <tr>
                <td class="fila">
                 <?php               
                // Download it's only avaliable
                // if all config conditions are passed.        
                 echo $enlace;
                ?>        
                </td>
            </tr>    
    </table>
<?php
}
else
{
    // Inactive, tool not linked from course
    echo get_lang("optioninactive");
}
?>
<?php

/*
==============================================================================
		FOOTER
==============================================================================
*/
Display::display_footer();
?>