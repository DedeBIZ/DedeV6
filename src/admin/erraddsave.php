<?php
/**
 * 文档错误管理
 *
 * @version        $id:erraddsave.php 19:09 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
require_once(DEDEINC.'/datalistcp.class.php');
require_once(DEDEINC.'/common.func.php');
if (empty($dopost)) $dopost = '';
if (empty($fmdo)) $fmdo = '';
function username($mid)
{
    global $dsql;
    if (!isset($mid) || empty($mid)) {
        return "游客";
        exit();
    } else {
        $sql = "SELECT uname FROM `#@__member` WHERE `mid` = '$mid'";
        $row = $dsql->GetOne($sql);
        return $row['uname'];
        exit();
    }
    exit();
}
function typename($me)
{
    switch ($me) {
        case $me == 1:
            return $me = "错别字";
            break;
        case $me == 2:
            return $me = "成语运用不当";
            break;
        case $me == 3:
            return $me = "专业术语写法不规则";
            break;
        case $me == 4:
            return $me = "产品与图片不符";
            break;
        case $me == 5:
            return $me = "事实年代以及文档错误";
            break;
        case $me == 6:
            return $me = "事实年代以及文档错误";
            break;
        case $me == 7:
            return $me = "其他错误";
            break;
        default:
            return $me = "未知错误";
            break;
    }
}
if ($dopost == "delete") {
    if ($id == '') {
        ShowMsg("参数无效", "-1");
        exit();
    }
    if ($fmdo == 'yes') {
        $id = explode("`", $id);
        foreach ($id as $var) {
            $query = "DELETE FROM `#@__erradd` WHERE `id` = '$var'";
            $dsql->ExecuteNoneQuery($query);
        }
        ShowMsg("成功删除指定的文档", "erraddsave.php");
        exit();
    } else {
        require_once(DEDEINC."/libraries/oxwindow.class.php");
        $wintitle = "删除文档错误";
        $wecome_info = "<a href='erraddsave.php'>错误管理</a> - 删除错误";
        $win = new OxWindow();
        $win->Init("erraddsave.php", "js/blank.js", "POST");
        $win->AddHidden("fmdo", "yes");
        $win->AddHidden("dopost", $dopost);
        $win->AddHidden("id", $id);
        $win->AddTitle("您确定要删除<b>$id</b>错误提示吗");
        $winform = $win->GetWindow("ok");
        $win->Display();
        exit();
    }
    exit();
}
$sql = "SELECT * FROM `#@__erradd` ORDER BY id DESC";
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN."/templets/erradd.htm");
$dlist->SetSource($sql);
$dlist->display();
?>