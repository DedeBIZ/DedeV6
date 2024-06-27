<?php
/**
 * 修改系统会员组
 *
 * @version        $id:sys_group_edit.php 22:28 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_Group');
if (empty($dopost)) $dopost = '';
if ($dopost == 'save') {
    if ($rank == 10) {
        ShowMsg('超级管理员的权限不允许修改', 'sys_group.php');
        exit();
    }
    $purview = '';
    if (is_array($purviews)) {
        foreach ($purviews as $p) {
            $purview .= "$p ";
        }
        $purview = trim($purview);
    }
    $dsql->ExecuteNoneQuery("UPDATE `#@__admintype` SET typename='$typename',purviews='$purview' WHERE CONCAT(`rank`)='$rank'");
    ShowMsg('成功修改会员组的权限', 'sys_group.php');
    exit();
} else if ($dopost == 'del') {
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__admintype` WHERE CONCAT(`rank`)='$rank' AND `system`='0';");
    ShowMsg("成功删除一个账户组", "sys_group.php");
    exit();
}
$groupRanks = array();
$groupSet = $dsql->GetOne("SELECT * FROM `#@__admintype` WHERE CONCAT(`rank`)='{$rank}' ");
$groupRanks = explode(' ', $groupSet['purviews']);
include DedeInclude('templets/sys_group_edit.htm');
//检查是否已经有此权限
function CRank($n)
{
    global $groupRanks;
    return in_array($n, $groupRanks) ? ' checked' : '';
}
?>