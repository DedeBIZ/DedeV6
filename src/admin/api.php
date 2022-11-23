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
require_once(DEDEADMIN . '/../system/common.inc.php');
require_once(DEDEINC . '/userlogin.class.php');
AjaxHead();
helper('cache');
$action = isset($action) && in_array($action, array('is_need_check_code', 'has_new_version', 'get_changed_files', 'update_backup', 'get_update_versions', 'update')) ? $action  : '';
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
    require_once(DEDEINC . '/libraries/dedehttpdown.class.php');
    checkLogin();
    //是否存在更新版本
    $offUrl = DEDEBIZURL . "/version?version={$cfg_version_detail}&formurl={$nurl}&phpver={$phpv}&os={$sp_os}&mysqlver={$mysql_ver}{$add_query}&json=1";
    $dhd = new DedeHttpDown();
    $dhd->OpenUrl($offUrl);
    $data = $dhd->GetHtml();
    echo $data;
} else if ($action === 'get_changed_files') {
    require_once(DEDEINC . '/libraries/dedehttpdown.class.php');
    checkLogin();
    // 获取本地更改过的文件
    $hashUrl = DEDEBIZCDN . '/release/' . $cfg_version_detail . '.json';
    $dhd = new DedeHttpDown();
    $dhd->OpenUrl($hashUrl);
    $data = $dhd->GetJSON();
    $changedFiles = array();
    foreach ($data as $file) {
        $realFile = DEDEROOT . str_replace("\\", '/', $file->filename);
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
    require_once(DEDEINC . '/libraries/dedehttpdown.class.php');
    checkLogin();
    // 获取本地更改过的文件
    $hashUrl = DEDEBIZCDN . '/release/' . $cfg_version_detail . '.json';
    $dhd = new DedeHttpDown();
    $dhd->OpenUrl($hashUrl);
    $data = $dhd->GetJSON();
    $changedFiles = array();
    $enkey = substr(md5(substr($cfg_cookie_encode, 0, 5)), 0, 10);
    $backupPath = DEDEDATA . "/backupfile_{$enkey}";
    RmRecurse($backupPath);
    mkdir($backupPath);
    foreach ($data as $file) {
        $realFile = DEDEROOT . str_replace("\\", '/', $file->filename);
        if (file_exists($realFile) && md5_file($realFile) !== $file->hash) {
            // 备份文件
            $dstFile = $backupPath . '/' . str_replace("\\", '/', $file->filename);
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
    require_once(DEDEINC . '/libraries/dedehttpdown.class.php');
    checkLogin();
    //获取本地更改过的文件
    $offUrl = DEDEBIZURL . "/versions?version={$cfg_version_detail}";
    $dhd = new DedeHttpDown();
    $dhd->OpenUrl($offUrl);
    $data = $dhd->GetHtml();
    $arr = json_decode($data);
    SetCache('update', 'vers', $arr->result->Versions);
    echo $data;
    exit;
} else if ($action === 'update') {
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
    $backupPath = DEDEDATA . "/updatefile_{$enkey}";
    mkdir($backupPath);
    foreach ($row as $k => $ver) {
        if ($ver->isdownload !== true) {
            //TODO 从远程服务器下载
            $fileList = json_decode(file_get_contents(dirname(__FILE__) . '/../../../tools/patch-6.1.9/files.txt'));
            $backupVerPath = $backupPath . '/' . $ver->ver;
            RmRecurse($backupVerPath);
            mkdir($backupVerPath);
            foreach ($fileList as $f) {
                if (!preg_match("/^\//", $f->filename)) {
                    //忽略src之外的目录
                    continue;
                }
                $fData = file_get_contents(dirname(__FILE__) . '/../../../tools/patch-6.1.9/src' . $f->filename);
                $realFile = $backupVerPath . $f->filename;
                @mkdir(dirname($realFile), 0777, true);
                file_put_contents($realFile, $fData);
            }
            $fData = file_get_contents(dirname(__FILE__) . '/../../../tools/patch-6.1.9/update.sql');
            $realFile = $backupVerPath . '/update.sql';
            file_put_contents($realFile, $fData);
            $row[$k]->isdownload = true;
            SetCache('update', 'vers', $row);
            echo json_encode(array(
                "code" => 0,
                "msg" => "正在下载{$ver->ver}的版本更新文件",
                "data" => array(
                    "finish"=>false,
                ),
            ));
            exit;
        }
    }
    foreach ($row as $k => $ver) {
        if ($ver->ispatched !== true) {
            //TODO 补丁应用
            $row[$k]->ispatched = true;
            SetCache('update', 'vers', $row);
            echo json_encode(array(
                "code" => 0,
                "msg" => "正在应用{$ver->ver}的版本补丁文件",
                "data" => array(
                    "finish"=>false,
                ),
            ));
            exit;
        }
    }
    echo json_encode(array(
        "code" => 0,
        "msg" => "",
        "data" => array(
            "finish"=>true,
        ),
    ));
    exit;

}
