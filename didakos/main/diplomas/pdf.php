<?php

// including necessary libraries
require ('../inc/global.inc.php');
// Important Id's
include(dirname ( __FILE__ )."../inc/conf/configuration.php");
$libpath = api_get_path(LIBRARY_PATH);
// section for the tabs
api_protect_course_script();
$course_code=$_SESSION["_course"]["official_code"];
$user_id=$_SESSION["_user"]["user_id"];

if ( base64_decode($_GET["crypt"]) == ($user_id . $course_code))
{
    // Database table definitions
    $t_design = Database::get_main_table(TABLE_DIPLOMAS_DESIGN); 
    $t_diplomas_course = Database :: get_main_table(TABLE_DIPLOMAS_COURSES);
    $t_diplomas_track = Database::get_main_table(TABLE_DIPLOMAS_TRACK);
    $t_course = Database :: get_main_table(TABLE_MAIN_COURSE);
    
    //require dompdf config file
    require_once("../gestor/diplomas/dompdf/dompdf_config.inc.php");

    // get config from course
    $sql = "select * from $t_diplomas_course dc, $t_design d where course_code = '".$course_code."' and d.id=dc.design_id";
    $res = api_sql_query($sql,__FILE__,__LINE__);
    $item = Database::fetch_array($res);

    // PDF INIT
    $dompdf = new DOMPDF();
    $dompdf->set_paper('a4', 'landscape');
    // Front page
    $frontal =   	'<style>' .
                            'div.texto_frontal{20px;margin-right: 20px;}' .
                            'div.texto_trasero{20px;margin-right: 20px;}' .
                            'div.frontal{background-image: url(../gestor/diplomas/design/' . $item["image"] . '); background-repeat: no-repeat; text-align: center; height: 570px}' .
                            'div.posterior{}' .
                    '</style>' .
                    '<div style="page-break-after: always;">' .
                            '<div class="frontal">' .
                                    '<br><br><br><br><br><br>' .
                                    '<div class="texto_frontal">'.$item['up_text'].'</div><br>' .
                                    '<div class="texto_frontal">'.$item['center_text'].'</div><br>' .
                                    '<div class="texto_frontal">'.$item['bottom_text'].'</div>' .
                                    '' .
                            '</div>'.
                    '</div>';   			
    //back page
    $trasera = 	  '<div class="posterior">' .
                            '<br><br>' .
                            '<div class="texto_trasero">' . $item['back_text'] . '</div>' .
                            '<br><br>' .
                        '</div>';
    // join front and back
    $frontal = replaceData ($frontal);
    $dompdf->load_html($frontal . $trasera );
    $dompdf->render();
    //Launch
    $dompdf->stream("diploma_" .$course_code . ".pdf");

    // Save download
    $sql = "insert into $t_diplomas_track (course_code,user_id,download_date) values ('" . $course_code . "'," . $user_id .",now())";
    $res= api_sql_query($sql, __FILE__, __LINE__);
}
else
{
 echo get_lang("accesodenegado");   
}

function replaceData($data)
{
    global $user_id;
    global $course_code;
    $t_user = Database :: get_main_table(TABLE_MAIN_USER);
    $t_course = Database :: get_main_table(TABLE_MAIN_COURSE);
    
    // First look count
    if (substr_count($data,"#user#")>=1)
    {
        // Them select data
        $sql = "Select firstname,lastname from $t_user where user_id=".$user_id;
        $res = api_sql_query($sql, __FILE__, __LINE__);
        $item = Database::fetch_array($res);  
        // And finally replace
        $data = str_replace ( "#user#", utf8_decode($item["firstname"]. " " .$item["lastname"]) ,$data);
    }
    
    if (substr_count($data,"#official_code#")>=1)
    {
        // Them select data
        $sql = "Select official_code from $t_user where user_id=".$user_id;
        $res = api_sql_query($sql, __FILE__, __LINE__);
        $item = Database::fetch_array($res);  
        // And finally replace
        $data = str_replace ( "#official_code#",$item["official_code"] ,$data);
    }
    
    // First look count
    if (substr_count($data,"#course#")>=1)
    {
        // Them select data
        $sql = "Select title from $t_course where code='".$course_code."'";
        $res = api_sql_query($sql, __FILE__, __LINE__);
        $item = Database::fetch_array($res);  
        // And finally replace
        $data = str_replace ( "#course#",utf8_decode($item["title"]),$data);
    }
    
    // First look count
    if (substr_count($data,"#date#")>=1)
    {
        $data = str_replace ( "#date#",date('l jS F Y'),$data);
    }
    
    return $data;
}
?>
