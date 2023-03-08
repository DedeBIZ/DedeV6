<?php
/**
 * 积分钱包
 * 
 * @version        $id:buy_action.php 8:38 2023年02月13日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckRank(0, 0);
$dopost = isset($dopost)? $dopost : '';
$menutype = 'mydede';
$menutype_son = 'op';
require_once DEDEINC.'/dedetemplate.class.php';
$product = isset($product) ? trim(HtmlReplace($product, 1)) : '';
$mid = $cfg_ml->M_ID;
$ptype = '';
$pname = '';
$price = '';
$mtime = time();
$paytype = isset($paytype)? intval($paytype) : 0;
$buyid = isset($buyid)? HtmlReplace($buyid, 1) : '';
if ($dopost === "bank_ok") {
    $moRow = $dsql->GetOne("SELECT * FROM `#@__member_operation` WHERE buyid='$buyid' AND mid={$mid}");
    if (empty($moRow)) {
        ShowMsg("订单查询错误，请确保是您自己发起的订单", "javascript:;");
        exit;
    }
    if ($moRow['sta'] == 2) {
        ShowMsg("已完成支付，无需重复付款", "javascript:;");
        exit;
    }
    $query = "UPDATE `#@__member_operation` SET sta = '1' WHERE buyid = '{$moRow['buyid']}'";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("已经完成付款，等待管理员审核", "operation.php");
    exit;
} else if ($dopost === "wechat_ok") {
    $moRow = $dsql->GetOne("SELECT * FROM `#@__member_operation` WHERE buyid='$buyid' AND mid={$mid}");
    if (empty($moRow)) {
        ShowMsg("订单查询错误，请确保是您自己发起的订单", "javascript:;");
        exit;
    }
    $pInfo = $dsql->GetOne("SELECT * FROM `#@__sys_payment` WHERE id = 1");
    $pData = (array)json_decode($pInfo['config']);
    try {
        $config = array(
            "appid" => $pData['AppID'],
            "mch_id" => $pData['MchID'],
            "mch_key" => $pData['APIv2Secret'],
        );
        $wechat = new \WeChat\Pay($config);
        $options = array(
            'out_trade_no' => $buyid,
        );
        $result = $wechat->queryOrder($options);
    } catch (Exception $e) {
        ShowMsg("生成微信支付信息失败，请联系网站管理员", "javascript:;");
        exit;
    }
    if ($result['return_code'] === "SUCCESS" && $result['trade_state'] === "SUCCESS") {
        if ($moRow['product'] === "card") {
            $row = $dsql->GetOne("SELECT * FROM `#@__moneycard_type` WHERE tid='{$moRow['pid']}'");
            $query = "UPDATE `#@__member_operation` SET sta = '2' WHERE buyid = '$buyid'";
            $dsql->ExecuteNoneQuery($query);
            $query = "UPDATE `#@__member` SET money = money+{$row['num']} WHERE mid = '$mid'";
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
        ShowMsg("已经完成付款", "index.php");
        exit;
    } else {
        ShowMsg("尚未完成付款操作", "index.php");
        exit;
    }
}
if (isset($pd_encode) && isset($pd_verify) && md5("payment".$pd_encode.$cfg_cookie_encode) == $pd_verify) {
    $result = json_decode(mchStrCode($pd_encode, 'DECODE'));
    $product = preg_replace("#[^0-9a-z]#i", "", $result->product);
    $pid = preg_replace("#[^0-9a-z]#i", "", $result->pid);
    $row  = $dsql->GetOne("SELECT * FROM `#@__member_operation` WHERE mid='$mid' AND sta=0 AND product='$product'");
    if (!isset($row['buyid'])) {
        ShowMsg("请不要重复提交表单", 'javascript:;');
        exit();
    }
    if ($paytype === 0) {
        ShowMsg("请选择支付方式", 'javascript:;');
        exit();
    }
    $buyid = $row['buyid'];
} else {
    $buyid = 'M'.$mid.'T'.$mtime.'RN'.mt_rand(100, 999);
    //删除会员旧的未付款的同类记录
    if (!empty($product)) {
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_operation` WHERE mid='$mid' AND sta=0 AND product='$product'");
    }
}
if (empty($product)) {
    ShowMsg("请选择一个产品", 'javascript:;');
    exit();
}
$pid = isset($pid) && is_numeric($pid) ? $pid : 0;
if ($product == 'member') {
    $ptype = "会员升级";
    $row = $dsql->GetOne("SELECT * FROM `#@__member_type` WHERE aid='{$pid}'");
    if (!is_array($row)) {
        ShowMsg("无法识别您的订单", 'javascript:;');
        exit();
    }
    $pname = $row['pname'];
    $price = $row['money'];
} else if ($product == 'card') {
    $ptype = "积分购买";
    $row = $dsql->GetOne("SELECT * FROM `#@__moneycard_type` WHERE tid='{$pid}'");
    if (!is_array($row)) {
        ShowMsg("无法识别您的订单", 'javascript:;');
        exit();
    }
    $pname = $row['pname'];
    $price = $row['money'];
}
if ($paytype === 0) {
    $inquery = "INSERT INTO `#@__member_operation` (`buyid`,`pname`,`product`,`money`,`mtime`,`pid`,`mid`,`sta`,`oldinfo`) VALUES ('$buyid','$pname','$product','$price','$mtime','$pid','$mid','0','$ptype');";
    $isok = $dsql->ExecuteNoneQuery($inquery);
    if (!$isok) {
        echo "数据库出错，请重新尝试".$dsql->GetError();
        exit();
    }
    if ($price == '') {
        echo "无法识别您的订单";
        exit();
    }
    //获取支付接口设置
    $payment_list = array();
    $dsql->SetQuery("SELECT * FROM `#@__sys_payment` WHERE `status`=1 ORDER BY sortrank ASC");
    $dsql->Execute();
    $i = 0;
    while ($row = $dsql->GetArray()) {
        $payment_list[] = $row;
        $i++;
    }
    $pr_encode = array();
    foreach ($_REQUEST as $key => $val) {
        if (!in_array($key, array('product', 'pid'))) {
            continue;
        }
        $val = preg_replace("#[^0-9a-z]#i", "", $val);
        $pr_encode[$key] = $val;
    }
    $pr_encode = str_replace('=', '', mchStrCode(json_encode($pr_encode)));
    $pr_verify = md5("payment".$pr_encode.$cfg_cookie_encode);
    $tpl = new DedeTemplate();
    $tpl->LoadTemplate(DEDEMEMBER.'/templets/buy_action_payment.htm');
    $tpl->Display();
} else {
    $moRow = $dsql->GetOne("SELECT * FROM `#@__member_operation` WHERE buyid='$buyid'");
    if ($moRow['sta'] == 2) {
        ShowMsg("已完成支付，无需重复付款", "javascript:;");
        exit;
    }
    if ($paytype === 1) {
        //微信支付
        include_once(DEDEINC.'/libraries/oxwindow.class.php');
        $pInfo = $dsql->GetOne("SELECT * FROM `#@__sys_payment` WHERE id = $paytype");
        $pData = (array)json_decode($pInfo['config']);
        $config = array(
            "appid" => $pData['AppID'],
            "mch_id" => $pData['MchID'],
            "mch_key" => $pData['APIv2Secret'],
        );
        try {
            $wechat = new \WeChat\Pay($config);
            $options = array(
                'product_id'       => $buyid,
                'body'             => $row['pname'],
                'out_trade_no'     => $buyid,
                'total_fee'        => $row['money']*100,
                'trade_type'       => 'NATIVE',
                'notify_url'       => $GLOBALS['cfg_basehost'].$GLOBALS['cfg_phpurl'].'/notify.php?dopost=wechat',
            );
            //生成预支付码
            $result = $wechat->createOrder($options);
            $payurl = $result['code_url'];
            $msg = "<div>打开微信扫一扫，扫描以下二维码支付<div><div id='qrcode' style='margin:15px 0;width:200px;height:200px'></div><div><a href='buy_action.php?dopost=wechat_ok&buyid={$buyid}' class='btn btn-success btn-sm'>已完成支付</a> <a href='operation.php' class='btn btn-outline-success btn-sm'>返回订单管理</a></div>";
            $script = '<script type="text/javascript">var qrcode = new QRCode(document.getElementById("qrcode"), {
                width : 300,
                height : 300,
                correctLevel : 3
            });qrcode.makeCode("'.$payurl.'");</script>';
            $wintitle = "微信支付";
            $wecome_info = " ";//这个空格不要去
            $win = new OxWindow();
            $win->AddMsgItem($msg);
            $winform = $win->GetWindow("hand", "&nbsp;", false);
            $win->Display(DEDEMEMBER."/templets/win_templet.htm");
        } catch (Exception $e) {
            ShowMsg("生成微信支付信息失败，请联系网站管理员", "javascript:;");
            exit;
        }
    } elseif ($paytype === 2) {
        include_once(DEDEINC.'/libraries/oxwindow.class.php');
        $pInfo = $dsql->GetOne("SELECT * FROM `#@__sys_payment` WHERE id = $paytype");
        $pData = (array)json_decode($pInfo['config']);
        $config = array(
            "sign_type" => $pData['SignType'],
            "appid" => $pData['APPID'],
            "private_key" => $pData['PrivateKey'],
            "public_key" => $pData['CertPublicKey'],
            "notify_url" => $GLOBALS['cfg_basehost'].$GLOBALS['cfg_phpurl'].'/notify.php?dopost=alipay',
            "return_url" => $GLOBALS['cfg_basehost'].$GLOBALS['cfg_phpurl'].'/return.php?dopost=alipay',
        );
        //var_dump($config);exit;
        //支付宝
        try {
            //实例支付对象
            $pay = \AliPay\Web::instance($config);
            $result = $pay->apply(array(
                'out_trade_no' => $buyid,//商户订单号
                'total_amount' => $row['money'],//支付金额
                'subject'      => $row['pname'],//支付订单描述
            ));
            echo $result;
        } catch (Exception $e) {
            ShowMsg("生成微信支付信息失败，请联系网站管理员", "javascript:;");
            exit;
        }
    }  elseif ($paytype === 3) {
        include_once(DEDEINC.'/libraries/oxwindow.class.php');
        //银行转账
        $pInfo = $dsql->GetOne("SELECT * FROM `#@__sys_payment` WHERE id = $paytype");
        $pData = (array)json_decode($pInfo['config']);
        $msg = "<p>请汇款至如下账户：</p><p>账户名：{$pData['AccountName']}</p><p>账号：{$pData['AccountNO']}</p><p>开户行：{$pData['Name']}</p><p>备注：{$buyid}</p><p>如您已经完成转账，请点击下面按钮，等待管理员确认后即可完成充值</p><div><a href='buy_action.php?dopost=bank_ok&buyid={$buyid}' class='btn btn-success btn-sm'>已完成银行转账</a> <a href='operation.php' class='btn btn-outline-success btn-sm'>返回订单管理</a></div>";
        $wintitle = "银行转账";
        $wecome_info = " ";//这个空格不要去
        $win = new OxWindow();
        $win->AddMsgItem($msg);
        $winform = $win->GetWindow("hand", "&nbsp;", false);
        $win->Display(DEDEMEMBER."/templets/win_templet.htm");
    } elseif ($paytype === 4) {
        //余额付款
        if ($cfg_ml->M_UserMoney < $row['money']) {
            ShowMsg("余额不足，请确保当前账户有足够金币支付", "javascript:;");
            exit;
        }
        $query = "UPDATE `#@__member_operation` SET sta = '2' WHERE buyid = '$buyid'";
        if ($product == 'card') {
            $dsql->ExecuteNoneQuery($query);
            $query = "UPDATE `#@__member` SET money = money+{$row['num']} WHERE mid = '$mid'";
            $dsql->ExecuteNoneQuery($query);
            $query = "UPDATE `#@__member` SET user_money = user_money-{$row['money']} WHERE mid = '$mid'";
            $dsql->ExecuteNoneQuery($query);
        } else if ($product == 'member') {
            $rank = $row['rank'];
            $exptime = $row['exptime'];
            $rs = $dsql->GetOne("SELECT uptime,exptime FROM `#@__member` WHERE mid='".$mid."'");
            if ($rs['uptime']!=0 && $rs['exptime']!=0) {
                $nowtime = time();
                $mhasDay = $rs['exptime'] - ceil(($nowtime - $rs['uptime'])/3600/24) + 1;
                $mhasDay=($mhasDay>0)? $mhasDay : 0;
            }
            $memrank = $dsql->GetOne("SELECT money,scores FROM `#@__arcrank` WHERE `rank`='$rank'");
            //更新会员信息
            $sqlm =  "UPDATE `#@__member` SET `rank`='$rank',`money`=`money`+'{$memrank['money']}',scores=scores+'{$memrank['scores']}',exptime='$exptime'+'$mhasDay',uptime='".time()."' WHERE mid='".$mid."'";
            $sqlmo = "UPDATE `#@__member_operation` SET sta='2',oldinfo='会员升级成功' WHERE buyid='$buyid' ";
            if (!($dsql->ExecuteNoneQuery($sqlm) && $dsql->ExecuteNoneQuery($sqlmo))) {
                ShowMsg("升级会员失败", "javascript:;");
                exit;
            }
        }
        ShowMsg("成功使用余额付款", "javascript:;");
        exit;
    } elseif ($paytype === 5) {
        //货到付款
        ShowMsg("虚拟物品，不支持货到付款", "javascript:;");
        exit;
    }
}
/**
 *  加密函数
 *
 * @access    public
 * @param     string  $string  字符串
 * @param     string  $operation  操作
 * @return    string
 */
function mchStrCode($string, $operation = 'ENCODE')
{
    $key_length = 4;
    $expiry = 0;
    $key = md5($GLOBALS['cfg_cookie_encode']);
    $fixedkey = md5($key);
    $egiskeys = md5(substr($fixedkey, 16, 16));
    $runtokey = $key_length ? ($operation == 'ENCODE' ? substr(md5(microtime(true)), -$key_length) : substr($string, 0, $key_length)) : '';
    $keys = md5(substr($runtokey, 0, 16).substr($fixedkey, 0, 16).substr($runtokey, 16).substr($fixedkey, 16));
    $string = $operation == 'ENCODE' ? sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$egiskeys), 0, 16).$string : base64_decode(substr($string, $key_length));
    $i = 0;
    $result = '';
    $string_length = strlen($string);
    for ($i = 0; $i < $string_length; $i++) {
        $result .= chr(ord($string[$i]) ^ ord($keys[$i % 32]));
    }
    if ($operation == 'ENCODE') {
        return $runtokey.str_replace('=', '', base64_encode($result));
    } else {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$egiskeys), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    }
}
?>