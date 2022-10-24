<?php
/**
 * 任务操作
 *
 * @version        $Id: task_do.php 1 23:07 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Archive\Archives;
use DedeBIZ\Archive\ListView;
use DedeBIZ\Archive\PartView;
use DedeBIZ\Archive\SgListView;
require(dirname(__FILE__).'/config.php');
$dopost = (!isset($dopost) ? '' : $dopost);
/*
返回到下一任务的URL特殊变量，除非知道作用，否则不能在任务传递中占用f临时，仅为了方便网址结构dopost当前任务（指向下一个任务），由用户自行处理或在nextdo中自动获得del上一次任务删除的变量morejob，设定后，表示当前任务需请求多次，会把dopost和nextdo处理后转为doposttmp,nextdotmp然后由用户自行处理
*/
function GetNextUrl($notallowArr = array('dopost', 'f', 'del'))
{
    $reurl = "task_do.php?f=0";
    foreach ($_GET as $k => $v) {
        if ($k == 'nextdo') {
            $nextdo = '';
            $nextdos = explode(',', $GLOBALS[$k]);
            if (isset($nextdos[1])) {
                for ($i = 1; $i < count($nextdos); $i++) {
                    if (trim($nextdos[$i]) == '') continue;
                    $nextdo .= ($nextdo == '' ? $nextdos[$i] : ','.$nextdos[$i]);
                }
            }
            //如果系统有多重任务， 把下一任务和任务列表参数提交给程序处理
            if (in_array('morejob', $notallowArr)) {
                $reurl .= "&doposttmp=".$nextdos[0];
                if ($nextdo != '') $reurl .= "&nextdotmp=$nextdo";
            } else {
                $reurl .= "&dopost=".$nextdos[0];
                if ($nextdo != '') $reurl .= "&nextdo=$nextdo";
            }
        } else if (in_array($k, $notallowArr)) {
            continue;
        } else {
            $reurl .= "&{$k}=".urlencode($GLOBALS[$k]);
        }
    }
    return $reurl;
}
//更新上一篇和下一篇
if ($dopost == 'makeprenext') {
    $aid = intval($aid);
    $preRow =  $dsql->GetOne("SELECT id FROM `#@__arctiny` WHERE id<$aid AND arcrank>-1 AND typeid='$typeid' ORDER BY id DESC");
    $nextRow = $dsql->GetOne("SELECT id FROM `#@__arctiny` WHERE id>$aid AND arcrank>-1 AND typeid='$typeid' ORDER BY id ASC");
    if (is_array($preRow)) {
        $envs['aid'] = $preRow['id'];
        $arc = new Archives($preRow['id']);
        $arc->MakeHtml();
    }
    if (is_array($nextRow)) {
        $envs['aid'] = $nextRow['id'];
        $arc = new Archives($nextRow['id']);
        $arc->MakeHtml();
    }
    if (empty($nextdo)) {
        ShowMsg(Lang("makehtml_makeprenext"), "close::tgtable");
        exit();
    } else {
        $jumpurl = GetNextUrl();
        ShowMsg(Lang("makehtml_makeprenext_continue"), $jumpurl, 0, 500);
        exit();
    }
}
//更新主页的任务
if ($dopost == 'makeindex') {
    $envs = $_sys_globals = array();
    $envs['aid'] = 0;
    $pv = new PartView();
    $row = $pv->dsql->GetOne('SELECT * FROM `#@__homepageset`');
    $templet = str_replace("{style}", $cfg_df_style, $row['templet']);
    $homeFile = dirname(__FILE__).'/'.$row['position'];
    $homeFile = str_replace("//", "/", str_replace("\\", "/", $homeFile));
    $fp = fopen($homeFile, 'w') or die(Lang('makehtml_err_index',array('file'=>$homeFile)));
    fclose($fp);
    $tpl = $cfg_basedir.$cfg_templets_dir.'/'.$templet;
    if (!file_exists($tpl)) {
        $tpl = $cfg_basedir.$cfg_templets_dir.'/default/index.htm';
        if (!file_exists($tpl)) exit( Lang('makehtml_err_notpl',array('tpl'=>$tpl)));
    }
    $GLOBALS['_arclistEnv'] = 'index';
    $pv->SetTemplet($tpl);
    $pv->SaveToHtml($homeFile);
    $pv->Close();
    if (empty($nextdo)) {
        ShowMsg(Lang("makehtml_success_index"), "close::tgtable");
        exit();
    } else {
        $jumpurl = GetNextUrl();
        ShowMsg(Lang("makehtml_success_index_continue"), $jumpurl, 0, 500);
        exit();
    }
}
//更新所有关连的栏目
else if ($dopost == 'makeparenttype') {
    require_once(DEDEDATA."/cache/inc_catalog_base.inc");
    $notallowArr = array('dopost', 'f', 'del', 'curpage', 'morejob');

    $jumpurl = GetNextUrl($notallowArr);

    if (empty($typeid)) {
        ShowMsg(Lang("makehtml_makeparenttype"), "close::tgtable");
        exit();
    }
    $topids = explode(',', GetTopids($typeid));
    if (empty($curpage)) $curpage = 0;
    $tid = $topids[$curpage];

    if (isset($cfg_Cs[$tid]) && $cfg_Cs[$tid][1] > 0) {
        $lv = new ListView($tid);
        $lv->CountRecord();
        $lv->MakeHtml();
        $lv->Close();
    } else {
        $lv = new SgListView($tid);
        $lv->CountRecord();
        $lv->MakeHtml();
        $lv->Close();
    }
    if ($curpage >= count($topids) - 1) {
        if (!empty($doposttmp)) {
            $jumpurl = preg_replace("#doposttmp|nextdotmp#", 'del', $jumpurl);
            $jumpurl .= "&dopost={$doposttmp}&nextdo={$nextdotmp}";
            ShowMsg(Lang("makehtml_success_makeparenttype",array('tid'=>$tid)), $jumpurl, 0, 500);
            exit();
        } else {
            ShowMsg(Lang("makehtml_success_makeparenttype",array('tid'=>$tid)), "close::tgtable");
            exit();
        }
    } else {
        $curpage++;
        $jumpurl .= "&curpage={$curpage}&dopost=makeparenttype";
        ShowMsg(Lang("makehtml_success_makeparenttype_continue",array('tid'=>$tid)), $jumpurl, 0, 500);
        exit();
    }
}
?>