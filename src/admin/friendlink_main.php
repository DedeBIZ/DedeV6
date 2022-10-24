<?php
/**
 * 友情链接管理
 *
 * @version        $Id: friendlink_main.php 1 8:48 2010年7月13日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DataListCP;
require_once(dirname(__FILE__).'/config.php');
setcookie('ENV_GOBACK_URL', $dedeNowurl, time() + 3600, '/');
if (empty($keyword)) $keyword = '';
if (empty($ischeck)) {
    $ischeck = 0;
    $ischeckSql = '';
} else {
    $ischeck = intval($ischeck);
    if ($ischeck == -1) $ischeckSql = " And ischeck < 1 ";
    else $ischeckSql = " And ischeck='$ischeck' ";
}
$keyword = HtmlReplace($keyword, -1);
$selCheckArr = array(0 => Lang('friendlink_ischeck_no'), -1 => Lang('friendlink_stat_0'), 1 => Lang('friendlink_ischeck_1'), 2 => Lang('friendlink_ischeck_2'));
$sql = "SELECT * FROM `#@__flink` WHERE CONCAT(`url`,`webname`,`email`) LIKE '%$keyword%' $ischeckSql ORDER BY dtime DESC";
$dlist = new DataListCP();
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('ischeck', $ischeck);
$dlist->SetTemplet(DEDEADMIN.'/templets/friendlink_main.htm');
$dlist->SetSource($sql);
$dlist->display();
function GetPic($pic)
{
    if ($pic == '') return Lang('friendlink_nopic');
    else return "<img src='$pic' style='max-width:80px;max-height:60px'>";
}
function GetSta($sta)
{
    if ($sta == 1) return Lang('friendlink_stat_1');
    if ($sta == 2) return Lang('friendlink_stat_2');
    else return Lang('friendlink_stat_0');
}
?>