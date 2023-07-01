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
        ShowMsg("全部缓存清理已完成", "javascript:;");
        exit();
    }
    //更新栏目缓存
    else if ($step == 1) {
        UpDateCatCache();
        ClearOptCache();
        ShowMsg("正在清理栏目缓存，继续清理枚举缓存", "sys_cache_up.php?dopost=ok&step=2&uparc=$uparc");
        exit();
    }
    //更新枚举缓存
    else if ($step == 2) {
        include_once(DEDEINC."/enums.func.php");
        WriteEnumsCache();
        //WriteAreaCache(); 已过期
        ShowMsg("正在清理枚举缓存，继续清理调用缓存", "sys_cache_up.php?dopost=ok&step=3&uparc=$uparc");
        exit();
    }
    //清理arclist调用缓存、过期会员浏览历史、过期短信、陈旧的流量统计数据
    else if ($step == 3) {
        echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$cfg_soft_lang\">";
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arccache`");
        $msg = array();
        $msg[] = "正在清理arclist调用缓存，继续清理过期会员浏览历史";
        $oldtime = time() - (90 * 24 * 3600);
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_pms` WHERE sendtime<'$oldtime' ");
        $msg[] = "正在清理过期短信，继续修正错误文档，要占较长时间";
        $limit = date('Ymd', strtotime('-15 days'));
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__statistics_detail` WHERE created_date < '$limit'");
        $msg[] = "正在清理15天之前流量统计";
        $url = "sys_cache_up.php?dopost=ok&step=-1&uparc=$uparc";
        if ($uparc == 1) {
            $url = "sys_cache_up.php?dopost=ok&step=9";
        }
        ShowMsg(implode("<br/>",$msg),$url);
        exit();
    }
    //修正错误文档
    else if ($step == 9) {
        ShowMsg('清理错误文档已取消，到系统修复工具进行操作', 'sys_cache_up.php?dopost=ok&step=-1&uparc=1', 0, 5000);
        exit();
    }
}
include DedeInclude('templets/sys_cache_up.htm');
?>