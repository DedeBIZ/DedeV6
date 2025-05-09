<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 单页文档标签
 *
 * @version        $id:likesgpage.lib.php 9:29 2010年7月6日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
function lib_likesgpage(&$ctag, &$refObj)
{
    global $dsql;
    $attlist = "row|10"; //把属性转为变量，如果不想进行此步骤，也可以直接从$ctag->CAttribute->Items获得，这样也可以支持中文名
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $innertext = trim($ctag->GetInnerText());
    $aid = (isset($refObj->Fields['aid']) ? $refObj->Fields['aid'] : 0);
    $revalue = '';
    if ($innertext == '') $innertext = GetSysTemplets("part_arclist.htm");
    $likeid = (empty($refObj->Fields['likeid']) ?  'all' : $refObj->Fields['likeid']);
    $dsql->SetQuery("SELECT aid,title,filename FROM `#@__sgpage` WHERE likeid LIKE '$likeid' LIMIT 0,$row");
    $dsql->Execute();
    $ctp = new DedeTagParse();
    $ctp->SetNameSpace('field', '[', ']');
    $ctp->LoadSource($innertext);
    while ($row = $dsql->GetArray()) {
        if ($aid != $row['aid']) {
            $row['url'] = $GLOBALS['cfg_cmsurl'].'/'.$row['filename'];
            foreach ($ctp->CTags as $tagid => $ctag) {
                if (!empty($row[$ctag->GetName()])) $ctp->Assign($tagid, $row[$ctag->GetName()]);
            }
            $revalue .= $ctp->GetResult();
        } else {
            $revalue .= '<p>'.$row['title'].'</p>';
        }
    }
    return $revalue;
}
?>