<?php
/**
 * 文件管理器操作
 *
 * @version        $id:file_manage_control.php 8:48 2010年7月13日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__)."/config.php");
CheckPurview('plus_文件管理器');
require(DEDEINC."/libraries/oxwindow.class.php");
require_once(DEDEADMIN.'/file_class.php');
$activepath = str_replace("..", "", $activepath);
$activepath = preg_replace("#^\/{1,}#", "/", $activepath);
if ($activepath == "/") $activepath = '';
if ($activepath == "") $inpath = $cfg_basedir;
else $inpath = $cfg_basedir.$activepath;
if (DEDEBIZ_SAFE_MODE && !preg_match("#^/static#",$activepath)) {
    ShowMsg("安全模式下仅允许查看修改static目录文档", -1);
    exit;
}
$files = json_decode(file_get_contents(DEDEDATA.'/admin/files.txt'));
$currentFolder = basename(__DIR__);
$realFiles = array();
foreach ($files as $ff) {
    $rfi = preg_replace("#^admin/#",$currentFolder.'/',$ff->filename);
    $realFiles[] = $rfi;
}
function realdir($path) {
    return dirname(realpath($path));
}

//文件管理器交互与逻辑控制文件
$fmm = new FileManagement();
$fmm->Init();
if ($fmdo == "rename") {
    $f = str_replace("..", "", $oldfilename);
    $f = $cfg_basedir.$activepath."/$oldfilename";
    if (!file_exists(dirname(__FILE__).'/../license.txt')) {
        ShowMsg("许可协议不存在，无法重名文件", "javascript:;");
        exit();
    }
    $f = str_replace(realdir(dirname(__FILE__).'/../license.txt').'/', "", $f);
    if (in_array($f,$realFiles)) {
        ShowMsg("系统文件禁止重名", "javascript:;");
        exit();
    }
    $oldfilename = str_replace("..","",$oldfilename);
    $newfilename = str_replace("..","",$newfilename);
    $fmm->RenameFile($oldfilename, $newfilename);
}
//新建目录
else if ($fmdo == "newdir") {
    CheckCSRF();
    $fmm->NewDir($newpath);
}
//移动文件
else if ($fmdo == "move") {
    $f = str_replace("..", "", $filename);
    $f = $cfg_basedir.$activepath."/$filename";
    if (!file_exists(dirname(__FILE__).'/../license.txt')) {
        ShowMsg("许可协议不存在，无法移动文件", "javascript:;");
        exit();
    }
    $f = str_replace(realdir(dirname(__FILE__).'/../license.txt').'/', "", $f);
    if (in_array($f,$realFiles)) {
        ShowMsg("系统文件禁止移动", "javascript:;");
        exit();
    }
    $fmm->MoveFile($filename, $newpath);
}
//删除文件
else if ($fmdo == "del") {
    $f = str_replace("..", "", $filename);
    $f = $cfg_basedir.$activepath."/$filename";
    if (!file_exists(dirname(__FILE__).'/../license.txt')) {
        ShowMsg("许可协议不存在，无法删除", "javascript:;");
        exit();
    }
    $f = str_replace(realdir(dirname(__FILE__).'/../license.txt').'/', "", $f);
    if (in_array($f,$realFiles)) {
        ShowMsg("系统文件禁止删除", "javascript:;");
        exit();
    }
    $fmm->DeleteFile($filename);
}
//文件修改
else if ($fmdo == "edit") {
    CheckCSRF();
    $filename = str_replace("..", "", $filename);
    if (preg_match('#\.(php|pl|cgi|asp|aspx|jsp|php5|php4|php3|shtm|shtml|htm)$#i', trim($filename)) || preg_match('#\.[\x00-\x1F\x7F]*$#', trim($filename))) {
        ShowMsg("文件扩展名已被系统禁止", "javascript:;");
        exit();
    }
    $file = "$cfg_basedir$activepath/$filename";
    if (in_array($file,$realFiles)) {
        ShowMsg("系统文件禁止编辑", "javascript:;");
        exit();
    }
    $str = stripslashes($str);
    $fp = fopen($file, "w");
    fputs($fp, $str);
    fclose($fp);
    if (empty($backurl)) {
        ShowMsg("成功保存一个文件", "file_manage_main.php?activepath=$activepath");
    } else {
        ShowMsg("成功保存一个文件", $backurl);
    }
    exit();
}
//文件上传
else if ($fmdo == "upload") {
    $j = 0;
    for ($i = 1; $i <= 50; $i++) {
        $upfile = "upfile".$i;
        $upfile_name = "upfile".$i."_name";
        if (!isset(${$upfile}) || !isset(${$upfile_name})) {
            continue;
        }
        $upfile = ${$upfile};
        $upfile_name = ${$upfile_name};
        if (preg_match('#\.(php|pl|cgi|asp|aspx|jsp|php5|php4|php3|shtm|shtml|htm)$#i', trim($upfile_name))) {
            ShowMsg("文件扩展名已被系统禁止", "javascript:;");
            exit();
        }
        if (is_uploaded_file($upfile)) {
            //检查文件类型
            $mime = get_mime_type($upfile);
            if (preg_match("#^unknow#", $mime)) {
                ShowMsg("系统不支持fileinfo组件，建议php.ini中开启", -1);
                exit;
            }
            if (!preg_match("#^(image|video|audio|application)#i", $mime)) {
                ShowMsg("仅支持媒体文件及应用程序上传", -1);
                exit;
            }
            if (!file_exists($cfg_basedir.$activepath."/".$upfile_name)) {
                move_uploaded_file($upfile, $cfg_basedir.$activepath."/".$upfile_name);
            }
            @unlink($upfile);
            $j++;
        }
    }
    ShowMsg("成功上传".$j."个文件到".$activepath."", "file_manage_main.php?activepath=$activepath");
    exit();
}
//空间检查
else if ($fmdo == "space") {
    if ($activepath == "") {
        $ecpath = "所有目录";
    } else {
        $ecpath = $activepath;
    }
    $wintitle = "指定空间检查大小";
    $activepath = $cfg_basedir.$activepath;
    $space = new SpaceUse;
    $space->checksize($activepath);
    $total = $space->totalsize;
    $totalkb = $space->setkb($total);
    $totalmb = $space->setmb($total);
    $win = new OxWindow();
    $win->Init("", "/static/web/js/admin.blank.js", "POST");
    $win->AddMsgItem("<tr>
        <td>
            <span>$totalkb</span>KB<br>
            <span>$totalmb</span>M<br>
            <span>$total</span>字节
        </td>
    </tr>
    <tr>
        <td align='center'><button type='button' class='btn btn-success btn-sm' onclick=\"location='file_manage_main.php';\">文件管理器</button></td>
    </tr>");
    $winform = $win->GetWindow("");
    $win->Display();
}
?>