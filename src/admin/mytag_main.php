<?php
/**
 * 自定义模型管理
 *
 * @version        $Id: mychannel_main.php 1 15:26 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DataListCP;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__).'/config.php');
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert(Lang("err_safemode_check"),ALERT_DANGER));
}
UserLogin::CheckPurview('temp_Other');
setcookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, '/');
make_hash();
$sql = "SELECT myt.aid,myt.tagname,tp.typename,myt.timeset,myt.endtime FROM `#@__mytag` myt LEFT JOIN `#@__arctype` tp ON tp.id=myt.typeid ORDER BY myt.aid DESC";
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN.'/templets/mytag_main.htm');
$dlist->SetSource($sql);
$dlist->display();
function TestType($tname)
{
    return $tname == '' ? Lang('catalog_all') : $tname;
}
function TimeSetValue($ts)
{
    return $ts == 0 ? Lang('mytag_ts_0') : Lang('mytag_ts_1');
}
?>