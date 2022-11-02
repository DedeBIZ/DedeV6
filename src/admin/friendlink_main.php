<?php
/**
 * 友情链接管理
 *
 * @version        $id:friendlink_main.php 8:48 2010年7月13日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
require_once(DEDEINC.'/datalistcp.class.php');
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
$selCheckArr = array(0 => '不限类型', -1 => '未审核', 1 => '内页', 2 => '首页');
$sql = "SELECT * FROM `#@__flink` WHERE  CONCAT(`url`,`webname`,`email`) LIKE '%$keyword%' $ischeckSql ORDER BY dtime desc";
$dlist = new DataListCP();
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('ischeck', $ischeck);
$dlist->SetTemplet(DEDEADMIN.'/templets/friendlink_main.htm');
$dlist->SetSource($sql);
$dlist->display();
function GetPic($pic)
{
    if ($pic == '') return '无图标';
    else return "<img src='$pic' style='max-width:60px;max-height:60px'>";
}
function GetSta($sta)
{
    if ($sta == 1) return '内页';
    if ($sta == 2) return '首页';
    else return '未审核';
}
?>