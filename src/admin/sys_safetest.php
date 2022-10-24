<?php
/**
 * 安全检测
 *
 * @version        $Id: sys_safetest.php 2 9:25 2010-11-12 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeHttpDown;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__).'/config.php');
UserLogin::CheckPurview('sys_Edit');
if (empty($action)) $action = '';
if (empty($message)) $message = Lang('sys_safetest_no_testing');
if (empty($filetype)) $filetype = 'php|inc';
if (empty($info)) $info = 'eval|cmd|system|exec|_GET|_POST|_REQUEST|base64_decode';
$fileHashURL = "https://cdn.dedebiz.com/release/{$cfg_version_detail}.json";
$del = new DedeHttpDown();
$del->OpenUrl($fileHashURL);
$filelist = $del->GetJSON();
$offFiles = array();
foreach ($filelist as $key => $ff) {
    $offFiles[$ff->filename] = $ff->hash;
}
$alter = "";
if (count($offFiles) == 0) {
    $alter = DedeAlert(Lang('sys_safetest_offical'), ALERT_DANGER);
}
function TestOneFile($f)
{
    global $message, $info, $offFiles;
    $str = '';
    //排除safefile和data/tplcache目录
    if (preg_match("#data/tplcache|.svn|data/cache#", $f)) return -1;
    $fp = fopen($f, 'r');
    while (!feof($fp)) {
        $str .= fgets($fp, 1024);
    }
    fclose($fp);
    if (preg_match("#(".$info.")[ \r\n\t]{0,}([\[\(])#i", $str)) {
        $trfile = preg_replace("#^".DEDEROOT."#", '', $f);
        $oldTrfile = $trfile;
        $trfile = '/'.substr(str_replace("\\", "/", $trfile), 1);
        $localFilehash = md5_file($f);
        $remoteFilehash = isset($offFiles[$trfile]) ? $offFiles[$trfile] : '';
        if ($localFilehash === $remoteFilehash) {
            return 0;
        }
        $message .= "<div style='clear:both;'>
        <div style='width:350px;float:left'>".Lang('sys_safetest_trfile')."：{$trfile}</div>
        <a class='btn btn-success btn-sm' href='sys_safetest.php?action=viewdiff&filename=$oldTrfile' target='_blank'>".Lang('sys_safetest_viewdiff')."</a>
        <a class='btn btn-success btn-sm' href='file_manage_view.php?fmdo=del&filename=$oldTrfile&activepath=' target='_blank'>".Lang('delete')."</a>
        <a class='btn btn-success btn-sm' href='file_manage_view.php?fmdo=edit&filename=$oldTrfile&activepath=' target='_blank'>".Lang('sys_safetest_edit')."</a>
        </div></div><hr>\r\n";
        return 1;
    }
    return 0;
}
function TestSafe($tdir)
{
    global $filetype;
    $dh = dir($tdir);
    while ($fname = $dh->read()) {
        $fnamef = $tdir.'/'.$fname;
        if (@is_dir($fnamef) && $fname != '.' && $fname != '..') {
            TestSafe($fnamef);
        }
        if (preg_match("#\.(".$filetype.")#i", $fnamef)) {
            TestOneFile($fnamef);
        }
    }
}
//检测
if ($action == 'test') {
    $message = '<link rel="stylesheet" href="../static/web/css/bootstrap.min.css"><link rel="stylesheet" href="../static/web/font/css/font-awesome.min.css">';
    AjaxHead();
    TestSafe(DEDEROOT);
    if ($message == '') $message = "<span class='text-dark'>".Lang('sys_safetest_notrfile')."</span>";
    echo $message;
    exit();
} else if ($action == 'viewdiff') {
    $filename = isset($filename) ? $filename : "";
    if (empty($filename)) {
        ShowMsg(Lang("sys_safetest_no_file"), "-1");
        exit;
    }
    $baseFile = "https://cdn.dedebiz.com/release/{$cfg_version_detail}$filename";
    $del = new DedeHttpDown();
    $del->OpenUrl($baseFile);
    $base = $del->GetHTML();
    $file = "$cfg_basedir/$filename";
    $new = "";
    if (is_file($file)) {
        $fp = fopen($file, "r");
        $new = fread($fp, filesize($file));
        fclose($fp);
    }
    include(dirname(__FILE__).'/templets/sys_safetest_viewdiff.htm');
    exit();
}
//清空模板缓存
else if ($action == 'clear') {
    global $cfg_tplcache_dir;
    $message = '';
    $d = DEDEROOT.$cfg_tplcache_dir;
    AjaxHead();
    sleep(1);
    if (preg_match("#data\/#", $cfg_tplcache_dir) && file_exists($d) && is_dir($d)) {
        $dh = dir($d);
        while ($filename = $dh->read()) {
            if ($filename == '.' || $filename == '..' || $filename == 'index.html') continue;
            @unlink($d.'/'.$filename);
        }
    }
    $message = "<span class='text-dark'>".Lang('sys_safetest_clearcache')."</span>";
    echo $message;
    exit();
}
include(dirname(__FILE__).'/templets/sys_safetest.htm');
?>