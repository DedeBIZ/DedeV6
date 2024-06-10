<?php
/**
 * 文件管理器查看
 *
 * @version        $id:file_manage_view.php 8:48 2010年7月13日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('plus_文件管理器');
require_once(DEDEINC."/libraries/oxwindow.class.php");
$activepath = str_replace("..", "", $activepath);
$activepath = preg_replace("#^\/{1,}#", "/", $activepath);
if ($activepath == "/") $activepath = '';
if ($activepath == "") $inpath = $cfg_basedir;
else $inpath = $cfg_basedir.$activepath;
//修改文件名
if ($fmdo == "rename") {
    if ($activepath == "") $ndirstring = "根目录";
    $ndirstring = $activepath;
    $wintitle = "修改指定文件名称";
    $win = new OxWindow();
    $win->Init("file_manage_control.php", "/static/web/js/admin.blank.js", "POST");
    $win->AddHidden("fmdo", $fmdo);
    $win->AddHidden("activepath", $activepath);
    $win->AddHidden("filename", $filename);
    $win->AddTitle("修改文件名，当前路径：$ndirstring");
    $win->AddItem("旧名称：", "<input type='input' name='oldfilename' id='oldfilename' class='admin-input-md' value='$filename'>");
    $win->AddItem("新名称：", "<input type='input' name='newfilename' id='newfilename' class='admin-input-md'>");
    $winform = $win->GetWindow("ok");
    $win->Display();
}
//新建目录
else if ($fmdo == "newdir") {
    if ($activepath == "") $activepathname = "根目录";
    else $activepathname = $activepath;
    $wintitle = "新建文件目录";
    $win = new OxWindow();
    $win->Init("file_manage_control.php", "/static/web/js/admin.blank.js", "POST");
    $win->AddHidden("fmdo", $fmdo);
    $win->AddHidden("activepath", $activepath);
    $win->AddHidden("token", make_hash());
    $win->AddTitle("当前目录 $activepathname ");
    $win->AddItem("新目录：", "<input type='input' name='newpath' id='newpath' class='admin-input-md'>");
    $winform = $win->GetWindow("ok");
    $win->Display();
}
//移动文件
else if ($fmdo == "move") {
    $wintitle = "移动指定文件";
    $win = new OxWindow();
    $win->Init("file_manage_control.php", "/static/web/js/admin.blank.js", "POST");
    $win->AddHidden("fmdo", $fmdo);
    $win->AddHidden("activepath", $activepath);
    $win->AddHidden("filename", $filename);
    $win->AddTitle("新位置前面不加斜杆/表示相对于当前位置，加斜杆/表示相对于根目录");
    $win->AddItem("被移动文件：", $filename);
    $win->AddItem("当前位置：", $activepath);
    $win->AddItem("新位置：", "<input type='input' name='newpath' id='newpath' class='admin-input-md'>");
    $winform = $win->GetWindow("ok");
    $win->Display();
}
//删除文件
else if ($fmdo == "del") {
    $wintitle = "删除指定文件";
    $win = new OxWindow();
    $win->Init("file_manage_control.php", "/static/web/js/admin.blank.js", "POST");
    $win->AddHidden("fmdo", $fmdo);
    $win->AddHidden("activepath", $activepath);
    $win->AddHidden("filename", $filename);
    if (@is_dir($cfg_basedir.$activepath."/$filename")) {
        $msg = "<tr><td>您确定要删除".$filename."目录吗</td></tr>";
    } else {
        $msg = "<tr><td>您确定要删除".$filename."文件吗</td></tr>";
    }
    $win->AddTitle("删除文件确认");
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("ok");
    $win->Display();
}
//修改文件
else if ($fmdo == "edit") {
    if (DEDEBIZ_SAFE_MODE) {
        die(DedeAlert("系统已启用安全模式，无法使用当前功能",ALERT_DANGER));
    }
    if (!isset($backurl)) {
        $backurl = '';
    }
    $activepath = str_replace("..", "", $activepath);
    $filename = str_replace("..", "", $filename);
    $file = "$cfg_basedir$activepath/$filename";
    $content = '';
    if (is_file($file)) {
        $fp = fopen($file, "r");
        $content = fread($fp, filesize($file));
        fclose($fp);
        $content = dede_htmlspecialchars($content);
    }
    $contentView = "<textarea name='str' id='str' class='admin-textarea-xl'>$content</textarea>\r\n";
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
    $content = '';
    $GLOBALS['filename'] = "newfile.txt";
    $GLOBALS['extension'] = 'text/html';
    $contentView = "<textarea id='str' name='str' class='admin-textarea-xl'></textarea>\r\n";
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