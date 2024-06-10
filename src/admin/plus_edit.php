<?php
/**
 * 修改插件
 *
 * @version        $id:plus_edit.php 15:46 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_plus');
$aid = preg_replace("#[^0-9]#", "", $aid);
if ($dopost == "show") {
    $dsql->ExecuteNoneQuery("UPDATE `#@__plus` SET isshow=1 WHERE aid='$aid';");
    ShowMsg("启用一个插件", "plus_main.php");
    exit();
} else if ($dopost == "hide") {
    $dsql->ExecuteNoneQuery("UPDATE `#@__plus` SET isshow=0 WHERE aid='$aid';");
    ShowMsg("隐藏一个插件", "plus_main.php");
    exit();
} else if ($dopost == "delete") {
    if (empty($job)) $job = '';
    if ($job == "") {
        //确认
        require_once(DEDEINC."/libraries/oxwindow.class.php");
        $wintitle = "删除指定插件";
        $win = new OxWindow();
        $win->Init("plus_edit.php", "/static/web/js/admin.blank.js", "POST");
        $win->AddHidden("job", "yes");
        $win->AddHidden("dopost", $dopost);
        $win->AddHidden("aid", $aid);
        $win->AddTitle("您确定要删除".$title."插件吗");
        $win->AddMsgItem("<tr><td>提示：仅删除插件导航，前往<a href='module_main.php?moduletype=plus'>模块管理</a>卸载删除</td></tr>");
        $winform = $win->GetWindow("ok");
        $win->Display();
        exit();
    } else if ($job == "yes") {
        //操作
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__plus` WHERE aid='$aid';");
        ShowMsg("删除一个插件", "plus_main.php");
        exit();
    }
} else if ($dopost == "saveedit") {
    //保存
    $inquery = "UPDATE `#@__plus` SET plusname='$plusname',menustring='$menustring',filelist='$filelist' WHERE aid='$aid';";
    $dsql->ExecuteNoneQuery($inquery);
    ShowMsg("完成修改插件配置", "plus_main.php");
    exit();
}
$row = $dsql->GetOne("SELECT * FROM `#@__plus` WHERE aid='$aid'");
include DedeInclude('templets/plus_edit.htm');
?>