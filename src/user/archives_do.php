<?php
/**
 * 文档管理
 * 
 * @version        $id:archives_do.php 13:52 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (empty($dopost)) $dopost = '';
$aid = isset($aid) && is_numeric($aid) ? $aid : 0;
$channelid = isset($channelid) && is_numeric($channelid) ? $channelid : 1;
//删除收藏
if ($dopost == "delStow") {
    CheckRank(0, 0);
    $type = empty($type) ? '' : HtmlReplace(trim($type), -1);
    $tupdate = '';
    if (!empty($type)) {
        $tupdate = " AND type = '$type'";
    }
    $ENV_GOBACK_URL = empty($_COOKIE['ENV_GOBACK_URL']) ? "mystow.php" : $_COOKIE['ENV_GOBACK_URL'];
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_stow` WHERE aid='$aid' AND mid='".$cfg_ml->M_ID."'$tupdate;");
    //更新用户统计
    $row = $dsql->GetOne("SELECT COUNT(*) AS nums FROM `#@__member_stow` WHERE `mid`='".$cfg_ml->M_ID."' ");
    $dsql->ExecuteNoneQuery("UPDATE `#@__member_tj` SET `stow`='$row[nums]' WHERE `mid`='".$cfg_ml->M_ID."'");

    ShowMsg("成功删除一条收藏记录", $ENV_GOBACK_URL);
    exit();
}
//添加投稿
else if ($dopost == "addArc") {
    CheckRank(0, 0);
    if ($channelid == 1) {
        $addcon = 'article_add.php?channelid='.$channelid;
    } else if ($channelid == 2) {
        $addcon = 'album_add.php?channelid='.$channelid;
    } else if ($channelid == 3) {
        $addcon = 'soft_add.php?channelid='.$channelid;
    } else {
        $row = $dsql->GetOne("SELECT useraddcon FROM `#@__channeltype` WHERE id='$channelid' ");
        if (!is_array($row)) {
            ShowMsg("模型参数错误", "-1");
            exit();
        }
        $addcon = $row['useraddcon'];
        if (trim($addcon) == '') {
            $addcon = 'archives_add.php';
        }
        $addcon = $addcon."?channelid=$channelid";
    }
    header("Location:$addcon");
    exit();
}
//修改投稿
else if ($dopost == "edit") {
    CheckRank(0, 0);
    if ($channelid == 1) {
        $edit = 'article_edit.php?channelid='.$channelid;
    } else if ($channelid == 2) {
        $edit = 'album_edit.php?channelid='.$channelid;
    } else if ($channelid == 3) {
        $edit = 'soft_edit.php?channelid='.$channelid;
    } else {
        $row = $dsql->GetOne("SELECT usereditcon FROM `#@__channeltype` WHERE id='$channelid' ");
        if (!is_array($row)) {
            ShowMsg("参数错误", "-1");
            exit();
        }
        $edit = $row['usereditcon'];
        if (trim($edit) == '') {
            $edit = 'archives_edit.php';
        }
        $edit = $edit."?channelid=$channelid";
    }
    header("Location:$edit"."&aid=$aid");
    exit();
}
//删除文档
else if ($dopost == "delArc") {
    CheckRank(0, 0);
    include_once(DEDEMEMBER."/inc/inc_batchup.php");
    $ENV_GOBACK_URL = empty($_COOKIE['ENV_GOBACK_URL']) ? 'content_list.php?channelid=' : $_COOKIE['ENV_GOBACK_URL'];
    $equery = "SELECT arc.channel,arc.senddate,arc.arcrank,ch.maintable,ch.addtable,ch.issystem,ch.arcsta FROM `#@__arctiny` arc LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel WHERE arc.id='$aid' ";
    $row = $dsql->GetOne($equery);
    if (!is_array($row)) {
        ShowMsg("您没有权限删除这篇文档", "-1");
        exit();
    }
    if (trim($row['maintable']) == '') $row['maintable'] = '#@__archives';
    if ($row['issystem'] == -1) {
        $equery = "SELECT mid FROM `{$row['addtable']}` WHERE aid='$aid' AND mid='".$cfg_ml->M_ID."' ";
    } else {
        $equery = "SELECT mid,litpic from `{$row['maintable']}` WHERE id='$aid' AND mid='".$cfg_ml->M_ID."' ";
    }
    $arr = $dsql->GetOne($equery);
    if (!is_array($arr)) {
        ShowMsg("您没有权限删除这篇文档", "-1");
        exit();
    }
    if ($row['arcrank'] >= 0) {
        $dtime = time();
        $maxtime = $cfg_mb_editday * 24 * 3600;
        if ($dtime - $row['senddate'] > $maxtime) {
            ShowMsg("这篇文档已经锁定，暂时无法删除", "-1");
            exit();
        }
    }
    $channelid = $row['channel'];
    $row['litpic'] = (isset($arr['litpic']) ? $arr['litpic'] : '');
    //删除文档
    if ($row['issystem'] != -1) $rs = DelArc($aid);
    else $rs = DelArcSg($aid);
    //删除缩略图
    if (trim($row['litpic']) != '' && preg_match("#^".$cfg_user_dir."/{$cfg_ml->M_ID}#", $row['litpic'])) {
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__uploads` WHERE url LIKE '{$row['litpic']}' AND mid='{$cfg_ml->M_ID}' ");
        @unlink($cfg_basedir.$row['litpic']);
    }
    if ($ENV_GOBACK_URL == 'content_list.php?channelid=') {
        $ENV_GOBACK_URL = $ENV_GOBACK_URL.$channelid;
    }
    if ($rs) {
        //更新用户记录
        countArchives($channelid);
        //扣除积分
        $dsql->ExecuteNoneQuery("Update `#@__member` set scores=scores-{$cfg_sendarc_scores} WHERE mid='".$cfg_ml->M_ID."' And (scores-{$cfg_sendarc_scores}) > 0; ");
        ShowMsg("成功删除一篇文档", $ENV_GOBACK_URL);
        exit();
    } else {
        ShowMsg("删除文档失败", $ENV_GOBACK_URL);
        exit();
    }
    exit();
}
//查看文档
else if ($dopost == "viewArchives") {
    CheckRank(0, 0);
    if ($type == "") {
        header("location:".$cfg_phpurl."/view.php?aid=".$aid);
    } else {
        header("location:/book/book.php?bid=".$aid);
    }
}
?>