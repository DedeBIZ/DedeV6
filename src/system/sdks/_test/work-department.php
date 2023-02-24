<?php
if (!defined('DEDEINC')) exit('dedebiz');
use WeChat\Contracts\BasicWeWork;
include '../include.php';
$config = include 'work-config.php';
try {
    $url = 'https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token=ACCESS_TOKEN';
    $result = BasicWeWork::instance($config)->callGetApi($url);
    echo '<pre>';
    print_r(BasicWeWork::instance($config)->config->get());
    print_r($result);
    echo '</pre>';
} catch (Exception $exception) {
    echo $exception->getMessage().PHP_EOL;
}
?>