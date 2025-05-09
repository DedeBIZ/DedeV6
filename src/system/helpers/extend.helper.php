<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 扩展助手
 *
 * @version        $id:extend.helper.php 13:58 2010年7月5日 tianya $
 * @package        DedeBIZ.Helpers
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
/**
 *  返回指定的字符
 *
 * @param     string  $n  字符ID
 * @return    string
 */
if (!function_exists('ParCv')) {
    function ParCv($n)
    {
        return chr($n);
    }
}
/**
 *  显示一个错误
 *
 * @return    void
 */
if (!function_exists('ParamError')) {
    function ParamError()
    {
        ShowMsg('当前网页不存在，系统自动返回主页', '/');
        exit();
    }
}
/**
 *  默认属性
 *
 * @param     string  $oldvar  旧的值
 * @param     string  $nv      新值
 * @return    string
 */
if (!function_exists('AttDef')) {
    function AttDef($oldvar, $nv)
    {
        return empty($oldvar) ? $nv : $oldvar;
    }
}
/**
 *  返回Ajax头信息
 *
 * @return     void
 */
if (!function_exists('AjaxHead')) {
    function AjaxHead()
    {
        @header("Pragma:no-cache\r\n");
        @header("Cache-Control:no-cache\r\n");
        @header("Expires:0\r\n");
    }
}
/**
 *  去除html和php标记
 *
 * @return     string
 */
if (!function_exists('dede_strip_tags')) {
    function dede_strip_tags($str)
    {
        $strs = explode('<', $str);
        $res = $strs[0];
        for ($i = 1; $i < count($strs); $i++) {
            if (!strpos($strs[$i], '>'))
                $res = $res.'&lt;'.$strs[$i];
            else
                $res = $res.'<'.$strs[$i];
        }
        return strip_tags($res);
    }
}
?>