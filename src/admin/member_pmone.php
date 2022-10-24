<?php
/**
 * 会员短消息
 *
 * @version        $Id: member_pmone.php 1 11:24 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('member_Pm');
//检查用户名的合法性
function CheckUserID($uid, $msgtitle = '用户名', $ckhas = true)
{
    global $cfg_mb_notallow, $cfg_md_idurl, $dsql;
    if ($cfg_mb_notallow != '') {
        $nas = explode(',', $cfg_mb_notallow);
        if (in_array($uid, $nas)) {
            return Lang('member_userid_notallow',array('msgtitle'=>$msgtitle));
        }
    }
    if ($cfg_md_idurl == 'Y' && preg_match("#[^a-z0-9]#i", $uid)) {
        return Lang('member_userid_needword',array('msgtitle'=>$msgtitle));
    }
    $ck_uid = utf82gb($uid);
    for ($i = 0; isset($ck_uid[$i]); $i++) {
        if (ord($ck_uid[$i]) > 0x80) {
            if (isset($ck_uid[$i + 1]) && ord($ck_uid[$i + 1]) > 0x40) {
                $i++;
            } else {
                return Lang('member_userid_ncharset',array('msgtitle'=>$msgtitle));
            }
        } else {
            if (preg_match("#[^0-9a-z@\.-]i#", $ck_uid[$i])) {
                return Lang('member_userid_charset_notallow',array('msgtitle'=>$msgtitle));
            }
        }
    }
    if ($ckhas) {
        $row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE userid LIKE '$uid'");
        if (is_array($row)) return Lang('member_userid_exists',array('msgtitle'=>$msgtitle));
    }
    return 'ok';
}
if (!isset($action)) $action = '';
if ($action == "post") {
    $floginid = $cUserLogin->getUserName();
    $fromid = $cUserLogin->getUserID();
    if ($subject == '') {
        ShowMsg(Lang("member_post_title_isempty"), "-1");
        exit();
    }
    $msg = CheckUserID($msgtoid, Lang("username"), false);
    if ($msg != 'ok') {
        ShowMsg($msg, "-1");
        exit();
    }
    $row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE userid like '$msgtoid'");
    if (!is_array($row)) {
        ShowMsg(Lang("member_post_user_noexists"), "-1");
        exit();
    }
    $subject = cn_substrR(HtmlReplace($subject, 1), 60);
    $message = cn_substrR(HtmlReplace($message, 0), 1024);
    $sendtime = $writetime = time();
    //发给收件人
    $inquery = "INSERT INTO `#@__member_pms` (`floginid`,`fromid`,`toid`,`tologinid`,`folder`,`subject`,`sendtime`,`writetime`,`hasview`,`isadmin`,`message`) VALUES ('$floginid','$fromid','{$row['mid']}','{$row['userid']}','inbox','$subject','$sendtime','$writetime','0','0','$message');";
    $dsql->ExecuteNoneQuery($inquery);
    ShowMsg(Lang('member_post_success'), 'member_pmone.php');
    exit();
}
require_once(DEDEADMIN."/templets/member_pmone.htm");
?>