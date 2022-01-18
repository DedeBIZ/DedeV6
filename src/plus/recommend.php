<?php

/**
 *
 * 信息推荐
 *
 * @version        $Id: recommend.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../include/common.inc.php");
require_once(DEDEINC."/channelunit.class.php");
if (!isset($action)) $action = '';
unset($_FILES);
if (isset($arcID)) $aid = $arcID;
$arcID = $aid = (isset($aid) && is_numeric($aid) ? $aid : 0);
$type = (!isset($type) ? "" : $type);

if (empty($aid)) {
    ShowMsg("文档ID不能为空!", "-1");
    exit();
}

//读取文档信息
if ($action == '') {
    //读取文档信息
    $arcRow = GetOneArchive($aid);
    if ($arcRow['aid'] == '') {
        ShowMsg("无法把未知文档推荐给好友!", "-1");
        exit();
    }
    extract($arcRow, EXTR_OVERWRITE);
}

//发送推荐信息
else if ($action == 'send') {
    if (!CheckEmail($email)) {
        ShowMsg("Email格式不正确", -1);
        exit();
    }
    $mailbody = '';
    $msg = RemoveXSS(dede_htmlspecialchars($msg));
    $mailtitle = "你的好友给你推荐了一篇文章";
    $mailbody .= "$msg \r\n\r\n";
    $mailbody .= "Powered by https://www.dedebiz.com DedeBIZ内容管理系统！";

    $headers = "From: ".$cfg_adminemail."\r\nReply-To: ".$cfg_adminemail;
    if (!empty($cfg_bizcore_appid) && !empty($cfg_bizcore_key)) {
        $client = new DedeBizClient($cfg_bizcore_hostname, $cfg_bizcore_port);
        $client->appid = $cfg_bizcore_appid;
        $client->key = $cfg_bizcore_key;
        $client->MailSend($email,$mailtitle,$mailtitle,$mailbody);
        $client->Close();
    } else {
        if ($cfg_sendmail_bysmtp == 'Y' && !empty($cfg_smtp_server)) {
            $mailtype = 'TXT';
            require_once(DEDEINC.'/mail.class.php');
            $smtp = new smtp($cfg_smtp_server, $cfg_smtp_port, true, $cfg_smtp_usermail, $cfg_smtp_password);
            $smtp->debug = false;
            $smtp->sendmail($email, $cfg_webname, $cfg_smtp_usermail, $mailtitle, $mailbody, $mailtype);
        } else {
            @mail($email, $mailtitle, $mailbody, $headers);
        }
    }

    ShowMsg("成功推荐一篇文章!", $arcurl);
    exit();
}
$arcRow = GetOneArchive($aid);
if ($arcRow['aid'] == '') {
    ShowMsg("无法把未知文档推荐给好友!", "-1");
    exit();
}
extract($arcRow, EXTR_OVERWRITE);
//显示模板(简单PHP文件)
include(DEDETEMPLATE.'/plus/recommend.htm');
