<?php
/**
 * 检测登录情况
 *
 * @version        $id:config.php 9:43 2010年7月8日 tianya $
 * @package        DedeBIZ.Dialog
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../../system/common.inc.php");
require_once(DEDEINC."/userlogin.class.php");
//获得当前脚本名称，如果系统被禁用了$_SERVER变量，请自行修改这个选项
$dedeNowurl   =  '';
$s_scriptName = '';
$isUrlOpen = @ini_get('allow_url_fopen');
$dedeNowurl = GetCurUrl();
$dedeNowurls = explode("?", $dedeNowurl);
$s_scriptName = $dedeNowurls[0];
//检验会员登录状态
$cuserLogin = new userLogin();
if ($cuserLogin->getUserID() <= 0) {
    if (empty($adminDirHand)) {
        ShowMsg("<p>提示：需要输入后台管理目录才能登录</p><form><span>请输入后台管理目录名：</span><input type='hidden' name='gotopage' value='".urlencode($dedeNowurl)."'><input type='text' name='adminDirHand' value='admin'><input type='submit' name='sbt' value='前往登录'></form>", "javascript:;");
        exit();
    }
    $adminDirHand = HtmlReplace($adminDirHand, 1);
    $gurl = "/../{$adminDirHand}/login.php?gotopage=".urlencode($dedeNowurl);
    echo "<script>location='$gurl';</script>";
    exit();
}
?>