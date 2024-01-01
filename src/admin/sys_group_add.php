<?php
/**
 * 添加系统会员组
 *
 * @version        $id:sys_group_add.php 22:28 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_Group');
if (!empty($dopost)) {
    $row = $dsql->GetOne("SELECT * FROM `#@__admintype` WHERE `rank`='".$rankid."'");
    if (is_array($row)) {
        ShowMsg('您所创建的组别的级别值已存在，不允许重复', '-1');
        exit();
    }
    if ($rankid > 10) {
        ShowMsg('组级别值不能大于10，否则一切权限设置均无效', '-1');
        exit();
    }
    $AllPurviews = '';
    if (is_array($purviews)) {
        foreach ($purviews as $pur) {
            $AllPurviews = $pur.' ';
        }
        $AllPurviews = trim($AllPurviews);
    }
    $dsql->ExecuteNoneQuery("INSERT INTO `#@__admintype` (`rank`,typename,`system`,purviews) VALUES ('$rankid','$groupname', 0, '$AllPurviews');");
    ShowMsg("成功创建一个新的会员组", "sys_group.php");
    exit();
}
include DedeInclude('templets/sys_group_add.htm');
?>