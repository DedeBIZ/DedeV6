<?php
if (!defined('DEDEINC')) exit('dedebiz');
try {

    // 1. 手动加载入口文件
    include "../include.php";

    // 2. 准备公众号配置参数
    $config = include "./config.php";

    // 3. 创建接口实例
    // $wechat = \We::WeChatQrcode($config);
    // $wechat = new \WeChat\Qrcode($config);
    $wechat = \WeChat\Qrcode::instance($config);

    // 4. 获取用户列表
    $result = $wechat->create('场景内容');
    echo var_export($result, true) . PHP_EOL;

    // 5. 创建二维码链接
    $url = $wechat->url($result['ticket']);
    echo var_export($url, true);


} catch (Exception $e) {

    // 出错啦，处理下吧
    echo $e->getMessage() . PHP_EOL;

}