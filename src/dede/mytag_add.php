<?php

/**
 * 增加自定义标记
 *
 * @version        $Id: mytag_add.php 1 15:35 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__) . "/config.php");
CheckPurview('temp_Other');
require_once(DEDEINC . "/typelink.class.php");
if (empty($dopost)) $dopost = "";

if ($dopost == "save") {
    CheckCSRF();
    $tagname = trim($tagname);
    $row = $dsql->GetOne("SELECT typeid FROM `#@__mytag` WHERE typeid='$typeid' AND tagname LIKE '$tagname'");
    if (is_array($row)) {
        ShowMsg("在相同栏目下已经存在同名的标记！", "-1");
        exit();
    }
    $starttime = GetMkTime($starttime);
    $endtime = GetMkTime($endtime);
    $inQuery = "INSERT INTO `#@__mytag`(typeid,tagname,timeset,starttime,endtime,normbody,expbody)
     VALUES('$typeid','$tagname','$timeset','$starttime','$endtime','$normbody','$expbody'); ";
    $dsql->ExecuteNoneQuery($inQuery);
    ShowMsg("成功增加一个自定义标记！", "mytag_main.php");
    exit();
}
$startDay = time();
$endDay = AddDay($startDay, 30);
$startDay = GetDateTimeMk($startDay);
$endDay = GetDateTimeMk($endDay);
include DedeInclude('templets/mytag_add.htm');
