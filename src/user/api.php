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
    $row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='".$cfg_ml->M_ID."' ");
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
} else if ($action === 'upload') {
    if (!$cfg_ml->IsLogin()) {
        echo json_encode(array(
            "code" => -1,
            "msg" => "未登录",
            "data" => null,
        ));
        exit;
    }
    if ($cfg_ml->CheckUserSpaceIsFull()) {
        echo json_encode(array(
            "code" => -1,
            "msg" => "您的空间已满，不允许上传新文件",
            "data" => null,
        ));
        exit;
    }
    $target_dir = "uploads/";//上传目录
    $type = isset($type)? $type : '';
    //获取允许的扩展
    $mediatype = 0;
    $allowedTypes = array();
    if ($type == 'litpic' || $type == 'face') {
        $mediatype = 1;
        $imgtypes = explode("|", $cfg_imgtype);
        foreach ($imgtypes as $value) {
            $allowedTypes[] = GetMimeTypeOrExtension($value);
        }
    } else if ($type == 'soft') {
        $mediatype = 4;
        $softtypes = explode("|", $cfg_softtype);
        foreach ($softtypes as $value) {
            $allowedTypes[] = GetMimeTypeOrExtension($value);
        }
    } else if ($type == 'media') {
        $mediatype = 3;
        $mediatypes = explode("|", $cfg_mediatype);
        foreach ($mediatypes as $value) {
            $allowedTypes[] = GetMimeTypeOrExtension($value);
        }
    } else {
        echo json_encode(array(
            "code" => -1,
            "msg" => "未定义文件类型",
            "data" => null,
        ));
        exit;
    }
    $ff = isset($_FILES['file'])? $_FILES['file'] : $_FILES['imgfile'];
    $uploadedFile = $ff['tmp_name'];
    $fileType = mime_content_type($uploadedFile);
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(array(
            "code" => -1,
            "msg" => "当前文件格式不支持",
            "data" => null,
        ));
        exit;
    }
    //获取扩展名
    $exts = GetMimeTypeOrExtension($fileType, 1);
    $width = 0;
    $height = 0;
    if ($mediatype === 1) {
        $imgSize = getimagesize($uploadedFile);
        if (!$imgSize) {
            echo json_encode(array(
                "code" => -1,
                "msg" => "无法获取当前图片正常尺寸",
                "data" => null,
            ));
            exit;
        }
        $width = $imgSize[0];
        $height = $imgSize[1];
    }
    if (!is_dir($cfg_basedir.$cfg_user_dir."/{$cfg_ml->M_ID}")) {
        MkdirAll($cfg_basedir.$cfg_user_dir."/{$cfg_ml->M_ID}", $cfg_dir_purview);
        CloseFtp();
    }
    //头像特殊处理
    if ($type === "face") {
        $target_file = $cfg_basedir.$cfg_user_dir."/{$cfg_ml->M_ID}/newface.png";
        $target_url = $cfg_mediasurl.'/userup'."/{$cfg_ml->M_ID}/newface.png";
    } else {
        $nowtme = time();
        $rnd = $nowtme.'-'.mt_rand(1000,9999);
        $target_file = $cfg_basedir.$cfg_user_dir."/{$cfg_ml->M_ID}/".$rnd.".".$exts;
        $fsize = filesize($ff["tmp_name"]);
        $target_url = $cfg_mediasurl.'/userup'."/{$cfg_ml->M_ID}/".$rnd.".".$exts;
        $row = $dsql->GetOne("SELECT aid,title,url FROM `#@__uploads` WHERE url LIKE '$target_url' AND mid='".$cfg_ml->M_ID."'; ");
        $uptime = time();
        if (is_array($row)) {
            $query = "UPDATE `#@__uploads` SET mediatype={$mediatype},width='{$width}',height='{$height}',filesize='{$fsize}',uptime='$uptime' WHERE aid='{$row['aid']}'; ";
            $dsql->ExecuteNoneQuery($query);
        } else {
            $inquery = "INSERT INTO `#@__uploads`(url,mediatype,width,height,playtime,filesize,uptime,mid) VALUES ('$target_url','$mediatype','".$width."','".$height."','0','".$fsize."','$uptime','".$cfg_ml->M_ID."'); ";
            $dsql->ExecuteNoneQuery($inquery);
        }
    }
    $rkey = $ck == 1? "url" : "data";
    if (move_uploaded_file($ff["tmp_name"], $target_file)) {
        if ($mediatype === 1) {
            //图片自动裁剪
            require_once DEDEINC."/libraries/imageresize.class.php";
            try {
                $image = new ImageResize($target_file);
                if ($type === "face") {
                    $image->crop(150, 150);
                } else {
                    $image->resize($cfg_ddimg_width, $cfg_ddimg_height);
                }
                $image->save($target_file);
                echo json_encode(array(
                    "code" => 0,
                    "uploaded" => 1,
                    "msg" => "上传成功",
                    $rkey => $target_url,
                ));
            } catch (ImageResizeException $e) {
                echo json_encode(array(
                    "code" => -1,
                    "msg" => "图片自动裁剪失败",
                    $rkey => null,
                ));
            }
        } else {
            echo json_encode(array(
                "code" => 0,
                "uploaded" => 1,
                "msg" => "上传成功",
                $rkey => $target_url,
            ));
        }
    } else {
        echo json_encode(array(
            "code" => -1,
            "msg" => "上传失败",
            $rkey => null,
        ));
    }
} else {
    $format = isset($format) ? "json" : "";
    if (!$cfg_ml->IsLogin()) {
        if ($format === 'json') {
            echo json_encode(array(
                "code" => -1,
                "msg" => "未登录",
                $rkey => null,
            ));
        } else {
            echo "";
        }
        exit;
    }
    $uid  = $cfg_ml->M_LoginID;
    !$cfg_ml->fields['face'] && $face = ($cfg_ml->fields['sex'] == '女') ? 'dfgirl' : 'dfboy';
    if ($format === 'json') {
        echo json_encode(array(
            "code" => 200,
            "msg" => "",
            "data" => array(
                "username" => $cfg_ml->M_UserName,
                "myurl" => $myurl,
                "facepic" => $cfg_ml->fields['face'],
                "memberurl" => $cfg_memberurl,
            ),
        ));
        exit;
    }
}
?>