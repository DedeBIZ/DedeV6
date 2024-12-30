<?php
if (!defined('DEDEINC')) exit ('dedebiz');
if (isset($_FILES['GLOBALS'])) exit ('Request not allow!');
/**
 * 文件上传安全校验方法
 *
 * @version        $id:uploadsafe.inc.php 15:59 2020年8月19日 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
//为了防止会员通过注入，这里强制限定的某些文件类型禁止上传
$cfg_not_allowall = "php|pl|cgi|asp|aspx|jsp|php3|shtm|shtml|htm";
$keyarr = array('name', 'type', 'tmp_name', 'size');
if (
    ($GLOBALS['cfg_html_editor'] == 'ckeditor' ||
        $GLOBALS['cfg_html_editor'] == 'ckeditor4')  && isset($_FILES['upload'])
) {
    $_FILES['imgfile'] = $_FILES['upload'];
    $CKUpload = TRUE;
    unset($_FILES['upload']);
}
foreach ($_FILES as $_key => $_value) {
    foreach ($keyarr as $k) {
        if (!isset($_FILES[$_key][$k])) {
            exit('dedebiz');
        }
    }
    if (preg_match('#^(cfg_|GLOBALS)#', $_key)) {
        echo DedeAlert('危险的请求参数', ALERT_DANGER);
        exit;
    }
    $$_key = $_FILES[$_key]['tmp_name'];
    ${$_key.'_name'} = $_FILES[$_key]['name'];
    ${$_key.'_type'} = $_FILES[$_key]['type'] = preg_replace('#[^0-9a-z\./]#i', '', $_FILES[$_key]['type']);
    ${$_key.'_size'} = $_FILES[$_key]['size'] = preg_replace('#[^0-9]#', '', $_FILES[$_key]['size']);
    if (is_array(${$_key.'_name'}) && count(${$_key.'_name'}) > 0) {
        foreach (${$_key.'_name'} as $key => $value) {
            $value = trim($value);
            if (!empty($value) && (preg_match("#\.(".$cfg_not_allowall.")$#i", $value) || !preg_match("#\.#", $value) || preg_match('#\.[\x00-\x1F\x7F]*$#', trim($value)))) {
                if (!defined('DEDEADMIN')) {
                    echo DedeAlert('禁止上传当前格式的文件', ALERT_DANGER);
                    exit;
                }
            }
        }
    } else {
        $fname = trim(${$_key.'_name'});
        if (!empty($fname) && (preg_match("#\.(".$cfg_not_allowall.")$#i", $fname) || !preg_match("#\.#", $fname) || preg_match('#\.[\x00-\x1F\x7F]*$#', trim($value)))) {
            if (!defined('DEDEADMIN')) {
                echo DedeAlert('禁止上传当前格式的文件', ALERT_DANGER);
                exit;
            }
        }
    }
    if (empty(${$_key.'_size'})) {
        ${$_key.'_size'} = @filesize($$_key);
    }
    $imtypes = array("image/pjpeg", "image/jpeg", "image/gif", "image/png", "image/xpng", "image/wbmp", "image/bmp");
    if (is_array(${$_key.'_type'}) && count(${$_key.'_type'}) > 0) {
        foreach (${$_key.'_type'} as $key => $value) {
            if (in_array(strtolower(trim($value)), $imtypes)) {
                $image_dd = @getimagesize($$_key);
                if ($image_dd == false) {
                    continue;
                }
                if (!is_array($image_dd)) {
                    echo DedeAlert('禁止上传当前格式的文件', ALERT_DANGER);
                    exit;
                }
            }
            $imtypes = array(
                "image/pjpeg", "image/jpeg", "image/gif", "image/png", "image/xpng", "image/wbmp", "image/bmp"
            );
            if (in_array(strtolower(trim($value)), $imtypes)) {
                $image_dd = @getimagesize($$_key);
                if ($image_dd == false) {
                    continue;
                }
                if (!is_array($image_dd)) {
                    echo DedeAlert('禁止上传当前格式的文件', ALERT_DANGER);
                    exit;
                }
            }
        }
    } else {
        if (in_array(strtolower(trim(${$_key.'_type'})), $imtypes)) {
            $image_dd = @getimagesize($$_key);
            if ($image_dd == false) {
                continue;
            }
            if (!is_array($image_dd)) {
                echo DedeAlert('禁止上传当前格式的文件', ALERT_DANGER);
                exit;
            }
        }
        $imtypes = array(
            "image/pjpeg", "image/jpeg", "image/gif", "image/png", "image/xpng", "image/wbmp", "image/bmp"
        );
        if (in_array(strtolower(trim(${$_key.'_type'})), $imtypes)) {
            $image_dd = @getimagesize($$_key);
            if ($image_dd == false) {
                continue;
            }
            if (!is_array($image_dd)) {
                echo DedeAlert('禁止上传当前格式的文件', ALERT_DANGER);
                exit;
            }
        }
    }
}
?>