<?php
/**
 * 编辑系统管理员
 *
 * @version        $Id: sys_admin_user_edit.php 1 16:22 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__).'/config.php');
UserLogin::CheckPurview('sys_User');
if (empty($dopost)) $dopost = '';
$id = preg_replace("#[^0-9]#", '', $id);
if ($dopost == 'saveedit') {
    CheckCSRF();
    $pwd = trim($pwd);
    if ($pwd != '' && preg_match("#[^0-9a-zA-Z_@!\.-]#", $pwd)) {
        ShowMsg(Lang('sys_admin_err_pwd_check'), '-1', 0, 3000);
        exit();
    }
    $safecodeok = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
    if ($safecodeok != $safecode) {
        ShowMsg(Lang("sys_admin_err_safecodeok_check"), "sys_admin_user_edit.php?id={$id}&dopost=edit");
        exit();
    }
    $pwdm = '';
    if ($pwd != '') {
        if (function_exists('password_hash')) {
            $pwdm = ",pwd='',pwd_new='".password_hash($pwd, PASSWORD_BCRYPT)."'";
            $pwd = ",pwd='',pwd_new='".password_hash($pwd, PASSWORD_BCRYPT)."'";
        } else {
            $pwdm = ",pwd='".md5($pwd)."'";
            $pwd = ",pwd='".substr(md5($pwd), 5, 20)."'";
        }
    }
    if (empty($typeids)) {
        $typeid = '';
    } else {
        $typeid = join(',', $typeids);
        if ($typeid == '0') $typeid = '';
    }
    if ($id != 1) {
        $query = "UPDATE `#@__admin` SET uname='$uname',usertype='$usertype',tname='$tname',email='$email',typeid='$typeid' $pwd WHERE id='$id'";
    } else {
        $query = "UPDATE `#@__admin` SET uname='$uname',tname='$tname',email='$email',typeid='$typeid' $pwd WHERE id='$id'";
    }
    $dsql->ExecuteNoneQuery($query);
    $query = "UPDATE `#@__member` SET uname='$uname',email='$email'$pwdm WHERE mid='$id'";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg(Lang("sys_admin_user_edit_success"), "sys_admin_user.php");
    exit();
} else if ($dopost == 'delete') {
    if (empty($userok)) $userok = "";
    if ($userok != "yes") {
        $randcode = mt_rand(10000, 99999);
        $safecode = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
        $wintitle = Lang("sys_admin_user_delete");
        $wecome_info = "<a href='sys_admin_user.php'>".Lang('sys_admin_user')."</a>::".Lang("sys_admin_user_delete");
        DedeWin::Instance()->Init("sys_admin_user_edit.php", "js/blank.js", "POST")
        ->AddHidden("dopost", $dopost)
        ->AddHidden("userok", "yes")
        ->AddHidden("randcode", $randcode)
        ->AddHidden("safecode", $safecode)
        ->AddHidden("id", $id)
        ->AddTitle(Lang("message_info"))
        ->AddMsgItem(Lang('sys_admin_user_delete_confirm',array('userid'=>$userid)), "50")
        ->AddMsgItem(Lang('safecode')."：<input name='safecode' type='text' id='safecode' style='width:260px'>（".Lang('safecode')."：<span class='text-danger'>$safecode</span>）", "30")
        ->GetWindow("ok")->Display();
        exit();
    }
    $safecodeok = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
    if ($safecodeok != $safecode) {
        ShowMsg(Lang("sys_admin_err_safecodeok_check"), "sys_admin_user.php");
        exit();
    }
    //不能删除id为1的创建人帐号，不能删除自己
    $rs = $dsql->ExecuteNoneQuery2("DELETE FROM `#@__admin` WHERE id='$id' AND id<>1 AND id<>'".$cUserLogin->getUserID()."'");
    if ($rs > 0) {
        //更新前台用户信息
        $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET matt='0' WHERE mid='$id' LIMIT 1");
        ShowMsg(Lang("sys_admin_user_delete_success"), "sys_admin_user.php");
    } else {
        ShowMsg(Lang("sys_admin_user_err_delete_admin"), "sys_admin_user.php", 0, 3000);
    }
    exit();
}
//显示用户信息
$randcode = mt_rand(10000, 99999);
$safecode = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
$typeOptions = '';
$row = $dsql->GetOne("SELECT * FROM `#@__admin` WHERE id='$id'");
$typeids = explode(',', $row['typeid']);
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
make_hash();
include DedeInclude('templets/sys_admin_user_edit.htm');
?>