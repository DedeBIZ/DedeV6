<?php
if (!defined('DEDEINC')) exit('dedebiz');
// 1. 手动加载入口文件
include "../include.php";

// 2. 准备公众号配置参数
$config = include "./alipay.php";

try {
    // 实例支付对象
    // $pay = We::AliPayTransfer($config);
    // $pay = new \AliPay\Transfer($config);
    $pay = \AliPay\Transfer::instance($config);

    // 参考链接：https://docs.open.alipay.com/api_28/alipay.fund.account.query/
    $result = $pay->queryAccount([
        'alipay_user_id'     => $config['appid'], // 订单号
        'account_scene_code' => 'SCENE_000_000_000',
    ]);
    echo '<pre>';
    var_export($result);
} catch (Exception $e) {
    echo $e->getMessage();
}

