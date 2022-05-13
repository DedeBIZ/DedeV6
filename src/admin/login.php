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
require_once(dirname(__FILE__).'/../system/common.inc.php');
require_once(DEDEINC.'/userlogin.class.php');
if (empty($dopost)) $dopost = '';
if (empty($gotopage)) $gotopage = '';
$gotopage = RemoveXSS($gotopage);
//检测安装目录安全性
if (is_dir(dirname(__FILE__).'/../install')) {
    if (!file_exists(dirname(__FILE__).'/../install/install_lock.txt')) {
        $fp = fopen(dirname(__FILE__).'/../install/install_lock.txt', 'w') or die('安装目录无写入权限，无法进行写入锁定文件，请安装完毕删除安装目录');
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
    $redmsg = '<div class="alert alert-warning"><div class=\"safe-tips\">您的管理目录的名称中包含默认名称admin，建议在FTP里把它修改为其它名称，那样会更安全</div></div>';
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
        ShowMsg('验证码不正确', 'login.php', 0, 1000);
        exit;
    } else {
        $cuserLogin = new userLogin($admindir);
        if (!empty($userid) && !empty($pwd)) {
            $res = $cuserLogin->checkUser($userid, $pwd);
            //success
            if ($res == 1) {
                $cuserLogin->keepUser();
                if (!empty($gotopage)) {
                    ShowMsg('成功登录，正在转向管理管理主页', $gotopage);
                    exit();
                } else {
                    ShowMsg('成功登录，正在转向管理管理主页', "index.php");
                    exit();
                }
            }
            //error
            else if ($res == -1) {
                ResetVdValue();
                ShowMsg('您的用户名不存在', 'login.php', 0, 1000);
                exit;
            } else {
                ResetVdValue();
                ShowMsg('您的密码错误', 'login.php', 0, 1000);
                exit;
            }
        }
        //password empty
        else {
            ResetVdValue();
            ShowMsg('用户和密码没填写完整', 'login.php', 0, 1000);
            exit;
        }
    }
}
include('templets/login.htm');