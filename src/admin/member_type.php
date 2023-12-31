<?php
/**
 * 会员等级分类
 *
 * @version        $id:member_type.php 14:14 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('member_Type');
if (empty($dopost)) $dopost = '';
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
            $query = "DELETE FROM `#@__member_type` WHERE aid='$aid' ";
        }
        if ($query != '') {
            $dsql->ExecuteNoneQuery($query);
        }
    }
    //添加新记录
    if (isset($check_new) && $pname_new != '') {
        $query = "INSERT INTO `#@__member_type` (`rank`,pname,money,exptime) VALUES ('{$rank_new}','{$pname_new}','{$money_new}','{$exptime_new}');";
        $dsql->ExecuteNoneQuery($query);
    }
    header("Content-Type:text/html; charset={$cfg_soft_lang}");
    echo "<script>alert('成功更新会员等级分类');</script>";
}
$arcranks = array();
$dsql->SetQuery("SELECT * FROM `#@__arcrank` WHERE `rank`>10 ");
$dsql->Execute();
while ($row = $dsql->GetArray()) {
    $arcranks[$row['rank']] = $row['membername'];
}
$times = array();
$times[7] = '一周';
$times[30] = '一个月';
$times[90] = '三个月';
$times[183] = '半年';
$times[366] = '一年';
$times[32767] = '终身';
require_once(DEDEADMIN."/templets/member_type.htm");
?>