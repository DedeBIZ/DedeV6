<?php
/**
 * 会员操作日志记录管理
 *
 * @version        $Id: member_operations.php 1 11:24 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DataListCP;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('member_Operations');
setcookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
if (empty($buyid)) $buyid = '';
$addsql = " WHERE buyid LIKE '%$buyid%' ";
if (isset($sta)) $addsql .= " AND sta='$sta' ";
$sql = "SELECT * FROM `#@__member_operation` $addsql ORDER BY aid DESC";
$dlist = new DataListCP();
//设定每页显示记录数
$dlist->pagesize = 30;
$dlist->SetParameter("buyid", $buyid);
if (isset($sta)) $dlist->SetParameter("sta", $sta);
$dlist->dsql->SetQuery("SELECT * FROM `#@__moneycard_type`");
$dlist->dsql->Execute('ts');
while ($rw = $dlist->dsql->GetArray('ts')) {
    $TypeNames[$rw['tid']] = $rw['pname'];
}
$tplfile = DEDEADMIN."/templets/member_operations.htm";
//这两句的顺序不能更换
$dlist->SetTemplate($tplfile);      //载入模板
$dlist->SetSource($sql);            //设定查询SQL
$dlist->Display();                  //显示
function GetMemberID($mid)
{
    global $dsql;
    if ($mid == 0) {
        return '0';
    }
    $row = $dsql->GetOne("SELECT userid FROM `#@__member` WHERE mid='$mid'");
    if (is_array($row)) {
        return "<a href='member_view.php?id={$mid}'>".$row['userid']."</a>";
    } else {
        return '0';
    }
}
function GetPType($tname)
{
    if ($tname == 'card') return Lang('member_ptype_card');
    else if ($tname == 'archive') return Lang('member_ptype_archive');
    else if ($tname == 'stc') return Lang('member_ptype_stc');
    else return Lang('member_ptype_other');
}
function GetSta($sta)
{
    if ($sta == 0) {
        return Lang('member_operations_sta_0');
    } else if ($sta == 1) {
        return Lang('member_operations_sta_1');
    } else {
        return Lang('member_operations_sta_2');
    }
}
?>