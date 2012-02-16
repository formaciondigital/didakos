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
*	This file is a code template; 
*	copy the code and paste it in a new file to begin your own work.
*
*	@package dokeos.plugin
==============================================================================
*/
	
/*
==============================================================================
		INIT SECTION
==============================================================================
*/ 
// global settings initialisation 
// also provides access to main, database and display API libraries
$language_file[] = 'glosario';

include("../inc/global.inc.php"); 

$dbl_click_id = 0; // used to avoid double-click
$is_allowed_to_edit = api_is_allowed_to_edit();
$curdirpathurl = 'glosario';
api_protect_course_script();
$web_code_path = api_get_path(WEB_CODE_PATH);

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/ 
//the main_api.lib.php, database.lib.php and display.lib.php
//libraries are included by default
/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/ 

$tool_name = get_lang('Glosario'); // title of the page (should come from the language file) 
Display::display_header($tool_name);
api_display_tool_title($tool_name);
$interbreadcrumb[]= array ('url'=>'', 'name'=> 'Glosario');
$dir_array=explode("/",$curdirpath);
$array_len=count($dir_array);

/*
==============================================================================
		FUNCTIONS
==============================================================================
*/ 

function GetWords ($expresion)
{
    $glosario_table = Database :: get_course_table(TABLE_GLOSARIO);
	$glosario = array ();
    $vocales = array('a','e','i','o','u');
    
    //Ahora vamos a insertar los casos especiales, vocales con tildes
    if (in_array($expresion,$vocales))
    {
        switch ($expresion)
        {
            case 'a':
                $expresion = utf8_decode('a áà');
                break;
            case 'e':
                $expresion = utf8_decode('e éè');
                break;
            case 'i':
                $expresion = utf8_decode('i íì');
                break;
            case 'o':
                $expresion = utf8_decode('o óò');
                break;
            case 'u':
                $expresion = utf8_decode('u úù');
                break;
           }
    }   
	$sql = "SELECT * FROM $glosario_table where lower(left(palabra,1)) REGEXP '[" . $expresion . "]' ORDER BY palabra";
	$result = api_sql_query($sql,__FILE__,__LINE__);
	while ($temp_row = Database::fetch_array($result))
	{
		$glosario[] = $temp_row;
	}
	return $glosario;
}

/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 
if (isset($_GET['action']) && $_GET['action']=='delete')
    {
        $table_glosario = Database::get_course_table(TABLE_GLOSARIO);
        $sql = "delete from $table_glosario where id=" . $_GET['id'];
       	api_sql_query($sql, __FILE__, __LINE__);
       	$msg = get_lang("ContenidoDelete");
    }
if (isset($_GET['msg']))
{
    $msg = $_GET['msg'];
}
    
if ($msg!="")
	{
	    
		Display::display_confirmation_message($msg);
	}

?>
<style> 
table.servicesT
{	font-family: Verdana;
font-weight: normal;
font-size: 12px;
color: #404040;
width: 800px;
background-color: #fafafa;
border: 1px #6699CC solid;
border-collapse: collapse;
border-spacing: 0px;
margin-top: 0px;}
table.servicesT td
{	border-bottom: 1px dotted #6699CC;
font-family: Verdana, sans-serif, Arial;
font-weight: normal;
font-size: 11px;
color: #404040;
background-color: white;
text-align: left;
padding-left: 3px;} 
</style>
<script language="javascript">
</script>

<div style="float:top">
<a href="index.php?letra=a" target="_self">A</a>
<a href="index.php?letra=b" target="_self">B</a>
<a href="index.php?letra=c" target="_self">C</a>
<a href="index.php?letra=d" target="_self">D</a>
<a href="index.php?letra=e" target="_self">E</a>
<a href="index.php?letra=f" target="_self">F</a>
<a href="index.php?letra=g" target="_self">G</a>
<a href="index.php?letra=h" target="_self">H</a>
<a href="index.php?letra=i" target="_self">I</a>
<a href="index.php?letra=j" target="_self">J</a>
<a href="index.php?letra=k" target="_self">K</a>
<a href="index.php?letra=l" target="_self">L</a>
<a href="index.php?letra=m" target="_self">M</a>
<a href="index.php?letra=n" target="_self">N</a>
<a href="index.php?letra=o" target="_self">O</a>
<a href="index.php?letra=p" target="_self">P</a>
<a href="index.php?letra=q" target="_self">Q</a>
<a href="index.php?letra=r" target="_self">R</a>
<a href="index.php?letra=s" target="_self">S</a>
<a href="index.php?letra=t" target="_self">T</a>
<a href="index.php?letra=u" target="_self">U</a>
<a href="index.php?letra=v" target="_self">V</a>
<a href="index.php?letra=w" target="_self">W</a>
<a href="index.php?letra=x" target="_self">X</a>
<a href="index.php?letra=y" target="_self">Y</a>
<a href="index.php?letra=z" target="_self">Z</a>
</div>
<br>
<div style="float:left">		
<?php                
if ($is_allowed_to_edit)
		{
			echo '<a href="glosarioAdd.php" target="_self">'. get_lang('TerminoAdd') . '</a>';
	    }
?>
<br><br>
<?php
if (isset($_GET['letra']))
{
    $letra = $_GET['letra'];
}
else
{
    //no tenemos una letra... ponemos la a por defecto
        $letra = 'a';
}

    //recibimos una letra por lo que mostramos un listado de elementos
    $words = GetWords($letra);
    echo '<table class="servicesT">';
    if (count($words)>0)
		{
	        if ($is_allowed_to_edit)
		        {
		            echo '<tr><td><b>'. get_lang('Opciones') . '</b></td><td><b>'. get_lang('Termino') . '</b></td><td><b>'. get_lang('Descripcion') . '</b></td></tr>';
		        }
		    else
		        {
		            echo '<tr><td><b>'. get_lang('Termino') . '</b></td><td><b>'. get_lang('Descripcion') . '</b></td></tr>';
		        }
		        
			foreach ($words as $element)
			{	
			    echo '<tr>';
			     /* Si tenemos permisos de edición podremos editar y eliminar*/
		        if ($is_allowed_to_edit)
		        {
                    echo '<td><a href="glosarioEdit.php?id='.$element['id'].'" target="_self"><img src="../img/edit.gif"></a>
                    <a href="index.php?action=delete&id='.$element['id'].'"  onclick="javascript:if(!confirm('."'".addslashes(htmlentities(get_lang("ConfirmYourChoice"),ENT_QUOTES,$charset))."'".')) return false;"><img src="../img/delete.gif"></a>';
                    	
		        }
			    echo '<td>' . $element['palabra'] . '</td><td>' . $element['descripcion'] . '</td>';
   			    echo '</tr>';
			}
		}
    else
        {
            echo '<tr><td>'. get_lang('NoHayElementos') . '</td></tr>';
        }
    echo '</table>';

?>
</div>
<?php
/*
==============================================================================
		FOOTER 
==============================================================================

*/ 
Display::display_footer();
?>
