<?php
if (!defined('DEDEINC')) exit('dedebiz');
/**
 * TAG调用标签
 *
 * @version        $Id: tag.lib.php 1 9:29 2010年7月6日Z tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
function lib_tag(&$ctag, &$refObj)
{
    global $dsql, $envs, $cfg_cmsurl,$cfg_tags_dir;
    //属性处理
    $attlist = "row|30,sort|new,getall|0,typeid|0,ishtml|0";
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $InnerText = $ctag->GetInnerText();
    if (trim($InnerText) == '') $InnerText = GetSysTemplets('tag_one.htm');
    $revalue = '';
    $ltype = $sort;
    $num = $row;
    $addsql = "WHERE 1=1";
    $tagsdir = str_replace("{cmspath}", $cfg_cmspath, $cfg_tags_dir);
    if ($getall == 0 && isset($refObj->Fields['tags']) && !empty($refObj->Fields['aid'])) {
        $dsql->SetQuery("SELECT tid FROM `#@__taglist` WHERE aid = '{$refObj->Fields['aid']}' ");
        $dsql->Execute();
        $ids = '';
        while ($row = $dsql->GetArray()) {
            $ids .= ($ids == '' ? $row['tid'] : ','.$row['tid']);
        }
        if ($ids != '') {
            $addsql .= " AND id IN($ids)";
        }
        if ($addsql == '') return '';
    } else {
        if (!empty($typeid)) {
            $addsql .= " AND typeid='$typeid'";
        }
    }
    if ($ltype == 'rand') $orderby = 'rand() ';
    else if ($ltype == 'week') $orderby = ' weekcc DESC ';
    else if ($ltype == 'month') $orderby = ' monthcc DESC ';
    else if ($ltype == 'hot') $orderby = ' count DESC ';
    else if ($ltype == 'total') $orderby = ' total DESC ';
    else $orderby = 'addtime DESC  ';
    $dsql->SetQuery("SELECT * FROM `#@__tagindex` $addsql ORDER BY $orderby LIMIT 0,$num");
    $dsql->Execute();
    $ctp = new DedeTagParse();
    $ctp->SetNameSpace('field', '[', ']');
    $ctp->LoadSource($InnerText);
    while ($row = $dsql->GetArray()) {
        $row['keyword'] = $row['tag'];
        $row['tag'] = dede_htmlspecialchars($row['tag']);
        if (isset($envs['makeTag']) && $envs['makeTag'] == 1 || $ishtml == 1) {
            $row['link'] = $cfg_cmsurl.$tagsdir."/".$row['id']."/";
        } else {
            $row['link'] = $cfg_cmsurl."/apps/tags.php?/".$row['id']."/";
        }
        $row['highlight'] = mt_rand(1, 10);
        foreach ($ctp->CTags as $tagid => $ctag) {
            if (isset($row[$ctag->GetName()])) {
                $ctp->Assign($tagid, $row[$ctag->GetName()]);
            }
        }
        $revalue .= $ctp->GetResult();
    }
    return $revalue;
}
?>