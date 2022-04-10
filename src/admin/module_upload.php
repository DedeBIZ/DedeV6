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
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_module');
require_once(DEDEINC."/dedemodule.class.php");
require_once(DEDEINC."/libraries/oxwindow.class.php");
if (empty($action)) $action = '';
$mdir = DEDEDATA.'/module';
if ($action == 'upload') {
    if (!is_uploaded_file($upfile)) {
        ShowMsg("您什么都没有上传", "javascript:;");
        exit();
    } else {
        include_once(DEDEINC."/libraries/zip.class.php");
        $tmpfilename = $mdir.'/'.ExecTime().mt_rand(10000, 50000).'.tmp';
        move_uploaded_file($upfile, $tmpfilename) or die("把上传的文件移动到 {$tmpfilename} 时失败，请检查 {$mdir} 目录是否有写入权限");
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
                ShowMsg("对不起，您上传的压缩包中不存在dede模块文件<br><a href='javascript:history.go(-1);'>重新上传</a>", "javascript:;");
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
            ShowMsg("对不起，您上传的文件可能不是织梦模块的标准格式文件<br><a href='javascript:history.go(-1);'>重新上传</a>", "javascript:;");
            exit();
        }
        if (preg_match("#[^0-9a-zA-Z]#", $infos['hash'])) {
            exit("hash check failed!");
        }
        $okfile = $mdir.'/'.$infos['hash'].'.xml';
        if ($dm->HasModule($infos['hash']) && empty($delhas)) {
            unlink($tmpfilename);
            $dm->Clear();
            ShowMsg("对不起，您上传的模块已经存在<br>如果要覆盖请先删除原来版本或选择强制删除的选项<br><a href='javascript:history.go(-1);'>重新上传</a>", "javascript:;");
            exit();
        }
        @unlink($okfile);
        copy($tmpfilename, $okfile);
        @unlink($tmpfilename);
        $dm->Clear();
        ShowMsg("成功上传一个新的模块", "module_main.php?action=view&hash={$infos['hash']}");
        exit();
    }
} else {
    $win = new OxWindow();
    $win->Init("module_upload.php", "js/blank.js", "POST' enctype='multipart/form-data");
    $win->mainTitle = "模块管理";
    $wecome_info = "<a href='module_main.php'>模块管理</a> &gt; 上传模块";
    $win->AddTitle('请选择要上传的文件');
    $win->AddHidden("action", 'upload');
    $msg = "<table width='600' border='0' cellspacing='0' cellpadding='0'>
  <tr>
    <td height='26'>文件格式：</td>
    <td>
      <label><input name='filetype' type='radio' value='0' checked='checked' /> 正常的模块包</label>
      <label><input type='radio' name='filetype' value='1' /> 经过 zip 压缩的模块包</label></td>
  </tr>
  <tr>
    <td height='26'>已有模块：</td>
    <td><label><input name='delhas' type='checkbox' id='delhas' value='1' /> 强制删除同名模块(这可能导致已经安装的模块无法卸载)</label></td>
  </tr>
  <tr>
    <td width='96' height='60'>请选择文件：</td>
    <td width='504'><input name='upfile' type='file' id='upfile' style='width:380px' /></td>
  </tr>
 </table>";
    $win->AddMsgItem("<div style='line-height:26px'>$msg</div>");
    $winform = $win->GetWindow('ok', '');
    $win->Display();
    exit();
}//ClearAllLink();