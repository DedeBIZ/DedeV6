<?php
if (!defined('DEDEINC')) exit('dedebiz');
/**
 * 过滤核心处理文件
 *
 * @version        $Id: filter.inc.php 1 15:59 2010年7月5日Z tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */

/**
 *  过滤不相关内容
 *
 * @access    public
 * @param     string  $fk 过滤键
 * @param     string  $svar 过滤值
 * @return    string
 */
$magic_quotes_gpc = ini_get('magic_quotes_gpc');
function _FilterAll($fk, &$svar)
{
    global $cfg_notallowstr, $cfg_replacestr, $magic_quotes_gpc;
    if (is_array($svar)) {
        foreach ($svar as $_k => $_v) {
            $svar[$_k] = _FilterAll($fk, $_v);
        }
    } else {
        if ($cfg_notallowstr != '' && preg_match("#".$cfg_notallowstr."#i", $svar)) {
            ShowMsg(" $fk has not allow words!", '-1');
            exit();
        }
        if ($cfg_replacestr != '') {
            $svar = preg_replace('/'.$cfg_replacestr.'/i', "***", $svar);
        }
    }
    if (!$magic_quotes_gpc) {
        //var_dump($svar);
        if (is_array($svar)) {
            foreach ($svar as $key => $value) {
                $svar[$key] = addslashes($svar[$key]);
            }
        } else {
            $svar = addslashes($svar);
        }
    }
    return $svar;
}

/* 对_GET,_POST,_COOKIE进行过滤 */
foreach (array('_GET', '_POST', '_COOKIE') as $_request) {
    foreach ($$_request as $_k => $_v) {
        ${$_k} = _FilterAll($_k, $_v);
    }
}
