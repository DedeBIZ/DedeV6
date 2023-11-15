<?php
/**
 * 修改附件
 *
 * @version        $id:media_edit.php 11:17 2010年7月19日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
//权限检查
CheckPurview('sys_Upload,sys_MyUpload');
if (empty($dopost)) $dopost = '';
$backurl = isset($_COOKIE['ENV_GOBACK_URL']) ? $_COOKIE['ENV_GOBACK_URL'] : "javascript:history.go(-1);";
//删除附件
if ($dopost == 'del') {
    CheckPurview('sys_DelUpload');
    if (empty($ids)) {
        $ids = '';
    }
    if ($ids == "") {
        $myrow = $dsql->GetOne("SELECT url FROM `#@__uploads` WHERE aid='".$aid."'");
        $truefile = $cfg_basedir.$myrow['url'];
        $rs = 0;
        if (!file_exists($truefile) || $myrow['url'] == "") {
            $rs = 1;
        } else {
            $rs = @unlink($truefile);
        }
        if ($rs == 1) {
            $msg = "成功删除一个附件";
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__uploads` WHERE aid='".$aid."'");
        }
        ShowMsg($msg, $backurl);
        exit();
    } else {
        $ids = explode(',', $ids);
        $idquery = '';
        foreach ($ids as $aid) {
            if ($idquery == "") {
                $idquery .= " WHERE aid='$aid' ";
            } else {
                $idquery .= " OR aid='$aid' ";
            }
        }
        $dsql->SetQuery("SELECT aid,url FROM `#@__uploads` $idquery ");
        $dsql->Execute();
        while ($myrow = $dsql->GetArray()) {
            $truefile = $cfg_basedir.$myrow['url'];
            $rs = 0;
            if (!file_exists($truefile) || $myrow['url'] == "") {
                $rs = 1;
            } else {
                $rs = @unlink($truefile);
            }
            if ($rs == 1) {
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__uploads` WHERE aid='".$myrow['aid']."'");
            }
        }
        ShowMsg('成功删除选定的文件', $backurl);
        exit();
    }
}
//保存修改
else if ($dopost == 'save') {
    if ($aid == "") exit();
    CheckCSRF();
    //检查是否有修改权限
    $myrow = $dsql->GetOne("SELECT * FROM `#@__uploads` WHERE aid='".$aid."'");
    if ($myrow['mid'] != $cuserLogin->getUserID()) {
        CheckPurview('sys_Upload');
    }
    //检测文件类型
    $addquery = '';
    if (is_uploaded_file($upfile)) {
        if ($mediatype == 1) {
            $sparr = array("image/pjpeg", "image/jpeg", "image/gif", "image/png", "image/xpng", "image/wbmp");
            if (!in_array($upfile_type, $sparr)) {
                ShowMsg("您上传的不是图片类型的文件", "javascript:history.go(-1);");
                exit();
            }
        } else if ($mediatype == 2) {
            $sparr = array("application/x-shockwave-flash");
            if (!in_array($upfile_type, $sparr)) {
                ShowMsg("您上传的不是Flash类型的文件", "javascript:history.go(-1);");
                exit();
            }
        } else if ($mediatype == 3) {
            if (!preg_match('#audio|media|video#i', $upfile_type)) {
                ShowMsg("您上传的为不正确类型的影音文件", "javascript:history.go(-1);");
                exit();
            }
            if (!preg_match("#\.".$cfg_mediatype."#", $upfile_name)) {
                ShowMsg("您上传的影音文件扩展名无法被识别，请修改系统配置的参数", "javascript:history.go(-1);");
                exit();
            }
        } else {
            if (!preg_match("#\.".$cfg_softtype."#", $upfile_name)) {
                ShowMsg("您上传的附件扩展名无法被识别，请修改系统配置的参数", "javascript:history.go(-1);");
                exit();
            }
        }
        //保存文件
        $nowtime = time();
        $oldfile = $myrow['url'];
        $oldfiles = explode('/', $oldfile);
        $fullfilename = $cfg_basedir.$oldfile;
        $oldfile_path = preg_replace("#".$oldfiles[count($oldfiles) - 1]."$#", "", $oldfile);
        if (!is_dir($cfg_basedir.$oldfile_path)) {
            MkdirAll($cfg_basedir.$oldfile_path, 777);
        }
        $mime = get_mime_type($upfile);
        if (preg_match("#^unknow#", $mime)) {
            ShowMsg("系统不支持fileinfo组件，建议php.ini中开启", -1);
            exit;
        }
        if (!preg_match("#^(image|video|audio|application)#i", $mime)) {
            ShowMsg("仅支持媒体文件及应用程序上传", -1);
            exit;
        }
        @move_uploaded_file($upfile, $fullfilename);
        if ($mediatype == 1) {
            require_once(DEDEINC."/image.func.php");
            if (in_array($upfile_type, $cfg_photo_typenames)) {
                WaterImg($fullfilename, 'up');
            }
        }
        $filesize = $upfile_size;
        $imgw = 0;
        $imgh = 0;
        if ($mediatype == 1) {
            $info = '';
            $sizes[0] = 0;
            $sizes[1] = 0;
            $sizes = @getimagesize($fullfilename, $info);
            $imgw = $sizes[0];
            $imgh = $sizes[1];
        }
        if ($imgw > 0) {
            $addquery = ",width='$imgw',height='$imgh',filesize='$filesize' ";
        } else {
            $addquery = ",filesize='$filesize' ";
        }
    } else {
        $fileurl = $filename;
    }
    //写入数据库
    $query = "UPDATE `#@__uploads` SET title='$title',mediatype='$mediatype',playtime='$playtime'";
    $query .= "$addquery WHERE aid='$aid' ";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg('成功修改一则附件数据', 'media_edit.php?aid='.$aid);
    exit();
}
//读取文档信息
$myrow = $dsql->GetOne("SELECT * FROM `#@__uploads` WHERE aid='".$aid."'");
if (!is_array($myrow)) {
    ShowMsg('找不到此编号文档', 'javascript:;');
    exit();
}
include DedeInclude('templets/media_edit.htm');
?>