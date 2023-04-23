<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 动态模板memberlist标签
 *
 * @version        $id:plus_memberlist.php 13:58 2010年7月5日 tianya $
 * @package        DedeBIZ.Tpllib
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
function plus_memberlist(&$atts, &$refObj, &$fields)
{
    global $dsql, $_vars;
    $attlist = "row=6,iscommend=0,orderby=logintime,signlen=50";
    FillAtts($atts, $attlist);
    FillFields($atts, $fields, $refObj);
    extract($atts, EXTR_OVERWRITE);
    $rearray = array();
    $wheresql = ' WHERE mb.spacesta > -1 AND mb.matt != 10';
    if ($iscommend > 0) $wheresql .= " AND  mb.matt='$iscommend' ";
    $sql = "SELECT mb.*,ms.spacename,ms.sign FROM `#@__member` mb LEFT JOIN `#@__member_space` ms ON ms.mid = mb.mid $wheresql ORDER BY mb.{$orderby} DESC LIMIT 0,$row ";
    $dsql->Execute('mb', $sql);
    while ($row = $dsql->GetArray('mb')) {
        $row['spaceurl'] = $GLOBALS['cfg_basehost'].'/user/index.php?uid='.$row['userid'];
        if (empty($row['face'])) {
            $row['face'] = ($row['sex'] == '?') ? $GLOBALS['cfg_memberurl'].'/templets/images/dfgirl.png' : $GLOBALS['cfg_memberurl'].'/templets/images/dfboy.png';
        }
        $rearray[] = $row;
    }
    return $rearray;
}
?>