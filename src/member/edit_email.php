<?php

/**
 * @version        $Id: edit_email.php 2020/12/18 tianya $
 * @package        DedeBIZ.Member
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__) . "/config.php");

$dopost = isset($dopost) ? $dopost : "";
if (!isset($backurl)) {
    $backurl = 'edit_email.php';
}
if ($dopost == 'save') {
    if (!empty($cfg_ml->fields['email']) || $cfg_ml->fields['checkmail'] != -1) {
        ShowMsg('Email已经设置，无需重复提交！', '-1');
        exit();
    }
    // 校验CSRF
    CheckCSRF();
    if (!CheckEmail($email)) {
        ShowMsg('Email格式不正确！', '-1');
        exit();
    }
    $email = HtmlReplace($email, -1);

    $query = "UPDATE `#@__member` SET `email` = '$email' WHERE mid='{$cfg_ml->M_ID}' ";
    $dsql->ExecuteNoneQuery($query);
    // 清除缓存
    $cfg_ml->DelCache($cfg_ml->M_ID);
    ShowMsg('成功更新邮箱信息！', $backurl);
    exit();
}
$email = $cfg_ml->fields['email'];
include(DEDEMEMBER . "/templets/edit_email.htm");
exit();
