<?php
/**
* @version        $id:index_do.php 8:24 2010年7月9日 tianya $
* @package        DedeBIZ.User
* @copyright      Copyright (c) 2022 DedeBIZ.COM
* @license        https://www.dedebiz.com/license
* @link           https://www.dedebiz.com
*/
require_once(dirname(__FILE__)."/config.php");
if (empty($dopost)) $dopost = '';
if (empty($fmdo)) $fmdo = '';
/*********************
function check_email()
 *******************/
if ($fmdo == 'sendMail') {
    if (!CheckEmail($cfg_ml->fields['email'])) {
        ShowMsg('您的邮箱格式有错误', '-1');
        exit();
    }
    if ($cfg_ml->fields['spacesta'] != -10) {
        ShowMsg('您的帐号不在邮件验证状态，本操作无效', '-1');
        exit();
    }
    $userhash = md5($cfg_cookie_encode.'--'.$cfg_ml->fields['mid'].'--'.$cfg_ml->fields['email']);
    $url = $cfg_basehost.(empty($cfg_cmspath) ? '/' : $cfg_cmspath)."/user/index_do.php?fmdo=checkMail&mid={$cfg_ml->fields['mid']}&userhash={$userhash}&do=1";
    $url = preg_replace("#http:\/\/#i", '', $url);
    $proto = IsSSL()? "https://" : "http://";
    $url = $proto.preg_replace("#\/\/#i", '/', $url);
    $mailtitle = "{$cfg_webname}，会员邮件验证通知";
    $mailbody = '';
    $mailbody .= "尊敬的用户<span class='text-primary'>{$cfg_ml->fields['uname']}</span>，您好：\r\n";
    $mailbody .= "欢迎注册成为<span class='text-primary'>{$cfg_webname}</span>会员\r\n";
    $mailbody .= "要通过注册，还必须进行最后一步操作，请点击或复制下面链接到地址栏访问这地址：\r\n";
    $mailbody .= "{$url}\r\n";
    $mailbody .= "Powered by DedeBIZ开发团队\r\n";
    $headers = "From: ".$cfg_adminemail."\r\nReply-To: ".$cfg_adminemail;
    if (!empty($cfg_bizcore_appid) && !empty($cfg_bizcore_key)) {
        $client = new DedeBizClient($cfg_bizcore_hostname, $cfg_bizcore_port);
        $client->appid = $cfg_bizcore_appid;
        $client->key = $cfg_bizcore_key;
        $client->MailSend($cfg_ml->fields['email'],$mailtitle,$mailtitle,$mailbody);
        $client->Close();
    } else {
        if ($cfg_sendmail_bysmtp == 'Y' && !empty($cfg_smtp_server)) {
            $mailtype = 'TXT';
            require_once(DEDEINC.'/libraries/mail.class.php');
            $smtp = new smtp($cfg_smtp_server, $cfg_smtp_port, true, $cfg_smtp_usermail, $cfg_smtp_password);
            $smtp->debug = false;
            $smtp->sendmail($cfg_ml->fields['email'], $cfg_webname, $cfg_smtp_usermail, $mailtitle, $mailbody, $mailtype);
        } else {
            @mail($cfg_ml->fields['email'], $mailtitle, $mailbody, $headers);
        }
    }
    ShowMsg('成功发送邮件，请稍后登录您的邮箱进行接收', '/user');
    exit();
} else if ($fmdo == 'checkMail') {
    $mid = intval($mid);
    if (empty($mid)) {
        ShowMsg('您的效验串不合法', '-1');
        exit();
    }
    $row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='{$mid}' ");
    $needUserhash = md5($cfg_cookie_encode.'--'.$mid.'--'.$row['email']);
    if ($needUserhash != $userhash) {
        ShowMsg('您的效验串不合法', '-1');
        exit();
    }
    if ($row['spacesta'] != -10) {
        ShowMsg('您的帐号不在邮件验证状态，本操作无效', '-1');
        exit();
    }
    $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET spacesta=0 WHERE mid='{$mid}' ");
    //清除会员缓存
    $cfg_ml->DelCache($mid);
    ShowMsg('操作成功，请重新登录系统', 'login.php');
    exit();
}
/*********************
function Case_user()
*******************/
else if ($fmdo == 'user') {
    //检查用户名是否存在
    if ($dopost == "checkuser") {
        AjaxHead();
        $msg = '';
        $uid = trim($uid);
        if ($cktype == 0) {
            $msgtitle = '用户名称';
        } else {
            $msgtitle = '用户名';
        }
        if ($cktype != 0 || $cfg_mb_wnameone == 'N') {
            $msg = CheckUserID($uid, $msgtitle);
        } else {
            $msg = CheckUserID($uid, $msgtitle, false);
        }
        if ($msg == 'ok') {
            $msg = "<span class='text-dark'>√{$msgtitle}可以使用</span>";
        } else {
            $msg = "<span class='text-danger'>×{$msg}</span>";
        }
        echo $msg;
        exit();
    }
    //检查email是否存在
    else  if ($dopost == "checkmail") {
        AjaxHead();
        if ($cfg_md_mailtest == 'N') {
            $msg = "<span class='text-dark'>√可以使用</span>";
        } else {
            if (!CheckEmail($email)) {
                $msg = "<span class='text-dark'>×Email格式有误</span>";
            } else {
                $row = $dsql->GetOne("SELECT mid FROM `#@__member` WHERE email LIKE '$email' LIMIT 1");
                if (!is_array($row)) {
                    $msg = "<span class='text-dark'>√可以使用</span>";
                } else {
                    $msg = "<span class='text-primary'>×Email已经被另一个帐号占用</span>";
                }
            }
        }
        echo $msg;
        exit();
    }
    //引入注册页面
    else if ($dopost == "regnew") {
        $step = empty($step) ? 1 : intval(preg_replace("/[^\d]/", '', $step));
        require_once(dirname(__FILE__)."/reg_new.php");
        exit();
    }
    /***************************
    //积分换金币
    function money2s() {  }
    ***************************/
    else if ($dopost == "money2s") {
        CheckRank(0, 0);
        if ($cfg_money_scores == 0) {
            ShowMsg('系统禁用了积分与金币兑换功能', '-1');
            exit();
        }
        $money = empty($money) ? "" : abs(intval($money));
        if (empty($money)) {
            ShowMsg('您没指定要兑换多少金币', '-1');
            exit();
        }
        $needscores = $money * $cfg_money_scores;
        if ($cfg_ml->fields['scores'] < $needscores) {
            ShowMsg('您积分不足，不能换取这么多的金币', '-1');
            exit();
        }
        $litmitscores = $cfg_ml->fields['scores'] - $needscores;
        //保存记录
        $mtime = time();
        $inquery = "INSERT INTO `#@__member_operation` (`buyid`,`pname`,`product`,`money`,`mtime`,`pid`,`mid`,`sta` ,`oldinfo`) VALUES ('ScoresToMoney','积分换金币操作','stc' ,'0' ,'$mtime' ,'0' ,'{$cfg_ml->M_ID}','0' ,'用 {$needscores} 积分兑了换金币：{$money} 个'); ";
        $dsql->ExecuteNoneQuery($inquery);
        //修改积分与金币值
        $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET `scores`=$litmitscores, money= money + $money WHERE mid='".$cfg_ml->M_ID."' ");
        //清除会员缓存
        $cfg_ml->DelCache($cfg_ml->M_ID);
        ShowMsg('成功兑换指定量的金币', 'operation.php');
        exit();
    }
}
/*********************
function login()
*******************/
else if ($fmdo == 'login') {
    //用户登录
    if ($dopost == "login") {
        if (!isset($vdcode)) {
            $vdcode = '';
        }
        if (CheckUserID($userid, '', false) != 'ok') {
            ResetVdValue();
            ShowMsg("您输入的用户名<span class='text-primary'>{$userid}</span>不合法", "index.php");
            exit();
        }
        if ($pwd == '') {
            ResetVdValue();
            ShowMsg("密码不能为空", "-1", 0, 2000);
            exit();
        }
        $isNeed = $cfg_ml->isNeedCheckCode($userid);
        if ($isNeed) {
            $svali = GetCkVdValue();
            if (strtolower($vdcode) != $svali || $svali == '') {
                ResetVdValue();
                ShowMsg('验证码错误', 'index.php');
                exit();
            }
        }
        //检查帐号
        $rs = $cfg_ml->CheckUser($userid, $pwd);
        if ($rs == 0) {
            ResetVdValue();
            ShowMsg("用户名不存在", "index.php", 0, 2000);
            exit();
        } else if ($rs == -1) {
            ResetVdValue();
            ShowMsg("密码错误", "index.php", 0, 2000);
            exit();
        } else if ($rs == -2) {
            ResetVdValue();
            ShowMsg("管理员帐号不允许从前台登录", "index.php", 0, 2000);
            exit();
        } else {
            //清除会员缓存
            $cfg_ml->DelCache($cfg_ml->M_ID);
            if (empty($gourl) || preg_match("#action|_do#i", $gourl)) {
                ShowMsg("成功登录，正在转向用户主页", "index.php", 0, 2000);
            } else {
                $gourl = str_replace('^', '&', $gourl);
                ShowMsg("成功登录，现在转向指定页面", $gourl, 0, 2000);
            }
            exit();
        }
    }
    //退出登录
    else if ($dopost == "exit") {
        $cfg_ml->ExitCookie();
        ShowMsg("成功退出登录", "index.php", 0, 2000);
        exit();
    }
} else {
    ShowMsg("本页面禁止返回", "index.php");
}
?>