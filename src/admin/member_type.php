<?php
/**
 * 会员类型
 *
 * @version        $Id: member_type.php 1 14:14 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('member_Type');
if (empty($dopost)) $dopost = "";
//保存修改
if ($dopost == "save") {
    $startID = 1;
    $endID = $idend;
    for (; $startID <= $endID; $startID++) {
        $query = '';
        $aid = ${'ID_'.$startID};
        $pname =   ${'pname_'.$startID};
        $rank =    ${'rank_'.$startID};
        $money =   ${'money_'.$startID};
        $exptime = ${'exptime_'.$startID};
        if (isset(${'check_'.$startID})) {
            if ($pname != '') {
                $query = "UPDATE `#@__member_type` SET pname='$pname',money='$money',`rank`='$rank',exptime='$exptime' WHERE aid='$aid'";
            }
        } else {
            $query = "DELETE FROM `#@__member_type` WHERE aid='$aid'";
        }
        if ($query != '') {
            $dsql->ExecuteNoneQuery($query);
        }
    }
    //增加新记录
    if (isset($check_new) && $pname_new != '') {
        $query = "INSERT INTO `#@__member_type` (`rank`,pname,money,exptime) VALUES ('{$rank_new}','{$pname_new}','{$money_new}','{$exptime_new}');";
        $dsql->ExecuteNoneQuery($query);
    }
    header("Content-Type: text/html; charset=utf-8");
    echo "<script> alert('".Lang('member_type_success')."'); </script>";
}
$arcranks = array();
$dsql->SetQuery("SELECT * FROM `#@__arcrank` WHERE `rank`>10");
$dsql->Execute();
while ($row = $dsql->GetArray()) {
    $arcranks[$row['rank']] = $row['membername'];
}
$times = array();
$times[7] = Lang('day_7');
$times[30] = Lang('day_30');
$times[90] = Lang('day_90');
$times[183] = Lang('day_183');
$times[366] = Lang('day_366');
$times[32767] = Lang('day_32767');
require_once(DEDEADMIN."/templets/member_type.htm");
?>