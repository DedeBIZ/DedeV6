<?php
if (!defined('DEDEINC')) {
    exit("Request Error!");
}
/**
 * 这仅是一个演示标签
 *
 * @version        $Id: demotag.lib.php 1 9:29 2010年7月6日Z tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2020, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */


function lib_demotag(&$ctag, &$refObj)
{
    global $dsql, $envs;

    //属性处理
    $attlist = "row|12,titlelen|24";
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $revalue = '';

    //你需编写的代码，不能用echo之类语法，把最终返回值传给$revalue
    //------------------------------------------------------

    $revalue = 'Hello Word!';

    //------------------------------------------------------
    return $revalue;
}
