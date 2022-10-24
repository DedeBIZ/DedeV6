<?php
/**
 * 编辑自定义表单
 *
 * @version        $Id: diy_add.php 1 14:31 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('c_Edit');
if (empty($dopost)) $dopost = "";
$diyid = (empty($diyid) ? 0 : intval($diyid));
if ($dopost == "save") {
    $public = isset($public) && is_numeric($public) ? $public : 0;
    $name = dede_htmlspecialchars($name);
    $query = "UPDATE `#@__diyforms` SET name='$name',listtemplate='$listtemplate',viewtemplate='$viewtemplate',posttemplate='$posttemplate',public='$public' WHERE diyid='$diyid'";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg(Lang("diy_success_edit"), "diy_main.php");
    exit();
}
else if ($dopost == "delete") {
    @set_time_limit(0);
    UserLogin::CheckPurview('c_Del');
    $row = $dsql->GetOne("SELECT * FROM `#@__diyforms` WHERE diyid='$diyid'");
    if (empty($job)) $job = "";
    //确认提示
    if ($job == "") {
        $wintitle = Lang('diy_main')."-".Lang('diy_delete');
        $wecome_info = "<a href='diy_main.php'>".Lang('diy_main')."</a>::".Lang('diy_delete');
        DedeWin::Instance()->Init("diy_edit.php", "js/blank.js", "POST")
        ->AddHidden("job", "yes")
        ->AddHidden("dopost", $dopost)
        ->AddHidden("diyid", $diyid)
        ->AddTitle(Lang("diy_delete_title",array("name"=>$row['name'])))
        ->GetWindow("ok")
        ->Display();
        exit();
    }
    //操作
    else if ($job == "yes") {
        $row = $dsql->GetOne("SELECT `table` FROM `#@__diyforms` WHERE diyid='$diyid'", PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            ShowMsg(Lang("diy_err_not_exists"), "-1");
            exit();
        }
        //删除表
        $dsql->ExecuteNoneQuery("DROP TABLE IF EXISTS `{$row['table']}`;");
        //删除频道配置信息
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__diyforms` WHERE diyid='$diyid'");
        ShowMsg(Lang("diy_success_delete"), "diy_main.php");
        exit();
    }
}
$row = $dsql->GetOne("SELECT * FROM `#@__diyforms` WHERE diyid='$diyid'");
include DEDEADMIN."/templets/diy_edit.htm";
?>