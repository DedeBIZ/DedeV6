<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 提示框函数
 * @version        $id:dedealert.func.php 2023年12月31日 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
//类似Bootstrap警告框
define('ALERT_PRIMARY', 1);
define('ALERT_SECONDARY', 2);
define('ALERT_SUCCESS', 3);
define('ALERT_DANGER', 4);
define('ALERT_WARNING', 5);
define('ALERT_INFO', 6);
define('ALERT_LIGHT', 7);
define('ALERT_DARK', 8);
define("ALERT_TPL", '<div style="position:relative;padding:0.75rem 1.25rem;margin-bottom:1rem;width:auto;font-size:14px;color:~color~;background:~background~;border-color:~border~;border:1px solid transparent;border-radius:0.5rem">~content~</div>');
//$content:文档，$type:alert类型
function DedeAlert($content, $type = ALERT_PRIMARY, $isHTML = false)
{
    $colors = array(
        ALERT_PRIMARY => array('#cfe2ff','#b6d4fe','#084298'),
        ALERT_SECONDARY => array('#e2e3e5','#d3d6d8','#41464b'),
        ALERT_SUCCESS => array('#d1e7dd','#badbcc','#0f5132'),
        ALERT_DANGER => array('#f8d7da','#f5c2c7','#842029'),
        ALERT_WARNING => array('#fff3cd','#ffecb5','#664d03'),
        ALERT_INFO => array('#cff4fc','#b6effb','#055160'),
        ALERT_LIGHT => array('#fefefe','#fdfdfe','#636464'),
        ALERT_DARK => array('#d3d3d4','#bcbebf','#141619'),
    );
    $content = $isHTML? RemoveXSS($content) : htmlspecialchars($content);
    $colors = isset($colors[$type])? $colors[$type] : $colors[ALERT_PRIMARY];
    list($background, $border, $color) = $colors;
    return str_replace(array('~color~','~background~','~border~', '~content~'),array($color,$background,$border,$content),ALERT_TPL);
}