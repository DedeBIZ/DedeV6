<?php
/**
 * 自定义标记管理
 *
 * @version        $id:mychannel_main.php 15:26 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert("系统已启用安全模式，无法使用当前功能",ALERT_DANGER));
}
CheckPurview('temp_Other');
require_once(DEDEINC.'/datalistcp.class.php');
DedeSetCookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, '/');
make_hash();
$sql = "SELECT myt.aid,myt.tagname,tp.typename,myt.timeset,myt.endtime FROM `#@__mytag` myt LEFT JOIN `#@__arctype` tp ON tp.id=myt.typeid ORDER BY myt.aid DESC ";
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN.'/templets/mytag_main.htm');
$dlist->SetSource($sql);
$dlist->display();
function TestType($tname)
{
    return $tname == '' ? '所有栏目' : $tname;
}
function TimeSetValue($ts)
{
    return $ts == 0 ? '不限时间' : '限时标记';
}
?>