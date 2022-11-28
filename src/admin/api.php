<?php

/**
 * 用于后台的api接口
 *
 * @version        $id:api.php 8:26 2022年11月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
define('AJAXLOGIN', TRUE);
define('DEDEADMIN', str_replace("\\", '/', dirname(__FILE__)));
require_once(DEDEADMIN.'/../system/common.inc.php');
require_once(DEDEINC.'/userlogin.class.php');
AjaxHead();
helper('cache');
$action = isset($action) && in_array($action, array('is_need_check_code', 'has_new_version', 'get_changed_files', 'update_backup', 'get_update_versions', 'update')) ? $action  : '';
$curDir = dirname(GetCurUrl()); //当前目录
/**
 * 表中是否存在某个字段
 *
 * @param  mixed $tablename 表名称
 * @param  mixed $field 字段名
 * @return void
 */
function TableHasField($tablename,$field)
{
    global $dsql;
    $dsql->GetTableFields($tablename,"tfd");
    while ($r = $dsql->GetFieldObject("tfd")) {
        if ($r->name === $field) {
            return true;
        }
    }
    return false;
}
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
            "msg" => "当前操作需要登录超级管理员账号",
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
    if (!TableHasField("#@__admin", "loginerr")) {
        $unQueryVer[] = "6.2.0";
    }

    require_once(DEDEINC.'/libraries/dedehttpdown.class.php');
    checkLogin();
    //是否存在更新版本
    $offUrl = DEDEBIZURL."/version?version={$cfg_version_detail}&formurl={$nurl}&phpver={$phpv}&os={$sp_os}&mysqlver={$mysql_ver}{$add_query}&json=1";
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
            "msg" => "请先获取版本更新记录",
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
                "msg" => "正在下载{$ver->ver}的版本更新文件",
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
                        $dsql->ExecuteNoneQuery(trim($sql));
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
                var_dump_cli('files','srcFile',$srcFile,'dstFile',$dstFile);
                // $rs = @copy($srcFile, $dstFile);
                // if($rs) {
                //     unlink($srcFile);
                // }
            }
            $row[$k]->ispatched = true;
            SetCache('update', 'vers', $row);
            echo json_encode(array(
                "code" => 0,
                "msg" => "正在应用{$ver->ver}的版本补丁文件",
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
}
