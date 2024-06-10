<?php
/**
 * 修改自定义标记
 *
 * @version        $id:mytag_edit.php 15:37 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert("系统已启用安全模式，无法使用当前功能",ALERT_DANGER));
}
CheckPurview('temp_Other');
require_once(DEDEINC."/typelink/typelink.class.php");
if (empty($dopost)) $dopost = '';
$aid = intval($aid);
$ENV_GOBACK_URL = empty($_COOKIE['ENV_GOBACK_URL']) ? 'mytag_main.php' : $_COOKIE['ENV_GOBACK_URL'];
if ($dopost == 'delete') {
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__mytag` WHERE aid='$aid'");
    ShowMsg("成功删除一个自定义标记", $ENV_GOBACK_URL);
    exit();
} else if ($dopost == "saveedit") {
    CheckCSRF();
    $starttime = GetMkTime($starttime);
    $endtime = GetMkTime($endtime);
    $query = "UPDATE `#@__mytag` SET typeid='$typeid',timeset='$timeset',starttime='$starttime',endtime='$endtime',normbody='$normbody',expbody='$expbody' WHERE aid='$aid' ";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("成功修改一个自定义标记", $ENV_GOBACK_URL);
    exit();
} else if ($dopost == "getjs") {
    require_once(DEDEINC."/libraries/oxwindow.class.php");
    $jscode = "<script src='{$cfg_phpurl}/mytag_js.php?aid=$aid'></script>";
    $showhtml = "<xmp>\r\n\r\n$jscode\r\n\r\n</xmp>";
    $showhtml .= "<iframe name='testfrm' frameborder='0' id='testfrm' src='mytag_edit.php?aid={$aid}&dopost=testjs'></iframe>";
    $wintitle = "获取自定义标记标签";
    $win = new OxWindow();
    $win->Init();
    $winform = $win->GetWindow("hand", $showhtml);
    $win->Display();
    exit();
} else if ($dopost == "testjs") {
    echo "<body>";
    echo "<script src='{$cfg_phpurl}/mytag_js.php?aid=$aid&nocache=1'></script>";
    exit();
}
$row = $dsql->GetOne("SELECT * FROM `#@__mytag` WHERE aid='$aid'");
include DedeInclude('templets/mytag_edit.htm');
?>