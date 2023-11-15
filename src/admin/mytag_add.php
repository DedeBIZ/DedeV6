<?php
/**
 * 添加自定义标记
 *
 * @version        $id:mytag_add.php 15:35 2010年7月20日 tianya $
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
if ($dopost == "save") {
    CheckCSRF();
    $tagname = trim($tagname);
    $row = $dsql->GetOne("SELECT typeid FROM `#@__mytag` WHERE typeid='$typeid' AND tagname LIKE '$tagname'");
    if (is_array($row)) {
        ShowMsg("在相同栏目下已经存在同名的标记", "-1");
        exit();
    }
    $starttime = GetMkTime($starttime);
    $endtime = GetMkTime($endtime);
    $inQuery = "INSERT INTO `#@__mytag` (typeid,tagname,timeset,starttime,endtime,normbody,expbody) VALUES ('$typeid','$tagname','$timeset','$starttime','$endtime','$normbody','$expbody'); ";
    $dsql->ExecuteNoneQuery($inQuery);
    ShowMsg("成功添加一个自定义标记", "mytag_main.php");
    exit();
}
$startDay = time();
$endDay = AddDay($startDay, 30);
$startDay = GetDateTimeMk($startDay);
$endDay = GetDateTimeMk($endDay);
include DedeInclude('templets/mytag_add.htm');
?>