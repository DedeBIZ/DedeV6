<?php
/**
 * 后台登录
 *
 * @version        $id:login.php 8:48 2010年7月13日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/../system/common.inc.php');
require_once(DEDEINC.'/userlogin.class.php');
if (empty($dopost)) $dopost = '';
if (empty($gotopage)) $gotopage = '';
$gotopage = RemoveXSS($gotopage);
//检测安装目录安全性
if (is_dir(dirname(__FILE__).'/../install')) {
    if (!file_exists(dirname(__FILE__).'/../install/install_lock.txt')) {
        $fp = fopen(dirname(__FILE__).'/../install/install_lock.txt', 'w') or die('安装目录无写入权限和写入锁定文件，请安装完成后删除安装目录');
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
if (preg_match('/admin/', $cururl)) {
    $redmsg = '<div class="alert alert-warning">检测到后台管理目录名称中包含admin，强烈建议后台管理目录修改为其它名称</div>';
} else {
    $redmsg = '';
}
//登录检测
$admindirs = explode('/', str_replace("\\", '/', dirname(__FILE__)));
$admindir = $admindirs[count($admindirs) - 1];
if ($dopost == 'login') {
    $cuserLogin = new userLogin($admindir);
    if (!empty($userid) && !empty($pwd)) {
        $isNeed = $cuserLogin->isNeedCheckCode($userid);
        if ($isNeed) {
            $validate = empty($validate) ? '' : strtolower(trim($validate));
            $svali = strtolower(GetCkVdValue());
            if ($validate == '' || $validate != $svali) {
                ResetVdValue();
                ShowMsg('验证码不正确', 'login.php');
                exit;
            }
        }
        $res = $cuserLogin->checkUser($userid, $pwd);
        if ($res == 1) {
            $cuserLogin->keepUser();
            if (!empty($gotopage)) {
                ShowMsg('正在登录后台管理，请稍等', $gotopage);
                exit();
            } else {
                ShowMsg('正在登录后台管理，请稍等', 'index.php');
                exit();
            }
        } else if ($res == -1) {
            ResetVdValue();
            ShowMsg('管理员账号错误', 'login.php');
            exit;
        } else {
            ResetVdValue();
            ShowMsg('管理员密码错误', 'login.php');
            exit;
        }
    } else {
        ResetVdValue();
        ShowMsg('管理员账号和密码没填完整', 'login.php');
        exit;
    }
}
include('templets/login.htm');
?>