<?php
/**
 * 更新文档操作
 *
 * @version        $id:makehtml_archives_action.php 9:11 2010年7月19日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_MakeHtml');
require_once(DEDEINC."/archive/archives.class.php");
$est1 = ExecTime();
$startid  = (empty($startid)  ? -1  : $startid);
$endid    = (empty($endid)    ? 0  : $endid);
$startdd  = (empty($startdd)  ? 0  : $startdd);
$pagesize = (empty($pagesize) ? 30 : $pagesize);
$totalnum = (empty($totalnum) ? 0  : $totalnum);
$typeid   = (empty($typeid)   ? 0  : $typeid);
$seltime  = (empty($seltime)  ? 0  : $seltime);
$stime    = (empty($stime)    ? '' : $stime);
$etime    = (empty($etime)    ? '' : $etime);
$sstime   = (empty($sstime)   ? 0  : $sstime);
$mkvalue  = (empty($mkvalue)  ? 0  : $mkvalue);
//一键更新传递的参数
if (!empty($uptype)) {
    if ($uptype != 'time') $startid = $mkvalue;
    else $t1 = $mkvalue;
} else {
    $uptype = '';
}
//获取条件
$idsql = '';
$gwhere = ($startid == -1 ? " WHERE arcrank=0 " : " WHERE id>=$startid AND arcrank=0 ");
if ($endid > $startid && $startid > 0) $gwhere .= " AND id <= $endid ";
if ($typeid != 0) {
    $ids = GetSonIds($typeid);
    $gwhere .= " AND typeid in($ids) ";
}
if ($idsql == '') $idsql = $gwhere;
if ($seltime == 1) {
    $t1 = GetMkTime($stime);
    $t2 = GetMkTime($etime);
    $idsql .= " And (senddate >= $t1 And senddate <= $t2) ";
} else if (isset($t1) && is_numeric($t1)) {
    $idsql .= " And senddate >= $t1 ";
}
//统计记录总数
if ($totalnum == 0) {
    $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__arctiny` $idsql");
    $totalnum = $row['dd'];
    //清空缓存
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__arccache`");
}
//获取记录，并更新网页
if ($totalnum > $startdd + $pagesize) {
    $limitSql = " LIMIT $startdd,$pagesize";
} else {
    $limitSql = " LIMIT $startdd,".($totalnum - $startdd);
}
$tjnum = $startdd;
if (empty($sstime)) $sstime = time();
//如果更新数量大于500，并且没选栏目，按栏目排序更新
if ($totalnum > 500 && empty($typeid)) {
    $dsql->Execute('out', "SELECT id FROM `#@__arctiny` $idsql ORDER BY typeid ASC $limitSql");
} else {
    $dsql->Execute('out', "SELECT id FROM `#@__arctiny` $idsql $limitSql");
}
while ($row = $dsql->GetObject('out')) {
    $tjnum++;
    $id = $row->id;
    $ac = new Archives($id);
    $rurl = $ac->MakeHtml(0);
}
$t2 = ExecTime();
$t2 = ($t2 - $est1);
$ttime = time() - $sstime;
$ttime = number_format(($ttime / 60), 2);
//返回提示信息
$tjlen = $totalnum > 0 ? ceil(($tjnum / $totalnum) * 100) : 100;
$tjsta = "<div class='progress mb-3'><div class='progress-bar progress-bar-striped bg-success' role='progressbar' aria-valuenow='".$tjlen."%' aria-valuemin='0' aria-valuemax='100' style='width:".$tjlen."%'>".$tjlen."%</div></div>";
$tjsta .= "更新文档[id：".($startdd + $pagesize)."]，用时{$ttime}分钟，完成更新文档总数".$tjlen."%";
//速度测试
if ($tjnum < $totalnum) {
    $nurl  = "makehtml_archives_action.php?endid=$endid&startid=$startid&typeid=$typeid";
    $nurl .= "&totalnum=$totalnum&startdd=".($startdd + $pagesize)."&pagesize=$pagesize";
    $nurl .= "&seltime=$seltime&sstime=$sstime&stime=".urlencode($stime)."&etime=".urlencode($etime)."&uptype=$uptype&mkvalue=$mkvalue";
    ShowMsg($tjsta, $nurl, 0, 100);
    exit();
} else {
    if ($typeid != '') {
        ShowMsg("更新文档".$totalnum."，用时{$ttime}分钟，开始更新栏目", "makehtml_list_action.php?typeid=$typeid&uptype=all&maxpagesize=50&upnext=1");
    } else {
        if ($uptype == '') {
            ShowMsg("更新文档".$totalnum."，用时{$ttime}分钟，完成所有文档更新", "javascript:;");
        } else {
            ShowMsg("完成所有文档更新，开始更新首页", "makehtml_all.php?action=make&step=3&uptype=$uptype&mkvalue=$mkvalue");
        }
    }
}
?>