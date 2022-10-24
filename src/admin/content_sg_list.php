<?php
/**
 * 单表模型列表
 *
 * @version        $Id: content_sg_list.php 1 14:31 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DataListCP;
use DedeBIZ\Login\UserLogin;
use DedeBIZ\TypeLink\TypeLink;
require_once(dirname(__FILE__)."/config.php");
$cid = isset($cid) ? intval($cid) : 0;
$channelid = isset($channelid) ? intval($channelid) : 0;
$mid = isset($mid) ? intval($mid) : 0;
if (!isset($keyword)) $keyword = '';
if (!isset($arcrank)) $arcrank = '';
if (empty($cid) && empty($channelid)) {
    ShowMsg(Lang("content_err_cid_channelid_isempty"), "javascript:;");
    exit();
}
//检查权限许可，总权限
UserLogin::CheckPurview('a_List,a_AccList,a_MyList');
//栏目浏览许可
if (UserLogin::TestPurview('a_List')) {
} else if (UserLogin::TestPurview('a_AccList')) {
    if ($cid == 0) {
        $ucid = $cid = $cUserLogin->getUserChannel();
    } else {
        UserLogin::CheckCatalog($cid, Lang("catalog_err_norank"));
    }
}
$adminid = $cUserLogin->getUserID();
$maintable = '#@__archives';
require_once(DEDEADMIN."/inc/inc_list_functions.php");
setcookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
$tl = new TypeLink($cid);
$listtable = @trim($tl->TypeInfos['addtable']);
if (!empty($channelid) && !empty($ucid) && $tl->TypeInfos['channeltype'] != $channelid) {
    ShowMsg(Lang('catalog_err_noperm'), 'javascript:;');
    exit();
}
if ($cid == 0) {
    $row = $tl->dsql->GetOne("SELECT typename,addtable FROM `#@__channeltype` WHERE id='$channelid'");
    $positionname = $row['typename']." &gt; ";
    $listtable = $row['addtable'];
} else {
    $positionname = str_replace($cfg_list_symbol, " &gt; ", $tl->GetPositionName())." &gt; ";
}
$optionarr = $tl->GetOptionArray($cid, $admin_catalogs, $channelid);
$whereSql = $channelid == 0 ? " WHERE arc.channel < -1 " : " WHERE arc.channel = '$channelid' ";
if (!empty($mid)) $whereSql .= " AND arc.mid = '$mid' ";
if ($keyword != '') $whereSql .= " AND (arc.title like '%$keyword%') ";
if ($cid != 0) $whereSql .= " AND arc.typeid in (".GetSonIds($cid).") ";
if ($arcrank != '') {
    $whereSql .= " AND arc.arcrank = '$arcrank' ";
    $CheckUserSend = "<button type='button' class='btn btn-success btn-sm' onClick=\"location='content_sg_list.php?cid={$cid}&channelid={$channelid}&dopost=listArchives';\">".Lang('content_list_all')."</button>";
} else {
    $CheckUserSend = "<button type='button' class='btn btn-success btn-sm' onClick=\"location='content_sg_list.php?cid={$cid}&channelid={$channelid}&dopost=listArchives&arcrank=-1';\">".Lang('content_uncheck')."</button>";
}
$query = "SELECT arc.aid,arc.aid as id,arc.typeid,arc.arcrank,arc.flag,arc.senddate,arc.channel,arc.title,arc.mid,arc.click,tp.typename,ch.typename as channelname FROM `$listtable` arc LEFT JOIN `#@__arctype` tp ON tp.id=arc.typeid LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel $whereSql ORDER BY arc.aid DESC";
$dlist = new DataListCP();
$dlist->pagesize = 30;
$dlist->SetParameter("dopost", "listArchives");
$dlist->SetParameter("keyword", $keyword);
$dlist->SetParameter("cid", $cid);
$dlist->SetParameter("channelid", $channelid);
$dlist->SetTemplate(DEDEADMIN."/templets/content_sg_list.htm");
$dlist->SetSource($query);
$dlist->Display();
$dlist->Close();
?>