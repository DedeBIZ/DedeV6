<?php
if (!defined('DEDEINC')) exit('Request Error!');

/**
 * 文档内容调用标签
 *
 * @version        $Id: arccontent.lib.php 2020年9月14日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */

require_once(DEDEINC . "/channelunit.class.php");
// 这是一个用来调用文档内容的标签，只是提供一种方法，不建议太多地方调用，毕竟比较损耗性能
/*
用法：
{dede:arccontent type='pre|next'}
[field:body/]
{/dede:arccontent}
*/
function lib_arccontent(&$ctag, &$refObj)
{
    global $dsql;
    $aid = $ctag->GetAtt('aid');
    $type = $ctag->GetAtt('type');
    $revalue = "";

    if (in_array($type, array("pre", "next")) &&  get_class($refObj) === "Archives") {
        // 在内容页面获取上一篇下一篇内容
        $asql = "WHERE id<{$refObj->Fields['id']}";
        if ($type === "next") {
            $asql = "WHERE id>{$refObj->Fields['id']}";
        }
        $row =  $dsql->GetOne("SELECT id,channel FROM `#@__arctiny` $asql AND arcrank>-1 AND typeid='{$refObj->Fields['typeid']}' ORDER BY id DESC");

        $channel = new ChannelUnit($row['channel'], $refObj->Fields['id']);
        $fields = $dsql->GetOne("SELECT * FROM `{$channel->ChannelInfos['addtable']}` WHERE aid = {$row['id']}");
    }

    if (!empty($aid)) {
        // 指定ID获取内容
        $row =  $dsql->GetOne("SELECT id,channel FROM `#@__arctiny` WHERE id={$aid} AND arcrank>-1");
        $channel = new ChannelUnit($row['channel'], $aid);
        $fields = $dsql->GetOne("SELECT * FROM `{$channel->ChannelInfos['addtable']}` WHERE aid = {$row['id']}");
    }


    $innerText = trim($ctag->GetInnerText());
    $ctp = new DedeTagParse();
    $ctp->SetNameSpace('field', '[', ']');
    $ctp->LoadSource($innerText);


    if (is_array($ctp->CTags)) {
        foreach ($ctp->CTags as $tagid => $ctag) {
            if (isset($fields[$ctag->GetName()])) {
                $ctp->Assign($tagid, $fields[$ctag->GetName()]);
            }
        }
        $revalue .= $ctp->GetResult();
    }

    return $revalue;
}
