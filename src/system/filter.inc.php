<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 过滤核心处理
 *
 * @version        $id:filter.inc.php 15:59 2010年7月5日 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
/**
 *  过滤不相关文档
 *
 * @access    public
 * @param     string  $fk 过滤键
 * @param     string  $svar 过滤值
 * @return    string
 */
function _FilterAll($fk, &$svar)
{
    global $cfg_notallowstr, $cfg_replacestr;
    if (is_array($svar)) {
        foreach ($svar as $_k => $_v) {
            $svar[$_k] = _FilterAll($fk, $_v);
        }
    } else {
        if ($cfg_notallowstr != '' && preg_match("#".$cfg_notallowstr."#i", $svar)) {
            ShowMsg("{$fk}字段中包含禁用词", '-1');
            exit();
        }
        if ($cfg_replacestr != '') {
            $svar = preg_replace('/'.$cfg_replacestr.'/i', "***", $svar);
        }
    }
    return $svar;
}
//对_GET,_POST,_COOKIE进行过滤
foreach (array('_GET', '_POST', '_COOKIE') as $_request) {
    foreach ($$_request as $_k => $_v) {
        ${$_k} = _FilterAll($_k, $_v);
    }
}
?>