<?php
/**
 * 会员查看
 *
 * @version        $id:member_view.php 14:15 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__)."/config.php");
CheckPurview('member_Edit');
$ENV_GOBACK_URL = isset($_COOKIE['ENV_GOBACK_URL']) ? "member_main.php" : '';
$id = preg_replace("#[^0-9]#", "", $id);
$row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='$id'");
$staArr = array(
    -10 => '等待验证邮件',
    -2 => '限制禁言会员',
    -1 => '未通过审核',
    0 => '审核通过需要填写信息',
    1 => '待补充完善信息',
    2 => '正常使用'
);
//如果这个会员是管理员帐号，必须有足够权限的会员才能操作
if ($row['matt'] == 10) CheckPurview('sys_User');
if ($row['uptime'] > 0 && $row['exptime'] > 0) {
    $mhasDay = $row['exptime'] - ceil((time() - $row['uptime']) / 3600 / 24) + 1;
} else {
    $mhasDay = 0;
}
function GetMemberTypeName($rank)
{
    global $dsql;
    if ($rank == 0) {
        return '注册会员';
    } else {
        $row = $dsql->GetOne("SELECT membername FROM `#@__arcrank` WHERE `rank`='".$rank."'");
        return $row['membername'];
    }
}

function GetHonor($scores)
{
    global $dsql;
    $sql = "SELECT titles From `#@__scores` WHERE integral<={$scores} ORDER BY integral DESC";
    $scrow = $dsql->GetOne($sql);
    return $scrow['titles'];
}
include DedeInclude('templets/member_view.htm');
?>