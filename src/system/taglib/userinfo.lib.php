<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 会员信息标签
 *
 * @version        $id:userinfo.lib.php tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2023 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
function lib_userinfo(&$ctag, &$refObj)
{
    global $dsql;
    $attlist="mid|0";
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $rmid = !empty($refObj->Fields['mid'])? intval($refObj->Fields['mid']) : 0;
    $mid = $mid > 0 ? $mid : $rmid;
    if ($mid == 0) return "";
    $revalue = '';
    $innerText = trim($ctag->GetInnerText());
    if (empty($innerText)) $innerText = GetSysTemplets('userinfo.htm');
    $sql = "SELECT U.*,US.spacename,US.sign,AR.membername as rankname FROM `#@__member` U LEFT JOIN `#@__member_space` US ON US.mid = U.mid  LEFT JOIN `#@__arcrank` AR ON AR.`rank` = U.`rank`  WHERE U.mid='{$mid}' LIMIT 0,1 ";
    $ctp = new DedeTagParse();
    $ctp->SetNameSpace('field','[',']');
    $ctp->LoadSource($innerText);
    $dsql->Execute('user',$sql);
    while($row = $dsql->GetArray('user'))
    {
        if ($row['matt']==10) return ''; //不显示管理员信息
        $row['userurl'] = $GLOBALS['cfg_memberurl'].'/index.php?uid='.$row['userid'];
        $row['face'] = empty($row['face'])? $GLOBALS['cfg_mainsite'].'/static/web/img/admin.png' : $row['face'];
        foreach($ctp->CTags as $tagid=>$ctag)
        {
            if (isset($row[$ctag->GetName()])){
                $ctp->Assign($tagid,$row[$ctag->GetName()]);
            }
        }
        $revalue .= $ctp->GetResult();
    }
    return $revalue;
}
?>