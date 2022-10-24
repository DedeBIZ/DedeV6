<?php
/**
 * 模块上传
 *
 * @version        $Id: module_upload.php 1 14:43 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeModule;
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\libraries\zip;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert(Lang("err_safemode_check"),ALERT_DANGER));
}
UserLogin::CheckPurview('sys_module');
if (empty($action)) $action = '';
$mdir = DEDEDATA.'/module';
if ($action == 'upload') {
    if (!is_uploaded_file($upfile)) {
        ShowMsg(Lang("tpl_upload_empty"), "javascript:;");
        exit();
    } else {
        $tmpfilename = $mdir.'/'.ExecTime().mt_rand(10000, 50000).'.tmp';
        move_uploaded_file($upfile, $tmpfilename) or die(Lang('module_upload_err_file',array('tmpfilename'=>$tmpfilename,'mdir'=>$mdir)));
        //ZIP格式的文件
        if ($filetype == 1) {
            $z = new zip();
            $files = $z->get_List($tmpfilename);
            $dedefileindex = -1;
            //为了节省资源，系统仅以.xml作为扩展名识别ZIP包里了dede模块格式文件
            if (is_array($files)) {
                for ($i = 0; $i < count($files); $i++) {
                    if (preg_match("#\.xml#i", $files[$i]['filename'])) {
                        $dedefile = $files[$i]['filename'];
                        $dedefileindex = $i;
                        break;
                    }
                }
            }
            if ($dedefileindex == -1) {
                unlink($tmpfilename);
                ShowMsg(Lang("module_upload_err_index"), "javascript:;");
                exit();
            }
            $ziptmp = $mdir.'/ziptmp';
            $z->Extract($tmpfilename, $ziptmp, $dedefileindex);
            unlink($tmpfilename);
            $tmpfilename = $mdir."/ziptmp/".$dedefile;
        }
        $dm = new DedeModule($mdir);
        $infos = $dm->GetModuleInfo($tmpfilename, 'file');
        if (empty($infos['hash'])) {
            unlink($tmpfilename);
            $dm->Clear();
            ShowMsg(Lang("module_upload_err_mfile"), "javascript:;");
            exit();
        }
        if (preg_match("#[^0-9a-zA-Z]#", $infos['hash'])) {
            exit("hash check failed!");
        }
        $okfile = $mdir.'/'.$infos['hash'].'.xml';
        if ($dm->HasModule($infos['hash']) && empty($delhas)) {
            unlink($tmpfilename);
            $dm->Clear();
            ShowMsg(Lang("module_upload_err_exists"), "javascript:;");
            exit();
        }
        @unlink($okfile);
        copy($tmpfilename, $okfile);
        @unlink($tmpfilename);
        $dm->Clear();
        ShowMsg(Lang("module_upload_success"), "module_main.php?action=view&hash={$infos['hash']}");
        exit();
    }
} else {
    $wecome_info = "<a href='module_main.php'>".Lang("module_main")."</a> &gt; ".Lang('module_upload');
    $msg = "<table width='900' cellspacing='0' cellpadding='0'>
  <tr>
    <td width='260'>".Lang('module_upload_filetype')."</td>
    <td>
      <label><input type='radio' name='filetype' value='0' checked='checked'> ".Lang('module_upload_filetype_0')."</label>
      <label><input type='radio' name='filetype' value='1'> ".Lang('module_upload_filetype_1')."</label>
    </td>
  </tr>
  <tr>
    <td>".Lang('module_upload_delhas')."</td>
    <td><label><input type='checkbox' name='delhas' id='delhas' value='1'> ".Lang('module_upload_delhas_tip')."</label></td>
  </tr>
  <tr>
    <td>".Lang('module_upload_upfile')."</td>
    <td><input name='upfile' type='file' id='upfile' style='width:390px'></td>
  </tr>
 </table>";
    DedeWin::Instance()->Init("module_upload.php", "js/blank.js", "POST' enctype='multipart/form-data")
    ->AddTitle(Lang('module_upload_title'))
    ->AddHidden("action", 'upload')
    ->AddMsgItem("<div>$msg</div>")
    ->GetWindow('ok', '')
    ->Display();
    exit();
}//ClearAllLink();
?>