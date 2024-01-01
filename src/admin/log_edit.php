<?php
/**
 * 修改系统日志
 *
 * @version        $id:log_edit.php 8:48 2010年7月13日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_Log');
if (empty($dopost)) {
    ShowMsg("请选择一项记录", "log_list.php");
    exit();
}
//清空所有日志
if ($dopost == "clear") {
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__log`");
    ShowMsg("成功清空所有日志", "log_list.php");
    exit();
} else if ($dopost == "del") {
    $bkurl = isset($_COOKIE['ENV_GOBACK_URL']) ? $_COOKIE['ENV_GOBACK_URL'] : "log_list.php";
    $ids = explode('`', $ids);
    $dquery = '';
    foreach ($ids as $id) {
        if ($dquery == "") {
            $dquery .= " lid='$id' ";
        } else {
            $dquery .= " Or lid='$id' ";
        }
    }
    if ($dquery != "") $dquery = " where ".$dquery;
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__log` $dquery");
    ShowMsg("成功删除指定日志", $bkurl);
    exit();
} else {
    ShowMsg("系统无法识别请求", "log_list.php");
    exit();
}
?>