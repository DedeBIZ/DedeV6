<?php
/**
 * @version        $Id: reg_new.php 1 8:38 2010年7月9日Z tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if ($cfg_mb_allowreg == 'N') {
    ShowMsg('系统关闭了新用户注册', 'index.php');
    exit();
}
if (!isset($dopost)) $dopost = '';
$step = empty($step) ? 1 : intval($step);
if ($step == 1) {
    if ($cfg_ml->IsLogin()) {
        ShowMsg('您已经登录系统，无需重新注册', 'index.php');
        exit();
    }
    if ($dopost == 'regbase') {
        $svali = GetCkVdValue();
        if (strtolower($vdcode) != $svali || $svali == '') {
            ResetVdValue();
            ShowMsg('验证码错误', '-1');
            exit();
        }
        $userid = $uname = trim($userid);
        $pwd = trim($userpwd);
        $pwdc = trim($userpwdok);
        $rs = CheckUserID($userid, '用户名');
        if ($rs != 'ok') {
            ShowMsg($rs, '-1');
            exit();
        }
        if (strlen($userid) > 20 || strlen($uname) > 36) {
            ShowMsg('您的用户名或用户笔名过长，不允许注册', '-1');
            exit();
        }
        if (strlen($userid) < $cfg_mb_idmin || strlen($pwd) < $cfg_mb_pwdmin) {
            ShowMsg("您的用户名或密码过短，不允许注册", "-1");
            exit();
        }
        if ($pwdc != $pwd) {
            ShowMsg('您两次输入的密码不一致', '-1');
            exit();
        }
        $uname = HtmlReplace($uname, 1);
        $userid = HtmlReplace($userid, 1);
        //检测用户名是否存在
        $row = $dsql->GetOne("SELECT mid FROM `#@__member` WHERE userid LIKE '$userid' ");
        if (is_array($row)) {
            ShowMsg("您指定的用户名 {$userid} 已存在，请使用别的用户名", "-1");
            exit();
        }
        //会员的默认金币
        $dfscores = 0;
        $dfmoney = 0;
        $dfrank = $dsql->GetOne("SELECT `money`,scores FROM `#@__arcrank` WHERE `rank`='10' ");
        if (is_array($dfrank)) {
            $dfmoney = $dfrank['money'];
            $dfscores = $dfrank['scores'];
        }
        $jointime = time();
        $logintime = time();
        $joinip = GetIP();
        $loginip = GetIP();
        $pp = "pwd";
        if (function_exists('password_hash')) {
            $pp = "pwd_new";
            $pwd = password_hash($userpwd, PASSWORD_BCRYPT);
        } else {
            $pwd = md5($userpwd);
        }
        $mtype = '个人';
        $spaceSta = ($cfg_mb_spacesta < 0 ? $cfg_mb_spacesta : 0);
        $inQuery = "INSERT INTO `#@__member` (`mtype` ,`userid` ,`$pp`,`uname` ,`sex` ,`rank` ,`money` ,`email` ,`scores` ,`matt`, `spacesta` ,`face`,`safequestion`,`safeanswer` ,`jointime` ,`joinip` ,`logintime` ,`loginip`) VALUES ('$mtype','$userid','$pwd','$uname','','10','$dfmoney','','$dfscores','0','$spaceSta','','','','$jointime','$joinip','$logintime','$loginip'); ";
        if ($dsql->ExecuteNoneQuery($inQuery)) {
            $mid = $dsql->GetLastID();
            //写入默认会员详细资料
            if ($mtype == '个人') {
                $space = 'person';
            } else if ($mtype == '企业') {
                $space = 'company';
            } else {
                $space = 'person';
            }
            //写入默认统计数据
            $membertjquery = "INSERT INTO `#@__member_tj` (`mid`,`article`,`album`,`archives`,`homecount`,`pagecount`,`feedback`,`friend`,`stow`) VALUES ('$mid','0','0','0','0','0','0','0','0'); ";
            $dsql->ExecuteNoneQuery($membertjquery);
            //写入默认空间配置数据
            $spacequery = "INSERT INTO `#@__member_space` (`mid`,`pagesize`,`matt`,`spacename`,`spacelogo`,`spacestyle`,`sign`,`spacenews`) VALUES ('{$mid}','10','0','{$uname}的空间','','$space','',''); ";
            $dsql->ExecuteNoneQuery($spacequery);
            //写入其它默认数据
            $dsql->ExecuteNoneQuery("INSERT INTO `#@__member_flink`(mid,title,url) VALUES ('$mid','DedeBIZ','https://www.dedebiz.com'); ");
            //模拟登录
            $cfg_ml = new MemberLogin(7 * 3600);
            $rs = $cfg_ml->CheckUser($userid, $userpwd);
            ShowMsg('您已经登录系统，无需重新注册', 'index.php');
            exit;
        } else {
            ShowMsg("注册失败，请检查资料是否有误或与管理员联系", "-1");
            exit();
        }
    }
    require_once(DEDEMEMBER."/templets/reg-new.htm");
} else {
    if (!$cfg_ml->IsLogin()) {
        ShowMsg("尚未完成基本信息的注册,请返回重新填写", "index_do.php?fmdo=user&dopost=regnew");
        exit;
    } else {
        ShowMsg('您已经登录系统，无需重新注册', 'index.php');
        exit;
    }
}
?>