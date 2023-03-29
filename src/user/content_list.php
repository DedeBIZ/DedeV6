<?php
/**
 * 文档列表
 * 
 * @version        $id:content_list.php 13:52 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/typelink/typelink.class.php");
require_once(DEDEINC."/datalistcp.class.php");
require_once(DEDEMEMBER."/inc/inc_list_functions.php");
CheckRank(0, 0);
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
$cInfos = $tl->dsql->GetOne("SELECT arcsta,issend,issystem,usertype FROM `#@__channeltype` WHERE id='$channelid';");
if (!is_array($cInfos)) {
    ShowMsg('模型不存在', '-1');
    exit();
}
$arcsta = $cInfos['arcsta'];
$dtime = time();
$maxtime = $cfg_mb_editday * 24 * 3600;
//禁止浏览无权限的模型
if ($cInfos['usertype'] != '' && $cInfos['usertype'] != $cfg_ml->M_MbType) {
    ShowMsg('您无权限浏览该部分', '-1');
    exit();
}
if ($cid == 0) {
    $row = $tl->dsql->GetOne("SELECT typename FROM `#@__channeltype` WHERE id='$channelid'");
    if (is_array($row)) {
        $positionname = $row['typename'];
    }
} else {
    $positionname = str_replace($cfg_list_symbol, "", $tl->GetPositionName())." ";
}
$whereSql = " where arc.channel = '$channelid' And arc.mid='$mid' ";
if ($keyword != '') {
    $keyword = cn_substr(trim(preg_replace("#[><\|\"\r\n\t%\*\.\?\(\)\$ ;,'%-]#", "", stripslashes($keyword))), 30);
    $keyword = addslashes($keyword);
    $whereSql .= " And (arc.title like '%$keyword%') ";
}
if ($cid != 0) $whereSql .= " And arc.typeid in (".GetSonIds($cid).")";
//添加分类查询
if ($arcrank == '1') {
    $whereSql .= " And arc.arcrank >= 0";
} else if ($arcrank == '-1') {
    $whereSql .= " And arc.arcrank = -1";
} else if ($arcrank == '-2') {
    $whereSql .= " And arc.arcrank = -2";
}
$classlist = '';
$dsql->SetQuery("SELECT * FROM `#@__mtypes` WHERE `mid` = '$cfg_ml->M_ID';");
$dsql->Execute();
while ($row = $dsql->GetArray()) {
    $classlist .= "<option value='content_list.php?channelid=".$channelid."&mtypesid=".$row['mtypeid']."'>".$row['mtypename']."</option>\r\n";
}
if ($mtypesid != 0) {
    $whereSql .= " And arc.mtype = '$mtypesid'";
}
$query = "SELECT arc.id,arc.typeid,arc.senddate,arc.flag,arc.ismake,arc.channel,arc.arcrank,arc.click,arc.title,arc.color,arc.litpic,arc.pubdate,arc.mid,tp.typename,ch.typename as channelname FROM `#@__archives` arc LEFT JOIN `#@__arctype` tp on tp.id=arc.typeid LEFT JOIN `#@__channeltype` ch on ch.id=arc.channel $whereSql ORDER BY arc.senddate DESC";
$dlist = new DataListCP();
$dlist->pagesize = 10;
$dlist->SetParameter("dopost", "listArchives");
$dlist->SetParameter("keyword", $keyword);
$dlist->SetParameter("cid", $cid);
$dlist->SetParameter("channelid", $channelid);
$dlist->SetTemplate(DEDEMEMBER."/templets/content_list.htm");
$dlist->SetSource($query);
$dlist->Display();
?>