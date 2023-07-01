<?php
/**
 * 友情链接类型
 *
 * @version        $id:friendlink_type.php 8:48 2010年7月13日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (empty($dopost)) $dopost = '';
//保存修改
if ($dopost == "save") {
    $startID = 1;
    $endID = $idend;
    for (; $startID <= $endID; $startID++) {
        $query = '';
        $tid = intval(${'ID_'.$startID});
        $pname =  HtmlReplace(${'pname_'.$startID},-1);
        if (isset(${'check_'.$startID})) {
            if ($pname != '') {
                $query = "UPDATE `#@__flinktype` SET typename='$pname' WHERE id='$tid' ";
                $dsql->ExecuteNoneQuery($query);
            }
        } else {
            $query = "DELETE FROM `#@__flinktype` WHERE id='$tid' ";
            $dsql->ExecuteNoneQuery($query);
        }
    }
    //添加新记录
    if (isset($check_new) && $pname_new != '') {
        $pname_new = HtmlReplace($pname_new, -1);
        $query = "INSERT INTO `#@__flinktype` (typename) VALUES ('{$pname_new}');";
        $dsql->ExecuteNoneQuery($query);
    }
    header("Content-Type:text/html; charset={$cfg_soft_lang}");
    echo "<script>alert('成功更新友情链接类型');</script>";
}
include DedeInclude('templets/friendlink_type.htm');
?>