<?php
/**
 * 支付回调
 *
 * @version        $id:notify.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2023 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
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
        //实例支付对象
        $pay = \AliPay\Web::instance($config);
        unset($_REQUEST['dopost']);
        unset($_REQUEST['sign_type']);
        $data = $pay->notify(false, $_REQUEST);
        if (isset($data['trade_no']) && !empty($data['trade_no'])) {
            //$pay = \AliPay\Transfer::instance($config);
            $result = $pay->query($data['out_trade_no']);
            if ($result['trade_status']=== "TRADE_SUCCESS") {
                if ($moRow['product'] === "card") {
                    $row = $dsql->GetOne("SELECT * FROM `#@__moneycard_type` WHERE tid='{$moRow['pid']}'");
                    $query = "UPDATE `#@__member_operation` SET sta = '2' WHERE buyid = '$buyid'";
                    $dsql->ExecuteNoneQuery($query);
                    $query = "UPDATE `#@__member` SET money = money+{$row['num']} WHERE mid = '{$moRow['mid']}'";
                    $dsql->ExecuteNoneQuery($query);
                } else if ($moRow['product'] === "member") {
                    $row = $dsql->GetOne("SELECT * FROM `#@__member_type` WHERE aid='{$moRow['pid']}'");
                    $rank = $row['rank'];
                    $exptime = $row['exptime'];
                    $rs = $dsql->GetOne("SELECT uptime,exptime FROM `#@__member` WHERE mid='".$moRow['mid']."'");
                    if ($rs['uptime']!=0 && $rs['exptime']!=0) {
                        $nowtime = time();
                        $mhasDay = $rs['exptime'] - ceil(($nowtime - $rs['uptime'])/3600/24) + 1;
                        $mhasDay=($mhasDay>0)? $mhasDay : 0;
                    }
                    $memrank = $dsql->GetOne("SELECT money,scores FROM `#@__arcrank` WHERE `rank`='$rank'");
                    //更新会员信息
                    $sqlm =  "UPDATE `#@__member` SET `rank`='$rank',`money`=`money`+'{$memrank['money']}',scores=scores+'{$memrank['scores']}',exptime='$exptime'+'$mhasDay',uptime='".time()."' WHERE mid='".$moRow['mid']."'";
                    $sqlmo = "UPDATE `#@__member_operation` SET sta='2',oldinfo='会员升级成功' WHERE buyid='{$moRow['pid']}' ";
                    if (!($dsql->ExecuteNoneQuery($sqlm) && $dsql->ExecuteNoneQuery($sqlmo))) {
                        ShowMsg("升级会员失败", "javascript:;");
                        exit;
                    }
                }
                ShowMsg("已经完成付款", $cfg_memberurl."/index.php");
                exit;
            }
        } else {
            ShowMsg("尚未完成付款操作", $cfg_memberurl."/index.php");
            exit;
        }
    } catch (Exception $e) {
        ShowMsg("付款失败，请检查支付接口设置", "javascript:;");
        exit;
    }
} else if ($dopost === 'wechat') {
    $pInfo = $dsql->GetOne("SELECT * FROM `#@__sys_payment` WHERE id = 1");
    $pData = (array)json_decode($pInfo['config']);
    $config = array(
        "appid" => $pData['AppID'],
        "mch_id" => $pData['MchID'],
        "mch_key" => $pData['APIv2Secret'],
    );
    $wechat = new \WeChat\Pay($config);
    $data = $wechat->getNotify();
    if ($data['return_code'] === 'SUCCESS' && $data['result_code'] === 'SUCCESS') {
        $buyid = $data['out_trade_no'];
        $moRow = $dsql->GetOne("SELECT * FROM `#@__member_operation` WHERE buyid='$buyid'");
        if (empty($moRow)) {
            ShowMsg("订单查询错误，请确保是您自己发起的订单", "javascript:;");
            exit;
        }
        if ($moRow['product'] === "card") {
            $row = $dsql->GetOne("SELECT * FROM `#@__moneycard_type` WHERE tid='{$moRow['pid']}'");
            $query = "UPDATE `#@__member_operation` SET sta = '2' WHERE buyid = '$buyid'";
            $dsql->ExecuteNoneQuery($query);
            $query = "UPDATE `#@__member` SET money = money+{$row['num']} WHERE mid = '{$moRow['mid']}'";
            $dsql->ExecuteNoneQuery($query);
        } else if ($moRow['product'] === "member") {
            $row = $dsql->GetOne("SELECT * FROM `#@__member_type` WHERE aid='{$moRow['pid']}'");
            $rank = $row['rank'];
            $exptime = $row['exptime'];
            $rs = $dsql->GetOne("SELECT uptime,exptime FROM `#@__member` WHERE mid='".$moRow['mid']."'");
            if ($rs['uptime']!=0 && $rs['exptime']!=0) {
                $nowtime = time();
                $mhasDay = $rs['exptime'] - ceil(($nowtime - $rs['uptime'])/3600/24) + 1;
                $mhasDay=($mhasDay>0)? $mhasDay : 0;
            }
            $memrank = $dsql->GetOne("SELECT money,scores FROM `#@__arcrank` WHERE `rank`='$rank'");
            //更新会员信息
            $sqlm =  "UPDATE `#@__member` SET `rank`='$rank',`money`=`money`+'{$memrank['money']}',scores=scores+'{$memrank['scores']}',exptime='$exptime'+'$mhasDay',uptime='".time()."' WHERE mid='".$moRow['mid']."'";
            $sqlmo = "UPDATE `#@__member_operation` SET sta='2',oldinfo='会员升级成功' WHERE buyid='{$moRow['pid']}' ";
            if (!($dsql->ExecuteNoneQuery($sqlm) && $dsql->ExecuteNoneQuery($sqlmo))) {
                ShowMsg("升级会员失败", "javascript:;");
                exit;
            }
        }
        echo "success";
        exit;
    } else {
        echo "error";
        exit;
    }
} else {
    ShowMsg("未知付款，请检查支付接口设置", "javascript:;");
    exit;
}
?>