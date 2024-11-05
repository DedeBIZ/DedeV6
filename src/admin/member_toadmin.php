<?php
/**
 * 提升会员
 *
 * @version        $id:member_toadmin.php 14:09 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('member_Edit');
if (empty($dopost)) $dopost = '';
if (empty($fmdo)) $fmdo = '';
$ENV_GOBACK_URL = isset($_COOKIE['ENV_GOBACK_URL']) ? 'member_main.php' : '';
$row = array();
//升级为管理员
if ($dopost == "toadmin") {
    $pwd = trim($pwd);
    if ($pwd != '' && preg_match("#[^0-9a-zA-Z_@!\.-]#", $pwd)) {
        ShowMsg('密码不合法，请使用数字0-9小写a-z大写A-Z符号_@!.-', '-1');
        exit();
    }
    $safecodeok = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
    if ($safecodeok != $safecode) {
        ShowMsg("请填写正确的验证安全码", "member_toadmin.php?id={$id}");
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
        ShowMsg("请为该管理员指定管理栏目", "member_toadmin.php?id={$id}");
        exit();
    }
    $typeid = join(',', $typeids);
    if ($typeid == '0') $typeid = '';
    if ($id != 1) {
        $query = "INSERT INTO `#@__admin` (id,usertype,userid$inputpwd,uname,typeid,tname,email) VALUES ('$id','$usertype','$userid'$inputpwdv,'$uname','$typeid','$tname','$email')";
    } else {
        $query = "INSERT INTO `#@__admin` (id,userid$inputpwd,uname,typeid,tname,email) VALUES ('$id','$userid'$inputpwdv,'$uname','$typeid','$tname','$email')";
    }
    $dsql->ExecuteNoneQuery($query);
    $query = "UPDATE `#@__member` SET `rank`='100',uname='$uname',matt='10',email='$email' $pwdm WHERE mid='$id'";
    $dsql->ExecuteNoneQuery($query);
    $row = $dsql->GetOne("SELECT * FROM `#@__admintype` WHERE `rank`='$usertype'");
    $floginid = $cuserLogin->getUserName();
    $fromid = $cuserLogin->getUserID();
    $subject = "恭喜您已经成功提升管理员";
    $message = "尊敬的{$userid}会员，您已经成功提升为{$row['typename']}，具体操作权限请同网站超级管理员联系";
    $sendtime = $writetime = time();
    $inquery = "INSERT INTO `#@__member_pms` (`floginid`,`fromid`,`toid`,`tologinid`,`folder`,`subject`,`sendtime`,`writetime`,`hasview`,`isadmin`,`message`) VALUES ('$floginid','$fromid','$id','$userid','inbox','$subject','$sendtime','$writetime','0','0','$message'); ";
    $dsql->ExecuteNoneQuery($inquery);
    ShowMsg("成功升级一个帐户", "member_main.php");
    exit();
}
$id = preg_replace("#[^0-9]#", "", $id);
//显示会员信息
$randcode = mt_rand(10000, 99999);
$safecode = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
$typeOptions = '';
$typeid = (empty($typeid)) ? '' : $typeid;
$typeids = explode(',', $typeid);
$dsql->SetQuery("SELECT id,typename FROM `#@__arctype` WHERE reid=0 AND (ispart=0 OR ispart=1)");
$dsql->Execute('op');
while ($nrow = $dsql->GetObject('op')) {
    $typeOptions .= "<option value='{$nrow->id}'".(in_array($nrow->id, $typeids) ? ' selected' : '').">{$nrow->typename}</option>\r\n";
    $dsql->SetQuery("SELECT id,typename FROM `#@__arctype` WHERE reid={$nrow->id} AND (ispart=0 OR ispart=1)");
    $dsql->Execute('s');
    while ($nrow = $dsql->GetObject('s')) {
        $typeOptions .= "<option value='{$nrow->id}'".(in_array($nrow->id, $typeids) ? ' selected' : '').">└─ {$nrow->typename}</option>\r\n";
    }
}
$row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='$id'");
include DedeInclude('templets/member_toadmin.htm');
?>