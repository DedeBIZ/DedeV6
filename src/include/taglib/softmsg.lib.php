<?php
if (!defined('DEDEINC')) {
    exit("dedebiz");
}
/**
 * 下载说明标签
 *
 * @version        $Id: softmsg.lib.php 1 9:29 2010年7月6日Z tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */

function lib_softmsg(&$ctag, &$refObj)
{
    global $dsql;
    //$attlist="type|textall,row|24,titlelen|24,linktype|1";
    //FillAttsDefault($ctag->CAttribute->Items,$attlist);
    //extract($ctag->CAttribute->Items, EXTR_SKIP);
    $revalue = '';
    $row = $dsql->GetOne(" SELECT * FROM `#@__softconfig` ");
    if (is_array($row)) $revalue = $row['downmsg'];
    return $revalue;
}
