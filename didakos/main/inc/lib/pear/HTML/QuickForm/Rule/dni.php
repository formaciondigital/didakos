<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors:   Jesús Zafra 
// +----------------------------------------------------------------------+
//
//

require_once('HTML/QuickForm/Rule.php');
include_once (api_get_path(LIBRARY_PATH).'utilidades.lib.php');

/**
* DNI validation rule
* @version     1.0
*/
class HTML_QuickForm_Rule_dni extends HTML_QuickForm_Rule
{

    /**
     * Validates an email address
     *
     * @param     string    $dni     DNI
     * @access    public
     * @return    boolean   true if email is valid
     */
	 
	 
    function validate($dni,  $options = null)
    {
	   
	   /*
	   Tipo:       ??? NIF CIF NIE 
       Correcto:        1   2   3   
       Incorrecto:  0  -1  -2  -3 
	   */  
 
	   $resultado = Utilidades::valida_nif_cif_nie($dni);
	   //si el resultado es un dni o un nie validos
	   if($resultado == 1 or $resultado == 3){
	   	return true;
	   }else{
		return false;
       }
	  

    }

    function getValidationScript($options = null)
    {
        return array('', "{jsVar} == ''");
    } // end func getValidationScript

} // end class HTML_QuickForm_Rule_dni
?>