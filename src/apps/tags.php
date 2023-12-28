<?php
/**
 * 标签页
 * 
 * @version        $id:tags.php 2010-06-30 11:43:09 tianya $
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../system/common.inc.php");
require_once(DEDEINC."/archive/taglist.class.php");
$PageNo = 1;
if (isset($_SERVER['QUERY_STRING'])) {
    $tag = trim($_SERVER['QUERY_STRING']);
    $tags = explode('/', $tag);
    if (isset($tags[1])) $tag = $tags[1];
    if (isset($tags[2])) $PageNo = intval($tags[2]);
} else {
    $tag = '';
}
$tag = FilterSearch(urldecode($tag));
if ($tag != addslashes($tag)) $tag = '';
if ($tag == '') $dlist = new TagList($tag, 'tag.htm');
else $dlist = new TagList($tag, 'tag_list.htm');
$dlist->Display();
exit();
?>