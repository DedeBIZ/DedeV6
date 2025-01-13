<?php
/**
 * 会员消费记录
 *
 * @version        $id:member_operations.php 11:24 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('member_Operations');
DedeSetCookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
require_once(DEDEINC.'/datalistcp.class.php');
if (empty($buyid)) $buyid = '';
$buyid = HtmlReplace($buyid , 1);
$sta = ''; //初始化$sta变量，感谢：乖乖女
$sta = HtmlReplace($sta, 1);
$addsql = " WHERE buyid LIKE '%$buyid%' ";
if (!empty($sta)) $addsql .= " AND sta='$sta' ";
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
    $row = $dsql->GetOne("SELECT userid FROM `#@__member` WHERE mid='$mid' ");
    if (is_array($row)) {
        return "<a href='member_edit.php?id={$mid}'>".$row['userid']."</a>";
    } else {
        return '0';
    }
}
function GetPType($tname)
{
    if ($tname == 'card') return '点数卡';
    else if ($tname == 'archive') return '购买文档';
    else if ($tname == 'stc') return '兑换金币';
    else return '会员升级';
}
function GetSta($sta)
{
    if ($sta == 0) {
        return '未付款';
    } else if ($sta == 1) {
        return '已付款';
    } else {
        return '已完成';
    }
}
?>