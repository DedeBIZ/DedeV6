<?php
/**
 * 系统权限组添加
 *
 * @version        $Id: sys_group_add.php 1 22:28 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
$dlang->extendLang('grouplist'); //加载用户权限语言包
UserLogin::CheckPurview('sys_Group');
if (!empty($dopost)) {
    $row = $dsql->GetOne("SELECT * FROM `#@__admintype` WHERE `rank`='".$rankid."'");
    if (is_array($row)) {
        ShowMsg(Lang('sys_group_add_err_noneresult'), '-1');
        exit();
    }
    if ($rankid > 10) {
        ShowMsg(Lang('sys_group_add_err_rank'), '-1');
        exit();
    }
    $AllPurviews = '';
    if (is_array($purviews)) {
        foreach ($purviews as $pur) {
            $AllPurviews = $pur.' ';
        }
        $AllPurviews = trim($AllPurviews);
    }
    $dsql->ExecuteNoneQuery("INSERT INTO `#@__admintype` (`rank`,typename,`system`,purviews) VALUES ('$rankid','$groupname',0,'$AllPurviews');");
    ShowMsg(Lang("sys_group_add_success"), "sys_group.php");
    exit();
}
include DedeInclude('templets/sys_group_add.htm');
?>