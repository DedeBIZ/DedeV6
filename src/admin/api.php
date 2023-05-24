<?php
/**
 * 后台api接口
 *
 * @version        $id:api.php 8:26 2022年11月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
define('AJAXLOGIN', TRUE);
define('IS_DEDEAPI', TRUE);
define('DEDEADMIN', str_replace("\\", '/', dirname(__FILE__)));
require_once(DEDEADMIN.'/../system/common.inc.php');
require_once(DEDEINC.'/userlogin.class.php');
AjaxHead();
helper('cache');
$action = isset($action) && in_array($action, array('is_need_check_code', 'has_new_version', 'get_changed_files', 'update_backup', 'get_update_versions', 'update', 'upload_image')) ? $action  : '';
$curDir = dirname(GetCurUrl()); //当前目录
/**
 * 登录鉴权
 *
 * @return void
 */
function checkLogin()
{
    $cuserLogin = new userLogin();
    if ($cuserLogin->getUserID() <= 0 || $cuserLogin->getUserType() != 10) {
        echo json_encode(array(
            "code" => -1,
            "msg" => "此操作需要登录超级管理员权限",
            "data" => null,
        ));
        exit;
    }
}
if ($action === 'is_need_check_code') {
    $cuserLogin = new userLogin();
    $isNeed = $cuserLogin->isNeedCheckCode($userid);
    echo json_encode(array(
        "code" => 0,
        "msg" => "",
        "data" => array(
            "isNeed" => $isNeed,
        ),
    ));
    exit;
} else if ($action === 'has_new_version') {
    //判断版本更新差异sql
    $unQueryVer = array();
    if (!TableHasField("#@__tagindex", "keywords")) {
        $unQueryVer[] = "6.0.2";
    }
    if (!TableHasField("#@__feedback", "replycount")) {
        $unQueryVer[] = "6.0.3";
    }
    if (!TableHasField("#@__arctype", "litimg")) {
        $unQueryVer[] = "6.1.0";
    }
    if (!$dsql->IsTable("#@__statistics")) {
        $unQueryVer[] = "6.1.7";
    }
    if (TableHasField("#@__tagindex", "tag_pinyin")) {
        $unQueryVer[] = "6.1.8";
    }
    if (!TableHasField("#@__admin", "pwd_new")) {
        $unQueryVer[] = "6.1.9";
    }
    if (!TableHasField("#@__arctype", "cnoverview")) {
        $unQueryVer[] = "6.1.10";
    }
    if (!TableHasField("#@__admin", "loginerr") || !TableHasField("#@__member", "loginerr")) {
        $unQueryVer[] = "6.2.0";
    }
    $row = $dsql->GetOne("SELECT COUNT(*) as dd FROM `#@__sysconfig` WHERE varname = 'cfg_bizcore_api'");
    if ($row['dd'] == 0) {
        $unQueryVer[] = "6.2.3";
    }
    if (!$dsql->IsTable("#@__sys_payment")) {
        $unQueryVer[] = "6.2.5";
    }
    if (!TableHasField("#@__arctype", "apienabled")) {
        $unQueryVer[] = "6.2.7";
    }
    if (count($unQueryVer) > 0) {
        $upsqls = GetUpdateSQL();
        foreach ($unQueryVer as $vv) {
            $ss = $upsqls[$vv];
            foreach ($ss as $s) {
                if (trim($s) != '') {
                    $dsql->safeCheck = false;
                    $dsql->ExecuteNoneQuery(trim($s));
                    $dsql->safeCheck = true;
                }
            }
        }
    }
    require_once(DEDEINC.'/libraries/dedehttpdown.class.php');
    checkLogin();
    //是否存在更新版本
    $phpv = phpversion();
    $sp_os = PHP_OS;
    $mysql_ver = $dsql->GetVersion();
    $nurl = $_SERVER['HTTP_HOST'];
    if (preg_match("#[a-z\-]{1,}\.[a-z]{2,}#i", $nurl)) {
        $nurl = urlencode($nurl);
    } else {
        $nurl = "test";
    }
    $add_query = '';
    $query = "SELECT COUNT(*) AS dd FROM `#@__member` ";
    $row1 = $dsql->GetOne($query);
    if ($row1) $add_query .= "&mcount={$row1['dd']}";
    $query = "SELECT COUNT(*) AS dd FROM `#@__arctiny` ";
    $row2 = $dsql->GetOne($query);
    if ($row2) $add_query .= "&acount={$row2['dd']}";
    $offUrl = DEDEBIZURL."/version?version={$cfg_version_detail}&formurl={$nurl}&phpver={$phpv}&os={$sp_os}&mysqlver={$mysql_ver}{$add_query}&json=1";
    if (strpos($_SERVER['SERVER_SOFTWARE'], 'Development Server') !== false && version_compare(phpversion(), '7.2', '<')) {
        echo json_encode(array(
            "code"=>-1,
            "msg"=>'获取版本信息失败',
        ));
        exit;
    }
    $dhd = new DedeHttpDown();
    $dhd->OpenUrl($offUrl);
    $data = $dhd->GetHtml();
    if (empty($data)) {
        echo json_encode(array(
            "code"=>-1,
            "msg"=>'获取版本信息失败',
        ));
    } else {
        echo $data;
    }
} else if ($action === 'get_changed_files') {
    require_once(DEDEINC.'/libraries/dedehttpdown.class.php');
    checkLogin();
    //获取本地更改过的文件
    $hashUrl = DEDEBIZCDN.'/release/'.$cfg_version_detail.'.json';
    $dhd = new DedeHttpDown();
    $dhd->OpenUrl($hashUrl);
    $data = $dhd->GetJSON();
    if (empty($data)) {
        echo json_encode(array(
            "code"=>-1,
            "msg"=>'获取版本信息失败',
        ));
        exit();
    }
    $changedFiles = array();
    foreach ($data as $file) {
        $realFile = DEDEROOT.str_replace("\\", '/', $file->filename);
        if (file_exists($realFile) && md5_file($realFile) !== $file->hash) {
            $changedFiles[] = $file;
            continue;
        }
    }
    echo json_encode(array(
        "code" => 0,
        "msg" => "",
        "data" => array(
            "files" => $changedFiles,
        ),
    ));
    exit;
} else if ($action === 'update_backup') {
    require_once(DEDEINC.'/libraries/dedehttpdown.class.php');
    checkLogin();
    //获取本地更改过的文件
    $hashUrl = DEDEBIZCDN.'/release/'.$cfg_version_detail.'.json';
    $dhd = new DedeHttpDown();
    $dhd->OpenUrl($hashUrl);
    $data = $dhd->GetJSON();
    if (empty($data)) {
        echo json_encode(array(
            "code"=>-1,
            "msg"=>'获取版本信息失败',
        ));
        exit;
    }
    $changedFiles = array();
    $enkey = substr(md5(substr($cfg_cookie_encode, 0, 5)), 0, 10);
    $backupPath = DEDEDATA."/backupfile_{$enkey}";
    RmRecurse($backupPath);
    mkdir($backupPath);
    foreach ($data as $file) {
        $realFile = DEDEROOT.str_replace("\\", '/', $file->filename);
        if (file_exists($realFile) && md5_file($realFile) !== $file->hash) {
            //备份文件
            $dstFile = $backupPath.'/'.str_replace("\\", '/', $file->filename);
            @mkdir(dirname($dstFile), 0777, true);
            copy($realFile, $dstFile);
        }
    }
    echo json_encode(array(
        "code" => 0,
        "msg" => "",
        "data" => array(
            "backupdir" => "data/backupfile_{$enkey}",
        ),
    ));
    exit;
} else if ($action === 'get_update_versions') {
    require_once(DEDEINC.'/libraries/dedehttpdown.class.php');
    checkLogin();
    //获取本地更改过的文件
    $offUrl = DEDEBIZURL."/versions?version={$cfg_version_detail}";
    $dhd = new DedeHttpDown();
    $dhd->OpenUrl($offUrl);
    $data = $dhd->GetHtml();
    if (empty($data)) {
        echo json_encode(array(
            "code"=>-1,
            "msg"=>'获取版本信息失败',
        ));
        exit;
    }
    $arr = json_decode($data);
    SetCache('update', 'vers', $arr->result->Versions);
    echo $data;
    exit;
} else if ($action === 'update') {
    require_once(DEDEINC.'/libraries/dedehttpdown.class.php');
    $row = GetCache('update', 'vers');
    if (count($row) === 0) {
        echo json_encode(array(
            "code" => -1,
            "msg" => "请获取版本更新记录",
            "data" => null,
        ));
        exit;
    }
    $enkey = substr(md5(substr($cfg_cookie_encode, 0, 5)), 0, 10);
    $backupPath = DEDEDATA."/updatefile_{$enkey}";
    @mkdir($backupPath);
    foreach ($row as $k => $ver) {
        if ($ver->isdownload !== true) {
            $filesUrl = DEDEBIZCDN.'/update/'.$ver->ver.'/files.txt';
            $dhd = new DedeHttpDown();
            $dhd->OpenUrl($filesUrl);
            $fileList = $dhd->GetJSON();
            $dhd->Close();
            $backupVerPath = $backupPath.'/'.$ver->ver;
            RmRecurse($backupVerPath);
            mkdir($backupVerPath);
            foreach ($fileList as $f) {
                if (!preg_match("/^\//", $f->filename)) {
                    //忽略src之外的目录
                    continue;
                }
                $fileUrl = DEDEBIZCDN.'/update/'.$ver->ver.'/src'.$f->filename;
                $dhd = new DedeHttpDown();
                $dhd->OpenUrl($fileUrl);
                $fData = $dhd->GetHtml();
                $dhd->Close();
                $f->filename = preg_replace('/^\/admin/', $curDir, $f->filename);
                $realFile = $backupVerPath.$f->filename;
                @mkdir(dirname($realFile), 0777, true);
                file_put_contents($realFile, $fData);
            }
            $sqlUrl = DEDEBIZCDN.'/update/'.$ver->ver.'/update.sql';
            $dhd = new DedeHttpDown();
            $dhd->OpenUrl($sqlUrl);
            $fData = $dhd->GetHtml();
            $dhd->Close();
            $realFile = $backupVerPath.'/update.sql';
            file_put_contents($realFile, $fData);
            $realFile = $backupVerPath.'/files.txt';
            file_put_contents($realFile, json_encode($fileList));
            $row[$k]->isdownload = true;
            SetCache('update', 'vers', $row);
            echo json_encode(array(
                "code" => 0,
                "msg" => "正在下载{$ver->ver}版本更新文件",
                "data" => array(
                    "finish" => false,
                ),
            ));
            exit;
        }
    }
    foreach ($row as $k => $ver) {
        if ($ver->ispatched !== true) {
            $backupVerPath = $backupPath.'/'.$ver->ver;
            //执行更新SQL文件
            $sql = file_get_contents($backupVerPath.'/update.sql');
            if (!empty($sql)) {
                $sql = preg_replace('#ENGINE=MyISAM#i', 'TYPE=MyISAM', $sql);
                $sql41tmp = 'ENGINE=MyISAM DEFAULT CHARSET='.$cfg_db_language;
                $sql = preg_replace('#TYPE=MyISAM#i', $sql41tmp, $sql);
                $sqls = explode(";\r\n", $sql);
                foreach ($sqls as $sql) {
                    if (trim($sql) != '') {
                        $dsql->safeCheck = false;
                        $dsql->ExecuteNoneQuery(trim($sql));
                        $dsql->safeCheck = true;
                    }
                }
            }
            //复制文件
            $fileList = json_decode(file_get_contents($backupVerPath.'/files.txt'));
            foreach ($fileList as $f) {
                if (!preg_match("/^\//", $f->filename)) {
                    //忽略src之外的目录
                    continue;
                }
                $f->filename = preg_replace('/^\/admin/', $curDir, $f->filename);
                $srcFile = $backupVerPath.$f->filename;
                $dstFile = str_replace(array("\\", "//"), '/', DEDEROOT.$f->filename);
                @mkdir(dirname($dstFile), 0777, true);
                $rs = @copy($srcFile, $dstFile);
                if ($rs) {
                    unlink($srcFile);
                }
            }
            $row[$k]->ispatched = true;
            SetCache('update', 'vers', $row);
            RmRecurse($backupVerPath);
            echo json_encode(array(
                "code" => 0,
                "msg" => "正在更新{$ver->ver}版本补丁文件",
                "data" => array(
                    "finish" => false,
                ),
            ));
            exit;
        }
    }
    echo json_encode(array(
        "code" => 0,
        "msg" => "",
        "data" => array(
            "finish" => true,
        ),
    ));
    exit;
} else if($action === 'upload_image'){
    checkLogin();
    $imgfile_name = $_FILES["file"]['name'];
    $activepath = $cfg_image_dir;
    $allowedTypes = array("image/pjpeg", "image/jpeg", "image/gif", "image/png", "image/xpng", "image/wbmp", "image/webp");
    $uploadedFile = $_FILES['file']['tmp_name'];
    $fileType = mime_content_type($uploadedFile);
    $imgSize = getimagesize($uploadedFile);
    if (!in_array($fileType, $allowedTypes) || !$imgSize) {
        echo json_encode(array(
            "code" => -1,
            "msg" => "仅支持图片格式文件",
            "data" => null,
        ));
        exit;
    }
    $nowtme = time();
    $mdir = MyDate($cfg_addon_savetype, $nowtme);
    if (!is_dir($cfg_basedir.$activepath."/$mdir")) {
        MkdirAll($cfg_basedir.$activepath."/$mdir", $cfg_dir_purview);
        CloseFtp();
    }
    $cuserLogin = new userLogin();
    $iseditor = isset($iseditor)? intval($iseditor) : 0;
    $filename_name = $cuserLogin->getUserID().'-'.dd2char(MyDate("ymdHis", $nowtme).mt_rand(100, 999));
    $filename = $mdir.'/'.$filename_name;
    $fs = explode('.', $imgfile_name);
    $filename = $filename.'.'.$fs[count($fs) - 1];
    $filename_name = $filename_name.'.'.$fs[count($fs) - 1];
    $fullfilename = $cfg_basedir.$activepath."/".$filename;
    move_uploaded_file($_FILES["file"]["tmp_name"], $fullfilename) or die(json_encode(array(
        "code" => -1,
        "msg" => "上传失败",
        "data" => null,
    )));
    $info = '';
    $sizes[0] = 0;
    $sizes[1] = 0;
    $sizes = getimagesize($fullfilename, $info);
    $imgwidthValue = $sizes[0];
    $imgheightValue = $sizes[1];
    $imgsize = filesize($fullfilename);
    $inquery = "INSERT INTO `#@__uploads` (arcid,title,url,mediatype,width,height,playtime,filesize,uptime,mid) VALUES ('0','$filename','".$activepath."/".$filename."','1','$imgwidthValue','$imgheightValue','0','{$imgsize}','{$nowtme}','".$cuserLogin->getUserID()."'); ";
    $dsql->ExecuteNoneQuery($inquery);
    $fid = $dsql->GetLastID();
    AddMyAddon($fid, $activepath.'/'.$filename);
    echo json_encode(array(
        "code" => 0,
        "msg" => "上传成功",
        "data" => $activepath."/".$filename,
    ));

}
?>