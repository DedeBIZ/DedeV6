<?php

/**
 * 生成Tag操作
 *
 * @version        $Id: makehtml_taglist_action.php 1 11:17 2020年8月19日Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2020, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__) . "/config.php");
CheckPurview('sys_MakeHtml');
require_once(DEDEINC . "/arc.taglist.class.php");

if (empty($total)) $total = 0; // TAGS总数
if (empty($pageno)) $pageno = 0;
if (empty($mkpage)) $mkpage = 1;
if (empty($offset)) $offset = 0; // 当前位置
if (empty($maxpagesize)) $maxpagesize = 50;
$tagid = isset($tagid) ? intval($tagid) : 0;

if ($total == 0 && $tagid == 0) {
    $total = $dsql->GetOne("SELECT count(*) as dd FROM `#@__tagindex`");
    $total = intval($total['dd']);
}

$allfinish = false;

if ($offset < ($total - 1)) {
    $tt = $dsql->GetOne("SELECT * FROM `#@__tagindex` LIMIT " . $offset . ",1;");
    $tagid = $tt['id'];
    $offset++;
} else {
    $allfinish = true;
}

$tag = $dsql->GetOne("SELECT * FROM `#@__tagindex` WHERE id='$tagid' LIMIT 0,1;");

MkdirAll($cfg_basedir . "/a/tags", $cfg_dir_purview);

if (is_array($tag) && count($tag) > 0) {
    $dlist = new TagList($tag['tag'], 'taglist.htm');
    $dlist->CountRecord();
    $ntotalpage = $dlist->TotalPage;

    if ($ntotalpage <= $maxpagesize) {
        $dlist->MakeHtml('', '');
        $finishType = TRUE;
    } else {
        $reurl = $dlist->MakeHtml($mkpage, $maxpagesize);
        $finishType = FALSE;
        $mkpage = $mkpage + $maxpagesize;
        if ($mkpage >= ($ntotalpage + 1)) $finishType = TRUE;
    }

    $nextpage = $pageno + 1;
    if ($nextpage >= $ntotalpage && $finishType && !($offset < ($total - 1))) {
        $dlist = new TagList('', 'tag.htm');
        $dlist->MakeHtml(1, 10);
        $reurl = '../a/tags/';
        if ($total > 0) {
            ShowMsg("完成TAG更新！<a href='$reurl' target='_blank'>浏览TAG首页</a>", "javascript:;");
        } else {
            $reurl .= GetPinyin($tag['tag']);
            ShowMsg("完成TAG更新：[" . $tag['tag'] . "]！<a href='$reurl' target='_blank'>浏览TAG首页</a>", "javascript:;");
        }
        exit();
    } else {
        if ($finishType) {
            if ($allfinish == true) {
                $total = 0;
            }
            $gourl = "makehtml_taglist_action.php?maxpagesize=$maxpagesize&tagid=$tagid&pageno=$nextpage&total=$total&offset=$offset";
            ShowMsg("成功生成TAG：[" . $tag['tag'] . "]，继续进行操作！", $gourl, 0, 100);
            exit();
        } else {
            $gourl = "makehtml_taglist_action.php?mkpage=$mkpage&maxpagesize=$maxpagesize&tagid=$tagid&pageno=$pageno&total=$total&offset=$offset";
            ShowMsg("成功生成TAG：[" . $tag['tag'] . "]，继续进行操作...", $gourl, 0, 100);
            exit();
        }
    }
}
