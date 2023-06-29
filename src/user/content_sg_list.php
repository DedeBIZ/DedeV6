<?php
/**
 * 图片文档列表
 * 
 * @version        $id:content_sg_list.php 13:52 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckRank(0, 0);//禁止游客操作
require_once(DEDEINC."/typelink/typelink.class.php");
require_once(DEDEINC."/datalistcp.class.php");
require_once(DEDEMEMBER."/inc/inc_list_functions.php");
setcookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
$cid = isset($cid) && is_numeric($cid) ? $cid : 0;
$channelid = isset($channelid) && is_numeric($channelid) ? $channelid : 0;
$mtypesid = isset($mtypesid) && is_numeric($mtypesid) ? $mtypesid : 0;
if (!isset($keyword)) $keyword = '';
if (!isset($arcrank)) $arcrank = '';
$positionname = '';
$menutype = 'content';
$mid = $cfg_ml->M_ID;
$tl = new TypeLink($cid);
$cInfos = $tl->dsql->GetOne("SELECT arcsta,issend,issystem,usertype,typename,addtable FROM `#@__channeltype` WHERE id='$channelid';");
if (!is_array($cInfos)) {
    ShowMsg('模型不存在', '-1');
    exit();
}
$arcsta = $cInfos['arcsta'];
//禁止浏览无权限的模型
if ($cInfos['usertype'] != '' && $cInfos['usertype'] != $cfg_ml->M_MbType) {
    ShowMsg('您无权限浏览该部分', '-1');
    exit();
}
if ($cid == 0) {
    $positionname = $cInfos['typename']." - ";
} else {
    $positionname = str_replace($cfg_list_symbol, " - ", $tl->GetPositionName())." - ";
}
$whereSql = " WHERE arc.channel = '$channelid' AND arc.mid='$mid' ";
if ($keyword != '') {
    $keyword = cn_substr(trim(preg_replace("#[><\|\"\r\n\t%\*\.\?\(\)\$ ;,'%-]#", "", stripslashes($keyword))), 30);
    $keyword = addslashes($keyword);
    $whereSql .= " AND (arc.title like '%$keyword%') ";
}
if ($cid != 0) {
    $whereSql .= " AND arc.typeid in (".GetSonIds($cid).")";
}
if ($arcrank == '1') {
    $whereSql .= " And arc.arcrank >= 0";
} else if ($arcrank == '-1') {
    $whereSql .= " And arc.arcrank = -1";
} else if ($arcrank == '-2') {
    $whereSql .= " And arc.arcrank = -2";
}
$query = "SELECT arc.aid,arc.aid as id,arc.typeid,arc.senddate,arc.channel,arc.click,arc.title,arc.mid,tp.typename,arc.arcrank FROM `{$cInfos['addtable']}` arc LEFT JOIN `#@__arctype` tp ON tp.id=arc.typeid $whereSql ORDER BY arc.aid DESC";
$dlist = new DataListCP();
$dlist->pagesize = 10;
$dlist->SetParameter("dopost", "listArchives");
$dlist->SetParameter("keyword", $keyword);
$dlist->SetParameter("cid", $cid);
$dlist->SetParameter("channelid", $channelid);
$dlist->SetTemplate(DEDEMEMBER."/templets/content_sg_list.htm");
$dlist->SetSource($query);
$dlist->Display();
?>