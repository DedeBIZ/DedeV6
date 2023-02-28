<?php
if (!defined('DEDEINC')) exit('dedebiz');
try {
    //手动加载入口文件
    include "../include.php";
    //准备公众号配置参数
    $config = include "./config.php";
    //创建接口实例
    //$wechat = \We::WeChatUser($config);
    //$wechat = new \WeChat\User($config);
    $wechat = \WeChat\User::instance($config);
    //获取会员列表
    $result = $wechat->getUserList();
    echo '<pre>';
    var_export($result);
    //批量获取会员资料
    foreach (array_chunk($result['data']['openid'], 100) as $item) {
        $userList = $wechat->getBatchUserInfo($item);
        var_export($userList);
    }
} catch (Exception $e) {
    //出错啦，处理下吧
    echo $e->getMessage().PHP_EOL;
}
?>