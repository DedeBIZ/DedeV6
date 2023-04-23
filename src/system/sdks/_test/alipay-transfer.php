<?php
if (!defined('DEDEINC')) exit ('dedebiz');
//手动加载入口文件
include "../include.php";
//准备公众号配置参数
$config = include "./alipay.php";
try {
    //实例支付对象
    //$pay = We::AliPayTransfer($config);
    //$pay = new \AliPay\Transfer($config);
    $pay = \AliPay\Transfer::instance($config);
    //参考链接：https://docs.open.alipay.com/api_28/alipay.fund.trans.toaccount.transfer
    $result = $pay->apply([
        'out_biz_no'      => time(),//订单号
        'payee_type'      => 'ALIPAY_LOGONID',//收款方账户类型(ALIPAY_LOGONID | ALIPAY_USERID)
        'payee_account'   => 'demo@sandbox.com',//收款方账户
        'amount'          => '10',//转账金额
        'payer_show_name' => '未寒',//付款方姓名
        'payee_real_name' => '张三',//收款方真实姓名
        'remark'          => '张三',//转账备注
    ]);
    echo '<pre>';
    var_export($result);
} catch (Exception $e) {
    echo $e->getMessage();
}
?>