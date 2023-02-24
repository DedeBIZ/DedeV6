<?php
if (!defined('DEDEINC')) exit('dedebiz');
try {
    //手动加载入口文件
    include "../include.php";
    //准备公众号配置参数
    $config = include "./pay-v3-config.php";
    //创建接口实例
    $payment = \WePayV3\Order::instance($config);
    //组装支付参数
    $result = $payment->create('jsapi', [
        'appid'        => 'wx60a43dd8161666d4',
        'mchid'        => $config['mch_id'],
        'description'  => '商品描述',
        'out_trade_no' => (string)time(),
        'notify_url'   => 'https://thinkadmin.top',
        'payer'        => ['openid' => 'o38gps3vNdCqaggFfrBRCRikwlWY'],
        'amount'       => ['total' => 2, 'currency' => 'CNY'],
    ]);
    echo '<pre>';
    echo "\n--- 创建支付参数 ---\n";
    var_export($result);
} catch (\Exception $exception) {
    //出错啦，处理下吧
    echo $exception->getMessage().PHP_EOL;
}
?>