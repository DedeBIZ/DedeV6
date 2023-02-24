<?php
if (!defined('DEDEINC')) exit('dedebiz');
spl_autoload_register(function ($classname) {
    $pathname = __DIR__.DIRECTORY_SEPARATOR;
    $filename = str_replace('\\', DIRECTORY_SEPARATOR, $classname).'.php';
    if (file_exists($pathname.$filename)) {
        foreach (['AliPay', 'WeChat', 'WeMini', 'WePay', 'We'] as $prefix) {
            if (stripos($classname, $prefix) === 0) {
                include $pathname.$filename;
                return true;
            }
        }
    }
    return false;
});
?>