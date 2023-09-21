<?php
/**
 * 检测重复文档
 *
 * @version        $id:article_test_same.php 14:31 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
@set_time_limit(0);
CheckPurview('sys_ArcBatch');
if (empty($dopost)) $dopost = '';
if ($dopost == 'analyse') {
    $arr = $dsql->getone("SELECT maintable FROM `#@__channeltype` WHERE id='$channelid' ");
    if (is_array($arr)) {
        $maintable = $arr['maintable'];
    } else {
        showmsg('栏目id不正确，无法处理', 'javascript:;');
        exit();
    }
    $pagesize = intval($pagesize);
    $dsql->SetQuery("SELECT COUNT(title) AS dd,title FROM `$maintable` WHERE channel='$channelid' GROUP BY title ORDER BY dd DESC LIMIT 0, $pagesize");
    $dsql->Execute();
    $allarc = 0;
    include DedeInclude('templets/article_result_same.htm');
    exit();
}
//删除选中的文档（只保留一条）
else if ($dopost == 'delsel') {
    require_once(DEDEINC."/typelink/typelink.class.php");
    require_once(dirname(__FILE__)."/inc/inc_batchup.php");
    if (empty($titles)) {
        header("Content-Type: text/html; charset={$cfg_ver_lang}");
        echo "<meta charset={$cfg_ver_lang}\">\r\n";
        echo "没有指定删除的文档";
        exit();
    }
    if (!$dsql->IsTable($maintable)) {
        ShowMsg("数据表名称错误", "javascript:;");
        exit();
    }
    $titless = split('`', $titles);
    if ($channelid < -1) {
        $orderby = ($deltype == 'delnew' ? " ORDER BY aid DESC " : " ORDER BY aid ASC ");
    } else {
        $orderby = ($deltype == 'delnew' ? " ORDER BY id DESC " : " ORDER BY id ASC ");
    }
    $totalarc = 0;
    foreach ($titless as $title) {
        $title = trim($title);
        $title = addslashes($title == '' ? '' : urldecode($title));
        if ($channelid < -1) {
            $q1 = "SELECT aid as id,title FROM `$maintable` WHERE channel='$channelid' AND title='$title' $orderby ";
        } else {
            $q1 = "SELECT id,title FROM `$maintable` WHERE channel='$channelid' AND title='$title' $orderby ";
        }
        $dsql->SetQuery($q1);
        $dsql->Execute();
        $rownum = $dsql->GetTotalRow();
        if ($rownum < 2) continue;
        $i = 1;
        while ($row = $dsql->GetObject()) {
            $i++;
            $naid = $row->id;
            $ntitle = $row->title;
            if ($i > $rownum) continue;
            $totalarc++;
            DelArc($naid, 'OFF');
        }
    }
    $dsql->ExecuteNoneQuery(" OPTIMIZE TABLE `$maintable`;");
    ShowMsg("成功删除{$totalarc}篇重复文档", "javascript:;");
    exit();
}
//向导页
$channelinfos = array();
$dsql->SetQuery("SELECT id,typename,maintable,addtable FROM `#@__channeltype`");
$dsql->Execute();
while ($row = $dsql->GetArray()) $channelinfos[] = $row;
include DedeInclude('templets/article_test_same.htm');
?>