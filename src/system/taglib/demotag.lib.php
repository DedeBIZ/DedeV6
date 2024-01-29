<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 演示标签
 *
 * @version        $id:demotag.lib.php 9:29 2010年7月6日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
function lib_demotag(&$ctag, &$refObj)
{
    global $dsql, $envs;
    $attlist = "row|10,titlelen|24";
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $revalue = '';
    //您需编写的代码，不能用echo之类语法，把最终返回值传给$revalue
    $revalue = '您好，欢迎使用DedeBIZ';
    return $revalue;
}
?>