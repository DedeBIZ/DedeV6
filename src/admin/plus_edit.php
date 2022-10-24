<?php
/**
 * 插件编辑
 *
 * @version        $Id: plus_edit.php 1 15:46 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('sys_plus');
$aid = preg_replace("#[^0-9]#", "", $aid);
if ($dopost == "show") {
    $dsql->ExecuteNoneQuery("UPDATE `#@__plus` SET isshow=1 WHERE aid='$aid';");
    ShowMsg(Lang("plus_success_show"), "plus_main.php");
    exit();
} else if ($dopost == "hide") {
    $dsql->ExecuteNoneQuery("UPDATE `#@__plus` SET isshow=0 WHERE aid='$aid';");
    ShowMsg(Lang("plus_success_hide"), "plus_main.php");
    exit();
} else if ($dopost == "delete") {
    if (empty($job)) $job = "";
    if ($job == "") //确认提示
    {
        $wintitle = Lang("plus_delete");
        $wecome_info = "<a href='plus_main.php'>".Lang('plus_main')."</a>::".Lang('plus_delete');
        DedeWin::Instance()->Init("plus_edit.php", "js/blank.js", "POST")->AddHidden("job", "yes")
        ->AddHidden("dopost", $dopost)->AddHidden("aid", $aid)->AddTitle(Lang('plus_delete_title',array('title'=>$title)))
        ->AddMsgItem(Lang("plus_delete_msg"))->GetWindow("ok")->Display();
        exit();
    } else if ($job == "yes") //操作
    {
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__plus` WHERE aid='$aid';");
        ShowMsg(Lang("plus_delete_success"), "plus_main.php");
        exit();
    }
} else if ($dopost == "saveedit") //保存修改
{
    $inquery = "UPDATE `#@__plus` SET plusname='$plusname',menustring='$menustring',filelist='$filelist' WHERE aid='$aid';";
    $dsql->ExecuteNoneQuery($inquery);
    ShowMsg(Lang("plus_saveedit_success"), "plus_main.php");
    exit();
}
$row = $dsql->GetOne("SELECT * FROM `#@__plus` WHERE aid='$aid'");
include DedeInclude('templets/plus_edit.htm');
?>