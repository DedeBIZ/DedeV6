<?php
if (!defined('DEDEINC')) exit('dedebiz');
//配置缓存处理函数
//\WeChat\Contracts\Tools::$cache_callable = [
//   'set' => function ($name, $value, $expired = 360) {
//       var_dump(func_get_args());
//   },
//   'get' => function ($name) {
//       var_dump(func_get_args());
//   },
//   'del' => function ($name) {
//       var_dump(func_get_args());
//   },
//   'put' => function ($name) {
//       var_dump(func_get_args());
//   },
//];
return [
    'token'          => 'test',
    'appid'          => 'wx60a43dd8161666d4',
    'appsecret'      => 'b4e28746f1bd73b5c6684f5e01883c36',
    'encodingaeskey' => 'BJIUzE0gqlWy0GxfPp4J1oPTBmOrNDIGPNav1YFH5Z5',
    //配置商户支付参数
    'mch_id'         => "1332187001",
    'mch_key'        => 'A82DC5BD1F3359081049C568D8502BC5',
    //配置商户支付双向证书目录 （p12 | key,cert 二选一，两者都配置时p12优先）
    'ssl_p12'        => __DIR__.DIRECTORY_SEPARATOR.'cert'.DIRECTORY_SEPARATOR.'1332187001_20181030_cert.p12',
    //'ssl_key'        => __DIR__.DIRECTORY_SEPARATOR.'cert'.DIRECTORY_SEPARATOR.'1332187001_20181030_key.pem',
    //'ssl_cer'        => __DIR__.DIRECTORY_SEPARATOR.'cert'.DIRECTORY_SEPARATOR.'1332187001_20181030_cert.pem',
    //配置缓存目录，需要拥有写权限
    'cache_path'     => '',
];
?>