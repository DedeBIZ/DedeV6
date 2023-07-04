<?php
/**
 * 选择图片发送
 *
 * @version        $id:select_images_post.php 9:43 2010年7月8日 tianya $
 * @package        DedeBIZ.Dialog
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/image.func.php");
define("DEDE_DIALOG_UPLOAD", true);
if (empty($activepath)) {
    $activepath = '';
    $activepath = str_replace('.', '', $activepath);
    $activepath = preg_replace("#\/{1,}#", '/', $activepath);
    if (strlen($activepath) < strlen($cfg_image_dir)) {
        $activepath = $cfg_image_dir;
    }
}
if (empty($imgfile)) {
    $imgfile = '';
}
if (!is_uploaded_file($imgfile)) {
    ShowMsg("您没有选择上传文件".$imgfile, "-1");
    exit();
}
$CKEditorFuncNum = (isset($CKEditorFuncNum)) ? $CKEditorFuncNum : 1;
$imgfile_name = trim(preg_replace("#[ \r\n\t\*\%\\\/\?><\|\":]{1,}#", '', $imgfile_name));
if (!preg_match("#\.(".$cfg_imgtype.")#i", $imgfile_name)) {
    ShowMsg("您上传的图片类型错误，请修改系统对扩展名配置", "-1");
    exit();
}
$nowtme = time();
$sparr = array("image/pjpeg", "image/jpeg", "image/gif", "image/png", "image/xpng", "image/wbmp", "image/webp");
$imgfile_type = strtolower(trim($imgfile_type));
if (!in_array($imgfile_type, $sparr)) {
    ShowMsg("您上传的图片格式错误，请使用jpg、png、gif、wbmp格式其中一种", "-1");
    exit();
}
$mdir = MyDate($cfg_addon_savetype, $nowtme);
if (!is_dir($cfg_basedir.$activepath."/$mdir")) {
    MkdirAll($cfg_basedir.$activepath."/$mdir", $cfg_dir_purview);
}
$iseditor = isset($iseditor)? intval($iseditor) : 0;
$filename_name = $cuserLogin->getUserID().'-'.dd2char(MyDate("ymdHis", $nowtme).mt_rand(100, 999));
$filename = $mdir.'/'.$filename_name;
$fs = explode('.', $imgfile_name);
$filename = $filename.'.'.$fs[count($fs) - 1];
$filename_name = $filename_name.'.'.$fs[count($fs) - 1];
$fullfilename = $cfg_basedir.$activepath."/".$filename;
$mime = get_mime_type($imgfile);
if (preg_match("#^unknow#", $mime)) {
    ShowMsg("系统不支持fileinfo组件，建议php.ini中开启", -1);
    exit;
}
if (!preg_match("#^(image|video|audio|application)#i", $mime)) {
    ShowMsg("仅支持媒体文件及应用程序上传", -1);
    exit;
}
move_uploaded_file($imgfile, $fullfilename) or die("上传文件到<span class='text-primary'>$fullfilename</span>失败");
@unlink($imgfile);
if (empty($resize)) {
    $resize = 0;
}
if ($resize == 1) {
    if (in_array($imgfile_type, $cfg_photo_typenames)) {
        ImageResize($fullfilename, $iwidth, $iheight);
    }
} else {
    if (in_array($imgfile_type, $cfg_photo_typenames)) {
        WaterImg($fullfilename, 'up');
    }
}
$info = '';
$sizes[0] = 0;
$sizes[1] = 0;
$sizes = getimagesize($fullfilename, $info);
$imgwidthValue = $sizes[0];
$imgheightValue = $sizes[1];
$imgsize = filesize($fullfilename);
$inquery = "INSERT INTO `#@__uploads` (arcid,title,url,mediatype,width,height,playtime,filesize,uptime,mid) VALUES ('0','$filename','".$activepath."/".$filename."','1','$imgwidthValue','$imgheightValue','0','{$imgsize}','{$nowtme}','".$cuserLogin->getUserID()."'); ";
$dsql->ExecuteNoneQuery($inquery);
$fid = $dsql->GetLastID();
AddMyAddon($fid, $activepath.'/'.$filename);
$CKUpload = isset($CKUpload) ? $CKUpload : FALSE;
if ($GLOBALS['cfg_html_editor'] == 'ckeditor' && $CKUpload) {
    $fileurl = $activepath.'/'.$filename;
    $result = array('url' => $fileurl, "uploaded" => 1, 'fileName' => $filename);
    echo json_encode($result);
    exit;
}
if (!empty($noeditor)) {
    ShowMsg("成功上传一张图片", "select_images.php?iseditor=$iseditor&imgstick=$imgstick&comeback=".urlencode($filename_name)."&v=$v&f=$f&CKEditorFuncNum=$CKEditorFuncNum&noeditor=yes&activepath=".urlencode($activepath)."/$mdir&d=".time());
} else {
    ShowMsg("成功上传一张图片", "select_images.php?iseditor=$iseditor&imgstick=$imgstick&comeback=".urlencode($filename_name)."&v=$v&f=$f&CKEditorFuncNum=$CKEditorFuncNum&activepath=".urlencode($activepath)."/$mdir&d=".time());
}
exit();
?>