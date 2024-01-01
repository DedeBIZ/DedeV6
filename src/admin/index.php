<?php
/**
 * 后台管理主页
 *
 * @version        $id:index.php 11:06 2010年7月13日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
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
require(DEDEADMIN.'/inc/inc_menu.php');
require(DEDEADMIN.'/inc/inc_menu_func.php');
$openitem = (empty($openitem) ? 1 : $openitem);
include(DEDEADMIN.'/templets/index.htm');
exit();
?>