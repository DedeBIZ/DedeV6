<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 当前栏目列表标签
 *
 * @version        $id:channelartlist.lib.php 9:29 2010年7月6日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(DEDEINC.'/archive/partview.class.php');
function lib_channelartlist(&$ctag, &$refObj)
{
    global $dsql, $envs, $_sys_globals;
    $attlist = "typeid|0,row|10,cacheid|,type|,notypeid|0,currentstyle|"; //后续添加否定栏目调用notypeid
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $innertext = trim($ctag->GetInnerText());
    $artlist = '';
    //读取固定的缓存块
    $cacheid = trim($cacheid);
    if ($cacheid != '') {
        $artlist = GetCacheBlock($cacheid);
        if ($artlist != '') return $artlist;
    }
    if (empty($typeid)) {
        $typeid = (!empty($refObj->TypeLink->TypeInfos['id']) ?  $refObj->TypeLink->TypeInfos['id'] : 0);
    }
    if ($innertext == '') $innertext = GetSysTemplets('part_channelartlist.htm');
    $totalnum = $row;
    if (empty($totalnum)) $totalnum = 20;
    //获得类别id总数的信息
    $typeids = array();
    $order = " ORDER BY sortrank ASC ";
    if ($type == 'reid') {        
        $reid = $refObj->TypeLink->TypeInfos['reid'];          
        $tpsql = " reid='$reid' AND ispart<>2 AND ishidden<>1 ";
    } else if ($typeid == 0 || $typeid == 'top') {
        $tpsql = " reid=0 AND ispart<>2 AND ishidden<>1 AND channeltype>0 ";
    } else {
        if (!preg_match('#,#', $typeid)) {
            $tpsql = " reid='$typeid' AND ishidden<>1 ";
        } else {
            $tpsql = " id IN($typeid) AND ishidden<>1 ";
            $order = " ORDER BY FIELD(id,$typeid) ";
        }
    }
    //否定栏目调用
    if ($notypeid != 0) {
        $tpsql = $tpsql." and not(id in($notypeid)) ";
    }
    $dsql->SetQuery("SELECT * FROM `#@__arctype` WHERE $tpsql $order LIMIT $totalnum");
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        $typeids[] = $row;
    }
    if (!isset($typeids[0])) return '';
    $GLOBALS['itemindex'] = 0;
    $GLOBALS['itemparity'] = 1;
    for ($i = 0; isset($typeids[$i]); $i++) {
        $GLOBALS['itemindex']++;
        $pv = new PartView($typeids[$i]['id']);
        $pv->Fields['typeurl'] = GetOneTypeUrlA($typeids[$i]);
        //栏目高亮
        if (isset($refObj->TypeLink->TypeInfos)) {
            if ($typeids[$i]['id'] == $refObj->TypeLink->TypeInfos['id'] || $typeids[$i]['id'] == $refObj->TypeLink->TypeInfos['reid'] || $typeids[$i]['id'] == $refObj->TypeLink->TypeInfos['topid'] || $typeids[$i]['id'] == GetTopid($refObj->TypeLink->TypeInfos['id']) )
            {
                $pv->Fields['currentstyle'] = $currentstyle ? $currentstyle : 'current';
            } else {
                $pv->Fields['currentstyle'] = '';
            }
        }
        $pv->SetTemplet($innertext,'string');
        $artlist .= $pv->GetResult();
        $GLOBALS['itemparity'] = ($GLOBALS['itemparity'] == 1 ? 2 : 1);
    }
    //注销环境变量，以防止后续调用中被使用
    $GLOBALS['envs']['typeid'] = $_sys_globals['typeid'];
    $GLOBALS['envs']['reid'] = '';
    if ($cacheid != '') {
        WriteCacheBlock($cacheid, $artlist);
    }
    return $artlist;
}
?>