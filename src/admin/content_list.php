<?php
/**
 * 所有文档列表
 *
 * @version        $id:content_list.php 14:31 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
require_once(DEDEINC.'/typelink/typelink.class.php');
require_once(DEDEINC.'/datalistcp.class.php');
require_once(DEDEADMIN.'/inc/inc_list_functions.php');
$cid = isset($cid) ? intval($cid) : 0;
$channelid = isset($channelid) ? intval($channelid) : 0;
$mid = isset($mid) ? intval($mid) : 0;
if (!isset($keyword)) $keyword = '';
if (!isset($flag)) $flag = '';
if (!isset($f)) $f = '';
if (!isset($arcrank)) $arcrank = '';
if (!isset($dopost)) $dopost = '';
$arcrank = RemoveXSS($arcrank);
//检查权限许可，总权限
CheckPurview('a_List,a_AccList,a_MyList');
//栏目浏览许可
$userCatalogSql = '';
if (TestPurview('a_List')) {;
} else if (TestPurview('a_AccList')) {
    if ($cid == 0 && $cfg_admin_channel == 'array') {
        $admin_catalog = join(',', $admin_catalogs);
        $userCatalogSql = " arc.typeid IN($admin_catalog) ";
    } else {
        CheckCatalog($cid, '您无权浏览非指定栏目的文档');
    }
    if (TestPurview('a_MyList')) $mid =  $cuserLogin->getUserID();
}
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
$adminid = $cuserLogin->getUserID();
$maintable = '#@__archives';
DedeSetCookie('ENV_GOBACK_URL', $dedeNowurl, time() + 3600, '/');
$tl = new TypeLink($cid);
//在不指定排序条件和关键词的情况下直接统计微表
if (empty($totalresult) && empty($keyword) && empty($orderby) && empty($flag)) {
    $tinyQuerys = array();
    if (!empty($userCatalogSql)) {
        $tinyQuerys[] = str_replace('arc.', '', $userCatalogSql);
    }
    if (!empty($channelid) && empty($cid)) {
        $tinyQuerys[] = " channel = '$channelid' ";
    } else {
        $tinyQuerys[] = " channel > 0 ";
    }
    if (!empty($arcrank)) {
        $tinyQuerys[] = " arcrank='$arcrank' ";
    } else {
        $tinyQuerys[] = " arcrank >= 0 ";
    }
    if (!empty($mid)) {
        $tinyQuerys[] = " mid='$mid' ";
    }
    if (!empty($cid)) {
        $tinyQuerys[] = " typeid in(".GetSonIds($cid).") ";
    }
    if ($stime > 0 && $etime > 0) {
        $tinyQuerys[] = " senddate>$stime ";
        $tinyQuerys[] = " senddate<$etime ";
    }
    if (count($tinyQuerys) > 0) {
        $tinyQuery = "WHERE ".join(' AND ', $tinyQuerys);
    }
    //缓存处理
    $sql = "SELECT COUNT(*) AS dd FROM `#@__arctiny` $tinyQuery ";
    $arr = $dsql->GetOne($sql);
    $totalresult = $arr['dd'];
}
if ($cid == 0) {
    if ($channelid == 0) {
        $positionname = '所有栏目 - ';
    } else {
        $row = $tl->dsql->GetOne("SELECT id,typename,maintable FROM `#@__channeltype` WHERE id='$channelid'");
        $positionname = $row['typename']." - ";
        $maintable = $row['maintable'];
        $channelid = $row['id'];
    }
} else {
    $positionname = str_replace($cfg_list_symbol, " - ", $tl->GetPositionName())." - ";
}
//当选择的是自定义模型栏目时，直接跳转自定义模型管理区
if (empty($channelid) && isset($tl->TypeInfos['channeltype'])) {
    $channelid = $tl->TypeInfos['channeltype'];
}
if ($channelid < -1) {
    header("location:content_sg_list.php?f=$f&cid=$cid&channelid=$channelid&keyword=$keyword");
    exit();
}
//栏目大于800则需要缓存数据
$optHash = md5($cid.serialize($admin_catalogs).$channelid);
$optCache = DEDEDATA."/tplcache/inc_option_$optHash.inc";
$typeCount = 0;
if (file_exists($cache1)) require_once($cache1);
else $cfg_Cs = array();
$typeCount = count($cfg_Cs);
if ($typeCount > 800) {
    if (file_exists($optCache)) {
        $optionarr = file_get_contents($optCache);
    } else {
        $optionarr = $tl->GetOptionArray($cid, $admin_catalogs, $channelid);
        file_put_contents($optCache, $optionarr);
    }
} else {
    $optionarr = $tl->GetOptionArray($cid, $admin_catalogs, $channelid);
}
$whereSql = empty($channelid) ? " WHERE arc.channel > 0  AND arc.arcrank > -2 " : " WHERE arc.channel = '$channelid' AND arc.arcrank > -2 ";
$flagsArr = '';
$dsql->Execute('f', 'SELECT * FROM `#@__arcatt` ORDER BY sortid ASC');
while ($frow = $dsql->GetArray('f')) {
    $flagsArr .= ($frow['att'] == $flag ? "<option value='{$frow['att']}' selected>{$frow['attname']}</option>\r\n" : "<option value='{$frow['att']}'>{$frow['attname']}</option>\r\n");
}
if (!empty($userCatalogSql)) {
    $whereSql .= " AND ".$userCatalogSql;
}
if (!empty($mid)) {
    $whereSql .= " AND arc.mid = '$mid' ";
}
if ($keyword != '') {
    $whereSql .= " AND (CONCAT(arc.id,arc.title,arc.writer) LIKE '%$keyword%') ";
}
if ($flag != '') {
    $whereSql .= " AND FIND_IN_SET('$flag', arc.flag) ";
}
if ($cid != 0) {
    $whereSql .= ' AND arc.typeid IN ('.GetSonIds($cid).')';
}
if ($stime > 0 && $etime > 0) {
    $whereSql .=  "AND arc.senddate>$stime AND arc.senddate<$etime";
}
if ($arcrank != '') {
    $whereSql .= " AND arc.arcrank = '$arcrank' ";
    $CheckUserSend = "<button type='button' class='btn btn-success btn-sm' onclick=\"location='catalog_do.php?cid=".$cid."&dopost=listArchives&gurl=content_list.php';\">所有文档</button>";
} else {
    $CheckUserSend = "<button type='button' class='btn btn-success btn-sm' onclick=\"location='catalog_do.php?cid=".$cid."&dopost=listArchives&arcrank=-1&gurl=content_list.php';\">稿件审核</button>";
}
$orderby = empty($orderby) ? 'id' : preg_replace("#[^a-z0-9]#", "", $orderby);
$orderbyField = 'arc.'.$orderby;
$query = "SELECT arc.id,arc.typeid,arc.senddate,arc.flag,arc.ismake,arc.channel,arc.arcrank,arc.click,arc.title,arc.color,arc.litpic,arc.pubdate,arc.mid FROM `$maintable` arc $whereSql ORDER BY $orderbyField DESC";
if (empty($f) || !preg_match("#form#", $f)) $f = 'form1.arcid1';
$dlist = new DataListCP();
//文档列表数
$dlist->pagesize = 30;
//GET参数
$dlist->SetParameter('dopost', 'listArchives');
$dlist->SetParameter('keyword', $keyword);
if (!empty($mid)) $dlist->SetParameter('mid', $mid);
$dlist->SetParameter('cid', $cid);
$dlist->SetParameter('flag', $flag);
$dlist->SetParameter('orderby', $orderby);
$dlist->SetParameter('arcrank', $arcrank);
$dlist->SetParameter('channelid', $channelid);
$dlist->SetParameter('f', $f);
$strTimerange = '';
if ($stime > 0 && $etime > 0) {
    $strTimerange = implode(" - ",array(MyDate("Y-m-d H:i:s",$stime),MyDate("Y-m-d H:i:s",$etime)));
    $dlist->SetParameter('timerange', $strTimerange);
}
if (empty($s_tmplets)) $s_tmplets = 'templets/content_list.htm';
$dlist->SetTemplate(DEDEADMIN.'/'.$s_tmplets);
$dlist->SetSource($query);
$dlist->Display();
$dlist->Close();
?>