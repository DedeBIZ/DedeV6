<?php
/**
 * 文件管理器
 *
 * @version        $Id: templets_tagsource.php 1 23:44 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__).'/config.php');
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert(Lang("err_safemode_check"),ALERT_DANGER));
}
UserLogin::CheckPurview('plus_文件管理器');
$libdir = DEDEINC.'/taglib';
$helpdir = DEDEINC.'/taglib/help';
//获取默认文件说明信息
function GetHelpInfo($tagname)
{
    global $helpdir;
    $helpfile = $helpdir.'/'.$tagname.'.txt';
    if (!file_exists($helpfile)) {
        return Lang('tag_err_helpinfo');
    }
    $fp = fopen($helpfile, 'r');
    $helpinfo = fgets($fp, 64);
    fclose($fp);
    return $helpinfo;
}
include DedeInclude('templets/templets_tagsource.htm');
?>