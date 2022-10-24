<?php
/**
 * 后台登录
 *
 * @version        $Id: login.php 1 8:48 2010年7月13日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
define('LANGSECTION', 'admin');
require_once(dirname(__FILE__).'/../system/common.inc.php');
if (empty($dopost)) $dopost = '';
if (empty($gotopage)) $gotopage = '';
$gotopage = RemoveXSS($gotopage);
//检测安装目录安全性
if (is_dir(dirname(__FILE__).'/../install')) {
    if (!file_exists(dirname(__FILE__).'/../install/install_lock.txt')) {
        $fp = fopen(dirname(__FILE__).'/../install/install_lock.txt', 'w') or DedeAlert(Lang('installed_not_writeable'), ALERT_DANGER);
        fwrite($fp, 'ok');
        fclose($fp);
    }
    $fileindex = "../install/index.html";
    if (!file_exists($fileindex)) {
        $fp = @fopen($fileindex, 'w');
        fwrite($fp, 'dir');
        fclose($fp);
    }
}
//检测后台目录是否更名
$cururl = GetCurUrl();
if (preg_match('/admin\/login/i', $cururl)) {
    $redmsg = '<div class="alert alert-warning"><div class="safe-tips">'.Lang('admin_path_suggest').'</div></div>';
} else {
    $redmsg = '';
}
//登录检测
$admindirs = explode('/', str_replace("\\", '/', dirname(__FILE__)));
$admindir = $admindirs[count($admindirs) - 1];
if ($dopost == 'login') {
    $validate = empty($validate) ? '' : strtolower(trim($validate));
    $svali = strtolower(GetCkVdValue());
    if ($validate == '' || $validate != $svali) {
        ResetVdValue();
        ShowMsg(Lang('incorrect_verification_code'), 'login.php', 0, 1000);
        exit;
    } else {
        $cUserLogin = new UserLogin($admindir);
        if (!empty($userid) && !empty($pwd)) {
            $res = $cUserLogin->checkUser($userid, $pwd);
            //success
            if ($res == 1) {
                $cUserLogin->keepUser();
                if (!empty($gotopage)) {
                    ShowMsg(Lang('login_success'), $gotopage);
                    exit();
                } else {
                    ShowMsg(Lang('login_success'), 'index.php');
                    exit();
                }
            }
            //error
            else if ($res == -1) {
                ResetVdValue();
                ShowMsg(Lang('username_not_exists'), 'login.php', 0, 1000);
                exit;
            } else {
                ResetVdValue();
                ShowMsg(Lang('password_incorrect'), 'login.php', 0, 1000);
                exit;
            }
        }
        //password empty
        else {
            ResetVdValue();
            ShowMsg(Lang('username_password_incorrect'), 'login.php', 0, 1000);
            exit;
        }
    }
}
include('templets/login.htm');
?>