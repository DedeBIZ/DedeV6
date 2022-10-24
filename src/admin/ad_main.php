<?php
/**
 * 广告管理
 *
 * @version        $Id: ad_main.php 1 8:26 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DataListCP;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__).'/config.php');
require_once(DEDEINC.'/common.func.php');
UserLogin::CheckPurview('plus_广告管理');
setcookie('ENV_GOBACK_URL', $dedeNowurl, time() + 3600, '/');
$clsid = isset($clsid) ? intval($clsid) : 0;
$keyword = isset($keyword) ? addslashes($keyword) : '';
$dsql->Execute('dd', 'SELECT * FROM `#@__myadtype` ORDER BY id DESC');
$option = '';
while ($arr = $dsql->GetArray('dd')) {
    if ($arr['id'] == $clsid) {
        $option .= "<option value='{$arr['id']}' selected='selected'>{$arr['typename']}</option>\n\r";
    } else {
        $option .= "<option value='{$arr['id']}'>{$arr['typename']}</option>\n\r";
    }
}
$where_sql = ' 1=1';
if ($clsid != 0) $where_sql .= " AND clsid = $clsid";
if ($keyword != '') $where_sql .= " AND (ad.adname like '%$keyword%') ";
$sql = "SELECT ad.aid,ad.clsid,ad.tagname,tp.typename as typename,ad.adname,ad.timeset,ad.endtime,ap.typename as clsname FROM `#@__myad` ad LEFT JOIN `#@__arctype` tp on tp.id=ad.typeid LEFT JOIN `#@__myadtype` ap on ap.id=ad.clsid WHERE $where_sql ORDER BY ad.aid DESC";
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN."/templets/ad_main.htm");
$dlist->SetSource($sql);
$dlist->display();
function TestType($tname, $type = "")
{
    if ($tname == "") {
        return ($type == 1) ? Lang("ad_main_testtype_1") : Lang("ad_main_testtype_0");
    } else {
        return $tname;
    }
}
function TimeSetValue($ts)
{
    if ($ts == 0) {
        return Lang("ad_main_timeset_0");
    } else {
        return Lang("ad_main_timeset_1");
    }
}
?>