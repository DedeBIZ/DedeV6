<?php
/**
 * 升级为管理员
 *
 * @version        $Id: member_toadmin.php 1 14:09 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('member_Edit');
if (empty($dopost)) $dopost = '';
if (empty($fmdo)) $fmdo = '';
$ENV_GOBACK_URL = isset($_COOKIE['ENV_GOBACK_URL']) ? 'member_main.php' : '';
$row = array();
//升级为管理员
if ($dopost == "toadmin") {
    $pwd = trim($pwd);
    if ($pwd != '' && preg_match("#[^0-9a-zA-Z_@!\.-]#", $pwd)) {
        ShowMsg(Lang('member_toadmin_err_password'), '-1', 0, 3000);
        exit();
    }
    $safecodeok = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
    if ($safecodeok != $safecode) {
        ShowMsg(Lang("member_toadmin_err_safecode"), "member_toadmin.php?id={$id}");
        exit();
    }
    $pwdm = '';
    if ($pwd != '') {
        $inputpwd = ",pwd";
        if (function_exists('password_hash')) {
            $inputpwd = ",pwd_new";
            $inputpwdv = ",'".password_hash($pwd, PASSWORD_BCRYPT)."'";
            $pwdm = ",pwd_new='".password_hash($pwd, PASSWORD_BCRYPT)."'";
        } else {
            $inputpwdv = ",'".substr(md5($pwd), 5, 20)."'";
            $pwdm = ",pwd='".md5($pwd)."'";
        }
    } else {
        $row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='$id'");
        if (function_exists('password_hash')) {
            $password = $row['pwd_new'];
            $inputpwd = ",pwd_new";
            $inputpwdv = ",'".$password."'";
            $pwdm = ",pwd_new='".$password."'";
        } else {
            $password = $row['pwd'];
            $inputpwd = ",pwd";
            $pwd = substr($password, 5, 20);
            $inputpwdv = ",'".$pwd."'";
            $pwdm = ",pwd='".$password."'";
        }
    }
    $typeids = (empty($typeids)) ? array() : $typeids;
    if ($typeids == '') {
        ShowMsg(Lang("member_toadmin_err_emptytypeids"), "member_toadmin.php?id={$id}");
        exit();
    }
    $typeid = join(',', $typeids);
    if ($typeid == '0') $typeid = '';
    if ($id != 1) {
        $query = "INSERT INTO `#@__admin`(id,usertype,userid$inputpwd,uname,typeid,tname,email) VALUES ('$id','$usertype','$userid'$inputpwdv,'$uname','$typeid','$tname','$email')";
    } else {
        $query = "INSERT INTO `#@__admin`(id,userid$inputpwd,uname,typeid,tname,email) VALUES ('$id','$userid'$inputpwdv,'$uname','$typeid','$tname','$email')";
    }
    $dsql->ExecuteNoneQuery($query);
    $query = "UPDATE `#@__member` SET `rank`='100',uname='$uname',matt='10',email='$email'$pwdm WHERE mid='$id'";
    $dsql->ExecuteNoneQuery($query);
    $row = $dsql->GetOne("SELECT * FROM `#@__admintype` WHERE `rank`='$usertype'");
    $floginid = $cUserLogin->getUserName();
    $fromid = $cUserLogin->getUserID();
    $subject = Lang("member_toadmin_subject");
    $message = Lang('member_toadmin_message',array('userid'=>$userid,'typename'=>$row['typename']));
    $sendtime = $writetime = time();
    $inquery = "INSERT INTO `#@__member_pms` (`floginid`,`fromid`,`toid`,`tologinid`,`folder`,`subject`,`sendtime`,`writetime`,`hasview`,`isadmin`,`message`)
      VALUES ('$floginid','$fromid','$id','$userid','inbox','$subject','$sendtime','$writetime','0','0','$message'); ";
    $dsql->ExecuteNoneQuery($inquery);
    ShowMsg(Lang("member_toadmin_success"), "member_main.php");
    exit();
}
$id = preg_replace("#[^0-9]#", "", $id);
//显示用户信息
$randcode = mt_rand(10000, 99999);
$safecode = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
$typeOptions = '';
$typeid = (empty($typeid)) ? '' : $typeid;
$typeids = explode(',', $typeid);
$dsql->SetQuery("SELECT id,typename FROM `#@__arctype` WHERE reid=0 AND (ispart=0 OR ispart=1)");
$dsql->Execute('op');
while ($nrow = $dsql->GetObject('op')) {
    $typeOptions .= "<option value='{$nrow->id}' class='btype'".(in_array($nrow->id, $typeids) ? ' selected' : '').">—{$nrow->typename}</option>\r\n";
    $dsql->SetQuery("SELECT id,typename FROM `#@__arctype` WHERE reid={$nrow->id} AND (ispart=0 OR ispart=1)");
    $dsql->Execute('s');
    while ($nrow = $dsql->GetObject('s')) {
        $typeOptions .= "<option value='{$nrow->id}' class='stype'".(in_array($nrow->id, $typeids) ? ' selected' : '').">—{$nrow->typename}</option>\r\n";
    }
}
$row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='$id'");
include DedeInclude('templets/member_toadmin.htm');
?>