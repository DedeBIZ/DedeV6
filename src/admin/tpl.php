<?php
/**
 * 文件管理器
 *
 * @version        $Id: tpl.php 1 23:44 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert(Lang("err_safemode_check"),ALERT_DANGER));
}
UserLogin::CheckPurview('plus_文件管理器');
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
//编辑模板
if ($action == 'edit' || $action == 'newfile') {
    if ($filename == '' && $action == 'edit') {
        ShowMsg(Lang('tpl_err_edit'), '-1');
        exit();
    }
    if (!file_exists($templetdird.'/'.$filename)  && $action == 'edit') {
        $action = 'newfile';
    }
    //读取文件内容
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
        $helpContent = fread($fp, filesize($tagHelpDir.$tag.'.txt'));
        fclose($fp);
        $helps[$tag] = explode('>>dede>>', $helpContent);
    }

    make_hash();
    include DEDEADMIN.'/templets/tpl_edit.htm';
    exit();
}
//保存编辑模板
else if ($action == 'saveedit') {
    CheckCSRF();
    if ($filename == '') {
        ShowMsg(Lang('tpl_err_saveedit'), '-1');
        exit();
    }
    if (!preg_match("#\.htm$#", $filename)) {
        ShowMsg(Lang('tpl_err_saveedit_ext'), '-1');
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
    ShowMsg(Lang('tpl_success_saveedit'), 'templets_main.php?acdir='.$acdir);
    exit();
}
//删除模板
else if ($action == 'del') {
    $truefile = $templetdird.'/'.$filename;
    if (unlink($truefile)) {
        ShowMsg(Lang('tpl_success_del'), 'templets_main.php?acdir='.$acdir);
        exit();
    } else {
        ShowMsg(Lang('tpl_err_del'), '-1');
        exit();
    }
}
//上传新模板
else if ($action == 'upload') {
    $acdir = str_replace('.', '', $acdir);
    make_hash();
    $wecome_info = "<a href='templets_main.php'>".Lang('tpl_main')."</a> &gt; ".Lang('tpl_upload');
    $msg = "
    <table width='600' cellspacing='0' cellpadding='0'>
  <tr>
    <td width='96' height='60'>".Lang('tpl_upload_select')."</td>
    <td width='504'>
        <input name='acdir' type='hidden' value='$acdir' />
        <input name='token' type='hidden' value='{$_SESSION['token']}' />
        <input name='upfile' type='file' id='upfile' style='width:390px' />
      </td>
  </tr>
 </table>
    ";
    DedeWin::Instance()->Init("tpl.php", "js/blank.js", "POST' enctype='multipart/form-data' ")
    ->AddTitle(Lang('tpl_upload_title'))
    ->AddHidden("action", 'uploadok')->AddMsgItem("<div>$msg</div>")
    ->GetWindow('ok', '')->Display();
    exit();
}
//上传新模板
else if ($action == 'uploadok') {
    CheckCSRF();
    if (!is_uploaded_file($upfile)) {
        ShowMsg(Lang("tpl_upload_empty"), "javascript:;");
        exit();
    } else {
        if (!preg_match("#\.htm$#", $upfile_name)) {
            ShowMsg(Lang('tpl_err_saveedit_ext'), "-1");
            exit();
        }
        if (preg_match("#[\\\\\/]#", $upfile_name)) {
            ShowMsg(Lang("tpl_upload_err_charset"), "-1");
            exit();
        }
        move_uploaded_file($upfile, $templetdird.'/'.$upfile_name);
        @unlink($upfile);
        ShowMsg(Lang("tpl_upload_success"), "templets_main.php?acdir=$acdir");
        exit();
    }
    exit();
}
//修改标签碎片
else if ($action == 'edittag' || $action == 'addnewtag') {
    if ($action == 'addnewtag') {
        $democode = '<'."?php
if (!defined('DEDEINC'))
{
    exit(\"Request Error!\");
}
function lib_demotag(&\$ctag,&\$refObj)
{
    global \$dsql,\$envs;
    //".Lang('templets_tagsource_attr')."
    \$attlist=\"row|12,titlelen|24\";
    FillAttsDefault(\$ctag->CAttribute->Items,\$attlist);
    extract(\$ctag->CAttribute->Items, EXTR_SKIP);
    \$revalue = '';
    //".Lang('templets_tagsource_code_tip')."
    \$revalue = 'Hello Word!';
    return \$revalue;
}
?".'>';
        $filename = "demotag.lib.php";
        $title = Lang("templets_tagsource_add");
    } else {
        if (!preg_match("#^[a-z0-9_-]{1,}\.lib\.php$#i", $filename)) {
            ShowMsg(Lang('templets_tagsource_notallow'), '-1');
            exit();
        }
        $fp = fopen(DEDEINC.'/taglib/'.$filename, 'r');
        $democode = fread($fp, filesize(DEDEINC.'/taglib/'.$filename));
        fclose($fp);
        $title = Lang("templets_tagsource_edit");
    }
    make_hash();
    include DEDEADMIN.'/templets/tpl_edit_tag.htm';
    exit();
}
//保存标签碎片修改
else if ($action == 'savetagfile') {
    CheckCSRF();
    if (!preg_match("#^[a-z0-9_-]{1,}\.lib\.php$#i", $filename)) {
        ShowMsg(Lang('tpl_upload_err_filename'), '-1');
        exit();
    }
    $tagname = preg_replace("#\.lib\.php$#i", "", $filename);
    $content = stripslashes($content);
    $truefile = DEDEINC.'/taglib/'.$filename;
    $fp = fopen($truefile, 'w');
    fwrite($fp, $content);
    fclose($fp);
    $msg = "
    <form name='form1' action='tag_test_action.php' target='blank' method='post'>
      <input type='hidden' name='dopost' value='make' />
        ".Lang('tpl_upload_savetagfile')."<br>
        <textarea name='partcode' cols='150' rows='6' style='width:90%;'>{dede:{$tagname} }{/dede:{$tagname}}</textarea><br>
        <button type='submit' name='B1' class='btn btn-success btn-sm'>".Lang('ok')."</button>
    </form>
    ";
    $wintitle = Lang("tpl_savetagfile_title");
    $wecome_info = "<a href='templets_tagsource.php'>".Lang('tpl_templets_tagsource')."</a> &gt; ".Lang('tpl_templets_new');
    DedeWin::Instance()->AddTitle(Lang('tpl_templets_new')."：")->AddMsgItem($msg)->GetWindow("hand", "&nbsp;", false)->Display();
    exit();
}
?>