<?php
/**
 * 主题模板编译
 *
 * @version        $id:templets_one.php 23:07 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('temp_One');
require_once(DEDEINC."/datalistcp.class.php");
DedeSetCookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
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
    return $im == 1 ? '需编译' : '不编译';
}
?>