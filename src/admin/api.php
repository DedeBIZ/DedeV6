<?php
define('AJAXLOGIN', TRUE);
define('DEDEADMIN', str_replace("\\", '/', dirname(__FILE__)));
require_once(DEDEADMIN.'/../system/common.inc.php');
require_once(DEDEINC.'/userlogin.class.php');
AjaxHead();

$action = isset($action) && in_array($action, array('is_need_check_code'))? $action  : '';

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
}