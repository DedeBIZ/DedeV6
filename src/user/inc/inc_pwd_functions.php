<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 密码函数
 * 
 * @version        $id:inc_pwd_functions.php 15:18 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
/**
 *  验证码生成函数
 *
 * @param     int  $length  需要生成的长度
 * @param     int  $numeric  是否为数字
 * @return    string
 */
function random($length, $numeric = 0)
{
    PHP_VERSION < '4.2.0' && mt_srand((float)microtime() * 1000000);
    if ($numeric) {
        $hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
    } else {
        $hash = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
    }
    return $hash;
}
/**
 *  邮件发送函数
 *
 * @param     string  $email  E-mail地址
 * @param     string  $mailtitle  E-mail标题
 * @param     string  $mailbody  E-mail文档
 * @param     string  $headers 头信息
 * @return    void
 */
function sendmail($email, $mailtitle, $mailbody, $headers)
{
    global $cfg_sendmail_bysmtp, $cfg_smtp_server, $cfg_smtp_port, $cfg_smtp_usermail, $cfg_smtp_user, $cfg_smtp_password, $cfg_adminemail, $cfg_bizcore_appid, $cfg_bizcore_key;
    if (!empty($cfg_bizcore_appid) && !empty($cfg_bizcore_key)) {
        $client = new DedeBizClient();
        $client->MailSend($email,$mailtitle,$mailtitle,$mailbody);
        $client->Close();
    } else {
        if ($cfg_sendmail_bysmtp == 'Y') {
            $mailtype = 'TXT';
            require_once(DEDEINC.'/libraries/mail.class.php');
            $smtp = new smtp($cfg_smtp_server, $cfg_smtp_port, true, $cfg_smtp_usermail, $cfg_smtp_password);
            $smtp->debug = false;
            $smtp->sendmail($email, $cfg_webname, $cfg_smtp_usermail, $mailtitle, $mailbody, $mailtype);
        } else {
            @mail($email, $mailtitle, $mailbody, $headers);
        }
    }
}
/**
 *  发送邮件，type为INSERT新建验证码，UPDATE修改验证码
 *
 * @param     int  $mid  会员id
 * @param     int  $userid  会员id
 * @param     string  $mailto  发送到
 * @param     string  $type  类型
 * @param     string  $send  发送到
 * @return    string
 */
function newmail($mid, $userid, $mailto, $type, $send)
{
    global $db, $cfg_adminemail, $cfg_webname, $cfg_basehost, $cfg_memberurl;
    $mailtime = time();
    $randval = random(8);
    $mailtitle = $cfg_webname.":密码修改";
    $mailto = $mailto;
    $headers = "From:".$cfg_adminemail."\r\nReply-To:$cfg_adminemail";
    $mailbody = "尊敬的".$userid."会员，临时登录密码：".$randval."\r\n请在三天内修改登录密码：".$cfg_basehost.$cfg_memberurl."/resetpassword.php?dopost=getpasswd&id=".$mid;
    if ($type == 'INSERT') {
        $key = md5($randval);
        $sql = "INSERT INTO `#@__pwd_tmp` (`mid` ,`membername` ,`pwd` ,`mailtime`) VALUES ('$mid', '$userid',  '$key', '$mailtime');";
        if ($db->ExecuteNoneQuery($sql)) {
            if ($send == 'Y') {
                sendmail($mailto, $mailtitle, $mailbody, $headers);
                return ShowMsg('验证码已经发送到原来的邮箱，请注意查收', 'login.php', '', '5000');
            } else if ($send == 'N') {
                return ShowMsg('稍后前往密码修改页', $cfg_basehost.$cfg_memberurl."/resetpassword.php?dopost=getpasswd&id=".$mid."&key=".$randval);
            }
        } else {
            return ShowMsg('修改失败，请联系管理员', 'login.php');
        }
    } elseif ($type == 'UPDATE') {
        $key = md5($randval);
        $sql = "UPDATE `#@__pwd_tmp` SET `pwd` = '$key',mailtime = '$mailtime' WHERE `mid` ='$mid';";
        if ($db->ExecuteNoneQuery($sql)) {
            if ($send === 'Y') {
                sendmail($mailto, $mailtitle, $mailbody, $headers);
                ShowMsg('修改验证码已经发送到原来的邮箱请查收', 'login.php');
            } elseif ($send === 'N') {
                return ShowMsg('稍后前往密码修改页', $cfg_basehost.$cfg_memberurl."/resetpassword.php?dopost=getpasswd&id=".$mid."&key=".$randval);
            }
        } else {
            ShowMsg('修改失败，请与管理员联系', 'login.php');
        }
    }
}
/**
 *  查询会员信息，mail会员输入邮箱地址，userid账号
 *
 * @param     string  $mail  邮件
 * @param     string  $userid  会员id
 * @return    mixed
 */
function member($mail, $userid)
{
    global $db;
    $msql = empty($mail)? "1=1" : "email='$mail'";
    $sql = "SELECT mid,email,safequestion FROM `#@__member` WHERE $msql AND userid = '$userid'";
    $row = $db->GetOne($sql);
    if (!is_array($row)) {
        ShowMsg("会员id输入错误", "-1");
        exit;
    } else {
        return $row;
    }
}
/**
 *  查询是否发送过验证码
 *
 * @param     string  $mid  会员id
 * @param     string  $userid  账号
 * @param     string  $mailto  发送邮件地址
 * @param     string  $send  邮件默认为Y，Y发送，N不发送
 * @return    string
 */
function sn($mid, $userid, $mailto, $send = 'Y')
{
    global $db;
    $tptim = (60 * 10);
    $dtime = time();
    $sql = "SELECT * FROM `#@__pwd_tmp` WHERE mid = '$mid'";
    $row = $db->GetOne($sql);
    //发送新邮件
    if (!is_array($row)) {
        newmail($mid, $userid, $mailto, 'INSERT', $send);
    }
    //10分钟后可以再次发送新验证码
    elseif ($dtime - $tptim > $row['mailtime']) {
        newmail($mid, $userid, $mailto, 'UPDATE', $send);
    }
    //重新发送新的验证码确认邮件
    else {
        return ShowMsg('请10分钟后再重新申请', 'login.php');
    }
}
?>