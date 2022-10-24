<?php
/**
 * 生成列表栏目操作
 *
 * @version        $Id: makehtml_list_action.php 1 11:09 2010年7月19日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Archive\ListView;
use DedeBIZ\Archive\SgListView;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('sys_MakeHtml');
require_once(DEDEDATA."/cache/inc_catalog_base.inc");
require_once(DEDEINC."/channel/channelunit.func.php");
if (!isset($upnext)) $upnext = 1;
if (empty($gotype)) $gotype = '';
if (empty($pageno)) $pageno = 0;
if (empty($mkpage)) $mkpage = 1;
if (empty($typeid)) $typeid = 0;
if (!isset($uppage)) $uppage = 0;
if (empty($maxpagesize)) $maxpagesize = 30;
$adminID = $cUserLogin->getUserID();
//检测获取所有栏目id
//普通生成或一键更新时更新所有栏目
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
//当前更新栏目的ID
$totalpage = count($idArray);
if (isset($idArray[$pageno])) {
    $tid = $idArray[$pageno];
} else {
    if ($gotype == '') {
        ShowMsg(Lang("makehtml_list_success"), "javascript:;");
        exit();
    } else if ($gotype == 'mkall' || $gotype == 'mkallct') {
        ShowMsg(Lang("makehtml_list_success_2"), "makehtml_all.php?action=make&step=10");
        exit();
    }
}
if ($pageno == 0 && $mkpage == 1) //清空缓存
{
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__arccache`");
}
$reurl = '';
//更新数组所记录的栏目
if (!empty($tid)) {
    if (!isset($cfg_Cs[$tid])) {
        ShowMsg(Lang('makehtml_list_err_cache'), 'javascript:;');
        exit();
    }
    if ($cfg_Cs[$tid][1] > 0) {
        $lv = new ListView($tid);
        $position = MfTypedir($lv->Fields['typedir']);
    } else {
        $lv = new SgListView($tid);
    }
    //这里统一统计
    $lv->CountRecord();
    if ($lv->TypeLink->TypeInfos['ispart'] == 0 && $lv->TypeLink->TypeInfos['isdefault'] != -1) $ntotalpage = $lv->TotalPage;
    else $ntotalpage = 1;
    //如果栏目的文档太多，分多批次更新
    if ($ntotalpage <= $maxpagesize || $lv->TypeLink->TypeInfos['ispart'] != 0 || $lv->TypeLink->TypeInfos['isdefault'] == -1) {
        $reurl = $lv->MakeHtml('', '');
        $finishType = TRUE;
    } else {
        $reurl = $lv->MakeHtml($mkpage, $maxpagesize);
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
        ShowMsg(Lang('makehtml_list_success_view', array('reurl'=>$reurl)), "javascript:;");
        exit();
    } else if ($gotype == 'mkall' || $gotype == 'mkallct') {
        ShowMsg(Lang("makehtml_list_success_mkall"), "makehtml_all.php?action=make&step=10");
        exit();
    }
} else {
    if ($finishType) {
        $gourl = "makehtml_list_action.php?gotype={$gotype}&uppage=$uppage&maxpagesize=$maxpagesize&typeid=$typeid&pageno=$nextpage";
        ShowMsg(Lang("makehtml_list_success_tid",array('tid'=>$tid)), $gourl, 0, 100);
        exit();
    } else {
        $gourl = "makehtml_list_action.php?gotype={$gotype}&uppage=$uppage&mkpage=$mkpage&maxpagesize=$maxpagesize&typeid=$typeid&pageno=$pageno";
        ShowMsg(Lang("makehtml_list_success_tid",array('tid'=>$tid)), $gourl, 0, 100);
        exit();
    }
}
?>