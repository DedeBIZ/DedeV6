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
    //获取通知参数
    $data = $wechat->getNotify();
    if ($data['return_code'] === 'SUCCESS' && $data['result_code'] === 'SUCCESS') {
        //@todo 去更新下原订单的支付状态
        $order_no = $data['out_trade_no'];
        //返回接收成功的回复
        ob_clean();
        echo $wechat->getNotifySuccessReply();
    }
} catch (Exception $e) {
    //出错啦，处理下吧
    echo $e->getMessage().PHP_EOL;
}
?>