<?php

/**
 * 云服务配置
 *
 * @version        $id:sys_cloud.php 22:28 2022年12月5日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__) . "/config.php");
CheckPurview('sys_Edit');
$dopost = isset($dopost) ? $dopost : '';
$client = new DedeBizClient();
if ($dopost == "cloud_get") {
    $rs = $client->CloudGet();
    if ($rs->code === 200) {
        echo json_encode(array(
            "code" => 0,
            "msg" => "",
            "data" => $rs->data,
        ));
    } else {
        echo json_encode(array(
            "code"=>-1,
            "msg"=>'获取配置信息失败',
        ));
    }
    exit;
} else if($dopost == "cloud_set"){
    $config = array(
        "aliyun_enabled" => $aliyun_enabled,
        "aliyun_access_key_id" => $aliyun_access_key_id,
        "aliyun_access_key_secret" => $aliyun_access_key_secret,
        "huaweicloud_enabled" => $huaweicloud_enabled,
        "huawei_access_key_id" => $huawei_access_key_id,
        "huawei_secret_access_key" => $huawei_secret_access_key,
        "tencent_enabled" => $tencent_enabled,
        "tencent_secret_id" => $tencent_secret_id,
        "tencent_secret_key" => $tencent_secret_key,
    );
    $rs = $client->CloudSet($config);
    if ($rs->code === 200) {
        echo json_encode(array(
            "code" => 0,
            "msg" => "",
            "data" => "ok",
        ));
    } else {
        echo json_encode(array(
            "code" => -1,
            "msg" => "设置失败，请检查服务是否正确",
        ));
    }
    exit;
}
if (!$client->IsEnabled()) {
    echo DedeAlert("商业扩展未启动或连接失败，请检查配置是否正确",ALERT_WARNING);
    exit();
}
include DedeInclude("templets/sys_cloud.htm");
?>