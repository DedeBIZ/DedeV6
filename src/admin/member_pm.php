<?php
/**
 * 会员短消息管理
 *
 * @version        $Id: member_pm.php 1 11:24 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DataListCP;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('member_Pm');
setcookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
if (!isset($folder)) $folder = '';
if (!isset($username)) $username = '';
if (!isset($keyword)) $keyword = '';
if (isset($dopost)) {
    $ID = preg_replace("#[^0-9]#", "", $ID);
    if ($dopost == "del" && !empty($ID)) {
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_pms` WHERE id='$ID'");
    }
}
$whereSql = '';
if (!empty($folder)) $whereSql = "WHERE folder='$folder'";
$postuser = Lang("member_postuser_1");
if ($folder == "inbox" || $folder == '') $postuser = Lang("member_postuser_2");
if (!empty($keyword)) {
    $whereSql .= " AND (subject like '%".$keyword."%' OR message like '%".$keyword."%')";
}
if (!empty($username)) {
    $whereSql .= " AND floginid like '%".$username."%'";
}
$sql = "SELECT * FROM `#@__member_pms` $whereSql ORDER BY sendtime DESC";
$dlist = new DataListCP();
$dlist->pagesize = 30;
$dlist->SetParameter("folder", $folder);
$dlist->SetParameter("username", $username);
$dlist->SetParameter("keyword", $keyword);
$dlist->SetTemplate(DEDEADMIN."/templets/member_pm.htm");
$dlist->SetSource($sql);
$dlist->Display();
$dlist->Close();
function GetFolders($me)
{
    if ($me == "outbox") return Lang('member_outbox');
    else if ($me == "inbox") return Lang('member_inbox');
}
function IsReader($me)
{
    $me = preg_replace("#[^0-1]#", "", $me);
    if ($me) return "<span class='text-dark'>√</span>";
    else return "<span class='text-danger'>×</span>";
}
?>