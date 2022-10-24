<?php
/**
 * swfupload上传
 *
 * @version        $Id: swfupload.php 1 16:22 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
helper('image');
//删除指定ID的图片
if ($dopost == 'del') {
    if (!isset($_SESSION['bigfile_info'][$id])) {
        echo '';
        exit();
    }
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__uploads` WHERE url LIKE '{$_SESSION['bigfile_info'][$id]}';");
    @unlink($cfg_basedir.$_SESSION['bigfile_info'][$id]);
    $_SESSION['file_info'][$id] = '';
    $_SESSION['bigfile_info'][$id] = '';
    echo Lang("deleted");
    exit();
}
//获取本地图片的缩略预览图
else if ($dopost == 'ddimg') {
    //生成缩略图
    ob_start();
    if (!preg_match("/^(http[s]?:\/\/)?([^\/]+)/i", $img)) $img = $cfg_basedir.$img;
    ImageResizeNew($img, $cfg_ddimg_width, $cfg_ddimg_height, '', false);
    $imagevariable = ob_get_contents();
    ob_end_clean();
    header('Content-type: image/jpeg');
    header('Content-Length: '.strlen($imagevariable));
    echo $imagevariable;
    exit();
}
//删除指定的图片(编辑图集时用)
else if ($dopost == 'delold') {
    $imgfile = $cfg_basedir.$picfile;
    if (!file_exists($imgfile) && !is_dir($imgfile) && preg_match("#^".$cfg_medias_dir."#", $imgfile)) {
        @unlink($imgfile);
    }
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__uploads` WHERE url LIKE '{$picfile}';");
    echo Lang("deleted");
    exit();
}
?>