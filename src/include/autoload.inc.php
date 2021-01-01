<?php
if(!defined('DEDEINC')) exit("Request Error!");
/**
 * @version        $Id: autoload.inc.php 1 17:44 2020-09-22 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */

function __autoload($classname)
{
    $classname = preg_replace("/[^0-9a-z_]/i", '', $classname);
    if( class_exists ( $classname ) )
    {
        return TRUE;
    }
    $libclassfile = $classname.'.class.php';
    if ( is_file ( DEDEINC.'/'.$libclassfile ) )
    {
        require DEDEINC.'/'.$libclassfile;
    }
    else
    {
        if (DEBUG_LEVEL === TRUE)
        {
            echo '<pre>';
            echo $classname.'类找不到';
            echo '</pre>';
            exit ();
        }
        else
        {
            header ( "location:/404.html" );
            die ();
        }
    }
}
