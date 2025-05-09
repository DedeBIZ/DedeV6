<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 会员信息标签
 *
 * @version        $id:memberlist.lib.php 9:29 2010年7月6日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
function lib_memberlist(&$ctag, &$refObj)
{
    global $dsql, $sqlCt;
    $attlist = "row|6,iscommend|0,orderby|logintime,signlen|50";
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $revalue = '';
    $innerText = trim($ctag->GetInnerText());
    if (empty($innerText)) $innerText = GetSysTemplets('memberlist.htm');
    $wheresql = ' WHERE mb.spacesta>-1 AND mb.matt<10 ';
    if ($iscommend > 0) $wheresql .= " AND  mb.matt='$iscommend' ";
    $sql = "SELECT mb.*,ms.spacename,ms.sign FROM `#@__member` mb LEFT JOIN `#@__member_space` ms ON ms.mid = mb.mid $wheresql ORDER BY mb.{$orderby} DESC LIMIT 0,$row ";
    $ctp = new DedeTagParse();
    $ctp->SetNameSpace('field', '[', ']');
    $ctp->LoadSource($innerText);
    $dsql->Execute('mb', $sql);
    while ($row = $dsql->GetArray('mb')) {
        $row['userurl'] = $row['spaceurl'] = $GLOBALS['cfg_basehost'].$GLOBALS['cfg_memberurl'].'/index.php?uid='.$row['userid'];
        $row['face'] = empty($row['face'])? $GLOBALS['cfg_mainsite'].'/static/web/img/admin.png' : $row['face'];
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