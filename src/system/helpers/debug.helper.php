<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 报错助手
 *
 * @version        $id:debug.helper.php 2 13:56 2010年7月5日 tianya $
 * @package        DedeBIZ.Helpers
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
/**
 * 浏览器友好的变量输出，便于调试时候使用
 *
 * @param     mixed   $var       要输出查看的文档
 * @param     bool    $echo      是否直接输出
 * @param     string  $label     加上说明标签,如果有,这显示"标签名:"这种形式
 * @param     bool    $strict    是否严格过滤
 * @return    string
 */
if (!function_exists('Dump')) {
    function Dump($var, $echo = true, $label = null, $strict = true)
    {
        $label = ($label === null) ? '' : rtrim($label).' ';
        if (!$strict) {
            if (ini_get('html_errors')) {
                $output = print_r($var, true);
                $output = "<pre>".$label.htmlspecialchars($output, ENT_QUOTES)."</pre>";
            } else {
                $output = $label." : ".print_r($var, true);
            }
        } else {
            ob_start();
            var_dump($var);
            $output = ob_get_clean();
            if (!extension_loaded('xdebug')) {
                $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
                $output = '<pre>'.$label.htmlspecialchars($output, ENT_QUOTES).'</pre>';
            }
        }
        if ($echo) {
            echo ($output);
            return null;
        } else
            return $output;
    }
}
/**
 *  获取执行时间，例如$t1 = ExecTime();在一段文档处理之后$t2 = ExecTime();我们可以2个时间的差值输出echo $t2-$t1;
 *
 *  @return    int
 */
if (!function_exists('ExecTime')) {
    function ExecTime()
    {
        $time = explode(" ", microtime());
        $usec = (float)$time[0];
        $sec = (float)$time[1];
        return $sec + $usec;
    }
}
?>