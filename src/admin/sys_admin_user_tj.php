<?php
/**
 * 获取用户的统计信息
 *
 * @version        $Id: sys_admin_user_edit.php 1 16:22 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_User');

if (isset($dopost) && $dopost == 'getone') {
    $row = $dsql->GetOne("SELECT userid FROM `#@__admin` WHERE id='$uid'; ");
    $userid = $row['userid'];
    $y = intval(MyDate('Y', time()));
    $m = intval(MyDate('m', time()));
    $d = intval(MyDate('d', time()));
    //全部
    $sql = "SELECT addtable FROM `#@__channeltype` WHERE issystem='-1'";
    $dsql->Execute('me', $sql);
    while ($frow = $dsql->GetArray('me')) {
        $dd = empty($dd) ? "0" : $dd;
        $cc = empty($cc) ? "0" : $cc;
        $row = $dsql->GetOne("SELECT COUNT(aid) AS dd,SUM(click) AS cc FROM `{$frow['addtable']}` WHERE mid='$uid'; ");
        $dd += $row['dd'];
        $cc += $row['cc'];
    }
    $row = $dsql->GetOne("SELECT COUNT(id) AS dd,SUM(click) AS cc FROM `#@__archives` WHERE mid='$uid'; ");
    $dd = $row['dd'] + $dd;
    $cc = $row['cc'] + $cc;
    //季度
    $starttime = 0;
    if (preg_match("#[123]#", $m) && $m < 10) $starttime = $y."-01-01 00:00:00";
    else if (preg_match("#[456]#", $m)) $starttime = $y."-04-01 00:00:00";
    else if (preg_match("#[789]#", $m)) $starttime = $y."-07-01 00:00:00";
    else  $starttime = $y."-10-01 00:00:00";
    $istarttime = GetMkTime($starttime);
    $sql = "SELECT addtable FROM `#@__channeltype` WHERE issystem='-1'";
    $dsql->Execute('me', $sql);
    while ($frow = $dsql->GetArray('me')) {
        $dds = empty($dds) ? "0" : $dds;
        $ccs = empty($ccs) ? "0" : $ccs;
        $row = $dsql->GetOne("SELECT COUNT(aid) AS dd,SUM(click) AS cc FROM `{$frow['addtable']}` WHERE senddate>$istarttime AND mid='$uid'; ");
        $dds += $row['dd'];
        $ccs += $row['cc'];
    }
    $row = $dsql->GetOne("SELECT COUNT(id) AS dd,SUM(click) AS cc FROM `#@__archives` WHERE senddate>$istarttime AND mid='$uid'; ");
    $dds = $row['dd'] + $dds;
    $ccs = $row['cc'] + $ccs;
    //当月
    $starttime = $y."-{$m}-01 00:00:00";
    $istarttime = GetMkTime($starttime);
    $sql = "SELECT addtable FROM `#@__channeltype` WHERE issystem='-1'";
    $dsql->Execute('me', $sql);
    while ($frow = $dsql->GetArray('me')) {
        $ddm = empty($ddm) ? "0" : $ddm;
        $ccm = empty($ccm) ? "0" : $ccm;
        $row = $dsql->GetOne("SELECT COUNT(aid) AS dd,SUM(click) AS cc FROM `{$frow['addtable']}` WHERE senddate>$istarttime AND mid='$uid'; ");
        $ddm += $row['dd'];
        $ccm += $row['cc'];
    }
    $row = $dsql->GetOne("SELECT COUNT(id) AS dd,SUM(click) AS cc FROM `#@__archives` WHERE senddate>$istarttime AND mid='$uid'; ");
    $ddm = $row['dd'] + $ddm;
    $ccm = $row['cc'] + $ccm;
    //本周
    $starttime = $y."-{$m}-{$d} 00:00:00";
    $istarttime = GetMkTime($starttime) - (7 * 24 * 3600);
    $sql = "SELECT addtable FROM `#@__channeltype` WHERE issystem='-1'";
    $dsql->Execute('me', $sql);
    while ($frow = $dsql->GetArray('me')) {
        $ddw = empty($ddw) ? "0" : $ddw;
        $ccw = empty($ccw) ? "0" : $ccw;
        $row = $dsql->GetOne("SELECT COUNT(aid) AS dd,SUM(click) AS cc FROM `{$frow['addtable']}` WHERE senddate>$istarttime AND mid='$uid'; ");
        $ddw += $row['dd'];
        $ccw += $row['cc'];
    }
    $row = $dsql->GetOne("SELECT COUNT(id) AS dd,SUM(click) AS cc FROM `#@__archives` WHERE senddate>$istarttime AND mid='$uid'; ");
    $ddw = $row['dd'] + $ddw;
    $ccw = $row['cc'] + $ccw;
    //当天
    $starttime = $y."-{$m}-{$d} 00:00:00";
    $istarttime = GetMkTime($starttime);
    $sql = "SELECT addtable FROM `#@__channeltype` WHERE issystem='-1'";
    $dsql->Execute('me', $sql);
    while ($frow = $dsql->GetArray('me')) {
        $ddd = empty($ddd) ? "0" : $ddd;
        $ccd = empty($ccd) ? "0" : $ccd;
        $row = $dsql->GetOne("SELECT COUNT(aid) AS dd,SUM(click) AS cc FROM `{$frow['addtable']}` WHERE senddate>$istarttime AND mid='$uid'; ");
        $ddd += $row['dd'];
        $ccd += $row['cc'];
    }
    $row = $dsql->GetOne("SELECT COUNT(id) AS dd,SUM(click) AS cc FROM `#@__archives` WHERE senddate>$istarttime AND mid='$uid'; ");
    $ddd = $row['dd'] + $ddd;
    $ccd = $row['cc'] + $ccd;
    $msg = "<table width='100%' align='center' cellpadding='3' cellspacing='1'>
    <tr align='center' bgcolor='#F8FCF1'>
      <td width='18%' height='26'>管理员::统计信息</td>
      <td width='18%'>全部(文档|点击)</td>
      <td width='16%'>季度</td>
      <td width='16%'>当月</td>
      <td width='16%'>近七天</td>
      <td width='16%'>当天</td>
    </tr>
    <tr align='center'>
      <td height='26'>{$userid}</td>
      <td>{$dd} | {$cc}</td>
      <td>{$dds} | {$ccs}</td>
      <td>{$ddm} | {$ccm}</td>
      <td>{$ddw} | {$ccw}</td>
      <td>{$ddd} | {$ccd}</td>
    </tr>
    </table><br style='clear:both'/>\r\n";
    AjaxHead();
    echo $msg;
    exit();
}
include DedeInclude('templets/sys_admin_user_tj.htm');