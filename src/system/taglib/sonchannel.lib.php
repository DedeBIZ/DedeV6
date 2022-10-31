<?php
/**
 * 子栏目调用标签
 *
 * @version        $Id: sonchannel.lib.php 1 9:29 2010年7月6日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
function lib_sonchannel(&$ctag, &$refObj)
{
    global $_sys_globals, $dsql;
    $attlist = "row|100,nosonmsg|,col|1";
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $innertext = $ctag->GetInnerText();
    $typeid = $_sys_globals['typeid'];
    if (empty($typeid)) {
        return $ctag->GetAtt('nosonmsg');
    }
    $sql = "SELECT id,typename,typedir,isdefault,ispart,defaultname,namerule2,moresite,siteurl,sitepath
   FROM `#@__arctype` WHERE reid='$typeid' AND ishidden<>1 ORDER BY sortrank ASC LIMIT 0,$row";
    //And id<>'$typeid'
    $dtp2 = new DedeTagParse();
    $dtp2->SetNameSpace("field", "[", "]");
    $dtp2->LoadSource($innertext);
    $dsql->SetQuery($sql);
    $dsql->Execute();
    $line = $row;
    $GLOBALS['autoindex'] = 0;
    $likeType = '';
    for ($i = 0; $i < $line; $i++) {
        if ($col > 1) $likeType .= "<dl>\r\n";
        for ($j = 0; $j < $col; $j++) {
            if ($col > 1) $likeType .= "<dd>\r\n";
            if ($row = $dsql->GetArray()) {
                $row['typelink'] = $row['typeurl'] = GetOneTypeUrlA($row);
                if (is_array($dtp2->CTags)) {
                    foreach ($dtp2->CTags as $tagid => $ctag) {
                        if (isset($row[$ctag->GetName()])) $dtp2->Assign($tagid, $row[$ctag->GetName()]);
                    }
                }
                $likeType .= $dtp2->GetResult();
            }
            if ($col > 1) $likeType .= "</dd>\r\n";
            $GLOBALS['autoindex']++;
        } //Loop Col
        if ($col > 1) {
            $i += $col - 1;
            $likeType .= "    </dl>\r\n";
        }
    } //Loop for $i
    $dsql->FreeResult();
    return $likeType;
}
?>