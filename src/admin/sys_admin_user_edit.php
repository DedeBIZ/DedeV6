<?php
/**
 * 修改管理员
 *
 * @version        $id:sys_admin_user_edit.php 16:22 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
CheckPurview('sys_User');
require_once(DEDEINC.'/typelink/typelink.class.php');
if (empty($dopost)) $dopost = '';
$id = preg_replace("#[^0-9]#", '', $id);
if ($dopost == 'saveedit') {
    CheckCSRF();
    $pwd = trim($pwd);
    if (preg_match("#[^0-9a-zA-Z_@!\.-]#", $userid)) {
        ShowMsg('账号不合法，请使用数字0-9小写a-z大写A-Z符号_@!.-', '-1');
        exit();
    }
    if ($pwd != '' && preg_match("#[^0-9a-zA-Z_@!\.-]#", $pwd)) {
        ShowMsg('密码不合法，请使用数字0-9小写a-z大写A-Z符号_@!.-', '-1');
        exit();
    }
    $safecodeok = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
    if ($safecodeok != $safecode) {
        ShowMsg("请填写正确的验证安全码", "sys_admin_user_edit.php?id={$id}&dopost=edit");
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
    $olduserid = preg_replace("/[^0-9a-zA-Z_@!\.-]/", '', $olduserid);
    $userid = preg_replace("/[^0-9a-zA-Z_@!\.-]/", '', $userid);
    $usql = '';
    if ($olduserid !== $userid) {
        $row = $dsql->GetOne("SELECT mid FROM `#@__member` WHERE userid LIKE '$userid' ");
        if (is_array($row)) {
            ShowMsg("您指定的账号{$userid}已存在，请使用别的账号", "-1");
            exit();
        }
        $row = $dsql->GetOne("SELECT id FROM `#@__admin` WHERE userid LIKE '$userid' ");
        if (is_array($row)) {
            ShowMsg("您指定的账号{$userid}已存在，请使用别的账号", "-1");
            exit();
        }
        $usql = ",userid='$userid'";
    }
    if ($id != 1) {
        $query = "UPDATE `#@__admin` SET uname='$uname',usertype='$usertype',tname='$tname',email='$email',typeid='$typeid' $pwd $usql WHERE id='$id'";
    } else {
        $query = "UPDATE `#@__admin` SET uname='$uname',tname='$tname',email='$email',typeid='$typeid' $pwd $usql WHERE id='$id'";
    }
    $dsql->ExecuteNoneQuery($query);
    $query = "UPDATE `#@__member` SET uname='$uname',email='$email' $pwdm $usql WHERE mid='$id'";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("成功修改一个账户", "sys_admin_user_edit.php?id=".$cuserLogin->getUserID()."&dopost=edit");
    exit();
} else if ($dopost == 'delete') {
    if (empty($userok)) $userok = '';
    if ($userok != "yes") {
        $randcode = mt_rand(10000, 99999);
        $safecode = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
        require_once(DEDEINC."/libraries/oxwindow.class.php");
        $wintitle = "删除指定管理员";
        $win = new OxWindow();
        $win->Init("sys_admin_user_edit.php", "/static/web/js/admin.blank.js", "POST");
        $win->AddHidden("dopost", $dopost);
        $win->AddHidden("userok", "yes");
        $win->AddHidden("randcode", $randcode);
        $win->AddHidden("safecode", $safecode);
        $win->AddHidden("id", $id);
        $win->AddMsgItem("<tr><td>您确定要删除".$userid."管理员吗</td></tr>");
        $win->AddMsgItem("<tr><td>验证安全码：<input name='safecode' type='text' id='safecode' class='admin-input-lg'>（安全码：".$safecode."）</td></tr>");
        $winform = $win->GetWindow("ok");
        $win->Display();
        exit();
    }
    $safecodeok = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
    if ($safecodeok != $safecode) {
        ShowMsg("请填写正确的验证安全码", "sys_admin_user.php");
        exit();
    }
    //不能删除id为1的创建人帐号，不能删除自己
    $rs = $dsql->ExecuteNoneQuery2("DELETE FROM `#@__admin` WHERE id='$id' AND id<>1 AND id<>'".$cuserLogin->getUserID()."' ");
    if ($rs > 0) {
        //更新前台管理员信息
        $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET matt='0' WHERE mid='$id' LIMIT 1");
        ShowMsg("成功删除一个帐户", "sys_admin_user.php");
    } else {
        ShowMsg("不能删除id为1的创建人帐号，不能删除自己", "sys_admin_user.php");
    }
    exit();
}
//显示管理员信息
$randcode = mt_rand(10000, 99999);
$safecode = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
//递归获取分类
function getTypeOptions($id=0,$sep="└─")
{
    global $dsql,$typeOptions,$typeids;
    $dsql->SetQuery("SELECT id,typename,ispart FROM `#@__arctype` WHERE reid={$id} AND (ispart=0 OR ispart=1 OR ispart=2) ORDER BY sortrank");
    $dsql->Execute($id);
    while ($nrow = $dsql->GetObject($id)) {
        $isDisabled = $nrow->ispart==2? " disabled" : "";
        $typeOptions .= "<option value='{$nrow->id}' ".(in_array($nrow->id, $typeids) ? ' selected' : '')."{$isDisabled}>{$sep} {$nrow->typename}</option>\r\n";
        getTypeOptions($nrow->id, $sep."─");
    }
}
$typeOptions = '';
$row = $dsql->GetOne("SELECT * FROM `#@__admin` WHERE id='$id'");
$typeids = explode(',', $row['typeid']);
getTypeOptions(0);
make_hash();
include DedeInclude('templets/sys_admin_user_edit.htm');
?>