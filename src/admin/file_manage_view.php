<?php
/**
 * 文件查看
 *
 * @version        $Id: file_manage_view.php 1 8:48 2010年7月13日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
use DedeBIZ\Template\DedeTagParse;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('plus_文件管理器');
$activepath = str_replace("..", "", $activepath);
$activepath = preg_replace("#^\/{1,}#", "/", $activepath);
if ($activepath == "/") $activepath = "";
if ($activepath == "") $inpath = $cfg_basedir;
else $inpath = $cfg_basedir.$activepath;
//显示控制层
if ($fmdo == "rename") {
    if ($activepath == "") $ndirstring = Lang("root_directory");
    $ndirstring = $activepath;
    $wintitle = Lang("file_manage");
    $wecome_info = Lang("file_manage")."::".Lang('file_rename')." [<a href='file_manage_main.php?activepath=$activepath'>".Lang("file_manage")."</a>]</a>";
    DedeWin::Instance()->Init("file_manage_control.php", "js/blank.js", "POST")
    ->AddHidden("fmdo", $fmdo)
    ->AddHidden("activepath", $activepath)
    ->AddHidden("filename", $filename)
    ->AddTitle(Lang("file_rename_title",array('ndirstring'=>$ndirstring)))
    ->AddItem(Lang("file_rename_oldname"), "<input name='oldfilename' type='input' id='oldfilename' size='40' value='$filename'>")
    ->AddItem(Lang("file_rename_newname"), "<input name='newfilename' type='input' size='40' id='newfilename'>")
    ->GetWindow("ok")->Display();
}
//新建目录
else if ($fmdo == "newdir") {
    if ($activepath == "") $activepathname = Lang("root_directory");
    else $activepathname = $activepath;
    $wintitle = Lang("file_manage");
    $wecome_info = Lang("file_manage")."::".Lang('file_rename_newdir')." [<a href='file_manage_main.php?activepath=$activepath'>".Lang("file_manage")."</a>]</a>";
    DedeWin::Instance()->Init("file_manage_control.php", "js/blank.js", "POST")
    ->AddHidden("fmdo", $fmdo)
    ->AddHidden("activepath", $activepath)
    ->AddHidden("token", make_hash())
    ->AddTitle(Lang("file_rename_newdir_title",array('activepathname'=>$activepathname)))
    ->AddItem(Lang('new_directory')."：", "<input name='newpath' type='input' id='newpath'>")
    ->GetWindow("ok")
    ->Display();
}
//移动文件
else if ($fmdo == "move") {
    $wintitle = Lang("file_manage");
    $wecome_info = Lang("file_manage")."::".Lang('file_rename_move')." [<a href='file_manage_main.php?activepath=$activepath'>".Lang("file_manage")."</a>]</a>";
    DedeWin::Instance()->Init("file_manage_control.php", "js/blank.js", "POST")
    ->AddHidden("fmdo", $fmdo)
    ->AddHidden("activepath", $activepath)
    ->AddHidden("filename", $filename)
    ->AddTitle(Lang("file_rename_move_title"))
    ->AddItem(Lang("file_rename_move_src"), $filename)
    ->AddItem(Lang("file_rename_move_curr"), $activepath)
    ->AddItem(Lang("file_rename_move_new"), "<input name='newpath' type='input' id='newpath' size='40'>")
    ->GetWindow("ok")
    ->Display();
}
//删除文件
else if ($fmdo == "del") {
    $wintitle = Lang("file_manage");
    $wecome_info = Lang("file_manage")."::".Lang('file_rename_del')." [<a href='file_manage_main.php?activepath=$activepath'>".Lang("file_manage")."</a>]</a>";
    $wmsg = Lang('content_delete_confirm',array('qstr'=>$filename));
    DedeWin::Instance()->Init("file_manage_control.php", "js/blank.js", "POST")
    ->AddHidden("fmdo", $fmdo)
    ->AddHidden("activepath", $activepath)
    ->AddHidden("filename", $filename)
    ->AddTitle(Lang("file_rename_del_title"))
    ->AddMsgItem($wmsg, "50")
    ->GetWindow("ok")
    ->Display();
}
//编辑文件
else if ($fmdo == "edit") {
    if (!isset($backurl)) {
        $backurl = "";
    }
    $activepath = str_replace("..", "", $activepath);
    $filename = str_replace("..", "", $filename);
    $file = "$cfg_basedir$activepath/$filename";
    $content = "";
    if (is_file($file)) {
        $fp = fopen($file, "r");
        $content = fread($fp, filesize($file));
        fclose($fp);
        $content = dede_htmlspecialchars($content);
    }
    $contentView = "<textarea name='str' id='str' style='width:98%;height:450px;background:#ffffff;'>$content</textarea>\r\n";
    $GLOBALS['filename'] = $filename;
    $path_parts  = pathinfo($filename);
    if ($path_parts['extension'] == 'php') {
        $GLOBALS['extension'] = 'text/x-php';
    } else if ($path_parts['extension'] == 'js') {
        $GLOBALS['extension'] = 'text/javascript';
    } else if ($path_parts['extension'] == 'css') {
        $GLOBALS['extension'] = 'text/css';
    } else {
        $GLOBALS['extension'] = 'text/html';
    }
    $ctp = new DedeTagParse();
    $ctp->LoadTemplate(DEDEADMIN."/templets/file_edit.htm");
    $ctp->display();
}
//新建文件
else if ($fmdo == "newfile") {
    $content = "";
    $GLOBALS['filename'] = "newfile.txt";
    $GLOBALS['extension'] = 'text/html';
    $contentView = "<textarea id='str' name='str' style='width:98%;height:400'></textarea>\r\n";
    $GLOBALS['token'] = make_hash();
    $ctp = new DedeTagParse();
    $ctp->LoadTemplate(DEDEADMIN."/templets/file_edit.htm");
    $ctp->display();
}
//上传文件
else if ($fmdo == "upload") {
    $ctp = new DedeTagParse();
    $ctp->LoadTemplate(DEDEADMIN."/templets/file_upload.htm");
    $ctp->display();
}
?>