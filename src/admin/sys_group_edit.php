<?php
/**
 * 系统权限组编辑
 *
 * @version        $Id: sys_group_edit.php 1 22:28 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('sys_Group');
if (empty($dopost)) $dopost = "";
if ($dopost == 'save') {
    if ($rank == 10) {
        ShowMsg(Lang('sys_group_edit_err_admin'), 'sys_group.php');
        exit();
    }
    $purview = "";
    if (is_array($purviews)) {
        foreach ($purviews as $p) {
            $purview .= "$p ";
        }
        $purview = trim($purview);
    }
    $dsql->ExecuteNoneQuery("UPDATE `#@__admintype` SET typename='$typename',purviews='$purview' WHERE CONCAT(`rank`)='$rank'");
    ShowMsg(Lang('sys_group_edit_success'), 'sys_group.php');
    exit();
} else if ($dopost == 'del') {
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__admintype` WHERE CONCAT(`rank`)='$rank' AND `system`='0';");
    ShowMsg(Lang("sys_group_delete_success!"), "sys_group.php");
    exit();
}
$groupRanks = array();
$groupSet = $dsql->GetOne("SELECT * FROM `#@__admintype` WHERE CONCAT(`rank`)='{$rank}'");
$groupRanks = explode(' ', $groupSet['purviews']);
include DedeInclude('templets/sys_group_edit.htm');
//检查是否已经有此权限
function CRank($n)
{
    global $groupRanks;
    return in_array($n, $groupRanks) ? ' checked' : '';
}
?>