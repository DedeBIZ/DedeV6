<?php
/**
 * 更新自由列表操作
 *
 * @version        $id:makehtml_freelist_action.php 9:11 2010年7月19日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_MakeHtml');
require_once(DEDEINC."/archive/freelist.class.php");
$startid = empty($startid)? 0 : intval($startid);
$endid = empty($endid)? 0 : intval($endid);
$ci = " aid >= $startid ";
if ($endid > 0 && $endid >= $startid) {
    $ci .= " And aid <= $endid ";
}
header("Content-Type:text/html; charset={$cfg_soft_lang}");
$dsql->SetQuery("SELECT aid FROM `#@__freelist` WHERE $ci");
$dsql->Execute();
$idArray = array();
while ($row = $dsql->GetArray()) {
    $idArray[] = $row['aid'];
}
if (!isset($pageno)) $pageno = 0;
$totalpage = count($idArray);
if (isset($idArray[$pageno])) {
    $lid = $idArray[$pageno];
} else {
    ShowMsg("完成所有列表更新", 'javascript:;');
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
    ShowMsg("完成所有列表更新", 'javascript:;');
} else {
    if ($finishType) {
        $gourl = "makehtml_freelist_action.php?maxpagesize=$maxpagesize&startid=$startid&endid=$endid&pageno=$nextpage";
        ShowMsg("更新列表".$tid."，继续更新列表", $gourl, 0, 100);
    } else {
        $gourl = "makehtml_freelist_action.php?mkpage=$mkpage&maxpagesize=$maxpagesize&startid=$startid&endid=$endid&pageno=$pageno";
        ShowMsg("更新列表".$tid."，继续更新列表", $gourl, 0, 100);
    }
}
$dsql->ExecuteNoneQuery("UPDATE `#@__freelist` SET nodefault='1' WHERE aid='$startid';");
?>