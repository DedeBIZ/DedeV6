<?php
/**
 * 标签源码管理
 *
 * @version        $id:templets_tagsource.php 23:44 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert("系统已启用安全模式，无法使用当前功能",ALERT_DANGER));
}
CheckPurview('plus_文件管理器');
$libdir = DEDEINC.'/taglib';
$helpdir = DEDEINC.'/taglib/help';
//获取默认文件说明信息
function GetHelpInfo($tagname)
{
    global $helpdir;
    $helpfile = $helpdir.'/'.$tagname.'.txt';
    if (!file_exists($helpfile)) {
        return '该标签没帮助信息';
    }
    $fp = fopen($helpfile, 'r');
    $helpinfo = fgets($fp, 64);
    fclose($fp);
    return $helpinfo;
}
include DedeInclude('templets/templets_tagsource.htm');
?>