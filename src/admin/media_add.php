<?php
/**
 * 添加附件
 *
 * @version        $id:media_add.php 2 15:25 2011-6-2 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
//添加权限检查
if (empty($dopost)) $dopost = '';
//上传
if ($dopost == "upload") {
    CheckCSRF();
    require_once(DEDEINC."/image.func.php");
    $sparr_image = array("image/pjpeg", "image/jpeg", "image/gif", "image/png", "image/xpng", "image/wbmp");
    $sparr_flash = array("application/xshockwaveflash");
    $okdd = 0;
    $uptime = time();
    $adminid = $cuserLogin->getUserID();
    $width = $height = '';
    for ($i = 0; $i <= 40; $i++) {
        if (isset(${"upfile".$i}) && is_uploaded_file(${"upfile".$i})) {
            $filesize = ${"upfile".$i."_size"};
            $upfile_type = ${"upfile".$i."_type"};
            $upfile_name = ${"upfile".$i."_name"};
            $dpath = MyDate("ymd", $uptime);
            if (in_array($upfile_type, $sparr_image)) {
                $mediatype = 1;
                $savePath = $cfg_image_dir."/".$dpath;
            } else if (in_array($upfile_type, $sparr_flash)) {
                $mediatype = 2;
                $savePath = $cfg_other_medias."/".$dpath;
            }
            //修复附件无法上传的错误
            else if (preg_match('#audio|media|video#i', $upfile_type) && preg_match("#\.".$cfg_mediatype."$#i", $upfile_name)) {
                $mediatype = 3;
                $savePath = $cfg_other_medias."/".$dpath;
            } else if (preg_match("#\.".$cfg_softtype."+\.".$cfg_softtype."$#i", $upfile_name)) {
                $mediatype = 4;
                $savePath = $cfg_soft_dir."/".$dpath;
            } else {
                continue;
            }
            $filename = "{$adminid}_".MyDate("His", $uptime).mt_rand(100, 999).$i;
            $fs = explode(".", ${"upfile".$i."_name"});
            $filename = $filename.".".$fs[count($fs) - 1];
            $filename = $savePath."/".$filename;
            if (!is_dir($cfg_basedir.$savePath)) {
                MkdirAll($cfg_basedir.$savePath, 777);
            }
            if (preg_match('#\.(php|pl|cgi|asp|aspx|jsp|php5|php4|php3|shtm|shtml|htm)$#i', trim($newname)) || preg_match('#\.(php|pl|cgi|asp|aspx|jsp|php5|php4|php3|shtm|shtml)$#i', trim($filename))) { 
                ShowMsg("文件扩展名已被系统禁止", "javascript:;"); 
                exit(); 
            }
            $fullfilename = $cfg_basedir.$filename;
            $mime = get_mime_type(${"upfile".$i});
            if (preg_match("#^unknow#", $mime)) {
                ShowMsg("系统不支持fileinfo组件，建议php.ini中开启", -1);
                exit;
            }
            if (!preg_match("#^(image|video|audio|application)#i", $mime)) {
                ShowMsg("仅支持媒体文件及应用程序上传", -1);
                exit;
            }
            if ($mediatype == 1) {
                @move_uploaded_file(${"upfile".$i}, $fullfilename);
                $info = '';
                $data = getImagesize($fullfilename, $info);
                $width = $data[0];
                $height = $data[1];
                if (in_array($upfile_type, $cfg_photo_typenames)) WaterImg($fullfilename, 'up');
            } else {
                @move_uploaded_file(${"upfile".$i}, $fullfilename);
            }
            if ($i > 1) {
                $ntitle = $title."_".$i;
            } else {
                $ntitle = $title;
            }
            $inquery = "INSERT INTO `#@__uploads` (title,url,mediatype,width,height,playtime,filesize,uptime,mid) VALUES ('$ntitle','$filename','$mediatype','$width','$height','$playtime','$filesize','$uptime','$adminid'); ";
            $okdd++;
            $dsql->ExecuteNoneQuery($inquery);
        }
    }
    ShowMsg("成功上传{$okdd}个文件", "media_main.php");
    exit();
}
include DedeInclude('templets/media_add.htm');
?>