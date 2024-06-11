<?php
/**
 * 文档批量维护操作
 *
 * @version        $id:content_batch_up.php 14:31 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_ArcBatch');
require_once(DEDEINC."/typelink/typelink.class.php");
require_once(DEDEADMIN."/inc/inc_batchup.php");
@set_time_limit(0);
//获取id条件
$startid = empty($startid)? 0 : intval($startid);
$endid = empty($endid)? 0 : intval($endid);
$seltime = empty($seltime)? 0 : intval($seltime);
$typeid = empty($typeid)? 0 : intval($typeid);
$userid = empty($userid)? '' : HtmlReplace($userid);
//生成网页操作由其它页面处理
if ($action == "makehtml") {
    $jumpurl  = "makehtml_archives_action.php?endid=$endid&startid=$startid";
    $jumpurl .= "&typeid=$typeid&pagesize=20&seltime=$seltime";
    $jumpurl .= "&stime=".urlencode($starttime)."&etime=".urlencode($endtime);
    header("Location: $jumpurl");
    exit();
}
$gwhere = " WHERE 1 ";
if ($startid > 0) $gwhere .= " AND id>= $startid ";
if ($endid > $startid) $gwhere .= " AND id<= $endid ";
$idsql = '';
if ($typeid != 0) {
    $ids = GetSonIds($typeid);
    $ids = preg_replace("#[^\d|,]#","",$ids);
    $gwhere .= " AND typeid IN($ids) ";
}
if ($seltime == 1) {
    $t1 = GetMkTime($starttime);
    $t2 = GetMkTime($endtime);
    $gwhere .= " AND (senddate >= $t1 AND senddate <= $t2) ";
}
if (!empty($userid)) {
    $row = $dsql->GetOne("SELECT `mid` FROM `#@__member` WHERE `userid` LIKE '$userid'");
    if (is_array($row)) {
        $gwhere .= " AND mid = {$row['mid']} ";
    }
}
//特殊操作
if (!empty($heightdone)) $action = $heightdone;
//指量审核
if ($action == 'check') {
    if (empty($startid) || empty($endid) || $endid < $startid) {
        ShowMsg('该操作必须指定起始id', 'javascript:;');
        exit();
    }
    $jumpurl  = "makehtml_archives_action.php?endid=$endid&startid=$startid";
    $jumpurl .= "&typeid=$typeid&pagesize=20&seltime=$seltime";
    $jumpurl .= "&stime=".urlencode($starttime)."&etime=".urlencode($endtime);
    $dsql->SetQuery("SELECT id,arcrank FROM `#@__arctiny` $gwhere");
    $dsql->Execute('c');
    while ($row = $dsql->GetObject('c')) {
        if ($row->arcrank == -1) {
            $dsql->ExecuteNoneQuery("UPDATE `#@__arctiny` SET arcrank=0 WHERE id='{$row->id}'");
            $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET arcrank=0 WHERE id='{$row->id}'");
        }
    }
    ShowMsg("完成数据库的审核处理，准备更新网页", $jumpurl);
    exit();
}
//批量删除
else if ($action == 'del') {
    if (empty($startid) || empty($endid) || $endid < $startid) {
        ShowMsg('该操作必须指定起始id', 'javascript:;');
        exit();
    }
    $dsql->SetQuery("SELECT id FROM `#@__archives` $gwhere");
    $dsql->Execute('x');
    $tdd = 0;
    while ($row = $dsql->GetObject('x')) {
        if (DelArc($row->id)) $tdd++;
    }
    ShowMsg("成功删除".$tdd."条记录", "javascript:;");
    exit();
}
//删除空标题文档
else if ($action == 'delnulltitle') {
    $dsql->SetQuery("SELECT id FROM `#@__archives` WHERE trim(title)='' ");
    $dsql->Execute('x');
    $tdd = 0;
    while ($row = $dsql->GetObject('x')) {
        if (DelArc($row->id)) $tdd++;
    }
    ShowMsg("成功删除".$tdd."条记录", "javascript:;");
    exit();
}
//删除空文档
else if ($action == 'delnullbody') {
    $dsql->SetQuery("SELECT aid FROM `#@__addonarticle` WHERE LENGTH(body) < 10 ");
    $dsql->Execute('x');
    $tdd = 0;
    while ($row = $dsql->GetObject('x')) {
        if (DelArc($row->aid)) $tdd++;
    }
    ShowMsg("成功删除".$tdd."条记录", "javascript:;");
    exit();
}
//修正缩略图错误
else if ($action == 'modddpic') {
    $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET litpic='' WHERE trim(litpic)='litpic' ");
    ShowMsg("成功修正缩略图错误", "javascript:;");
    exit();
}
//批量移动
else if ($action == 'move') {
    if (empty($typeid)) {
        ShowMsg('该操作必须指定栏目', 'javascript:;');
        exit();
    }
    $typeold = $dsql->GetOne("SELECT * FROM `#@__arctype` WHERE id='$typeid';");
    $typenew = $dsql->GetOne("SELECT * FROM `#@__arctype` WHERE id='$newtypeid';");
    if (!is_array($typenew)) {
        ShowMsg("无法检测移动到的新栏目的信息，不能完成操作", "javascript:;");
        exit();
    }
    if ($typenew['ispart'] != 0) {
        ShowMsg("您不能把数据移动到非最终列表的栏目", "javascript:;");
        exit();
    }
    if ($typenew['channeltype'] != $typeold['channeltype']) {
        ShowMsg("不能把数据移动到文档类型不同的栏目", "javascript:;");
        exit();
    }
    $gwhere .= " And channel='".$typenew['channeltype']."' And title like '%$keyword%'";

    $ch = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id={$typenew['channeltype']} ");
    $addtable = $ch['addtable'];

    $dsql->SetQuery("SELECT id FROM `#@__archives` $gwhere");
    $dsql->Execute('m');
    $tdd = 0;
    while ($row = $dsql->GetObject('m')) {
        $rs = $dsql->ExecuteNoneQuery("UPDATE `#@__arctiny` SET typeid='$newtypeid' WHERE id='{$row->id}'");
        $rs = $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET typeid='$newtypeid' WHERE id='{$row->id}'");
        if ($addtable != '') {
            $dsql->ExecuteNoneQuery("UPDATE `$addtable` SET typeid='$newtypeid' WHERE aid='{$row->id}' ");
        }
        if ($rs) $tdd++;
    }
    if ($tdd > 0) {
        $jumpurl  = "makehtml_archives_action.php?endid=$endid&startid=$startid";
        $jumpurl .= "&typeid=$newtypeid&pagesize=20&seltime=$seltime";
        $jumpurl .= "&stime=".urlencode($starttime)."&etime=".urlencode($endtime);
        ShowMsg("成功移动 $tdd 条记录，准备重新生成网页", $jumpurl);
    } else {
        ShowMsg("完成操作，没移动任何数据", "javascript:;");
    }
}
//删除空标题文档
else if ($action == 'delnulltitle') {
    $dsql->SetQuery("SELECT id FROM `#@__archives` WHERE trim(title)='' ");
    $dsql->Execute('x');
    $tdd = 0;
    while ($row = $dsql->GetObject('x')) {
        if (DelArc($row->id)) $tdd++;
    }
    ShowMsg("成功删除".$tdd."条记录", "javascript:;");
    exit();
}
//修正缩略图错误
else if ($action == 'modddpic') {
    $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET litpic='' WHERE trim(litpic)='litpic' ");
    ShowMsg("成功修正缩略图错误", "javascript:;");
    exit();
}
?>