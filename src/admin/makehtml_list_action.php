<?php
/**
 * 更新列表栏目操作
 *
 * @version        $id:makehtml_list_action.php 11:09 2010年7月19日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_MakeHtml');
require_once(DEDEINC."/channelunit.func.php");
if (!isset($upnext)) $upnext = 1;
if (empty($gotype)) $gotype = '';
if (empty($gotype)) UpDateCatCache();
require_once(DEDEDATA."/cache/inc_catalog_base.inc");
if (empty($pageno)) $pageno = 0;
if (empty($mkpage)) $mkpage = 1;
if (empty($typeid)) $typeid = 0;
if (!isset($uppage)) $uppage = 0;
if (empty($maxpagesize)) $maxpagesize = 30;
$adminID = $cuserLogin->getUserID();
//检测获取所有栏目id，普通更新或一键更新时更新所有栏目
if ($gotype == '' || $gotype == 'mkallct') {
    if ($upnext == 1 || $typeid == 0) {
        if ($typeid > 0) {
            $tidss = GetSonIds($typeid, 0);
            $idArray = explode(',', $tidss);
        } else {
            foreach ($cfg_Cs as $k => $v) $idArray[] = $k;
        }
    } else {
        $idArray = array();
        $idArray[] = $typeid;
    }
}

//一键更新带缓存的情况
else if ($gotype == 'mkall') {
    $uppage = 1;
    $mkcachefile = DEDEDATA."/mkall_cache_{$adminID}.php";
    $idArray = array();
    if (file_exists($mkcachefile)) require_once($mkcachefile);
}
if (!$idArray) {
    ShowMsg("尚未添加栏目，请先添加栏目再进行更新", "javascript:;");
    exit;
}
//当前更新栏目id
$totalpage = count($idArray);
if (isset($idArray[$pageno])) {
    $tid = $idArray[$pageno];
} else {
    if ($gotype == '') {
        ShowMsg("完成所有栏目更新", "javascript:;");
        exit();
    } else if ($gotype == 'mkall' || $gotype == 'mkallct') {
        ShowMsg("完成所有栏目更新，最后数据优化", "makehtml_all.php?action=make&step=10");
        exit();
    }
}
if ($pageno == 0 && $mkpage == 1) //清空缓存
{
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__arccache`");
}
$reurl = '';
//更新数组所记录栏目
if (!empty($tid)) {
    if (!isset($cfg_Cs[$tid])) {
        ShowMsg('没有该栏目数据，可能缓存文件没有更新，请检查是否有写入权限', 'javascript:;');
        exit();
    }
    if ($cfg_Cs[$tid][1] > 0) {
        require_once(DEDEINC."/archive/listview.class.php");
        $lv = new ListView($tid);
        $position = MfTypedir($lv->Fields['typedir']);
    } else {
        require_once(DEDEINC."/archive/sglistview.class.php");
        $lv = new SgListView($tid);
    }
    //这里统一统计
    $lv->CountRecord();
    if ($lv->TypeLink->TypeInfos['ispart'] == 0 && $lv->TypeLink->TypeInfos['isdefault'] != -1) $ntotalpage = $lv->TotalPage;
    else $ntotalpage = 1;
    //如果栏目的文档太多，分多批次更新
    if ($ntotalpage <= $maxpagesize || $lv->TypeLink->TypeInfos['ispart'] != 0 || $lv->TypeLink->TypeInfos['isdefault'] == -1) {
        $reurl = $lv->MakeHtml('', '', 0);
        $finishType = TRUE;
    } else {
        $reurl = $lv->MakeHtml($mkpage, $maxpagesize, 0);
        $finishType = FALSE;
        $mkpage = $mkpage + $maxpagesize;
        if ($mkpage >= ($ntotalpage + 1)) $finishType = TRUE;
    }
}
$nextpage = $pageno + 1;
if ($nextpage >= $totalpage && $finishType) {
    if ($gotype == '') {
        if (empty($reurl)) {
            $reurl = '../apps/list.php?tid='.$tid;
        }
        ShowMsg("完成所有栏目更新，<a href='$reurl' target='_blank'>浏览栏目</a>", "javascript:;");
        exit();
    } else if ($gotype == 'mkall' || $gotype == 'mkallct') {
        ShowMsg("完成所有栏目更新，最后数据优化", "makehtml_all.php?action=make&step=10");
        exit();
    }
} else {
    $typename = isset($cfg_Cs[$tid][3])? base64_decode($cfg_Cs[$tid][3]) : "";
    if ($finishType) {
        $gourl = "makehtml_list_action.php?gotype={$gotype}&uppage=$uppage&maxpagesize=$maxpagesize&typeid=$typeid&pageno=$nextpage";
        ShowMsg("更新栏目id：<span class='text-primary'>".$tid."</span>，栏目名称：<span class='text-primary'>{$typename}</span>，继续执行任务", $gourl, 0, 100);
        exit();
    } else {
        $gourl = "makehtml_list_action.php?gotype={$gotype}&uppage=$uppage&mkpage=$mkpage&maxpagesize=$maxpagesize&typeid=$typeid&pageno=$pageno";
        ShowMsg("更新栏目id：<span class='text-primary'>".$tid."</span>，栏目名称：<span class='text-primary'>{$typename}</span>，继续执行任务", $gourl, 0, 100);
        exit();
    }
}
?>