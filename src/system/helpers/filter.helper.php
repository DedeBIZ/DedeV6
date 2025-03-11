<?php
if (!defined('DEDEINC')) exit ('dedebiz');
require_once DEDEINC."/libraries/HTMLPurifier/HTMLPurifier.auto.php";
/**
 * 过滤助手
 *
 * @version        $id:filter.helper.php 2010-07-05 11:43:09 tianya $
 * @package        DedeBIZ.Helpers
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
/**
 *  去除html中不规则文档字符
 *
 * @access    public
 * @param     string  $str  需要处理的字符串
 * @param     string  $rptype  返回类型
 *            $rptype = 0 表示仅替换 html标记
 *            $rptype = 1 表示替换 html标记同时去除连续空白字符
 *            $rptype = 2 表示替换 html标记同时去除所有空白字符
 *            $rptype = -1 表示仅替换 html危险的标记 
 * @return    string
 */
if (!function_exists('HtmlReplace')) {
    function HtmlReplace($str, $rptype = 0)
    {
        if (!is_string($str)) {
            return '';
        }
    
        $str = stripslashes($str); // 取消转义
    
        // 初始化 HTMLPurifier 配置（静态变量优化性能）
        static $purifier = null;
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', ''); // 只保留文本
        if ($purifier === null) {
            $config->set('Cache.SerializerPath', DEDEDATA.'/cache');
            $purifier = new HTMLPurifier($config);
        }
    
        // 处理不同模式
        if ($rptype == 0) {
            // 仅替换 HTML 标记
            $str = $purifier->purify($str);
        } elseif ($rptype == 1) {
            // 替换 HTML 标记 + 去除连续空白字符
            $str = $purifier->purify($str);
            $str = preg_replace("/[\r\n\t ]+/", ' ', $str); // 合并多余空格
        } elseif ($rptype == 2) {
            // 替换 HTML 标记 + 去除所有空白字符
            $str = $purifier->purify($str);
            $str = preg_replace("/\s+/", '', $str);
        } else {
            // 仅替换 HTML 危险标记
            $config->set('HTML.ForbiddenElements', ['script', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'meta', 'link']);
            $str = $purifier->purify($str);
        }
    
        return addslashes($str);
    }
}
/**
 *  修复浏览器XSS hack的函数
 *
 * @param     string   $val  需要处理的文档
 * @return    string
 */
if (!function_exists('RemoveXSS')) {
    function RemoveXSS($val)
    {
        static $purifier = null;
        if ($purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            
            // 启用缓存（提升性能）
            $config->set('Cache.SerializerPath', DEDEDATA.'/cache'); // 生产环境建议设定缓存目录
            
            // 允许的 HTML 元素（可以根据需要调整）
            $config->set('HTML.Allowed', 'p,b,strong,i,em,u,a[href|title],ul,ol,li,img[src|alt|width|height],br,span[class]');
            
            // 过滤 JavaScript、CSS 注入
            $config->set('CSS.AllowedProperties', []);
            $config->set('URI.DisableExternalResources', true);
            $config->set('URI.DisableResources', true);
            
            $purifier = new HTMLPurifier($config);
        }
        
        return $purifier->purify($val);
    }
}
/**
 *  处理禁用HTML但允许换行的文档
 *
 * @access    public
 * @param     string  $msg  需要过滤的文档
 * @return    string
 */
if (!function_exists('TrimMsg')) {
    function TrimMsg($msg)
    {
        $msg = trim(stripslashes($msg));
        $msg = nl2br(dede_htmlspecialchars($msg));
        $msg = str_replace("  ", "&nbsp;&nbsp;", $msg);
        return addslashes($msg);
    }
}
/**
 *  过滤用于搜索的字符串
 *
 * @param     string  $keyword  关键词
 * @return    string
 */
if (!function_exists('FilterSearch')) {
    function FilterSearch($keyword)
    {
        global $cfg_soft_lang;
        if ($cfg_soft_lang == 'utf-8') {
            $keyword = preg_replace("/[\"\r\n\t\$\\><']/", '', $keyword);
            if ($keyword != stripslashes($keyword)) {
                return '';
            } else {
                return $keyword;
            }
        } else {
            $restr = '';
            for ($i = 0; isset($keyword[$i]); $i++) {
                if (ord($keyword[$i]) > 0x80) {
                    if (isset($keyword[$i + 1]) && ord($keyword[$i + 1]) > 0x40) {
                        $restr .= $keyword[$i].$keyword[$i + 1];
                        $i++;
                    } else {
                        $restr .= ' ';
                    }
                } else {
                    if (preg_match("/[^0-9a-z@#\.]/", $keyword[$i])) {
                        $restr .= ' ';
                    } else {
                        $restr .= $keyword[$i];
                    }
                }
            }
        }
        return $restr;
    }
}
?>