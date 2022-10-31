<?php
/**
 * 生成自由列表操作
 *
 * @version        $Id: makehtml_freelist_action.php 1 9:11 2010年7月19日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_MakeHtml');
require_once(DEDEINC."/archive/freelist.class.php");
if (empty($startid)) $startid = 0;
$ci = " aid >= $startid ";
if (!empty($endid) && $endid >= $startid) {
    $ci .= " And aid <= $endid ";
}
header("Content-Type: text/html; charset={$cfg_soft_lang}");
$dsql->SetQuery("SELECT aid FROM `#@__freelist` WHERE $ci");
$dsql->Execute();
while ($row = $dsql->GetArray()) {
    $idArray[] = $row['aid'];
}
if (!isset($pageno)) $pageno = 0;
if (empty($idArray)) $idArray = '';
$totalpage = count($idArray);
if (isset($idArray[$pageno])) {
    $lid = $idArray[$pageno];
} else {
    ShowMsg("完成所有文件创建", 'javascript:;');
    exit();
}
$lv = new FreeList($lid);
$ntotalpage = $lv->TotalPage;
if (empty($mkpage)) $mkpage = 1;
if (empty($maxpagesize)) $maxpagesize = 30;
//如果栏目的文档太多，分多批次更新
if ($ntotalpage <= $maxpagesize) {
    $lv->MakeHtml();
    $finishType = true;
} else {
    $lv->MakeHtml($mkpage, $maxpagesize);
    $finishType = false;
    $mkpage = $mkpage + $maxpagesize;
    if ($mkpage >= ($ntotalpage + 1)) {
        $finishType = true;
    }
}
$lv->Close();
$nextpage = $pageno + 1;
if ($nextpage == $totalpage) {
    ShowMsg("完成所有文件创建", 'javascript:;');
} else {
    if ($finishType) {
        $gourl = "makehtml_freelist_action.php?maxpagesize=$maxpagesize&startid=$startid&endid=$endid&pageno=$nextpage";
        ShowMsg("创建列表：".$tid."，继续执行任务", $gourl, 0, 100);
    } else {
        $gourl = "makehtml_freelist_action.php?mkpage=$mkpage&maxpagesize=$maxpagesize&startid=$startid&endid=$endid&pageno=$pageno";
        ShowMsg("创建列表：".$tid."，继续执行任务", $gourl, 0, 100);
    }
}
$dsql->ExecuteNoneQuery("UPDATE `#@__freelist` SET nodefault='1' where aid='$startid';");
?>