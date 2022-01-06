<?php
if (!defined('DEDEINC')) {
    exit("Request Error!");
}
/**
 * 调用任意表的数据标签
 *
 * @version        $Id: loop.lib.php 1 9:29 2010年7月6日Z tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */

require_once(DEDEINC.'/dedevote.class.php');
function lib_loop(&$ctag, &$refObj)
{
    global $dsql;
    $attlist = "table|,tablename|,row|8,sort|,if|,ifcase|,orderway|desc";
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);

    $innertext = trim($ctag->GetInnertext());
    $revalue = '';
    if (!empty($table)) $tablename = $table;

    if ($tablename == '' || $innertext == '') return '';
    if ($if != '') $ifcase = $if;

    if ($sort != '') $sort = " ORDER BY $sort $orderway ";
    if ($ifcase != '') $ifcase = " WHERE $ifcase ";
    $dsql->SetQuery("SELECT * FROM $tablename $ifcase $sort LIMIT 0,$row");
    $dsql->Execute();
    $ctp = new DedeTagParse();
    $ctp->SetNameSpace("field", "[", "]");
    $ctp->LoadSource($innertext);
    $GLOBALS['autoindex'] = 0;
    while ($row = $dsql->GetArray()) {
        $GLOBALS['autoindex']++;
        foreach ($ctp->CTags as $tagid => $ctag) {
            if ($ctag->GetName() == 'array') {
                $ctp->Assign($tagid, $row);
            } else {
                if (!empty($row[$ctag->GetName()])) $ctp->Assign($tagid, $row[$ctag->GetName()]);
            }
        }
        $revalue .= $ctp->GetResult();
    }
    return $revalue;
}
