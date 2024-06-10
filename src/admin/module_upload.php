<?php
/**
 * 模块上传
 *
 * @version        $id:module_upload.php 14:43 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert("系统已启用安全模式，无法使用当前功能",ALERT_DANGER));
}
CheckPurview('sys_module');
require_once(DEDEINC."/dedemodule.class.php");
require_once(DEDEINC."/libraries/oxwindow.class.php");
if (empty($action)) $action = '';
$mdir = DEDEDATA.'/module';
if ($action == 'upload') {
    if (!is_uploaded_file($upfile)) {
        ShowMsg("请选择上传的模块插件文件", "-1");
        exit();
    } else {
        include_once(DEDEINC."/libraries/zip.class.php");
        $tmpfilename = $mdir.'/'.ExecTime().mt_rand(10000, 50000).'.tmp';
        move_uploaded_file($upfile, $tmpfilename) or die("上传文件移动到{$tmpfilename}时失败，请检查{$mdir}目录是否有写入权限");
        $dm = new DedeModule($mdir);
        $infos = $dm->GetModuleInfo($tmpfilename, 'file');
        if (empty($infos['hash'])) {
            unlink($tmpfilename);
            $dm->Clear();
            ShowMsg("您上传的插件不是正常模块格式文件", "-1");
            exit();
        }
        if (preg_match("#[^0-9a-zA-Z]#", $infos['hash'])) {
            exit("hash check failed!");
        }
        $okfile = $mdir.'/'.$infos['hash'].'.xml';
        if ($dm->HasModule($infos['hash']) && empty($delhas)) {
            unlink($tmpfilename);
            $dm->Clear();
            ShowMsg("您上传的模块已存在，请删除原模块文件或强制同名模块上传", "-1");
            exit();
        }
        @unlink($okfile);
        copy($tmpfilename, $okfile);
        @unlink($tmpfilename);
        $dm->Clear();
        ShowMsg("成功上传一个新模块", "module_main.php?action=view&hash={$infos['hash']}");
        exit();
    }
} else {
    $win = new OxWindow();
    $win->Init("module_upload.php", "/static/web/js/admin.blank.js", "POST' enctype='multipart/form-data");
    $wintitle = "上传模块插件";
    $win->AddTitle('请选择上传的模块插件文件');
    $win->AddHidden("action", 'upload');
    $msg = "<tr>
        <td width='260'>文件格式</td>
        <td><label><input type='radio' name='filetype' value='0' checked> 正常模块插件格式</label></td>
    </tr>
    <tr>
        <td>已有模块</td>
        <td><label><input type='checkbox' name='delhas' id='delhas' value='1'> 是否删除同名模块会导致已经安装模块卸载失败</label></td>
    </tr>
    <tr>
        <td>选择文件</td>
        <td><input type='file' name='upfile' id='upfile' class='admin-input-lg'></td>
    </tr>";
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow('ok', '');
    $win->Display();
    exit();
}//ClearAllLink();
?>