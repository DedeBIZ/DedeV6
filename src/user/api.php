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
} else if($action === 'upload_face'){
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
    $target_dir = "uploads/"; //上传目录

    $allowedTypes = array('image/png', 'image/jpg', 'image/jpeg');
    $uploadedFile = $_FILES['file']['tmp_name'];
    $fileType = mime_content_type($uploadedFile);
    $imgSize = getimagesize($uploadedFile);
    if (!in_array($fileType, $allowedTypes) || !$imgSize) {
        echo json_encode(array(
            "code" => -1,
            "msg" => "仅支持图片格式文件",
            "data" => null,
        ));
        exit;
    }
    
    if (!is_dir($cfg_basedir.$cfg_user_dir."/{$cfg_ml->M_ID}")) {
        MkdirAll($cfg_basedir.$cfg_user_dir."/{$cfg_ml->M_ID}", $cfg_dir_purview);
        CloseFtp();
    }
    $target_file = $cfg_basedir.$cfg_user_dir."/{$cfg_ml->M_ID}/newface.png"; //上传文件名
    $target_url = $cfg_mediasurl.'/userup'."/{$cfg_ml->M_ID}/newface.png";
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        require_once DEDEINC."/libraries/imageresize.class.php";
        try{
            $image = new ImageResize($target_file);
            $image->crop(150, 150);
            $image->save($target_file);
            echo json_encode(array(
                "code" => 0,
                "msg" => "上传成功",
                "data" => $target_url,
            ));
        } catch (ImageResizeException $e) {
            echo json_encode(array(
                "code" => -1,
                "msg" => "图片自动裁剪失败",
                "data" => null,
            ));
        }
    } else {
        echo json_encode(array(
            "code" => -1,
            "msg" => "上传失败",
            "data" => null,
        ));
    }
} else {
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