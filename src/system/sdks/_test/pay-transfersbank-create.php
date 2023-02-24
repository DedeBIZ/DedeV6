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
        'partner_trade_no' => time(),
        'enc_bank_no'      => '6212263602037318102',
        'enc_true_name'    => '邹景立',
        'bank_code'        => '1002',
        'amount'           => '100',
        'desc'             => '打款测试',
    ];
    echo '<pre>';
    $result = $wechat->createTransfersBank($options);
    var_export($result);
} catch (Exception $e) {
    //出错啦，处理下吧
    echo $e->getMessage().PHP_EOL;

}
?>