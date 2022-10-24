<?php
/**
 * 自定义标记修改
 *
 * @version        $Id: mytag_edit.php 1 15:37 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
require(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert(Lang("err_safemode_check"),ALERT_DANGER));
}
UserLogin::CheckPurview('temp_Other');
if (empty($dopost)) $dopost = '';
$aid = intval($aid);
$ENV_GOBACK_URL = empty($_COOKIE['ENV_GOBACK_URL']) ? 'mytag_main.php' : $_COOKIE['ENV_GOBACK_URL'];
if ($dopost == 'delete') {
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__mytag` WHERE aid='$aid'");
    ShowMsg(Lang("mytag_delete_success"), $ENV_GOBACK_URL);
    exit();
} else if ($dopost == "saveedit") {
    CheckCSRF();
    $starttime = GetMkTime($starttime);
    $endtime = GetMkTime($endtime);
    $query = "UPDATE `#@__mytag`
     SET
     typeid='$typeid',
     timeset='$timeset',
     starttime='$starttime',
     endtime='$endtime',
     normbody='$normbody',
     expbody='$expbody'
    WHERE aid='$aid' ";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg(Lang("mytag_edit_success"), $ENV_GOBACK_URL);
    exit();
} else if ($dopost == "getjs") {
    $jscode = "<script src='{$cfg_phpurl}/mytag_js.php?aid=$aid'></script>";
    $showhtml = "<xmp style='color:#333333;background-color:#ffffff'>\r\n\r\n$jscode\r\n\r\n</xmp>";
    $showhtml .= Lang('view')."：<iframe name='testfrm' frameborder='0' src='mytag_edit.php?aid={$aid}&dopost=testjs' id='testfrm' width='100%' height='250'></iframe>";
    $wintitle = Lang('mytag_main')."-".Lang('mytag_jscode');
    $wecome_info = "<a href='mytag_main.php'>".Lang('mytag_main')."</a>::".Lang('mytag_jscode');
    DedeWin::Instance()->Init()->AddTitle(Lang('mytag_jscode_title'))->GetWindow('hand', $showhtml)->Display();
    exit();
} else if ($dopost == "testjs") {
    echo "<body>";
    echo "<script src='{$cfg_phpurl}/mytag_js.php?aid=$aid&nocache=1'></script>";
    exit();
}
$row = $dsql->GetOne("SELECT * FROM `#@__mytag` WHERE aid='$aid'");
include DedeInclude('templets/mytag_edit.htm');
?>