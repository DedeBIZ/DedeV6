<?php
if (!defined('DEDEINC')) exit('dedebiz');
try {
    //手动加载入口文件
    include "../include.php";
    //准备公众号配置参数
    $config = include "./config.php";
    //创建接口实例
    //$wechat = new \WeChat\Pay($config);
    //$wechat = \We::WeChatPay($config);
    $wechat = \WeChat\Pay::instance($config);
    //组装参数，可以参考官方商户文档
    $options = [
        'transaction_id' => '1008450740201411110005820873',
        'out_refund_no'  => '商户退款单号',
        'total_fee'      => '1',
        'refund_fee'     => '1',
    ];
    $result = $wechat->createRefund($options);
    var_export($result);
} catch (Exception $e) {
    //出错啦，处理下吧
    echo $e->getMessage().PHP_EOL;
}
?>