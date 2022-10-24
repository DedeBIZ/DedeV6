<?php
/**
 * 图片选择
 *
 * @version        $Id: select_images_post.php 2022-07-01 tianya $
 * @package        DedeBIZ.Dialog
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
helper('image');
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
    ShowMsg(Lang("friendlink_err_imglogo_empty",array('file'=>$imgfile)), "-1");
    exit();
}
$CKEditorFuncNum = (isset($CKEditorFuncNum)) ? $CKEditorFuncNum : 1;
$imgfile_name = trim(preg_replace("#[ \r\n\t\*\%\\\/\?><\|\":]{1,}#", '', $imgfile_name));

if (!preg_match("#\.(".$cfg_imgtype.")#i", $imgfile_name)) {
    ShowMsg(Lang("dialog_err_imagetype"), "-1");
    exit();
}
$nowtme = time();
$sparr = array("image/pjpeg", "image/jpeg", "image/gif", "image/png", "image/xpng", "image/wbmp", "image/webp");
$imgfile_type = strtolower(trim($imgfile_type));
if (!in_array($imgfile_type, $sparr)) {
    ShowMsg(Lang("dialog_err_imageformat"), "-1");
    exit();
}
$mdir = MyDate($cfg_addon_savetype, $nowtme);
if (!is_dir($cfg_basedir.$activepath."/$mdir")) {
    MkdirAll($cfg_basedir.$activepath."/$mdir", $cfg_dir_purview);
    CloseFtp();
}
$filename_name = $cUserLogin->getUserID().'-'.dd2char(MyDate("ymdHis", $nowtme).mt_rand(100, 999));
$filename = $mdir.'/'.$filename_name;
$fs = explode('.', $imgfile_name);
$filename = $filename.'.'.$fs[count($fs) - 1];
$filename_name = $filename_name.'.'.$fs[count($fs) - 1];
$fullfilename = $cfg_basedir.$activepath."/".$filename;
$mime = get_mime_type($imgfile);
if (preg_match("#^unknow#", $mime)) {
    ShowMsg(Lang("media_no_fileinfo"), -1);
    exit;
}
if (!preg_match("#^(image|video|audio|application)#i", $mime)) {
    ShowMsg(Lang("media_only_media"), -1);
    exit;
}
move_uploaded_file($imgfile, $fullfilename) or die(Lang('media_err_upload',array('filename'=>$fullfilename)));
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
$inquery = "INSERT INTO `#@__uploads`(arcid,title,url,mediatype,width,height,playtime,filesize,uptime,mid) VALUES ('0','$filename','".$activepath."/".$filename."','1','$imgwidthValue','$imgheightValue','0','{$imgsize}','{$nowtme}','".$cUserLogin->getUserID()."');";
$dsql->ExecuteNoneQuery($inquery);
$fid = $dsql->GetLastID();
UserLogin::AddMyAddon($fid, $activepath.'/'.$filename);
$CKUpload = isset($CKUpload) ? $CKUpload : FALSE;
if ($GLOBALS['cfg_html_editor'] == 'ckeditor' && $CKUpload) {
    $fileurl = $activepath.'/'.$filename;
    $result = array('url' => $fileurl, "uploaded" => 1, 'fileName' => $filename);
    echo json_encode($result);
    exit;
}
if (!empty($noeditor)) {
    ShowMsg(Lang("dialog_success_uploadimage"), "select_images.php?imgstick=$imgstick&comeback=".urlencode($filename_name)."&v=$v&f=$f&CKEditorFuncNum=$CKEditorFuncNum&noeditor=yes&activepath=".urlencode($activepath)."/$mdir&d=".time());
} else {
    ShowMsg(Lang("dialog_success_uploadimage"), "select_images.php?imgstick=$imgstick&comeback=".urlencode($filename_name)."&v=$v&f=$f&CKEditorFuncNum=$CKEditorFuncNum&activepath=".urlencode($activepath)."/$mdir&d=".time());
}
exit();
?>