<?php
/**
 * 清除缓存
 *
 * @version        $Id: sys_cache_up.php 1 16:22 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('sys_ArcBatch');
if (empty($dopost)) $dopost = '';
if (empty($step)) $step = 1;
if ($dopost == "ok") {
    if (empty($uparc)) $uparc = 0;
    if ($step == -1) {
        if ($uparc == 0) sleep(1);
        ShowMsg(Lang("sys_cache_up_success"), "javascript:;");
        exit();
    }
    //更新栏目缓存
    else if ($step == 1) {
        UpDateCatCache();
        ClearOptCache();
        ShowMsg(Lang("sys_cache_up_success_1"), "sys_cache_up.php?dopost=ok&step=2&uparc=$uparc");
        exit();
    }
    //更新枚举缓存
    else if ($step == 2) {
        helper('enums');
        WriteEnumsCache();
        //WriteAreaCache(); 已过期
        ShowMsg(Lang("sys_cache_up_success_2"), "sys_cache_up.php?dopost=ok&step=3&uparc=$uparc");
        exit();
    }
    //清理arclist调用缓存、过期会员访问历史、过期短信
    else if ($step == 3) {
        echo '<meta charset="utf-8">';
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arccache`");
        echo DedeAlert(Lang("sys_cache_up_success_3_1"), ALERT_SUCCESS);
        $oldtime = time() - (90 * 24 * 3600);
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_pms` WHERE sendtime<'$oldtime'");
        echo DedeAlert(Lang("sys_cache_up_success_3_2"), ALERT_SUCCESS);
        if ($uparc == 1) {
            echo "<script>location='sys_cache_up.php?dopost=ok&step=9';</script>";
        } else {
            echo "<script>location='sys_cache_up.php?dopost=ok&step=-1&uparc=$uparc';</script>";
        }
        exit();
    }
    //修正错误文档
    else if ($step == 9) {
        ShowMsg(Lang('sys_cache_up_success_9'), 'sys_cache_up.php?dopost=ok&step=-1&uparc=1', 0, 5000);
        exit();
    }
}
include DedeInclude('templets/sys_cache_up.htm');
?>