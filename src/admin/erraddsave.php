<?php
/**
 * 纠错管理
 *
 * @version        $Id: erraddsave.php 1 19:09 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DataListCP;
use DedeBIZ\libraries\DedeWin;
require_once(dirname(__FILE__).'/config.php');
require_once(DEDEINC.'/common.func.php');
if (empty($dopost)) $dopost = '';
if (empty($fmdo)) $fmdo = '';
function username($mid)
{
    global $dsql;
    if (!isset($mid) || empty($mid)) {
        return Lang("tourist");
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
            return $me = Lang("erraddsave_type_1");
            break;
        case $me == 2:
            return $me = Lang("erraddsave_type_2");
            break;
        case $me == 3:
            return $me = Lang("erraddsave_type_3");
            break;
        case $me == 4:
            return $me = Lang("erraddsave_type_4");
            break;
        case $me == 5:
            return $me = Lang("erraddsave_type_5");
            break;
        case $me == 6:
            return $me = Lang("erraddsave_type_6");
            break;
        case $me == 7:
            return $me = Lang("erraddsave_type_7");
            break;
        default:
            return $me = Lang("erraddsave_type_unknow");
            break;
    }
}
if ($dopost == "delete") {
    if ($id == '') {
        ShowMsg(Lang("invalid_parameter"), "-1");
        exit();
    }
    if ($fmdo == 'yes') {
        $id = explode("`", $id);
        foreach ($id as $var) {
            $query = "DELETE FROM `#@__erradd` WHERE `id` = '$var'";
            $dsql->ExecuteNoneQuery($query);
        }
        ShowMsg(Lang("content_delete_success"), "erraddsave.php");
        exit();
    } else {
        $wintitle = Lang("delete");
        $wecome_info = "<a href='erraddsave.php'>".Lang('erraddsave')."</a>::".Lang('erraddsave_delete');
        DedeWin::Instance()->Init("erraddsave.php", "js/blank.js", "POST")
        ->AddHidden("fmdo", "yes")
        ->AddHidden("dopost", $dopost)
        ->AddHidden("id", $id)
        ->AddTitle(Lang('content_delete_confirm',array('qstr'=>$id)))
        ->GetWindow("ok")
        ->Display();
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