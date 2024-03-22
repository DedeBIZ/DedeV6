<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 指定排序栏目信息标签
 *
 * @version        $id:autochannel.lib.php 9:29 2010年7月6日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
function lib_autochannel(&$ctag, &$refObj)
{
    global $dsql;
    $attlist = 'partsort|0,typeid=-1';
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $innertext = trim($ctag->GetInnerText());
    $topid = $typeid;
    $sortid = $partsort;
    if ($topid == '-1' || $topid == '') {
        $topid = (isset($refObj->TypeLink->TypeInfos['id']) ? $refObj->TypeLink->TypeInfos['id'] : 0);
    }
    if (empty($sortid)) $sortid = 1;
    $getstart = $sortid - 1;
    $row = $dsql->GetOne("SELECT id,typename FROM `#@__arctype` WHERE reid='{$topid}' AND ispart<2 AND ishidden<>'1' ORDER BY sortrank ASC LIMIT $getstart,1");
    if (!is_array($row)) return '';
    else $typeid = $row['id'];
    if (trim($innertext) == '') $innertext = GetSysTemplets('part_autochannel.htm');
    $row = $dsql->GetOne("SELECT id,typedir,isdefault,defaultname,ispart,namerule2,typename,moresite,siteurl,sitepath FROM `#@__arctype` WHERE id='$typeid' ");
    if (!is_array($row)) return '';
    $dtp = new DedeTagParse();
    $dtp->SetNameSpace('field', '[', ']');
    $dtp->LoadSource($innertext);
    if (!is_array($dtp->CTags)) {
        unset($dtp);
        return '';
    } else {
        $row['typelink'] = GetTypeUrl(
            $row['id'],
            MfTypedir($row['typedir']),
            $row['isdefault'],
            $row['defaultname'],
            $row['ispart'],
            $row['namerule2'],
            $row['siteurl'],
            $row['sitepath']
        );
        foreach ($dtp->CTags as $tagid => $ctag) {
            if (isset($row[$ctag->GetName()])) $dtp->Assign($tagid, $row[$ctag->GetName()]);
        }
        $revalue = $dtp->GetResult();
        unset($dtp);
        return $revalue;
    }
}
?>