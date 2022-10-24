<?php
/**
 * 增加自定义标记
 *
 * @version        $Id: mytag_add.php 1 15:35 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert(Lang("err_safemode_check"),ALERT_DANGER));
}
UserLogin::CheckPurview('temp_Other');
if (empty($dopost)) $dopost = "";
if ($dopost == "save") {
    CheckCSRF();
    $tagname = trim($tagname);
    $row = $dsql->GetOne("SELECT typeid FROM `#@__mytag` WHERE typeid='$typeid' AND tagname LIKE '$tagname'");
    if (is_array($row)) {
        ShowMsg(Lang("mytag_add_err_same"), "-1");
        exit();
    }
    $starttime = GetMkTime($starttime);
    $endtime = GetMkTime($endtime);
    $inQuery = "INSERT INTO `#@__mytag`(typeid,tagname,timeset,starttime,endtime,normbody,expbody) VALUES ('$typeid','$tagname','$timeset','$starttime','$endtime','$normbody','$expbody');";
    $dsql->ExecuteNoneQuery($inQuery);
    ShowMsg(Lang("mytag_add_success"), "mytag_main.php");
    exit();
}
$startDay = time();
$endDay = AddDay($startDay, 30);
$startDay = GetDateTimeMk($startDay);
$endDay = GetDateTimeMk($endDay);
include DedeInclude('templets/mytag_add.htm');
?>