<?php
/**
 * 生成Tag操作
 *
 * @version        $Id: makehtml_taglist_action.php 1 11:17 2020年8月19日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_MakeHtml');
require_once(DEDEINC."/archive/taglist.class.php");

if (empty($pageno)) $pageno = 0;
if (empty($mktime)) $mktime = time();
if (empty($mkpage)) $mkpage = 1;
if (empty($upall)) $upall = 0; //是否更新全部 0为更新单个 1为更新全部
if (empty($ctagid)) $ctagid = 0; //当前处理的tagid
if (empty($maxpagesize)) $maxpagesize = 50;
$startid = isset($startid) ? intval($startid) : 0;
$endid = isset($endid) ? intval($endid) : 0;
$tagid = isset($tagid) ? intval($tagid) : 0;
$tagsdir = str_replace("{cmspath}", $cfg_cmspath, $cfg_tags_dir);
// 生成
if ($tagid > 0) {
    $upall = 0; //更新单个模式
    $ctagid = $tagid;
} else {
    $upall = 1; //更新全部模式
}
$allfinish = false; //是否全部完成
$gwhere = ($startid == 0 ? "WHERE 1=1" : "WHERE id>=$startid");
if ($endid > $startid && $startid > 0) $gwhere .= " AND id <= $endid ";
// 判断生成模式
if ($upall == 1 && $ctagid == 0) {
    $rr = $dsql->GetOne("SELECT * FROM `#@__tagindex` $gwhere AND mktime <> {$mktime} LIMIT 1");
    if (isset($rr['id']) && $rr['id'] > 0) {
        $ctagid = $rr['id'];
    } else {
        $allfinish = true;
    }
}
if ($ctagid == 0 && $allfinish) {
    $dlist = new TagList('', 'tag.htm');
    $dlist->SetTagsDir($tagsdir);
    $dlist->MakeHtml(1, 10);
    $reurl = '..'.$tagsdir;
    ShowMsg("完成TAG更新<a href='$reurl' target='_blank'>浏览TAG首页</a>", "javascript:;");
    exit;
}
$tag = $dsql->GetOne("SELECT * FROM `#@__tagindex` WHERE id='$ctagid' LIMIT 0,1;");
// 创建TAGS目录
MkdirAll($cfg_basedir.$cfg_tags_dir, $cfg_dir_purview);
if (is_array($tag) && count($tag) > 0) {
    $dlist = new TagList($tag['id'], 'taglist.htm');
    $dlist->CountRecord();
    $dlist->SetTagsDir($tagsdir);
    $ntotalpage = $dlist->TotalPage;

    if ($ntotalpage <= $maxpagesize) {
        $dlist->MakeHtml('', '');
        $finishType = TRUE; //生成一个TAG完成
    } else {
        $reurl = $dlist->MakeHtml($mkpage, $maxpagesize);
        $finishType = FALSE;
        $mkpage = $mkpage + $maxpagesize;
        if ($mkpage >= ($ntotalpage + 1)) $finishType = TRUE;
    }

    $nextpage = $pageno + 1;
    $onefinish = $nextpage >= $ntotalpage && $finishType;
    if (($upall == 0 && $onefinish) || ($upall == 1 && $allfinish && $onefinish)) {
        $dlist = new TagList('', 'tag.htm');
        $dlist->SetTagsDir($tagsdir);
        $dlist->MakeHtml(1, 10);
        $reurl = '..'.$tagsdir;
        if ($upall == 1) {
            ShowMsg("完成TAG更新<a href='$reurl' target='_blank'>浏览TAG首页</a>", "javascript:;");
        } else {
            $query = "UPDATE `#@__tagindex` SET mktime=uptime WHERE id='$ctagid' ";
            $dsql->ExecuteNoneQuery($query);
            $reurl .= '/'.$ctagid;
            ShowMsg("完成TAG更新：[".$tag['tag']."]<a href='$reurl' target='_blank'>浏览TAG首页</a>", "javascript:;");
        }
        exit();
    } else {
        if ($finishType) {
            //完成了一个跳到下一个
            if ($upall == 1) {
                $query = "UPDATE `#@__tagindex` SET mktime={$mktime} WHERE id='$ctagid' ";
                $dsql->ExecuteNoneQuery($query);
                $ctagid = 0;
                $nextpage = 0;
            }
            $gourl = "makehtml_taglist_action.php?maxpagesize=$maxpagesize&tagid=$tagid&pageno=$nextpage&upall=$upall&ctagid=$ctagid&startid=$startid&endid=$endid&mktime=$mktime";
            var_dump_cli($gourl);
            ShowMsg("成功生成TAG：[".$tag['tag']."]，继续进行操作", $gourl, 0, 100);
            exit();
        } else {
            //继续当前这个
            $gourl = "makehtml_taglist_action.php?mkpage=$mkpage&maxpagesize=$maxpagesize&tagid=$tagid&pageno=$pageno&upall=$upall&ctagid=$ctagid&startid=$startid&endid=$endid&mktime=$mktime";
            var_dump_cli($gourl);
            ShowMsg("成功生成TAG：[".$tag['tag']."]，继续进行操作", $gourl, 0, 100);
            exit();
        }
    }
}
