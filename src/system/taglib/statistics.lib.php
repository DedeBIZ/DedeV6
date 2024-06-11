<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 统计标签
 *
 * @version        $id:statistics.lib.php 9:29 2022年3月26日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(DEDEINC."/libraries/statistics.class.php");
function lib_statistics(&$ctag, &$refObj)
{
    global $envs;
    $attlist = '';
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $pms = array();
    $pms['url_type'] = isset($envs['url_type'])? $envs['url_type'] : 0;
    $pms['typeid'] = isset($envs['typeid'])? $envs['typeid'] : 0;
    $pms['aid'] = isset($envs['aid'])? $envs['aid'] : 0;
    $pms['value'] = isset($envs['value'])? $envs['value'] : '';
    $revalue = '<script async src="'.$GLOBALS['cfg_cmspath'].'/apps/statistics.php?'.http_build_query($pms).'"></script>';
    return $revalue;
}
?>