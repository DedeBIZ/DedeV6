<?php
/**
 * 修改资料
 * 
 * @version        $id:edit_baseinfo.php 8:38 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckRank(0, 0);//禁止游客操作
$menutype = 'config';
if (!isset($dopost)) $dopost = '';
$pwd2 = (empty($pwd2)) ? "" : $pwd2;
$row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='".$cfg_ml->M_ID."'");
$face = $row['face'];
$newface = isset($newface)? $newface : '';
if ($dopost == 'save') {
    //校验CSRF
    CheckCSRF();
    if ($userpwd != $userpwdok) {
        ShowMsg('您两次输入的新密码不一致', 'edit_baseinfo.php');
        exit();
    }
    $addupquery = '';
    $admaddupquery = '';
    $pp = "pwd";
    $pwd = '';
    if ($userpwd == '') {
        if (function_exists('password_hash')) {
            $pp = "pwd_new";
            $pwd = $row['pwd_new'];
            $addupquery = ',pwd=\'\'';
        } else {
            $pwd = $row['pwd'];
        }
    } else {
        if (function_exists('password_hash')) 
        {
            $pp = "pwd_new";
            $pwd = password_hash($userpwd, PASSWORD_BCRYPT);
            $pwd2 = password_hash($userpwd, PASSWORD_BCRYPT);
            $addupquery = ',pwd=\'\'';
            $admaddupquery = ',pwd=\'\'';
        } else {
            $pwd = md5($userpwd);
            $pwd2 = substr(md5($userpwd), 5, 20);
        }
    }
    //修改头像
    $target_file = $cfg_basedir.$cfg_user_dir."/{$cfg_ml->M_ID}/newface.png";
    if (!empty($newface) && file_exists($target_file)) {
        $rnd = mt_rand(10000, 99999);
        rename($target_file, $cfg_basedir.$cfg_user_dir."/{$cfg_ml->M_ID}/face{$rnd}.png");
        $target_url = $cfg_mediasurl.'/userup'."/{$cfg_ml->M_ID}/face{$rnd}.png";
        $addupquery = ",face='{$target_url}'";
        @unlink($target_file);
    }
    //修改安全问题或邮箱
    if ($email != $row['email'] || ($newsafequestion != 0 && $newsafeanswer != '')) {
        if ($row['safequestion'] != 0 && ($row['safequestion'] != $safequestion || $row['safeanswer'] != $safeanswer)) {
            ShowMsg('您的旧安全问题及答案不正确，不能修改邮箱或安全问题', 'edit_baseinfo.php');
            exit();
        }
        //修改邮箱
        if (!empty($email)) {
            if ($email != $row['email']) {
                if (!CheckEmail($email)) {
                    ShowMsg('邮箱格式不正确', 'edit_baseinfo.php');
                    exit();
                } else {
                    $addupquery .= ",email='$email',spacesta='-10'";
                }
            }
        }
        //修改安全问题
        if ($newsafequestion != 0 && $newsafeanswer != '') {
            if (strlen($newsafeanswer) > 30) {
                ShowMsg('您的新安全问题的答案太长了，请保持在30字节以内', 'edit_baseinfo.php');
                exit();
            } else {
                $newsafequestion = HtmlReplace($newsafequestion, 1);
                $newsafeanswer = HtmlReplace($newsafeanswer, 1);
                $addupquery .= ",safequestion='$newsafequestion',safeanswer='$newsafeanswer'";
            }
        }
    }
    //修改uname
    if ($uname != $row['uname']) {
        $rs = CheckUserID($uname, '昵称或公司名称', FALSE);
        if ($rs != 'ok') {
            ShowMsg($rs, 'edit_baseinfo.php');
            exit();
        }
        $addupquery .= ",uname='$uname'";
    }
    //性别
    if (!in_array($sex, array('男', '女', '保密'))) {
        ShowMsg('请选择正常的性别', 'edit_baseinfo.php');
        exit();
    }
    $query1 = "UPDATE `#@__member` SET $pp='$pwd',sex='$sex'{$addupquery} WHERE mid='".$cfg_ml->M_ID."' ";
    $dsql->ExecuteNoneQuery($query1);
    //如果是管理员，修改其后台密码
    if ($cfg_ml->fields['matt'] == 10 && $pwd2 != "") {
        $query2 = "UPDATE `#@__admin` SET $pp='$pwd2'{$admaddupquery} WHERE id='".$cfg_ml->M_ID."' ";
        $dsql->ExecuteNoneQuery($query2);
    }
    //清除会员缓存
    $cfg_ml->DelCache($cfg_ml->M_ID);
    ShowMsg('成功更新您的基本资料', 'edit_baseinfo.php');
    exit();
}
include(DEDEMEMBER."/templets/edit_baseinfo.htm");
?>