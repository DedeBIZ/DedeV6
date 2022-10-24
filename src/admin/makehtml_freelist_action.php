<?php
/**
 * 生成自由列表操作
 *
 * @version        $Id: makehtml_freelist_action.php 1 9:11 2010年7月19日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Archive\FreeList;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('sys_MakeHtml');
if (empty($startid)) $startid = 0;
$ci = " aid >= $startid ";
if (!empty($endid) && $endid >= $startid) {
    $ci .= " And aid <= $endid ";
}
header("Content-Type: text/html; charset=utf-8");
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
    ShowMsg(Lang("makehtml_all_step_10"), 'javascript:;');
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
    ShowMsg(Lang("makehtml_all_step_10"), 'javascript:;');
} else {
    if ($finishType) {
        $gourl = "makehtml_freelist_action.php?maxpagesize=$maxpagesize&startid=$startid&endid=$endid&pageno=$nextpage";
        ShowMsg(Lang("makehtml_freelist_success",array('tid'=>$tid)), $gourl, 0, 100);
    } else {
        $gourl = "makehtml_freelist_action.php?mkpage=$mkpage&maxpagesize=$maxpagesize&startid=$startid&endid=$endid&pageno=$pageno";
        ShowMsg(Lang("makehtml_freelist_success",array('tid'=>$tid)), $gourl, 0, 100);
    }
}
$dsql->ExecuteNoneQuery("UPDATE `#@__freelist` SET  nodefault='1' WHERE aid='$startid';");
?>