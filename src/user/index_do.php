<?php
/**
 * 会员登录
 * 
 * @version        $id:login.php 8:38 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (empty($dopost)) $dopost = '';
if (empty($fmdo)) $fmdo = '';
if ($fmdo == 'sendMail') {
    if (!CheckEmail($cfg_ml->fields['email'])) {
        ShowMsg('邮箱格式有错误', 'index.php');
        exit();
    }
    if ($cfg_ml->fields['spacesta'] != -10) {
        ShowMsg('帐号不在邮件验证状态，本操作无效', 'index.php');
        exit();
    }
    $userhash = md5($cfg_cookie_encode.'--'.$cfg_ml->fields['mid'].'--'.$cfg_ml->fields['email']);
    $url = $cfg_basehost.$cfg_memberurl."/index_do.php?fmdo=checkMail&mid={$cfg_ml->fields['mid']}&userhash={$userhash}&do=1";
    $url = preg_replace("#http:\/\/#i", '', $url);
    $proto = IsSSL()? "https://" : "http://";
    $url = $proto.preg_replace("#\/\/#i", '/', $url);
    $mailtitle = "来自{$cfg_webname}：邮件验证通知";
    $mailbody = '';
    $mailbody .= "尊敬的{$cfg_ml->fields['uname']}会员，欢迎成为{$cfg_webname}会员！\r\n通过注册还须进行最后一步操作，请点击链接或复制链接到地址栏访问：{$url}";
    $headers = "From: ".$cfg_adminemail."\r\nReply-To: ".$cfg_adminemail;
    if (!empty($cfg_bizcore_appid) && !empty($cfg_bizcore_key)) {
        $client = new DedeBizClient();
        $client->MailSend($cfg_ml->fields['email'],$mailtitle,$mailtitle,$mailbody);
        $client->Close();
    } else {
        if ($cfg_sendmail_bysmtp == 'Y' && !empty($cfg_smtp_server)) {
            $mailtype = 'HTML';
            require_once(DEDEINC.'/libraries/mail.class.php');
            $smtp = new smtp($cfg_smtp_server, $cfg_smtp_port, true, $cfg_smtp_usermail, $cfg_smtp_password);
            $smtp->debug = false;
            $smtp->sendmail($cfg_ml->fields['email'], $cfg_webname, $cfg_smtp_usermail, $mailtitle, $mailbody, $mailtype);
        } else {
            @mail($cfg_ml->fields['email'], $mailtitle, $mailbody, $headers);
        }
    }
    ShowMsg('成功发送邮件，请稍后登录邮箱进行接收', 'index.php');
    exit();
} else if ($fmdo == 'checkMail') {
    $mid = intval($mid);
    if (empty($mid)) {
        ShowMsg('效验串不合法', 'index.php');
        exit();
    }
    $row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='{$mid}' ");
    $needUserhash = md5($cfg_cookie_encode.'--'.$mid.'--'.$row['email']);
    if ($needUserhash != $userhash) {
        ShowMsg('效验串不合法', 'index.php');
        exit();
    }
    if ($row['spacesta'] != -10) {
        ShowMsg('操作无效，帐号不在邮件验证状态', 'index.php');
        exit();
    }
    $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET spacesta=0 WHERE mid='{$mid}' ");
    //清除会员缓存
    $cfg_ml->DelCache($mid);
    ShowMsg('正在验证中', 'login.php');
    exit();
} else if ($fmdo == 'user') {
    //检查账号是否存在
    if ($dopost == "checkuser") {
        AjaxHead();
        $msg = '';
        $uid = trim($uid);
        if ($cktype == 0) {
            $msgtitle = '账号';
        } else {
            $msgtitle = '账号';
        }
        if ($cktype != 0 || $cfg_mb_wnameone == 'N') {
            $msg = CheckUserID($uid, $msgtitle);
        } else {
            $msg = CheckUserID($uid, $msgtitle, false);
        }
        if ($msg == 'ok') {
            $msg = "{$msgtitle}可以使用";
        } else {
            $msg = "{$msg}";
        }
        echo $msg;
        exit();
    }
    //检查邮箱是否存在
    else  if ($dopost == "checkmail") {
        AjaxHead();
        if ($cfg_md_mailtest == 'N') {
            $msg = "可以使用";
        } else {
            if (!CheckEmail($email)) {
                $msg = "邮箱格式有误";
            } else {
                $row = $dsql->GetOne("SELECT mid FROM `#@__member` WHERE email LIKE '$email' LIMIT 1");
                if (!is_array($row)) {
                    $msg = "可以使用";
                } else {
                    $msg = "邮箱已经被另一个账号占用";
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
    //积分换金币
    else if ($dopost == "money2s") {
        CheckRank(0, 0);//禁止游客操作
        if ($cfg_money_scores == 0) {
            ShowMsg('系统禁用了积分与金币兑换功能', 'index.php');
            exit();
        }
        $money = empty($money) ? "" : abs(intval($money));
        if (empty($money)) {
            ShowMsg('您没指定要兑换多少金币', 'index.php');
            exit();
        }
        $needscores = $money * $cfg_money_scores;
        if ($cfg_ml->fields['scores'] < $needscores) {
            ShowMsg('您积分不足，不能换取这么多的金币', 'index.php');
            exit();
        }
        $litmitscores = $cfg_ml->fields['scores'] - $needscores;
        //保存记录
        $mtime = time();
        $inquery = "INSERT INTO `#@__member_operation` (`buyid`,`pname`,`product`,`money`,`mtime`,`pid`,`mid`,`sta` ,`oldinfo`) VALUES ('ScoresToMoney','积分换金币操作','stc' ,'0' ,'$mtime' ,'0' ,'{$cfg_ml->M_ID}','0' ,'用{$needscores}积分兑了换金币{$money}个'); ";
        $dsql->ExecuteNoneQuery($inquery);
        //修改积分与金币值
        $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET `scores`=$litmitscores, money= money + $money WHERE mid='".$cfg_ml->M_ID."' ");
        //清除会员缓存
        $cfg_ml->DelCache($cfg_ml->M_ID);
        ShowMsg('成功兑换指定量的金币', 'operation.php');
        exit();
    }
} else if ($fmdo == 'login') {
    //会员登录
    if ($dopost == "login") {
        if (!isset($vdcode)) {
            $vdcode = '';
        }
        if (CheckUserID($userid, '', false) != 'ok') {
            ResetVdValue();
            ShowMsg("您输入的账号{$userid}已禁止", "index.php");
            exit();
        }
        if ($pwd == '') {
            ResetVdValue();
            ShowMsg('密码不能为空', 'index.php');
            exit();
        }
        $isNeed = $cfg_ml->isNeedCheckCode($userid);
        if ($isNeed) {
            $svali = GetCkVdValue();
            if (strtolower($vdcode) != $svali || $svali == '') {
                ResetVdValue();
                ShowMsg('验证码不正确', 'index.php');
                exit();
            }
        }
        //检查帐号
        $rs = $cfg_ml->CheckUser($userid, $pwd);
        if ($rs == 0) {
            ResetVdValue();
            ShowMsg('账号输入错误', 'index.php');
            exit();
        } else if ($rs == -1) {
            ResetVdValue();
            ShowMsg('密码输入错误', 'index.php');
            exit();
        } else if ($rs == -2) {
            ResetVdValue();
            ShowMsg('管理员帐号不允许从前台登录', 'index.php');
            exit();
        } else {
            //清除会员缓存
            $cfg_ml->DelCache($cfg_ml->M_ID);
            if (empty($gourl) || preg_match("#action|_do#i", $gourl)) {
                ShowMsg('正在登录会员中心，请稍等', 'index.php');
            } else {
                $gourl = str_replace('^', '&', $gourl);
                ShowMsg('正在前往指定页面，请稍等', $gourl);
            }
            exit();
        }
    }
    //退出登录
    else if ($dopost == "exit") {
        $cfg_ml->ExitCookie();
        ShowMsg('已退出会员中心', 'index.php');
        exit();
    }
} else if ($fmdo == 'purl'){
    require_once(DEDEINC.'/libraries/oxwindow.class.php');
    CheckRank(0, 0);//禁止游客操作
    $row = $dsql->GetOne("SELECT count(*) as dd FROM `#@__member` WHERE `pmid`='{$cfg_ml->M_ID}' ");
    $msg = "<p>您已经邀请了{$row['dd']}人：</p>
    <div class='media mb-3'>
        <span class='btn btn-primary btn-sm mr-2'>链</span>
        <div class='media-body pb-3 border-bottom border-gray'>
            <div class='d-flex justify-content-between align-items-center w-100'>
                <h5>链接邀请</h5>
                <a href='javascript:Copylink()' class='btn btn-outline-primary btn-sm'>复制链接</a>
            </div>
            <span class='d-block'>复制链接分享给其他人，对方通过链接注册后双方均可获得{$cfg_userad_adds}积分<span id='text' style='font-size:0'>{$cfg_basehost}{$cfg_memberurl}/index_do.php?fmdo=user&dopost=regnew&pid={$cfg_ml->M_LoginID}</span>
        </div>
    </div>
    <div class='media mb-3'>
        <span class='btn btn-success btn-sm mr-2'>码</span>
        <div class='media-body pb-3 border-bottom border-gray'>
            <div class='d-flex justify-content-between align-items-center w-100'>
                <h5>二维码邀请</h5>
                <a href='javascript:ShowQrcode()' class='btn btn-outline-success btn-sm'>查看二维码</a>
            </div>
            <span class='d-block'>分享二维码到移动设备，通过二维码扫码注册，双方均可获得{$cfg_userad_adds}积分</span>
        </div>
    </div>
    <div class='text-center'><a href='index.php' class='btn btn-success btn-sm'>返回</a></div>
    <div id='qrcode'></div>
    <style>.modal-body img{margin:0 auto}#qrcode{display:none;margin:15px auto;width:200px;height:200px}</style>
    <script>
        var qrcode = new QRCode(document.getElementById(\"qrcode\"), {
            width : 200,
            height : 200,
            correctLevel : 3
        });
        qrcode.makeCode('{$cfg_basehost}{$cfg_memberurl}/index_do.php?fmdo=user&dopost=regnew&pid={$cfg_ml->M_LoginID}');
    </script>
    <script>
        function Copylink() {
            var val = document.getElementById('text');
            window.getSelection().selectAllChildren(val);
            document.execCommand(\"Copy\");
            ShowMsg(\"复制推广链接成功\");
        }
        function ShowQrcode(){
            ShowMsg(document.getElementById('qrcode').innerHTML);
        }
    </script>";
    $wintitle = "快来邀请好友赚积分啦";
    $wecome_info = "邀请好友赚积分";
    $win = new OxWindow();
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", false);
    $win->Display(DEDEMEMBER."/templets/win_templet.htm");
    exit;
} else {
    ShowMsg('操作失败', 'index.php');
}
?>