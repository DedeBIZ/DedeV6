<?php
/**
 * 会员管理操作
 *
 * @version        $Id: member_do.php 1 13:47 2010年7月19日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
if (empty($dopost)) $dopost = '';
if (empty($fmdo)) $fmdo = '';
$ENV_GOBACK_URL = isset($_COOKIE['ENV_GOBACK_URL']) ? 'member_main.php' : '';
//删除会员
if ($dopost == "delmember") {
    UserLogin::CheckPurview('member_Del');
    if ($fmdo == 'yes') {
        $id = preg_replace("#[^0-9]#", '', $id);
        $safecodeok = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
        if ($safecodeok != $safecode) {
            ShowMsg(Lang("sys_admin_err_safecodeok_check"), "member_do.php?id={$id}&dopost=delmember");
            exit();
        }
        if (!empty($id)) {
            //删除用户信息
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
                //删除用户相关数据
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_stow` WHERE mid='$id'");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_flink` WHERE mid='$id'");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_operation` WHERE mid='$id'");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_pms` WHERE toid='$id' Or fromid='$id'");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_friends` WHERE mid='$id' Or fid='$id'");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__feedback` WHERE mid='$id'");
                $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET mid='0' WHERE mid='$id'");
            } else {
                ShowMsg(Lang("member_err_del_admin"), $ENV_GOBACK_URL, 0, 5000);
                exit();
            }
        }
        ShowMsg(Lang("member_success_del"), $ENV_GOBACK_URL);
        exit();
    }
    $randcode = mt_rand(10000, 99999);
    $safecode = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
    $wintitle = Lang("member_del_title");
    $wecome_info = "<a href='".$ENV_GOBACK_URL."'>".Lang('member_main2')."</a>::".Lang('member_del');
    DedeWin::Instance()->Init("member_do.php", "js/blank.js", "POST")
    ->AddHidden("fmdo", "yes")
    ->AddHidden("dopost", $dopost)
    ->AddHidden("id", $id)
    ->AddHidden("randcode", $randcode)
    ->AddHidden("safecode", $safecode)
    ->AddTitle(Lang("member_del_title2",array('id'=>$id)))
    ->AddMsgItem(Lang('member_toadmin_safecode')."：<input name='safecode' type='text' id='safecode' style='width:260px'>（".Lang('safecode')."：<span class='text-danger'>$safecode</span>）", "30")
    ->GetWindow("ok")
    ->Display();
} else if ($dopost == "delmembers") {
    UserLogin::CheckPurview('member_Del');
    if ($fmdo == 'yes') {
        $safecodeok = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
        if ($safecodeok != $safecode) {
            ShowMsg(Lang("sys_admin_err_safecodeok_check"), "member_do.php?id={$id}&dopost=delmembers");
            exit();
        }
        if (!empty($id)) {
            //删除用户信息
            $rs = $dsql->ExecuteNoneQuery2("DELETE FROM `#@__member` WHERE mid IN (".str_replace("`", ",", $id).") And matt<>10 ");
            if ($rs > 0) {
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_tj` WHERE mid IN (".str_replace("`", ",", $id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_space` WHERE mid IN (".str_replace("`", ",", $id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_company` WHERE mid IN (".str_replace("`", ",", $id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_person` WHERE mid IN (".str_replace("`", ",", $id).") ");
                //删除用户相关数据
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_stow` WHERE mid IN (".str_replace("`", ",", $id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_flink` WHERE mid IN (".str_replace("`", ",", $id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_operation` WHERE mid IN (".str_replace("`", ",", $id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_pms` WHERE toid IN (".str_replace("`", ",", $id).") Or fromid IN (".str_replace("`", ",", $id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_friends` WHERE mid IN (".str_replace("`", ",", $id).") Or fid IN (".str_replace("`", ",", $id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__feedback` WHERE mid IN (".str_replace("`", ",", $id).") ");
                $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET mid='0' WHERE mid IN (".str_replace("`", ",", $id).")");
            } else {
                ShowMsg(Lang("member_err_del_admin"), $ENV_GOBACK_URL, 0, 3000);
                exit();
            }
        }
        ShowMsg(Lang("member_success_delall"), $ENV_GOBACK_URL);
        exit();
    }
    $randcode = mt_rand(10000, 99999);
    $safecode = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
    $wintitle = Lang("member_del_title");
    $wecome_info = "<a href='".$ENV_GOBACK_URL."'>".Lang('member_main2')."</a>::".Lang('member_del');
    DedeWin::Instance()->Init("member_do.php", "js/blank.js", "POST")
    ->AddHidden("fmdo", "yes")
    ->AddHidden("dopost", $dopost)
    ->AddHidden("id", $id)
    ->AddHidden("randcode", $randcode)
    ->AddHidden("safecode", $safecode)
    ->AddTitle(Lang("member_del_title2",array('id'=>$id)))
    ->AddMsgItem(Lang('member_toadmin_safecode')."：<input name='safecode' type='text' id='safecode' size='16' style='width:260px' /> (".Lang('safecode')."：<span class='text-danger'>$safecode</span>)", "30")
    ->GetWindow("ok")
    ->Display();
}
//推荐会员
else if ($dopost == "recommend") {
    UserLogin::CheckPurview('member_Edit');
    $id = preg_replace("#[^0-9]#", "", $id);
    if ($matt == 0) {
        $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET matt=1 WHERE mid='$id' AND matt<>10 LIMIT 1");
        ShowMsg(Lang("member_recommend_0"), $ENV_GOBACK_URL);
        exit();
    } else {
        $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET matt=0 WHERE mid='$id' AND matt<>10 LIMIT 1");
        ShowMsg(Lang("member_recommend_1"), $ENV_GOBACK_URL);
        exit();
    }
}
//修改会员
else if ($dopost == 'edituser') {
    UserLogin::CheckPurview('member_Edit');
    if (!isset($_POST['id'])) exit('dedebiz');
    $pwdsql = empty($pwd) ? '' : ",pwd='".md5($pwd)."'";
    if (function_exists('password_hash')) {
        $pwdsql = empty($pwd) ? '' : ",pwd_new='".password_hash($pwd, PASSWORD_BCRYPT)."'";
    }
    if (empty($sex)) $sex = '男';
    $uptime = GetMkTime($uptime);
    if ($matt == 10 && $oldmatt != 10) {
        ShowMsg(Lang("member_edituser_err_to"), "-1");
        exit();
    }
    $query = "UPDATE `#@__member` SET email='$email',uname='$uname',sex='$sex',matt='$matt',money='$money',scores='$scores',`rank`='$rank',spacesta='$spacesta',uptime='$uptime',exptime='$exptime' $pwdsql WHERE mid='$id' AND matt<>10";
    $rs = $dsql->ExecuteNoneQuery2($query);
    if ($rs == 0) {
        $query = "UPDATE `#@__member` SET email='$email',uname='$uname',sex='$sex',money='$money',scores='$scores',`rank`='$rank',spacesta='$spacesta',uptime='$uptime',exptime='$exptime' $pwdsql WHERE mid='$id'";
        $rs = $dsql->ExecuteNoneQuery2($query);
    }
    ShowMsg(Lang('member_success_edituser'), 'member_view.php?id='.$id);
    exit();
}
//登录会员的控制面板
else if ($dopost == "memberlogin") {
    UserLogin::CheckPurview('member_Edit');
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
        ShowMsg(Lang("member_success_edituser_del"), "member_operations.php");
        exit();
    }
} else if ($dopost == "upoperations") {
    $nid = preg_replace('#[^0-9,]#', '', preg_replace('#`#', ',', $nid));
    $nid = explode(',', $nid);
    if (is_array($nid)) {
        foreach ($nid as $var) {
            $query = "UPDATE `#@__member_operation` SET sta = '1' WHERE aid = '$var'";
            $dsql->ExecuteNoneQuery($query);
            ShowMsg(Lang("member_success_edituser_set"), "member_operations.php");
            exit();
        }
    }
} else if ($dopost == "okoperations") {
    $nid = preg_replace('#[^0-9,]#', '', preg_replace('#`#', ',', $nid));
    $nid = explode(',', $nid);
    if (is_array($nid)) {
        foreach ($nid as $var) {
            $query = "UPDATE `#@__member_operation` SET sta = '2' WHERE aid = '$var'";
            $dsql->ExecuteNoneQuery($query);
            ShowMsg(Lang("member_success_edituser_set"), "member_operations.php");
            exit();
        }
    }
}
?>