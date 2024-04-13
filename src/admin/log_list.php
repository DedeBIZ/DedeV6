<?php
/**
 * 系统日志列表
 *
 * @version        $id:log_list.php 8:48 2010年7月13日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_Log');
require_once(DEDEINC."/datalistcp.class.php");
require_once(DEDEINC."/common.func.php");
DedeSetCookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
$sql = $where = '';
if (empty($adminid)) $adminid = 0;
if (empty($cip)) $cip = '';
if (empty($dtime)) $dtime = 0;
if ($adminid > 0) $where .= " AND `#@__log`.adminid='$adminid' ";
if ($cip != "") $where .= " AND `#@__log`.cip LIKE '%$cip%' ";
if ($dtime > 0) {
    $nowtime = time();
    $starttime = $nowtime - ($dtime * 24 * 3600);
    $where .= " AND `#@__log`.dtime>'$starttime' ";
}
$sql = "SELECT `#@__log`.*,`#@__admin`.userid FROM `#@__log` LEFT JOIN `#@__admin` ON `#@__admin`.id=`#@__log`.adminid WHERE 1=1 $where ORDER BY `#@__log`.lid DESC";
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
$dlist->SetTemplate(DEDEADMIN."/templets/log_list.htm");
$dlist->SetSource($sql);
$dlist->Display();
?>