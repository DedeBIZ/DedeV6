<?php
/**
 * 内容处理函数
 *
 * @version        $Id: content_batch_up.php 1 14:31 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('sys_ArcBatch');
require_once(DEDEADMIN."/inc/inc_batchup.php");
@set_time_limit(0);
if (empty($startid)) $startid = 0;
if (empty($endid)) $endid = 0;
if (empty($seltime)) $seltime = 0;
if (empty($typeid)) $typeid = 0;
if (empty($userid)) $userid = '';
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
        ShowMsg(Lang('content_error_id_check'), 'javascript:;');
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
    ShowMsg(Lang("content_batch_check_success"), $jumpurl);
    exit();
}
//批量删除
else if ($action == 'del') {
    if (empty($startid) || empty($endid) || $endid < $startid) {
        ShowMsg(Lang('content_error_id_check'), 'javascript:;');
        exit();
    }
    $dsql->SetQuery("SELECT id FROM `#@__archives` $gwhere");
    $dsql->Execute('x');
    $tdd = 0;
    while ($row = $dsql->GetObject('x')) {
        if (DelArc($row->id)) $tdd++;
    }
    ShowMsg(Lang('content_batch_delete_success',array('tdd'=>$tdd)), "javascript:;");
    exit();
}
//删除空标题文档
else if ($action == 'delnulltitle') {
    $dsql->SetQuery("SELECT id FROM `#@__archives` WHERE trim(title)=''");
    $dsql->Execute('x');
    $tdd = 0;
    while ($row = $dsql->GetObject('x')) {
        if (DelArc($row->id)) $tdd++;
    }
    ShowMsg(Lang('content_batch_delete_success',array('tdd'=>$tdd)), "javascript:;");
    exit();
}
//删除空内容文档
else if ($action == 'delnullbody') {
    $dsql->SetQuery("SELECT aid FROM `#@__addonarticle` WHERE LENGTH(body) < 10");
    $dsql->Execute('x');
    $tdd = 0;
    while ($row = $dsql->GetObject('x')) {
        if (DelArc($row->aid)) $tdd++;
    }
    ShowMsg(Lang('content_batch_delete_success',array('tdd'=>$tdd)), "javascript:;");
    exit();
}
//修正缩略图错误
else if ($action == 'modddpic') {
    $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET litpic='' WHERE trim(litpic)='litpic'");
    ShowMsg(Lang("content_batch_modddpic_success"), "javascript:;");
    exit();
}
//批量移动
else if ($action == 'move') {
    if (empty($typeid)) {
        ShowMsg(Lang('content_batch_err_typeid_isempty'), 'javascript:;');
        exit();
    }
    $typeold = $dsql->GetOne("SELECT * FROM `#@__arctype` WHERE id='$typeid';");
    $typenew = $dsql->GetOne("SELECT * FROM `#@__arctype` WHERE id='$newtypeid';");
    if (!is_array($typenew)) {
        ShowMsg(Lang("content_batch_err_typenew"), "javascript:;");
        exit();
    }
    if ($typenew['ispart'] != 0) {
        ShowMsg(Lang("content_batch_err_ispart"), "javascript:;");
        exit();
    }
    if ($typenew['channeltype'] != $typeold['channeltype']) {
        ShowMsg(Lang("content_batch_err_channeltype"), "javascript:;");
        exit();
    }
    $gwhere .= " And channel='".$typenew['channeltype']."' And title like '%$keyword%'";
    $ch = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id={$typenew['channeltype']}");
    $addtable = $ch['addtable'];
    $dsql->SetQuery("SELECT id FROM `#@__archives` $gwhere");
    $dsql->Execute('m');
    $tdd = 0;
    while ($row = $dsql->GetObject('m')) {
        $rs = $dsql->ExecuteNoneQuery("UPDATE `#@__arctiny` SET typeid='$newtypeid' WHERE id='{$row->id}'");
        $rs = $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET typeid='$newtypeid' WHERE id='{$row->id}'");
        if ($addtable != '') {
            $dsql->ExecuteNoneQuery("UPDATE `$addtable` SET typeid='$newtypeid' WHERE aid='{$row->id}'");
        }
        if ($rs) $tdd++;
        //DelArc($row->id,true);
    }
    if ($tdd > 0) {
        $jumpurl  = "makehtml_archives_action.php?endid=$endid&startid=$startid";
        $jumpurl .= "&typeid=$newtypeid&pagesize=20&seltime=$seltime";
        $jumpurl .= "&stime=".urlencode($starttime)."&etime=".urlencode($endtime);
        ShowMsg(Lang('content_batch_move_success',array('tdd'=>$tdd )), $jumpurl);
    } else {
        ShowMsg(Lang("content_batch_finish_move_success"), "javascript:;");
    }
}
//删除空标题内容
else if ($action == 'delnulltitle') {
    $dsql->SetQuery("SELECT id FROM `#@__archives` WHERE trim(title)=''");
    $dsql->Execute('x');
    $tdd = 0;
    while ($row = $dsql->GetObject('x')) {
        if (DelArc($row->id)) $tdd++;
    }
    ShowMsg(Lang('content_batch_delete_success',array('tdd'=>$tdd)), "javascript:;");
    exit();
}
?>