<?php
/**
 * 任务操作
 *
 * @version        $id:task_do.php 23:07 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__).'/config.php');
$dopost = (!isset($dopost) ? '' : $dopost);
//返回到下一任务链接，特殊变量，除非知道作用，否则不能在任务传递中占用：f临时，仅为了方便网址结构，dopost当前任务指向下一个任务，由会员自行处理或在nextdo中自动获得，del上一次任务删除的变量，morejob设定后，表示当前任务需请求多次，会把dopost和nextdo处理后转为doposttmp和nextdotmp然后由会员自行处理
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
            //如果系统有多重任务，把下一任务和任务列表参数提交给程序处理
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
//更新上篇和下篇
if ($dopost == 'makeprenext') {
    require_once(DEDEINC.'/archive/archives.class.php');
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
        ShowMsg("完成所有上下篇文档更新任务", "close::tgtable");
        exit();
    } else {
        $jumpurl = GetNextUrl();
        ShowMsg("完成下篇文档更新任务，继续更新其它任务", $jumpurl);
        exit();
    }
}
//更新首页的任务
if ($dopost == 'makeindex') {
    require_once(DEDEINC.'/archive/partview.class.php');
    $envs = $_sys_globals = array();
    $envs['aid'] = 0;
    $pv = new PartView();
    $row = $pv->dsql->GetOne('SELECT * FROM `#@__homepageset`');
    $templet = str_replace("{style}", $cfg_df_style, $row['templet']);
    $homeFile = dirname(__FILE__).'/'.$row['position'];
    $homeFile = str_replace("//", "/", str_replace("\\", "/", $homeFile));
    $fp = fopen($homeFile, 'w') or die("无法更新网站首页到：".$homeFile."位置");
    fclose($fp);
    $tpl = $cfg_basedir.$cfg_templets_dir.'/'.$templet;
    if (!file_exists($tpl)) {
        $tpl = $cfg_basedir.$cfg_templets_dir.'/dedebiz/index.htm';
        if (!file_exists($tpl)) exit("无法找到首页模板：$tpl");
    }
    $GLOBALS['_arclistEnv'] = 'index';
    $pv->SetTemplet($tpl);
    $pv->SaveToHtml($homeFile);
    $pv->Close();
    if (empty($nextdo)) {
        ShowMsg("完成所有首页更新任务", "close::tgtable");
        exit();
    } else {
        $jumpurl = GetNextUrl();
        ShowMsg("完成首页更新，正在前往其它更新任务", $jumpurl);
        exit();
    }
}
//更新所有关连的栏目
else if ($dopost == 'makeparenttype') {
    require_once(DEDEDATA."/cache/inc_catalog_base.inc");
    require_once(DEDEINC.'/archive/listview.class.php');
    $notallowArr = array('dopost', 'f', 'del', 'curpage', 'morejob');
    $jumpurl = GetNextUrl($notallowArr);
    if (empty($typeid)) {
        ShowMsg("完成所有栏目更新任务", "close::tgtable");
        exit();
    }
    $topids = explode(',', GetTopids($typeid));
    if (empty($curpage)) $curpage = 0;
    $tid = $topids[$curpage];
    if (isset($cfg_Cs[$tid]) && $cfg_Cs[$tid][1] > 0) {
        require_once(DEDEINC."/archive/listview.class.php");
        $lv = new ListView($tid);
        $lv->CountRecord();
        $lv->MakeHtml();
        $lv->Close();
    } else {
        require_once(DEDEINC."/archive/sglistview.class.php");
        $lv = new SgListView($tid);
        $lv->CountRecord();
        $lv->MakeHtml();
        $lv->Close();
    }
    if ($curpage >= count($topids) - 1) {
        if (!empty($doposttmp)) {
            $jumpurl = preg_replace("#doposttmp|nextdotmp#", 'del', $jumpurl);
            $jumpurl .= "&dopost={$doposttmp}&nextdo={$nextdotmp}";
            ShowMsg("完成栏目：{$tid}更新，继续更新后续任务", $jumpurl);
            exit();
        } else {
            ShowMsg("完成栏目：{$tid}更新，完成所有更新任务", "close::tgtable");
            exit();
        }
    } else {
        $curpage++;
        $jumpurl .= "&curpage={$curpage}&dopost=makeparenttype";
        ShowMsg("完成栏目：{$tid}更新，继续更新其它栏目", $jumpurl);
        exit();
    }
}
?>