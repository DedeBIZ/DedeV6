<?php
/**
 * 调用日志管理
 *
 * @version        $id:ai_log_main.php 2025 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2025 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('ai_LogList');
require_once(DEDEINC."/datalistcp.class.php");
require_once(DEDEINC."/common.func.php");
DedeSetCookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
CheckPurview('ai_Del');
$sql = $where = '';
if (empty($adminid)) $adminid = 0;
if (empty($cip)) $cip = '';
if (empty($dtime)) $dtime = 0;
if ($adminid > 0) $where .= " AND `#@__ai_log`.adminid='$adminid' ";
if ($cip != "") $where .= " AND `#@__ai_log`.cip LIKE '%$cip%' ";
if ($dtime > 0) {
    $nowtime = time();
    $starttime = $nowtime - ($dtime * 24 * 3600);
    $where .= " AND `#@__ai_log`.dtime>'$starttime' ";
}
$sql = "SELECT `#@__ai_log`.*,`#@__admin`.userid FROM `#@__ai_log` LEFT JOIN `#@__admin` ON `#@__admin`.id=`#@__ai_log`.adminid WHERE 1=1 $where ORDER BY `#@__ai_log`.lid DESC";
$adminlist = '';
$dsql->SetQuery("SELECT id,uname FROM `#@__admin`");
$dsql->Execute('admin');
while ($myrow = $dsql->GetObject('admin')) {
    $adminlist .= "<option value='{$myrow->id}'>{$myrow->uname}</option>\r\n";
}
$dlist = new DataListCP();
$dlist->pagesize = 30;
$dlist->SetParameter("adminid", $adminid);
$dlist->SetParameter("cip", $cip);
$dlist->SetParameter("dtime", $dtime);
$dlist->SetTemplate(DEDEADMIN."/templets/ai_log_main.htm");
$dlist->SetSource($sql);
$dlist->Display();
?>