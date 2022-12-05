<?php
/**
 * 文件管理控制
 *
 * @version        $id:file_manage_control.php 8:48 2010年7月13日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__)."/config.php");
CheckPurview('plus_file');
require(DEDEINC."/libraries/oxwindow.class.php");
require_once(DEDEADMIN.'/file_class.php');
$activepath = str_replace("..", "", $activepath);
$activepath = preg_replace("#^\/{1,}#", "/", $activepath);
if ($activepath == "/") $activepath = "";
if ($activepath == "") $inpath = $cfg_basedir;
else $inpath = $cfg_basedir.$activepath;
//文件管理器交互与逻辑控制文件
$fmm = new FileManagement();
$fmm->Init();
/*---------------
function __rename();
----------------*/
if ($fmdo == "rename") {
    $fmm->RenameFile($oldfilename, $newfilename);
}
//新建目录
/*---------------
function __newdir();
----------------*/
else if ($fmdo == "newdir") {
    CheckCSRF();
    $fmm->NewDir($newpath);
}
//移动文件
/*---------------
function __move();
----------------*/
else if ($fmdo == "move") {
    $fmm->MoveFile($filename, $newpath);
}
//删除文件
/*---------------
function __delfile();
----------------*/
else if ($fmdo == "del") {
    $fmm->DeleteFile($filename);
}
//文件修改
/*---------------
function __saveEdit();
----------------*/
else if ($fmdo == "edit") {
    CheckCSRF();
    $filename = str_replace("..", "", $filename);
    if (preg_match('#\.(php|pl|cgi|asp|aspx|jsp|php5|php4|php3|shtm|shtml)$#i', trim($filename))) {
        ShowMsg("指定的文件名已被系统禁止", "javascript:;");
        exit();
    }
    $file = "$cfg_basedir$activepath/$filename";
    $str = stripslashes($str);
    $fp = fopen($file, "w");
    fputs($fp, $str);
    fclose($fp);
    if (empty($backurl)) {
        ShowMsg("成功保存一个文件", "file_manage_main.php?activepath=$activepath");
    } else {
        ShowMsg("成功保存文件", $backurl);
    }
    exit();
}
/*
文件修改，可视化模式
function __saveEditView();
else if ($fmdo=="editview")
{
    $filename = str_replace("..","",$filename);
    $file = "$cfg_basedir$activepath/$filename";
    $str = eregi_replace('&quot;','\\"',$str);
    $str = stripslashes($str);
    $fp = fopen($file,"w");
    fputs($fp,$str);
    fclose($fp);
    if (empty($backurl))
    {
        $backurl = "file_manage_main.php?activepath=$activepath";
    }
    ShowMsg("成功保存文件",$backurl);
    exit();
}
*/
//文件上传
/*---------------
function __upload();
----------------*/
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
    ShowMsg("成功上传<span class='text-primary'>$j</span>个文件到: $activepath", "file_manage_main.php?activepath=$activepath");
    exit();
}
//空间检查
else if ($fmdo == "space") {
    if ($activepath == "") {
        $ecpath = "所有目录";
    } else {
        $ecpath = $activepath;
    }
    $titleinfo = "目录[<a href='file_manage_main.php?activepath=$activepath'>$ecpath</a>]空间使用状况：<br>";
    $wintitle = "文件管理";
    $wecome_info = "<a href='file_manage_main.php?activepath=$activepath'>文件管理</a>::空间大小检查";
    $activepath = $cfg_basedir.$activepath;
    $space = new SpaceUse;
    $space->checksize($activepath);
    $total = $space->totalsize;
    $totalkb = $space->setkb($total);
    $totalmb = $space->setmb($total);
    $win = new OxWindow();
    $win->Init("", "js/blank.js", "POST");
    $win->AddTitle($titleinfo);
    $win->AddMsgItem("$totalmb M<br>$totalkb KB<br>$total 字节");
    $winform = $win->GetWindow("");
    $win->Display();
}
?>