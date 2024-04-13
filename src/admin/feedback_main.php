<?php
/**
 * 评论管理
 *
 * @version        $id:feedback_main.php 19:09 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
//权限检查
CheckPurview('sys_Feedback');
require_once(DEDEINC."/datalistcp.class.php");
require_once(DEDEINC."/typelink/typelink.class.php");
DedeSetCookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
function IsCheck($st)
{
    return $st==1 ? '已审核' : '待审核';
}
function jsTrimjajx($str, $len)
{
    $str = preg_replace("/{quote}(.*){\/quote}/is", '', $str);
    $str = str_replace('&lt;br/&gt;', ' ', $str);
    $str = cn_substr($str, $len);
    $str = preg_replace("/['\"\r\n]/", "", $str);
    $str = str_replace('&lt;', '<', $str);
    $str = str_replace('&gt;', '>', $str);
    return $str;
}
if (!empty($job)) {
    $ids = preg_replace("#[^0-9,]#", '', $fid);
    if (empty($ids)) {
        ShowMsg("您没选中任何选项", $_COOKIE['ENV_GOBACK_URL']);
        exit;
    }
} else {
    $job = '';
}
//更新回复统计
function UpdateReplycount($id)
{
    global $dsql;
    $row = $dsql->GetOne("SELECT COUNT(*) as dd FROM `#@__feedback` WHERE fid = $id AND ischeck=1");
    $dsql->ExecNoneQuery("UPDATE `#@__feedback` SET `replycount`='{$row['dd']}' WHERE `id`=$id;");
}
//删除评论
if ($job == 'del') {
    $query = "DELETE FROM `#@__feedback` WHERE id IN($ids) ";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("成功删除指定的评论", $_COOKIE['ENV_GOBACK_URL']);
    exit();
}
//删除相同IP的所有评论
else if ($job == 'delall') {
    $dsql->SetQuery("SELECT ip FROM `#@__feedback` WHERE id IN ($ids) ");
    $dsql->Execute();
    $ips = '';
    while ($row = $dsql->GetArray()) {
        $ips .= ($ips == '' ? " ip = '{$row['ip']}' " : " Or ip = '{$row['ip']}' ");
    }
    if ($ips != '') {
        $query = "DELETE FROM `#@__feedback` WHERE $ips ";
        $dsql->ExecuteNoneQuery($query);
    }
    ShowMsg("删除所有相同地址评论", $_COOKIE['ENV_GOBACK_URL']);
    exit();
}
//审核评论
else if ($job == 'check') {
    $query = "UPDATE `#@__feedback` SET ischeck=1 WHERE id IN($ids) ";
    $dsql->ExecuteNoneQuery($query);
    $dquery = "SELECT * FROM `#@__feedback` WHERE id IN($ids)";
    $dsql->SetQuery($dquery);
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        UpdateReplycount($row['fid']);
    }
    ShowMsg("成功审核指定评论", $_COOKIE['ENV_GOBACK_URL']);
    exit();
}
//浏览评论
else {
    $bgcolor = '';
    $typeid = isset($typeid) && is_numeric($typeid) ? $typeid : 0;
    $aid = isset($aid) && is_numeric($aid) ? $aid : 0;
    $fid = isset($fid) && is_numeric($fid) ? $fid : 0;
    $keyword = !isset($keyword) ? '' : $keyword;
    $ip = !isset($ip) ? '' : $ip;
    $tl = new TypeLink($typeid);
    $openarray = $tl->GetOptionArray($typeid, $admin_catalogs, 0);
    $addsql = ($typeid != 0  ? " And typeid IN (".GetSonIds($typeid).")" : '');
    $addsql .= ($aid != 0  ? " And aid=$aid " : '');
    $addsql .= ($ip != ''  ? " And ip LIKE '$ip' " : '');
    if ($fid > 0) {
        $addsql .= " AND fid={$fid} ";
    }
    $querystring = "SELECT * FROM `#@__feedback` WHERE msg LIKE '%$keyword%' $addsql ORDER BY dtime DESC";
    $dlist = new DataListCP();
    $dlist->pagesize = 30;
    $dlist->SetParameter('aid', $aid);
    $dlist->SetParameter('ip', $ip);
    $dlist->SetParameter('typeid', $typeid);
    $dlist->SetParameter('keyword', $keyword);
    $dlist->SetTemplate(DEDEADMIN.'/templets/feedback_main.htm');
    $dlist->SetSource($querystring);
    $dlist->Display();
}
?>