<?php
/**
 * 会员查看
 *
 * @version        $Id: member_view.php 1 14:15 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('member_Edit');
$ENV_GOBACK_URL = isset($_COOKIE['ENV_GOBACK_URL']) ? "member_main.php" : '';
$id = preg_replace("#[^0-9]#", "", $id);
$row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='$id'");
$staArr = array(
    -10 => Lang('member_sta_-10'),
    -2 => Lang('member_sta_-2'),
    -1 => Lang('member_sta_-1'),
    0 => Lang('member_sta_0'),
    1 => Lang('member_sta_1'),
    2 => Lang('member_sta_2')
);
//如果这个用户是管理员帐号，必须有足够权限的用户才能操作
if ($row['matt'] == 10) UserLogin::CheckPurview('sys_User');
if ($row['uptime'] > 0 && $row['exptime'] > 0) {
    $mhasDay = $row['exptime'] - ceil((time() - $row['uptime']) / 3600 / 24) + 1;
} else {
    $mhasDay = 0;
}
include DedeInclude('templets/member_view.htm');
?>