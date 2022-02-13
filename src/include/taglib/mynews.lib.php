<?php

/**
 * 站内新闻调用标签
 *
 * @version        $Id:mynews.lib.php 1 9:29 2010年7月6日Z tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */


function lib_mynews(&$ctag, &$refObj)
{
    global $dsql, $envs;
    //属性处理
    $attlist = "row|1,titlelen|24";
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);

    $innertext = trim($ctag->GetInnerText());
    if (empty($row)) $row = 1;
    if (empty($titlelen)) $titlelen = 30;
    if (empty($innertext)) $innertext = GetSysTemplets('mynews.htm');

    $idsql = '';
    if ($envs['typeid'] > 0) $idsql = " WHERE typeid='".GetTopid($this->TypeID)."' ";
    $dsql->SetQuery("SELECT * FROM #@__mynews $idsql ORDER BY senddate DESC LIMIT 0,$row");
    $dsql->Execute();
    $ctp = new DedeTagParse();
    $ctp->SetNameSpace('field', '[', ']');
    $ctp->LoadSource($innertext);
    $revalue = '';
    while ($row = $dsql->GetArray()) {
        foreach ($ctp->CTags as $tagid => $ctag) {
            @$ctp->Assign($tagid, $row[$ctag->GetName()]);
        }
        $revalue .= $ctp->GetResult();
    }
    return $revalue;
}
