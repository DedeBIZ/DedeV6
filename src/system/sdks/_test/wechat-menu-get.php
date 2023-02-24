<?php
if (!defined('DEDEINC')) exit('dedebiz');
try {
    //手动加载入口文件
    include "../include.php";
    //准备公众号配置参数
    $config = include "./config.php";
    //创建接口实例
    //$menu = \We::WeChatMenu($config);
    //$menu = new \WeChat\Menu($config);
    $menu = \WeChat\Menu::instance($config);
    //获取菜单数据
    $result = $menu->get();
    var_export($result);
} catch (Exception $e) {
    //出错啦，处理下吧
    echo $e->getMessage().PHP_EOL;
}
?>