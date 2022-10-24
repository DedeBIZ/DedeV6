<?php
/**
 * 图片水印
 *
 * @version        $Id: sys_info_mark.php 1 22:28 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('sys_Edit');
helper('image');
if ($cfg_photo_support == '') {
    echo Lang('sys_info_mark_nogd');
    exit();
}
$ImageWaterConfigFile = DEDEDATA."/mark/inc_photowatermark_config.php";
if (empty($action)) $action = "";
$allow_mark_types = array(
    'image/gif',
    'image/xpng',
    'image/png',
);
if ($action == "save") {
    $vars = array('photo_markup', 'photo_markdown', 'photo_marktype', 'photo_wwidth', 'photo_wheight', 'photo_waterpos', 'photo_watertext', 'photo_fontsize', 'photo_fontcolor', 'photo_marktrans', 'photo_diaphaneity');
    $configstr = $shortname = "";
    foreach ($vars as $v) {
        $tmp = stripslashes(${'get_'.$v});
        ${$v} = addslashes(str_replace("'", "", $tmp));
        $configstr .= "\${$v} = '".${$v}."';\r\n";
    }
    if (is_uploaded_file($newimg)) {
        $imgfile_type = strtolower(trim($newimg_type));
        if (!in_array($imgfile_type, $allow_mark_types)) {
            ShowMsg(Lang("sys_info_mark_err_imgtype_0"), "-1");
            exit();
        }
        if ($imgfile_type == 'image/xpng' || $imgfile_type == 'image/png') {
            $shortname = ".png";
        } else if ($imgfile_type == 'image/gif') {
            $shortname = ".gif";
        } else {
            ShowMsg(Lang("sys_info_mark_err_imgtype_1"), "-1");
            exit;
        }
        $photo_markimg = 'mark'.$shortname;
        $mime = get_mime_type($newimg);
        if (preg_match("#^unknow#", $mime)) {
            ShowMsg(Lang("media_no_fileinfo"), -1);
            exit;
        }
        if (!preg_match("#^(image|video|audio|application)#i", $mime)) {
            ShowMsg(Lang("media_only_media"), -1);
            exit;
        }
        @move_uploaded_file($newimg, DEDEDATA."/mark/".$photo_markimg);
    }
    $configstr .= "\$photo_markimg = '{$photo_markimg}';\r\n";
    $configstr = "<"."?php\r\n".$configstr."?".">\r\n";
    $fp = fopen($ImageWaterConfigFile, "w") or die(Lang('sys_info_mark_err_write',array('ImageWaterConfigFile'=>$ImageWaterConfigFile)));
    fwrite($fp, $configstr);
    fclose($fp);
    echo "<script>alert('".Lang('operation_successful')."');</script>\r\n";
}
require_once($ImageWaterConfigFile);
include DedeInclude('templets/sys_info_mark.htm');
?>