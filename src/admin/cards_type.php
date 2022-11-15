<?php
/**
 * 点卡类型
 *
 * @version        $id:cards_type.php 14:31 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
CheckPurview('member_Type');
if (empty($dopost)) $dopost = "";

//保存修改
if ($dopost == "save") {
    $startID = 1;
    $endID = $idend;
    for (; $startID <= $endID; $startID++) {
        $query = '';
        $tid = ${'ID_'.$startID};
        $pname =   ${'pname_'.$startID};
        $money =    ${'money_'.$startID};
        $num =   ${'num_'.$startID};
        if (isset(${'check_'.$startID})) {
            if ($pname != '') {
                $query = "UPDATE `#@__moneycard_type` SET pname='$pname',money='$money',num='$num' WHERE tid='$tid'";
                $dsql->ExecuteNoneQuery($query);
                $query = "UPDATE `#@__moneycard_record` SET money='$money',num='$num' WHERE ctid='$tid' ; ";
                $dsql->ExecuteNoneQuery($query);
            }
        } else {
            $query = "DELETE FROM `#@__moneycard_type` WHERE tid='$tid' ";
            $dsql->ExecuteNoneQuery($query);
            $query = "DELETE FROM `#@__moneycard_record` WHERE ctid='$tid' AND isexp<>-1 ; ";
            $dsql->ExecuteNoneQuery($query);
        }
    }

    //增加新记录
    if (isset($check_new) && $pname_new != '') {
        $query = "INSERT INTO `#@__moneycard_type` (num,pname,money) VALUES ('{$num_new}','{$pname_new}','{$money_new}');";
        $dsql->ExecuteNoneQuery($query);
    }
    header("charset={$cfg_soft_lang}");
    echo "<script>alert('成功更新点卡产品分类表');</script>";
}
require_once(DEDEADMIN."/templets/cards_type.htm");
?>