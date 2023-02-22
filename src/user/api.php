<?php
/**
 * @version        $id:api.php 8:38 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
define('AJAXLOGIN', TRUE);
define('IS_DEDEAPI', TRUE);
require_once(dirname(__FILE__)."/config.php");
AjaxHead();
$action = isset($action)? $action : '';
if ($action === 'is_need_check_code') {
    $isNeed = $cfg_ml->isNeedCheckCode($userid);
    echo json_encode(array(
        "code" => 0,
        "msg" => "",
        "data" => array(
            "isNeed" => $isNeed,
        ),
    ));
    exit;
} else if ($action === 'get_old_email') {
    $oldpwd = isset($oldpwd)? $oldpwd : '';
    if (empty($oldpwd)) {
        echo json_encode(array(
            "code" => -1,
            "msg" => "旧密码不能为空",
            "data" => null,
        ));
        exit;
    }
    $row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='".$cfg_ml->M_ID."'");
    if (function_exists('password_hash') && !empty($row['pwd_new'])) {
        if (!is_array($row) || !password_verify($oldpwd, $row['pwd_new'])) {
            echo json_encode(array(
                "code" => -1,
                "msg" => "旧密码校验错误",
                "data" => null,
            ));
            exit;
        }
    } else {
        if (!is_array($row) || $row['pwd'] != md5($oldpwd)) {
            echo json_encode(array(
                "code" => -1,
                "msg" => "旧密码校验错误",
                "data" => null,
            ));
            exit;
        }
    }
    echo json_encode(array(
        "code" => 0,
        "msg" => "",
        "data" => array(
            "email" => $row['email'],
        ),
    ));
}  else {
    $format = isset($format) ? "json" : "";
    if (!$cfg_ml->IsLogin()) {
        if ($format === 'json') {
            echo json_encode(array(
                "code" => -1,
                "msg" => "未登录",
                "data" => null,
            ));
        } else {
            echo "";
        }
        exit;
    }
    $uid  = $cfg_ml->M_LoginID;
    !$cfg_ml->fields['face'] && $face = ($cfg_ml->fields['sex'] == '女') ? 'dfgirl' : 'dfboy';
    $facepic = empty($face) ? $cfg_ml->fields['face'] : $GLOBALS['cfg_memberurl'].'/templets/images/'.$face.'.png';
    if ($format === 'json') {
        echo json_encode(array(
            "code" => 200,
            "msg" => "",
            "data" => array(
                "username" => $cfg_ml->M_UserName,
                "myurl" => $myurl,
                "facepic" => $facepic,
                "memberurl" => $cfg_memberurl,
            ),
        ));
        exit;
    }
}
?>