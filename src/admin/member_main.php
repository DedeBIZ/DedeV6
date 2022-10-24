<?php
/**
 * 会员管理
 *
 * @version        $Id: member_main.php 1 10:49 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DataListCP;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('member_List');
setcookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
if (!isset($sex)) $sex = '';
if (!isset($mtype)) $mtype = '';
if (!isset($spacesta)) $spacesta = -10;
if (!isset($matt)) $matt = 10;
if (!isset($keyword)) $keyword = '';
else $keyword = trim(FilterSearch($keyword));
$mtypeform = empty($mtype) ? "<option value=''>类型</option>\r\n" : "<option value='$mtype'>$mtype</option>\r\n";
$sexform = empty($sex) ? "<option value=''>性别</option>\r\n" : "<option value='$sex'>$sex</option>\r\n";
$sortkey = empty($sortkey) ? 'mid' : preg_replace("#[^a-z]#i", '', $sortkey);
$staArr = array(-2 => Lang('member_sta_-2'), -1 => Lang('member_sta_-1'), 0 => Lang('member_sta_0'), 1 => Lang('member_sta_1'), 2 => Lang('member_sta_2'));
$staArrmatt = array(1 => Lang('member_matt_1'), 0 => Lang('member_matt_0'));
$MemberTypes = array();
$dsql->SetQuery("SELECT `rank`,membername FROM `#@__arcrank` WHERE `rank`>0");
$dsql->Execute();
while ($row = $dsql->GetObject()) {
    $MemberTypes[$row->rank] = $row->membername;
}
if ($sortkey == 'mid') {
    $sortform = "<option value='mid'>mid/".Lang('member_regtime')."</option>\r\n";
} else if ($sortkey == 'rank') {
    $sortform = "<option value='rank'>".Lang('member_rank')."</option>\r\n";
} else if ($sortkey == 'money') {
    $sortform = "<option value='money'>".Lang('member_money')."</option>\r\n";
} else if ($sortkey == 'scores') {
    $sortform = "<option value='scores'>".Lang('member_scores')."</option>\r\n";
} else {
    $sortform = "<option value='logintime'>".Lang('member_logintime')."</option>\r\n";
}
$wheres[] = " (userid LIKE '%$keyword%' OR uname LIKE '%$keyword%' OR email LIKE '%$keyword%') ";
if ($sex   != '') {
    $wheres[] = " sex LIKE '$sex' ";
}
if ($mtype != '') {
    $wheres[] = " mtype LIKE '$mtype' ";
}
if ($spacesta != -10) {
    $wheres[] = " spacesta = '$spacesta' ";
}
if ($matt != 10) {
    $wheres[] = " matt= '$matt' ";
}
$whereSql = join(' AND ', $wheres);
if ($whereSql != '') {
    $whereSql = ' WHERE '.$whereSql;
}
$sql  = "SELECT * FROM `#@__member` $whereSql ORDER BY $sortkey DESC";
$dlist = new DataListCP();
$dlist->SetParameter('sex', $sex);
$dlist->SetParameter('spacesta', $spacesta);
$dlist->SetParameter('matt', $matt);
$dlist->SetParameter('mtype', $mtype);
$dlist->SetParameter('sortkey', $sortkey);
$dlist->SetParameter('keyword', $keyword);
$dlist->SetTemplet(DEDEADMIN."/templets/member_main.htm");
$dlist->SetSource($sql);
$dlist->display();
function GetMemberName($rank, $mt)
{
    global $MemberTypes;
    if (isset($MemberTypes[$rank])) {
        if ($mt == 'ut') return " <span class='text-danger'>".Lang('member_wupdate')."：".$MemberTypes[$rank]."</span>";
        else return $MemberTypes[$rank];
    } else {
        if ($mt == 'ut') return '';
        else return $mt;
    }
}
function GetMAtt($m)
{
    if ($m < 1) return '';
    else if ($m == 10) return " <span class='text-danger'>[".Lang('member_mattr')."]</span>";
    else return " <i class=\"fa fa-user-o\" aria-hidden=\"true\"></i> <span class='text-danger'>[".Lang('recommend2')."]</span>";
}
?>