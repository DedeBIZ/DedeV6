<?php
/**
 * 管理一个模板
 *
 * @version        $Id: templets_one.php 1 23:07 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DataListCP;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('temp_One');
setcookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
$addquery = '';
$keyword = (!isset($keyword) ? '' : $keyword);
$likeid = (!isset($likeid) ? '' : $likeid);
$addq = $likeid != '' ? " AND likeid LIKE '$likeid' " : '';
$sql = "SELECT aid,title,ismake,uptime,filename,likeid FROM `#@__sgpage` WHERE title LIKE '%$keyword%' $addq ORDER BY aid DESC";
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN."/templets/templets_one.htm");
$dlist->SetSource($sql);
$dlist->display();
function GetIsMake($im)
{
    return $im == 1 ? Lang('needmake') : Lang('nomake');
}
?>