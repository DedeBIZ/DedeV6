<?php
define('AJAXLOGIN', TRUE);
define('DEDEADMIN', str_replace("\\", '/', dirname(__FILE__)));
require_once(DEDEADMIN.'/../system/common.inc.php');
require_once(DEDEINC.'/userlogin.class.php');
AjaxHead();
$action = isset($action) && in_array($action, array('is_need_check_code','has_new_version','get_changed_files'))? $action  : '';
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
} else if ($action === 'has_new_version'){
    require_once(DEDEINC.'/libraries/dedehttpdown.class.php');
    checkLogin();
    //是否存在更新版本
    $offUrl = DEDEBIZURL."/version?version={$cfg_version_detail}&formurl={$nurl}&phpver={$phpv}&os={$sp_os}&mysqlver={$mysql_ver}{$add_query}&json=1";
    $dhd = new DedeHttpDown();
    $dhd->OpenUrl($offUrl);
    $data = $dhd->GetHtml();
    echo $data;
} else if ($action === 'get_changed_files'){
    require_once(DEDEINC.'/libraries/dedehttpdown.class.php');
    checkLogin();
    // 获取本地更改过的文件
    $hashUrl = DEDEBIZCDN.'/release/'.$cfg_version_detail.'.json';
    $dhd = new DedeHttpDown();
    $dhd->OpenUrl($hashUrl);
    $data = $dhd->GetJSON();
    $changedFiles = array();
    foreach ($data as $file) {
        $realFile = DEDEROOT. str_replace("\\", '/', $file->filename);
        if ( file_exists($realFile) && md5_file($realFile) !== $file->hash) {
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
}
?>