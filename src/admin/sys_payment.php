<?php
/**
 * 支付接口设置
 *
 * @version        $id:sys_info_mark.php 22:28 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
if (!empty($_REQUEST['dopost'])) define('IS_DEDEAPI', TRUE);
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC.'/datalistcp.class.php');
CheckPurview('sys_Data');
$dopost = (empty($dopost)) ? '' : $dopost;
if ($dopost === "get_payments") {
    $sql = "SELECT * FROM `#@__sys_payment`";
    $dsql->SetQuery($sql);
    $dsql->Execute('payment');
    $payments = array();
    while ($myrow = $dsql->GetArray('payment')) {
        $payments[$myrow['code']] = $myrow;
    }
    echo json_encode(array(
        "code" => 0,
        "msg" => "",
        "data" => $payments,
    ));
    exit;
} else if ($dopost === "save_config") {
    $json = file_get_contents("php://input");
    $config = json_decode($json);
    foreach($config as $key => $item) {
        $status = 0;
        $sortrank = 0;
        $configItem = new stdClass;
        foreach($item as $kk => $ii) {
            if ($kk === "Enabled") {
                $status = $ii === true ? 1 : 0;
            } else if ($kk === "Sortrank") {
                $sortrank = intval($ii);
            } else {
                $configItem->$kk = $ii;
            }
        }
        $cfg = json_encode($configItem, JSON_UNESCAPED_UNICODE);
        $upQuery = "UPDATE `#@__sys_payment` SET sortrank='$sortrank',status='$status',config='$cfg' WHERE code='$key'; ";
        if (!$dsql->ExecuteNoneQuery($upQuery)) {
            echo json_encode(array(
                "code" => -1,
                "msg" => "保存配置失败",
            ));
            exit;
        }
    }
    echo json_encode(array(
        "code" => 0,
        "msg" => "",
        "data" => "success",
    ));
    exit;
}
include DedeInclude('templets/sys_payment.htm');
?>