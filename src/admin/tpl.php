<?php
/**
 * 新建/修改模板
 *
 * @version        $id:tpl.php 23:44 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert("系统已启用安全模式，无法使用当前功能",ALERT_DANGER));
}
CheckPurview('plus_文件管理器');
$action = isset($action) ? trim($action) : '';
if (empty($acdir)) $acdir = $cfg_df_style;
$templetdir = $cfg_basedir.$cfg_templets_dir;
$templetdird = $templetdir.'/'.$acdir;
$templeturld = $cfg_templeturl.'/'.$acdir;
if (empty($filename))    $filename = '';
$filename = preg_replace("#[\/\\\\]#", '', $filename);
if (preg_match("#\.#", $acdir)) {
    ShowMsg('Not Allow dir '.$acdir.'!', '-1');
    exit();
}
//修改模板
if ($action == 'edit' || $action == 'newfile') {
    if ($filename == '' && $action == 'edit') {
        ShowMsg('未指定要修改的模板', '-1');
        exit();
    }
    if (!file_exists($templetdird.'/'.$filename)  && $action == 'edit') {
        $action = 'newfile';
    }
    //读取文件文档
    //$content = dede_htmlspecialchars(trim(file_get_contents($truePath.$filename)));
    if ($action == 'edit') {
        $fp = fopen($templetdird.'/'.$filename, 'r');
        $content = fread($fp, filesize($templetdird.'/'.$filename));
        fclose($fp);
        $content = preg_replace("#<textarea#i", "##textarea", $content);
        $content = preg_replace("#</textarea#i", "##/textarea", $content);
        $content = preg_replace("#<form#i", "##form", $content);
        $content = preg_replace("#</form#i", "##/form", $content);
    } else {
        if (empty($filename)) $filename = 'newtpl.htm';
        $content = '';
    }
    //获取标签帮助信息
    $helps = $dtags = array();
    $tagHelpDir = DEDEINC.'/taglib/help/';
    $dir = dir($tagHelpDir);
    while (false !== ($entry = $dir->read())) {
        if ($entry != '.' && $entry != '..' && !is_dir($tagHelpDir.$entry)) {
            $dtags[] = str_replace('.txt', '', $entry);
        }
    }
    $dir->close();
    foreach ($dtags as $tag) {
        //$helpContent = file_get_contents($tagHelpDir.$tag.'.txt');
        $fp = fopen($tagHelpDir.$tag.'.txt', 'r');
        if ($fp) {
            $helpContent = fread($fp, filesize($tagHelpDir.$tag.'.txt'));
            fclose($fp);
            $helps[$tag] = explode('>>dede>>', $helpContent);
        }
    }
    make_hash();
    include DEDEADMIN.'/templets/tpl_edit.htm';
    exit();
}
//保存修改模板
else if ($action == 'saveedit') {
    CheckCSRF();
    if ($filename == '') {
        ShowMsg('未指定要修改的文件或文件名不合法', '-1');
        exit();
    }
    if (!preg_match("#\.htm$#", $filename)) {
        ShowMsg('模板只能用.htm扩展名', '-1');
        exit();
    }
    $content = stripslashes($content);
    $content = preg_replace("/##textarea/i", "<textarea", $content);
    $content = preg_replace("/##\/textarea/i", "</textarea", $content);
    $content = preg_replace("/##form/i", "<form", $content);
    $content = preg_replace("/##\/form/i", "</form", $content);
    $truefile = $templetdird.'/'.$filename;
    $fp = fopen($truefile, 'w');
    fwrite($fp, $content);
    fclose($fp);
    ShowMsg('修改或新建模板成功', 'templets_main.php?acdir='.$acdir);
    exit();
}
//删除模板
else if ($action == 'del') {
    $truefile = $templetdird.'/'.$filename;
    if (unlink($truefile)) {
        ShowMsg('删除模板成功', 'templets_main.php?acdir='.$acdir);
        exit();
    } else {
        ShowMsg('删除模板失败', '-1');
        exit();
    }
}
//上传新模板
else if ($action == 'upload') {
    require_once(DEDEINC.'/libraries/oxwindow.class.php');
    $acdir = str_replace('.', '', $acdir);
    $win = new OxWindow();
    make_hash();
    $win->Init("tpl.php", "/static/web/js/admin.blank.js", "POST' enctype='multipart/form-data' ");
    $wintitle = "上传模板";
    $win->AddTitle('请选择要上传的模块文件');
    $win->AddHidden("action", 'uploadok');
    $msg = "<tr>
            <td width='260'>选择文件</td>
            <td>
                <input name='acdir' type='hidden' value='$acdir'>
                <input name='token' type='hidden' value='{$_SESSION['token']}'>
                <input name='upfile' type='file' id='upfile' class='admin-input-lg'>
            </td>
        </tr>";
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow('ok', '');
    $win->Display();
    exit();
}
//上传新模板
else if ($action == 'uploadok') {
    CheckCSRF();
    if (!is_uploaded_file($upfile)) {
        ShowMsg("请选择上传的模板文件", "javascript:;");
        exit();
    } else {
        if (!preg_match("#\.(htm|html)$#", $upfile_name)) {
            ShowMsg("模板只能用.htm或.html扩展名", "-1");
            exit();
        }
        if (preg_match("#[\\\\\/]#", $upfile_name)) {
            ShowMsg("模板文件名有非法字符，禁止上传", "-1");
            exit();
        }
        move_uploaded_file($upfile, $templetdird.'/'.$upfile_name);
        @unlink($upfile);
        ShowMsg("成功上传一个模板", "templets_main.php?acdir=$acdir");
        exit();
    }
    exit();
}
//修改标签碎片
else if ($action == 'edittag' || $action == 'addnewtag') {
    if ($action == 'addnewtag') {
        $democode = '<'."?php
if (!defined('DEDEINC')) {
    exit(\"Request Error!\");
}
function lib_demotag(\$ctag, \$refObj)
{
    global \$dsql, \$envs;
    \$attlist = \"row|10,titlelen|30\";
    FillAttsDefault(\$ctag->CAttribute->Items,\$attlist);
    extract(\$ctag->CAttribute->Items, EXTR_SKIP);
    \$revalue = '';
    //您需编写的代码，不能用echo之类语法，把最终返回值传给\$revalue
    \$revalue = '您好，欢迎使用DedeBIZ';
    return \$revalue;
}
?".'>';
        $filename = "demotag.lib.php";
        $title = "新建标签";
    } else {
        if (!preg_match("#^[a-z0-9_-]{1,}\.lib\.php$#i", $filename)) {
            ShowMsg('文件不是标准的标签碎片文件，不允许在此修改', '-1');
            exit();
        }
        $fp = fopen(DEDEINC.'/taglib/'.$filename, 'r');
        $democode = fread($fp, filesize(DEDEINC.'/taglib/'.$filename));
        fclose($fp);
        $title = "修改标签";
    }
    make_hash();
    include DEDEADMIN.'/templets/tpl_edit_tag.htm';
    exit();
}
//保存标签碎片修改
else if ($action == 'savetagfile') {
    CheckCSRF();
    if (!preg_match("#^[a-z0-9_-]{1,}\.lib\.php$#i", $filename)) {
        ShowMsg('文件名不合法，不允许进行操作', '-1');
        exit();
    }
    require_once(DEDEINC.'/libraries/oxwindow.class.php');
    $tagname = preg_replace("#\.lib\.php$#i", "", $filename);
    $content = stripslashes($content);
    $truefile = DEDEINC.'/taglib/'.$filename;
    $fp = fopen($truefile, 'w');
    fwrite($fp, $content);
    fclose($fp);
    $msg = "<form name='form1' action='tag_test_action.php' target='blank' method='post'>
        <tr>
            <td><textarea name='partcode' class='admin-textarea-xl'>{dede:{$tagname}}{/dede:{$tagname}}</textarea></td>
        </tr>
        <tr>
            <td align='center'><button type='submit' name='B1' class='btn btn-success btn-sm'>确定</button></td>
        </tr>
    </form>";
    $wintitle = "新建修改标签";
    $win = new OxWindow();
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", false);
    $win->Display();
    exit();
}
?>