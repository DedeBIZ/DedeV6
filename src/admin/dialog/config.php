<?php
/**
 * 该页仅用于检测用户登录的情况，如要手工修改系统配置，请修改common.inc.php
 *
 * @version        $Id: config.php 2022-07-01 tianya $
 * @package        DedeBIZ.Dialog
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
define('LANGSECTION', 'admin');
require_once(dirname(__FILE__)."/../../system/common.inc.php");
//获得当前脚本名称，如果您的系统被禁用了$_SERVER变量，请自行修改这个选项
$dedeNowurl   =  '';
$s_scriptName = '';
$isUrlOpen = @ini_get('allow_url_fopen');
$dedeNowurl = GetCurUrl();
$dedeNowurls = explode("?", $dedeNowurl);
$s_scriptName = $dedeNowurls[0];
//检验用户登录状态
$cUserLogin = new UserLogin();
if ($cUserLogin->getUserID() <= 0) {
    if (empty($adminDirHand)) {
        ShowMsg(Lang("dialog_nologin"), "javascript:;");
        exit();
    }
    $adminDirHand = HtmlReplace($adminDirHand, 1);
    $gurl = "../../{$adminDirHand}/login.php?gotopage=".urlencode($dedeNowurl);
    echo "<script>location='$gurl';</script>";
    exit();
}
?>