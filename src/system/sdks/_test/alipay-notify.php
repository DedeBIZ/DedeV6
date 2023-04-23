<?php
if (!defined('DEDEINC')) exit ('dedebiz');
//手动加载入口文件
include "../include.php";
//准备公众号配置参数
$config = include "./alipay.php";
try {
    //实例支付对象
    //$pay = \We::AliPayApp($config);
    //$pay = new \AliPay\App($config);
    $pay = \AliPay\App::instance($config);
    $data = $pay->notify();
    if (in_array($data['trade_status'], ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
        //@todo 更新订单状态，支付完成
        file_put_contents('notify.txt', "收到来自支付宝的异步通知\r\n", FILE_APPEND);
        file_put_contents('notify.txt', '订单号：'.$data['out_trade_no']."\r\n", FILE_APPEND);
        file_put_contents('notify.txt', '订单金额：'.$data['total_amount']."\r\n\r\n", FILE_APPEND);
    } else {
        file_put_contents('notify.txt', "收到异步通知\r\n", FILE_APPEND);
    }
} catch (\Exception $e) {
    //异常处理
    echo $e->getMessage();
}
?>