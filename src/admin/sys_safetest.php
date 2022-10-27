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
require_once(dirname(__FILE__).'/config.php');
require_once(DEDEINC.'/libraries/dedehttpdown.class.php');
CheckPurview('sys_Edit');
if (empty($action)) $action = '';
if (empty($message)) $message = '尚未进行检测……';
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
    $alter = DedeAlert('无法同官方网站文件服务器通信，校验时候无法保证本地文件是否同官方服务器文件是否一致', ALERT_DANGER);
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
        <div style='float:left;width:360px'>可疑文件：{$trfile}</div>
        <a class='btn btn-success btn-sm' href='sys_safetest.php?action=viewdiff&filename=$oldTrfile' target='_blank'>修改记录</a>
        <a class='btn btn-success btn-sm' href='file_manage_view.php?fmdo=del&filename=$oldTrfile&activepath=' target='_blank'>删除</a>
        <a class='btn btn-success btn-sm' href='file_manage_view.php?fmdo=edit&filename=$oldTrfile&activepath=' target='_blank'>查看源码</a>
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
    if ($message == '') $message = "<span class='text-dark'>没发现可疑文件</span>";
    echo $message;
    exit();
} else if ($action == 'viewdiff') {
    $filename = isset($filename) ? $filename : "";
    if (empty($filename)) {
        ShowMsg("没有选择对应的文件", "-1");
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
    $message = "<span class='text-dark'>成功清空模板缓存</span>";
    echo $message;
    exit();
}
include(dirname(__FILE__).'/templets/sys_safetest.htm');
?>