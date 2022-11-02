<?php
if (!defined('DEDEINC')) exit('dedebiz');
/**
 * 广告调用
 *
 * @version        $id:myad.lib.php 9:29 2010年7月6日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(DEDEINC.'/taglib/mytag.lib.php');
function lib_myad(&$ctag, &$refObj)
{
    $attlist = "typeid|0,name|";
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $body = lib_GetMyTagT($refObj, $typeid, $name, '#@__myad');
    return $body;
}
?>