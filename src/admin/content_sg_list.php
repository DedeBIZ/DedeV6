<?php
/**
 * 单表文档列表
 *
 * @version        $id:content_sg_list.php 14:31 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
$channelid = isset($channelid) ? intval($channelid) : 0;
$mid = isset($mid) ? intval($mid) : 0;
if (!isset($keyword)) $keyword = '';
if (!isset($arcrank)) $arcrank = '';
if (empty($cid) && empty($channelid)) {
    ShowMsg("该页面必须指定栏目id或文档模型id才能浏览", "javascript:;");
    exit();
}
//检查权限许可，总权限
CheckPurview('a_List,a_AccList,a_MyList');
//栏目浏览许可
if (TestPurview('a_List')) {
} else if (TestPurview('a_AccList')) {
    if ($cid == 0) {
        $ucid = $cid = $cuserLogin->getUserChannel();
    } else {
        CheckCatalog($cid, "您无权浏览非指定栏目的文档");
    }
}
$cid = isset($cid) ? intval($cid) : 0;
$adminid = $cuserLogin->getUserID();
$maintable = '#@__archives';
require_once(DEDEINC."/typelink/typelink.class.php");
require_once(DEDEINC."/datalistcp.class.php");
require_once(DEDEADMIN."/inc/inc_list_functions.php");
DedeSetCookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
$tl = new TypeLink($cid);
$listtable = @trim($tl->TypeInfos['addtable']);
if (!empty($channelid) && !empty($ucid) && $tl->TypeInfos['channeltype'] != $channelid) {
    ShowMsg('您没权限浏览此页', 'javascript:;');
    exit();
}
if ($cid == 0) {
    $row = $tl->dsql->GetOne("SELECT typename,addtable FROM `#@__channeltype` WHERE id='$channelid'");
    $positionname = $row['typename']." - ";
    $listtable = $row['addtable'];
} else {
    $positionname = str_replace($cfg_list_symbol, " - ", $tl->GetPositionName())." - ";
}
$optionarr = $tl->GetOptionArray($cid, $admin_catalogs, $channelid);
$whereSql = $channelid == 0 ? " WHERE arc.channel < -1 " : " WHERE arc.channel = '$channelid' ";
$stime = 0;
$etime = 0;
$timerange = isset($timerange)? explode(" - ",$timerange) : array();
if (count($timerange) === 2) {
    $stime = strtotime($timerange[0]);
    $etime = strtotime($timerange[1]);
}
if ($stime > $etime) {
    $stime = 0;
    $etime = 0;
}
if (!empty($mid)) $whereSql .= " AND arc.mid = '$mid' ";
if ($stime > 0 && $etime > 0) {
    $whereSql .=  "AND arc.senddate>$stime AND arc.senddate<$etime";
}
if ($keyword != '') $whereSql .= " AND (arc.title like '%$keyword%') ";
if ($cid != 0 && !empty(GetSonIds($cid))) $whereSql .= " AND arc.typeid in (".GetSonIds($cid).")";
if ($arcrank != '') {
    $whereSql .= " AND arc.arcrank = '$arcrank' ";
    $CheckUserSend = "<button type='button' class='btn btn-success btn-sm' onclick=\"location='content_sg_list.php?cid={$cid}&channelid={$channelid}&dopost=listArchives';\">所有文档</button>";
} else {
    $CheckUserSend = "<button type='button' class='btn btn-success btn-sm' onclick=\"location='content_sg_list.php?cid={$cid}&channelid={$channelid}&dopost=listArchives&arcrank=-1';\">稿件审核</button>";
}
$query = "SELECT arc.aid,arc.aid as id,arc.typeid,arc.arcrank,arc.flag,arc.senddate,arc.channel,arc.title,arc.mid,arc.click,tp.typename,ch.typename as channelname FROM `$listtable` arc LEFT JOIN `#@__arctype` tp ON tp.id=arc.typeid LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel $whereSql ORDER BY arc.aid DESC";
$dlist = new DataListCP();
$dlist->pagesize = 30;
$dlist->SetParameter("dopost", "listArchives");
$dlist->SetParameter("keyword", $keyword);
$dlist->SetParameter("cid", $cid);
$dlist->SetParameter("channelid", $channelid);
$strTimerange = '';
if ($stime > 0 && $etime > 0) {
    $strTimerange = implode(" - ",array(MyDate("Y-m-d H:i:s",$stime),MyDate("Y-m-d H:i:s",$etime)));
    $dlist->SetParameter('timerange', $strTimerange);
}
$dlist->SetTemplate(DEDEADMIN."/templets/content_sg_list.htm");
$dlist->SetSource($query);
$dlist->Display();
$dlist->Close();
?>