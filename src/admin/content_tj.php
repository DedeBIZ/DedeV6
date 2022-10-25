<?php
/**
 * 内容统计
 *
 * @version        $Id: content_tj.php 1 14:31 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_ArcTj');
$row1 = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__arctiny` ");
$row2 = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__feedback` ");
$row3 = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__member` ");
/**
 *  获取文档
 *
 * @param     object  $dsql
 * @param     string  $ordertype  排序类型
 * @return    string
 */
function GetArchives($dsql, $ordertype)
{
    $starttime = time() - (24 * 3600 * 30);
    if ($ordertype == 'monthFeedback' || $ordertype == 'monthHot') {
        $swhere = " where senddate>$starttime ";
    } else {
        $swhere = "";
    }
    if (preg_match("#feedback#", $ordertype)) {
        $ordersql = " ORDER BY scores DESC ";
    } else {
        $ordersql = " ORDER BY click DESC ";
    }
    $query = "SELECT id,title,click,scores FROM `#@__archives` $swhere $ordersql LIMIT 0,30 ";
    $dsql->SetQuery($query);
    $dsql->Execute('ga');
    while ($row = $dsql->GetObject('ga')) {
        if (preg_match("#feedback#i", $ordertype)) {
            $moreinfo = "[<a target='_blank' href='".$GLOBALS['cfg_phpurl']."/feedback.php?aid={$row->id}'>评论：{$row->scores}</a>]";
        } else {
            $moreinfo = "[点击：{$row->click}]";
        }
        echo "·<a href='archives_do.php?aid={$row->id}&dopost=viewArchives' target='_blank'>";
        echo cn_substr($row->title, 30)."</a>{$moreinfo}<br>\r\n";
    }
}
include DedeInclude('templets/content_tj.htm');
?>