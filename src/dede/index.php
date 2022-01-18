<?php

/**
 * 管理后台首页
 *
 * @version        $Id: index.php 1 11:06 2010年7月13日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */

if (preg_match("#PHP (.*) Development Server#", $_SERVER['SERVER_SOFTWARE'])) {
    if ($_SERVER['REQUEST_URI'] == dirname($_SERVER['SCRIPT_NAME'])) {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location:'.$_SERVER['REQUEST_URI'].'/');
    }
}


require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC.'/dedetag.class.php');
$defaultIcoFile = DEDEDATA.'/admin/quickmenu.txt';
$myIcoFile = DEDEDATA.'/admin/quickmenu-'.$cuserLogin->getUserID().'.txt';

if (!file_exists($myIcoFile)) $myIcoFile = $defaultIcoFile;

require(DEDEADMIN.'/inc/inc_menu_map.php');
include(DEDEADMIN.'/templets/index2.htm');
exit();
