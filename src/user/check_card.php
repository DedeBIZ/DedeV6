<?php
/**
 * @version        $id:check_card.php 8:38 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckRank(0, 0);
$svali = GetCkVdValue();
if (strtolower($vdcode) != $svali || $svali == "") {
    ShowMsg("验证码不正确", "-1");
    exit();
}
$cardid = preg_replace("#[^0-9A-Za-z-]#", "", $cardid);
if (empty($cardid)) {
    ShowMsg("卡号为空", "-1");
    exit();
}
$row = $dsql->GetOne("SELECT * FROM `#@__moneycard_record` WHERE cardid='$cardid' ");
if (!is_array($row)) {
    ShowMsg("卡号错误，不存在此卡号", "-1");
    exit();
}
if ($row['isexp'] == -1) {
    ShowMsg("此卡号已经失效，不能再次使用", "-1");
    exit();
}
$hasMoney = $row['num'];
$dsql->ExecuteNoneQuery("UPDATE `#@__moneycard_record` SET uid='".$cfg_ml->M_ID."',isexp='-1',utime='".time()."' WHERE cardid='$cardid' ");
$dsql->ExecuteNoneQuery("UPDATE `#@__member` SET money=money+$hasMoney WHERE mid='".$cfg_ml->M_ID."'");
ShowMsg("充值成功，您本次增加的金币为：{$hasMoney} 个", -1);
exit();
?>