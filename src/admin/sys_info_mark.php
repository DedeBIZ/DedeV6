<?php
/**
 * 图片水印设置
 *
 * @version        $id:sys_info_mark.php 22:28 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_Edit');
require_once(DEDEINC."/image.func.php");
if ($cfg_photo_support == '') {
    echo "未安装GD库，不允许使用该功能";
    exit();
}
$ImageWaterConfigFile = DEDEDATA."/mark/inc_photowatermark_config.php";
if (empty($action)) $action = '';
$allow_mark_types = array(
    'image/gif',
    'image/xpng',
    'image/png',
);
if ($action == "save") {
    $vars = array('photo_markup', 'photo_markdown', 'photo_marktype', 'photo_wwidth', 'photo_wheight', 'photo_waterpos', 'photo_watertext', 'photo_fontsize', 'photo_fontcolor', 'photo_marktrans', 'photo_diaphaneity');
    $configstr = $shortname = '';
    foreach ($vars as $v) {
        $tmp = stripslashes(${'get_'.$v});
        ${$v} = addslashes(str_replace("'", "", $tmp));
        $configstr .= "\${$v} = '".${$v}."';\r\n";
    }
    if (is_uploaded_file($newimg)) {
        $imgfile_type = strtolower(trim($newimg_type));
        if (!in_array($imgfile_type, $allow_mark_types)) {
            ShowMsg("上传的图片格式错误，请使用gif、png格式的其中一种", "-1");
            exit();
        }
        if ($imgfile_type == 'image/xpng' || $imgfile_type == 'image/png') {
            $shortname = ".png";
        } else if ($imgfile_type == 'image/gif') {
            $shortname = ".gif";
        } else {
            ShowMsg("水印图片仅支持gif、png格式的其中一种", "-1");
            exit;
        }
        $photo_markimg = 'mark'.$shortname;
        $mime = get_mime_type($newimg);
        if (preg_match("#^unknow#", $mime)) {
            ShowMsg("系统不支持fileinfo组件，建议php.ini中开启", -1);
            exit;
        }
        if (!preg_match("#^(image|video|audio|application)#i", $mime)) {
            ShowMsg("仅支持媒体文件及应用程序上传", -1);
            exit;
        }
        @move_uploaded_file($newimg, DEDEDATA."/mark/".$photo_markimg);
    }
    $configstr .= "\$photo_markimg = '{$photo_markimg}';\r\n";
    $configstr = "<"."?php\r\n".$configstr."?".">\r\n";
    $fp = fopen($ImageWaterConfigFile, "w") or die("写入文件".$ImageWaterConfigFile."失败，请检查权限");
    fwrite($fp, $configstr);
    fclose($fp);
    echo "<script>alert('修改配置成功');</script>\r\n";
}
require_once($ImageWaterConfigFile);
include DedeInclude('templets/sys_info_mark.htm');
?>