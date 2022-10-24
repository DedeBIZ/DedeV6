<?php
/**
 * 文件管理控制
 *
 * @version        $Id: file_manage_control.php 1 8:48 2010年7月13日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
require(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('plus_文件管理器');
require_once(DEDEADMIN.'/file_class.php');
$activepath = str_replace("..", "", $activepath);
$activepath = preg_replace("#^\/{1,}#", "/", $activepath);
if ($activepath == "/") $activepath = "";
if ($activepath == "") $inpath = $cfg_basedir;
else $inpath = $cfg_basedir.$activepath;
//显示控制层
$fmm = new FileManagement();
$fmm->Init();
if ($fmdo == "rename") {
    $fmm->RenameFile($oldfilename, $newfilename);
}
//新建目录
else if ($fmdo == "newdir") {
    CheckCSRF();
    $fmm->NewDir($newpath);
}
//移动文件
else if ($fmdo == "move") {
    $fmm->MoveFile($filename, $newpath);
}
//删除文件
else if ($fmdo == "del") {
    $fmm->DeleteFile($filename);
}
//文件编辑
else if ($fmdo == "edit") {
    CheckCSRF();
    $filename = str_replace("..", "", $filename);
    if (preg_match('#\.(php|pl|cgi|asp|aspx|jsp|php5|php4|php3|shtm|shtml)$#i', trim($filename))) {
        ShowMsg(Lang("media_ext_forbidden"), "javascript:;");
        exit();
    }
    $file = "$cfg_basedir$activepath/$filename";
    $str = stripslashes($str);
    $fp = fopen($file, "w");
    fputs($fp, $str);
    fclose($fp);
    if (empty($backurl)) {
        ShowMsg(Lang("file_success_edit_one"), "file_manage_main.php?activepath=$activepath");
    } else {
        ShowMsg(Lang("file_success_edit"), $backurl);
    }
    exit();
}
/*
文件编辑，可视化模式
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
                ShowMsg(Lang("media_no_fileinfo"), -1);
                exit;
            }
            if (!preg_match("#^(image|video|audio|application)#i", $mime)) {
                ShowMsg(Lang("media_only_media"), -1);
                exit;
            }
            if (!file_exists($cfg_basedir.$activepath."/".$upfile_name)) {
                move_uploaded_file($upfile, $cfg_basedir.$activepath."/".$upfile_name);
            }
            @unlink($upfile);
            $j++;
        }
    }
    ShowMsg(Lang('file_success_upload',array('j'=>$j,'activepath'=>$activepath)), "file_manage_main.php?activepath=$activepath");
    exit();
}
//空间检查
else if ($fmdo == "space") {
    if ($activepath == "") {
        $ecpath = Lang("file_alldir");
    } else {
        $ecpath = $activepath;
    }
    $titleinfo = Lang('dir')."[<a href='file_manage_main.php?activepath=$activepath'>$ecpath</a>]".Lang('file_spaceinfo')."：<br>";
    $wintitle = Lang("file_manage");
    $wecome_info = Lang('file_manage')."::".Lang('file_sizecheck')." [<a href='file_manage_main.php?activepath=$activepath'>".Lang('file_manage')."</a>]</a>";
    $activepath = $cfg_basedir.$activepath;
    $space = new SpaceUse;
    $space->checksize($activepath);
    $total = $space->totalsize;
    $totalkb = $space->setkb($total);
    $totalmb = $space->setmb($total);
    DedeWin::Instance()->Init("", "js/blank.js", "POST")->AddTitle($titleinfo)
    ->AddMsgItem("$totalmb M<br>$totalkb KB<br>$total ".Lang('byte'))
    ->GetWindow("")->Display();
}
?>