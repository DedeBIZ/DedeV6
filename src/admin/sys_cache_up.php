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
function clean_cachefiles($path) {
    $list = array();
    foreach (glob($path.'/*') as $item) {
        if(is_dir($item)) {
            $list = array_merge($list, clean_cachefiles($item));
        } else {
            $list[] = $item;
        }
    }
    foreach ($list as $tmpfile) {
        @unlink($tmpfile);
    }
    return true;
}
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
        ShowMsg("开始清理栏目缓存，继续清理枚举缓存", "sys_cache_up.php?dopost=ok&step=2&uparc=$uparc");
        exit();
    }
    //更新枚举缓存
    else if ($step == 2) {
        include_once(DEDEINC."/enums.func.php");
        WriteEnumsCache();
        ShowMsg("正在清理枚举缓存，继续清理调用缓存", "sys_cache_up.php?dopost=ok&step=3&uparc=$uparc");
        exit();
    }
    //清理arclist调用缓存、过期会员浏览历史、过期短信、之前15天流量统计
    else if ($step == 3) {
        echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$cfg_soft_lang\">";
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arccache`");
        $msg = array();
        $msg[] = "继续清理arclist调用缓存，过期会员浏览历史";
        $oldtime = time() - (90 * 24 * 3600);
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_pms` WHERE sendtime<'$oldtime' ");
        $msg[] = "过期短信，错误文档";
        $limit = date('Ymd', strtotime('-15 days'));
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__statistics_detail` WHERE created_date < '$limit'");
        $msg[] = "之前15天流量统计";
        $url = "sys_cache_up.php?dopost=ok&step=-1&uparc=$uparc";
        clean_cachefiles("../data/cache");
        clean_cachefiles("../data/tplcache");
        clean_cachefiles("../data/sessions");
        clean_cachefiles("../static/enums");
        if ($uparc == 1) {
            $url = "sys_cache_up.php?dopost=ok&step=9";
        }
        ShowMsg(implode("，", $msg), $url);
        exit();
    }
    //修正错误文档
    else if ($step == 9) {
        ShowMsg('清理错误文档已取消，建议用系统修复工具清理', 'sys_cache_up.php?dopost=ok&step=-1&uparc=1');
        exit();
    }
}
include DedeInclude('templets/sys_cache_up.htm');
?>