<?php
/**
 * 支付返回页
 *
 * @version        $id:return.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2023 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../system/common.inc.php");
$dopost = isset($dopost)? $dopost : '';
$buyid = isset($out_trade_no)? HtmlReplace($out_trade_no, 1) : '';
if ($dopost === 'alipay') {
    $moRow = $dsql->GetOne("SELECT * FROM `#@__member_operation` WHERE buyid='$buyid'");
    if (empty($moRow)) {
        ShowMsg("订单查询错误，请确保是您自己发起的订单", "javascript:;");
        exit;
    }
    if ($moRow['sta'] == 2) {
        ShowMsg("已完成支付，无需重复付款", "javascript:;");
        exit;
    }
    $pInfo = $dsql->GetOne("SELECT * FROM `#@__sys_payment` WHERE id = 2");
    $pData = (array)json_decode($pInfo['config']);
    $config = array(
        "sign_type" => $pData['SignType'],
        "appid" => $pData['APPID'],
        "private_key" => $pData['PrivateKey'],
        "public_key" => $pData['CertPublicKey'],
    );
    //支付宝
    try {
        // 实例支付对象
        $pay = \AliPay\Web::instance($config);
        unset($_REQUEST['dopost']);
        unset($_REQUEST['sign_type']);
        $data = $pay->notify();
        if (isset($data['trade_no']) && !empty($data['trade_no'])) {
            // $pay = \AliPay\Transfer::instance($config);
            $result = $pay->query($data['out_trade_no']);
            if ($result['trade_status']=== "TRADE_SUCCESS") {
                $row = $dsql->GetOne("SELECT * FROM `#@__moneycard_type` WHERE tid='{$moRow['pid']}'");
                $query = "UPDATE `#@__member_operation` SET sta = '2' WHERE buyid = '$buyid'";
                $dsql->ExecuteNoneQuery($query);
                $query = "UPDATE `#@__member` SET money = money+{$row['num']} WHERE mid = '{$moRow['mid']}'";
                $dsql->ExecuteNoneQuery($query);
                ShowMsg("已经完成付款", $cfg_memberurl."/index.php");
                exit;
            }
        } else {
            ShowMsg("尚未完成付款操作", $cfg_memberurl."/index.php");
            exit;
        }
    } catch (Exception $e) {
        ShowMsg("付款错误", "javascript:;");
        exit;
    }
}
?>