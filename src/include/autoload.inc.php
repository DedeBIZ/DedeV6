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
    $classname = strtolower($classname);
    if( class_exists ( $classname ) )
    {
        return TRUE;
    }
    if (in_array($classname, array("archives","freelist","listview","partview","rssview",
    "searchview","sglistview","sgpage","specview","taglist"))) {
        $classname = "arc.".$classname;
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
