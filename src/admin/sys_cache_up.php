<?php
/**
 * 更新缓存
 *
 * @version        $id:sys_cache_up.php 16:22 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_ArcBatch');
if (empty($dopost)) $dopost = '';
if (empty($step)) $step = 1;
if ($dopost == "ok") {
    if (empty($uparc)) $uparc = 0;
    if ($step == -1) {
        if ($uparc == 0) sleep(1);
        ShowMsg("全部缓存清理完成", "javascript:;");
        exit();
    } else if ($step == 1) {
        UpDateCatCache();
        ClearOptCache();
        ShowMsg("完成所有栏目缓存清理，开始清理枚举缓存", "sys_cache_up.php?dopost=ok&step=2&uparc=$uparc");
        exit();
    } else if ($step == 2) {
        include_once(DEDEINC."/enums.func.php");
        WriteEnumsCache();
        ShowMsg("正在清理枚举缓存，继续清理文档调用缓存", "sys_cache_up.php?dopost=ok&step=3&uparc=$uparc");
        exit();
    } else if ($step == 3) {
        echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$cfg_soft_lang\">";
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arccache`");
        $msg = array();
        $msg[] = "正在清理文档调用，过期会员浏览记录";
        $oldtime = time() - (90 * 24 * 3600);
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_pms` WHERE sendtime<'$oldtime' ");
        $msg[] = "过期会员短信";
        $limit = date('Ymd', strtotime('-15 days'));
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__statistics_detail` WHERE created_date<'$limit' ");
        $msg[] = "过期流量统计等缓存";
        $url = "sys_cache_up.php?dopost=ok&step=-1&uparc=$uparc";
        if ($uparc == 1) {
            $url = "sys_cache_up.php?dopost=ok&step=9";
        }
        ShowMsg(implode("，", $msg), $url);
        exit();
    } else if ($step == 9) {
        ShowMsg('已取消清理错误文档，建议用系统修复工具', 'sys_cache_up.php?dopost=ok&step=-1&uparc=1');
        exit();
    }
}
include DedeInclude('templets/sys_cache_up.htm');
?>