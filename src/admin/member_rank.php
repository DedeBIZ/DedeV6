<?php
/**
 * 会员级别设置
 *
 * @version        $id:member_rank.php 12:37 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('member_Type');
if (empty($dopost)) $dopost = '';
//保存修改
if ($dopost == 'save') {
    $startID = 1;
    $endID = $idend;
    for (; $startID <= $endID; $startID++) {
        $query = '';
        $id = ${"ID_".$startID};
        $name = ${"name_".$startID};
        $rank = ${"rank_".$startID};
        $money = ${"money_".$startID};
        $scores = ${"scores_".$startID};
        if (isset(${"check_".$startID})) {
            if ($rank > 0) {
                $query = "UPDATE `#@__arcrank` SET membername='$name',money='$money',`rank`='$rank',scores='$scores' WHERE id='$id' ";
            }
        } else {
            $query = "DELETE FROM `#@__arcrank` WHERE id='$id' AND `rank`<>10";
        }
        if ($query != '') $dsql->ExecuteNoneQuery($query);
    }
    if (isset($check_new)) {
        if ($rank_new > 0 && $name_new != '' && $rank_new > 10) {
            $inquery = "INSERT INTO `#@__arcrank` (`rank`,`membername`,`adminrank`,`money`,`scores`,`purviews`) VALUES ('$rank_new','$name_new','5','$money_new','$scores',''); ";
            $dsql->ExecuteNoneQuery($inquery);
        }
    }
    echo "<script>alert('成功更新会员等级表');</script>";
}
if ($dopost == 'del') {
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__arcrank` WHERE id='$id' AND `rank`<>10");
    ShowMsg("删除成功", "member_rank.php");
    exit();
}
$dsql->SetQuery("SELECT * FROM `#@__arcrank` WHERE `rank`>0 ORDER BY `rank`");
$dsql->Execute();
include DedeInclude('templets/member_rank.htm');
?>