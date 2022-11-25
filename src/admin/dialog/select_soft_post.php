<?php
/**
 * 软件发送
 *
 * @version        $id:select_soft_post.php 9:43 2010年7月8日 tianya $
 * @package        DedeBIZ.Dialog
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
if (!isset($cfg_basedir)) {
    include_once(dirname(__FILE__).'/config.php');
}
if (empty($uploadfile)) $uploadfile = '';
if (empty($uploadmbtype)) $uploadmbtype = '软件类型';
if (empty($bkurl)) $bkurl = 'select_soft.php';
$CKEditorFuncNum = (isset($CKEditorFuncNum)) ? $CKEditorFuncNum : 1;
$newname = (empty($newname) ? '' : preg_replace("#[\\ \"\*\?\t\r\n<>':\/|]#", "", $newname));
$uploadfile = isset($imgfile) && empty($uploadfile) ? $imgfile : $uploadfile;
$uploadfile_name = isset($imgfile_name) && empty($uploadfile_name) ? $imgfile_name : $uploadfile_name;
if (!is_uploaded_file($uploadfile)) {
    ShowMsg("您没有选择上传的文件或上传的文件大小被限制", "-1");
    exit();
}
//软件类型所有支持的附件
$cfg_softtype = $cfg_softtype;
$cfg_softtype = str_replace('||', '|', $cfg_softtype);
$uploadfile_name = trim(preg_replace("#[ \r\n\t\*\%\\\/\?><\|\":]{1,}#", '', $uploadfile_name));
if (!preg_match("#\.(".$cfg_softtype.")#i", $uploadfile_name)) {
    ShowMsg("您所上传的<span class='text-primary'>{$uploadmbtype}</span>不在许可列表", "-1");
    exit();
}
$nowtme = time();
if ($activepath == $cfg_soft_dir) {
    $newdir = MyDate($cfg_addon_savetype, $nowtme);
    $activepath = $activepath.'/'.$newdir;
    if (!is_dir($cfg_basedir.$activepath)) {
        MkdirAll($cfg_basedir.$activepath, $cfg_dir_purview);
        CloseFtp();
    }
}
//文件名前为手工指定，后者自动处理
if (!empty($newname)) {
    $filename = $newname;
    if (!preg_match("#\.#", $filename)) $fs = explode('.', $uploadfile_name);
    else $fs = explode('.', $filename);
    if (preg_match("#".$cfg_not_allowall."#", $fs[count($fs) - 1])) {
        ShowMsg("指定的文件名已被系统禁止", "javascript:;");
        exit();
    }
    if (!preg_match("#\.#", $filename)) $filename = $filename.'.'.$fs[count($fs) - 1];
} else {
    $filename = $cuserLogin->getUserID().'-'.dd2char(MyDate('ymdHis', $nowtme));
    $fs = explode('.', $uploadfile_name);
    if (preg_match("#".$cfg_not_allowall."#", $fs[count($fs) - 1])) {
        ShowMsg("您上传的文件可能存在不安全因素，系统拒绝操作", "-1");
        exit();
    }
    $filename = $filename.'.'.$fs[count($fs) - 1];
}
if (preg_match('#\.(php|pl|cgi|asp|aspx|jsp|php5|php4|php3|shtm|shtml)$#i', trim($filename))) {
    ShowMsg("指定的文件名已被系统禁止", "javascript:;");
    exit();
}
$fullfilename = $cfg_basedir.$activepath.'/'.$filename;
$fullfileurl = $activepath.'/'.$filename;
$mime = get_mime_type($uploadfile);
if (preg_match("#^unknow#", $mime)) {
    ShowMsg("系统不支持fileinfo组件，建议php.ini中开启", -1);
    exit;
}
if (!preg_match("#^(image|video|audio|application)#i", $mime)) {
    ShowMsg("仅支持媒体文件及应用程序上传", -1);
    exit;
}
move_uploaded_file($uploadfile, $fullfilename) or die("上传文件到<span class='text-primary'>$fullfilename</span>失败");
@unlink($uploadfile);
if ($uploadfile_type == 'application/x-shockwave-flash') {
    $mediatype = 2;
} else if (preg_match('#image#i', $uploadfile_type)) {
    $mediatype = 1;
} else if (preg_match('#audio|media|video#i', $uploadfile_type)) {
    $mediatype = 3;
} else {
    $mediatype = 4;
}
$inquery = "INSERT INTO `#@__uploads` (arcid,title,url,mediatype,width,height,playtime,filesize,uptime,mid) VALUES ('0','$filename','$fullfileurl','$mediatype','0','0','0','{$uploadfile_size}','{$nowtme}','".$cuserLogin->getUserID()."'); ";
$dsql->ExecuteNoneQuery($inquery);
$fid = $dsql->GetLastID();
AddMyAddon($fid, $fullfileurl);
if ($ck == 1) {
    $funcNum = isset($_GET['CKEditorFuncNum']) ? $_GET['CKEditorFuncNum'] : 1;
    $url = $fullfileurl;
    $arr = array(
        "uploaded" => 1,
        "fileName" => $filename,
        "url" => $url,
    );
    echo json_encode($arr);
} else {
    ShowMsg("成功上传文件", $bkurl."?comeback=".urlencode($filename)."&f=$f&CKEditorFuncNum=$CKEditorFuncNum&activepath=".urlencode($activepath)."&d=".time());
    exit();
}
?>