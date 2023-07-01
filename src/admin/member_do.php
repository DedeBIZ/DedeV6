<?php
/**
 * 会员管理操作
 *
 * @version        $id:member_do.php 13:47 2010年7月19日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/libraries/oxwindow.class.php");
if (empty($dopost)) $dopost = '';
if (empty($fmdo)) $fmdo = '';
$ENV_GOBACK_URL = isset($_COOKIE['ENV_GOBACK_URL']) ? 'member_main.php' : '';
//删除会员
if ($dopost == "delmember") {
    CheckPurview('member_Del');
    if ($fmdo == 'yes') {
        $id = preg_replace("#[^0-9]#", '', $id);
        $safecodeok = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
        if ($safecodeok != $safecode) {
            ShowMsg("请填写正确的验证安全码", "member_do.php?id={$id}&dopost=delmember");
            exit();
        }
        if (!empty($id)) {
            //删除会员信息
            $row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='$id' LIMIT 1 ");
            $rs = 0;
            if ($row['matt'] == 10) {
                $nrow = $dsql->GetOne("SELECT * FROM `#@__admin` WHERE id='$id' LIMIT 1 ");
                //已经删除关连的管理员帐号
                if (!is_array($nrow)) $rs = $dsql->ExecuteNoneQuery2("DELETE FROM `#@__member` WHERE mid='$id' LIMIT 1");
            } else {
                $rs = $dsql->ExecuteNoneQuery2("DELETE FROM `#@__member` WHERE mid='$id' LIMIT 1");
            }
            if ($rs > 0) {
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_tj` WHERE mid='$id' LIMIT 1");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_space` WHERE mid='$id' LIMIT 1");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_company` WHERE mid='$id' LIMIT 1");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_person` WHERE mid='$id' LIMIT 1");
                //删除会员相关数据
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_stow` WHERE mid='$id' ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_flink` WHERE mid='$id' ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_operation` WHERE mid='$id' ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_pms` WHERE toid='$id' Or fromid='$id' ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_friends` WHERE mid='$id' Or fid='$id' ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__feedback` WHERE mid='$id' ");
                $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET mid='0' WHERE mid='$id'");
            } else {
                ShowMsg("无法删除此会员，如果这个会员是管理员，必须先删除这个管理员才能删除此帐号", $ENV_GOBACK_URL, 0, 5000);
                exit();
            }
        }
        ShowMsg("成功删除一个账户", $ENV_GOBACK_URL);
        exit();
    }
    $randcode = mt_rand(10000, 99999);
    $safecode = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
    $wintitle = "删除指定会员";
    $wecome_info = "<a href='".$ENV_GOBACK_URL."'>会员管理</a> - 删除会员";
    $win = new OxWindow();
    $win->Init("member_do.php", "js/blank.js", "POST");
    $win->AddHidden("fmdo", "yes");
    $win->AddHidden("dopost", $dopost);
    $win->AddHidden("id", $id);
    $win->AddHidden("randcode", $randcode);
    $win->AddHidden("safecode", $safecode);
    $win->AddTitle("您确定要删除id<span class='text-primary'>".$id."</span>会员吗");
    $win->AddMsgItem("<tr><td>验证安全码：<input name='safecode' type='text' id='safecode' class='admin-input-lg'>（安全码：<span class='text-primary'>$safecode</span>）</td></tr>");
    $winform = $win->GetWindow("ok");
    $win->Display();
} else if ($dopost == "delmembers") {
    CheckPurview('member_Del');
    if ($fmdo == 'yes') {
        $safecodeok = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
        if ($safecodeok != $safecode) {
            ShowMsg("请填写正确的验证安全码", "member_do.php?id={$id}&dopost=delmembers");
            exit();
        }
        if (!empty($id)) {
            //删除会员信息
            $rs = $dsql->ExecuteNoneQuery2("DELETE FROM `#@__member` WHERE mid IN (".str_replace("`", ",", $id).") And matt<>10 ");
            if ($rs > 0) {
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_tj` WHERE mid IN (".str_replace("`", ",", $id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_space` WHERE mid IN (".str_replace("`", ",", $id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_company` WHERE mid IN (".str_replace("`", ",", $id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_person` WHERE mid IN (".str_replace("`", ",", $id).") ");
                //删除会员相关数据
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_stow` WHERE mid IN (".str_replace("`", ",", $id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_flink` WHERE mid IN (".str_replace("`", ",", $id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_operation` WHERE mid IN (".str_replace("`", ",", $id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_pms` WHERE toid IN (".str_replace("`", ",", $id).") Or fromid IN (".str_replace("`", ",", $id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_friends` WHERE mid IN (".str_replace("`", ",", $id).") Or fid IN (".str_replace("`", ",", $id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__feedback` WHERE mid IN (".str_replace("`", ",", $id).") ");
                $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET mid='0' WHERE mid IN (".str_replace("`", ",", $id).")");
            } else {
                ShowMsg("无法删除此会员，如果这个会员是管理员关连的id，必须先删除这个管理员才能删除此帐号", $ENV_GOBACK_URL, 0, 3000);
                exit();
            }
        }
        ShowMsg("成功删除这些会员", $ENV_GOBACK_URL);
        exit();
    }
    $randcode = mt_rand(10000, 99999);
    $safecode = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
    $wintitle = "删除指定会员";
    $wecome_info = "<a href='".$ENV_GOBACK_URL."'>会员管理</a> - 删除会员";
    $win = new OxWindow();
    $win->Init("member_do.php", "js/blank.js", "POST");
    $win->AddHidden("fmdo", "yes");
    $win->AddHidden("dopost", $dopost);
    $win->AddHidden("id", $id);
    $win->AddHidden("randcode", $randcode);
    $win->AddHidden("safecode", $safecode);
    $win->AddTitle("您确定要删除id<span class='text-primary'>".$id."</span>会员吗");
    $win->AddMsgItem("<tr><td>验证安全码：<input name='safecode' type='text' id='safecode' size='16' class='admin-input-lg' /> (安全码：<span class='text-primary'>$safecode</span>)</td></tr>");
    $winform = $win->GetWindow("ok");
    $win->Display();
}
//推荐会员
else if ($dopost == "recommend") {
    CheckPurview('member_Edit');
    $id = preg_replace("#[^0-9]#", "", $id);
    if ($matt == 0) {
        $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET matt=1 WHERE mid='$id' AND matt<>10 LIMIT 1");
        ShowMsg("成功设置一个账户推荐", $ENV_GOBACK_URL);
        exit();
    } else {
        $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET matt=0 WHERE mid='$id' AND matt<>10 LIMIT 1");
        ShowMsg("成功取消一个账户推荐", $ENV_GOBACK_URL);
        exit();
    }
}
//修改会员
else if ($dopost == 'edituser') {
    CheckPurview('member_Edit');
    $send_max = isset($send_max)? intval($send_max) : 0;
    $user_money = isset($user_money)? floatval($user_money) : 0.0;
    $money = isset($money)? intval($money) : 0;
    $scores = isset($scores)? intval($scores) : 0;
    $rank = isset($rank)? intval($rank) : 0;
    $id = isset($id)? intval($id) : 0;
    $email = isset($email)? HtmlReplace($email,1) : '';
    if (!CheckEmail($email)) {
        ShowMsg("邮箱格式错误", "-1");
        exit();
    }
    $uname = isset($uname)? HtmlReplace($uname,1) : '';
    $spacesta = isset($spacesta)? HtmlReplace($spacesta,1) : '';
    $exptime = isset($exptime)? HtmlReplace($exptime,1) : '';
    if (!isset($_POST['id'])) exit ('dedebiz');
    $pwdsql = empty($pwd) ? '' : ",pwd='".md5($pwd)."'";
    if (function_exists('password_hash')) {
        $pwdsql = empty($pwd) ? '' : ",pwd_new='".password_hash($pwd, PASSWORD_BCRYPT)."'";
    }
    if (empty($sex)) $sex = '男';
    $uptime = GetMkTime($uptime);
    if ($matt == 10 && $oldmatt != 10) {
        ShowMsg("不支持直接把前台会员转为管理的操作", "-1");
        exit();
    }
    $query = "UPDATE `#@__member` SET send_max='$send_max',email='$email',uname='$uname',sex='$sex',matt='$matt',user_money='$user_money',money='$money',scores='$scores',`rank`='$rank',spacesta='$spacesta',uptime='$uptime',exptime='$exptime'$pwdsql WHERE mid='$id' AND matt<>10 ";
    $rs = $dsql->ExecuteNoneQuery2($query);
    if ($rs == 0) {
        $query = "UPDATE `#@__member` SET send_max='$send_max',email='$email',uname='$uname',sex='$sex',user_money='$user_money',money='$money',scores='$scores',`rank`='$rank',spacesta='$spacesta',uptime='$uptime',exptime='$exptime'$pwdsql WHERE mid='$id' ";
        $rs = $dsql->ExecuteNoneQuery2($query);
        if ($rank == 10 || $rank == 100) {
            $dsql->ExecuteNoneQuery2("UPDATE `#@__admin` SET `uname`='$uname' WHERE id='$id'");
        }
    }
    ShowMsg('成功修改会员资料', 'member_edit.php?id='.$id);
    exit();
}
//登录会员
else if ($dopost == "memberlogin") {
    CheckPurview('member_Edit');
    PutCookie('DedeUserID', $id, 1800);
    PutCookie('DedeLoginTime', time(), 1800);
    if (empty($jumpurl)) header("location:../user/index.php");
    else header("location:$jumpurl");
} else if ($dopost == "deoperations") {
    $nid = preg_replace('#[^0-9,]#', '', preg_replace('#`#', ',', $nid));
    $nid = explode(',', $nid);
    if (is_array($nid)) {
        foreach ($nid as $var) {
            $query = "DELETE FROM `#@__member_operation` WHERE aid = '$var'";
            $dsql->ExecuteNoneQuery($query);
        }
        ShowMsg("删除成功", "member_operations.php");
        exit();
    }
} else if ($dopost == "upoperations") {
    $nid = preg_replace('#[^0-9,]#', '', preg_replace('#`#', ',', $nid));
    $nid = explode(',', $nid);
    if (is_array($nid)) {
        foreach ($nid as $var) {
            $moRow = $dsql->GetOne("SELECT * FROM `#@__member_operation` WHERE aid='$var'");
            if ($moRow['sta'] == 0) {
                $query = "UPDATE `#@__member_operation` SET sta = '1' WHERE aid = '$var'";
                $dsql->ExecuteNoneQuery($query);
            }
        }
        ShowMsg("设置成功", "member_operations.php");
        exit();
    }
} else if ($dopost == "okoperations") {
    $nid = preg_replace('#[^0-9,]#', '', preg_replace('#`#', ',', $nid));
    $nid = explode(',', $nid);
    if (is_array($nid)) {
        foreach ($nid as $var) {
            $moRow = $dsql->GetOne("SELECT * FROM `#@__member_operation` WHERE aid='$var'");
            if ($moRow['sta'] == 1) {
                if ($moRow['product'] === "card") {
                    //积分
                    $proRow = $dsql->GetOne("SELECT * FROM `#@__moneycard_type` WHERE tid={$moRow['pid']}");
                    $query = "UPDATE `#@__member` SET money = money+{$proRow['num']} WHERE mid = '{$moRow['mid']}'";
                    $dsql->ExecuteNoneQuery($query);
                } else if ($moRow['product'] === "member"){
                    $row = $dsql->GetOne("SELECT * FROM `#@__member_type` WHERE aid='{$moRow['pid']}'");
                    $rank = $row['rank'];
                    $exptime = $row['exptime'];
                    $rs = $dsql->GetOne("SELECT uptime,exptime FROM `#@__member` WHERE mid='".$moRow['mid']."'");
                    if ($rs['uptime']!=0 && $rs['exptime']!=0) {
                        $nowtime = time();
                        $mhasDay = $rs['exptime'] - ceil(($nowtime - $rs['uptime'])/3600/24) + 1;
                        $mhasDay=($mhasDay>0)? $mhasDay : 0;
                    }
                    $memrank = $dsql->GetOne("SELECT money,scores FROM `#@__arcrank` WHERE `rank`='$rank'");
                    //更新会员信息
                    $sqlm =  "UPDATE `#@__member` SET `rank`='$rank',`money`=`money`+'{$memrank['money']}',scores=scores+'{$memrank['scores']}',exptime='$exptime'+'$mhasDay',uptime='".time()."' WHERE mid='".$moRow['mid']."'";
                    $sqlmo = "UPDATE `#@__member_operation` SET sta='2',oldinfo='会员升级成功' WHERE buyid='{$moRow['pid']}' ";
                    if (!($dsql->ExecuteNoneQuery($sqlm) && $dsql->ExecuteNoneQuery($sqlmo))) {
                        ShowMsg("升级会员失败", "javascript:;");
                        exit;
                    }
                }
                $query = "UPDATE `#@__member_operation` SET sta = '2' WHERE aid = '$var'";
                $dsql->ExecuteNoneQuery($query);
            }
            ShowMsg("设置成功", "member_operations.php");
            exit();
        }
    }
}
?>