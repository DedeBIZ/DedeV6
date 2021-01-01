<?php

/**
 *
 * 评论
 *
 * @version        $Id: feedback.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__) . "/../include/common.inc.php");

if ($cfg_feedback_forbid == 'Y') {
    echo json_encode(array(
        "code" => -1,
        "msg" => "系统已经禁止评论功能",
        "data" => null,
    ));
    exit();
}

require_once(DEDEINC . "/filter.inc.php");
if (!isset($action)) {
    $action = '';
}

$msg = isset($msg) ? $msg : "";
$feedbacktype = isset($feedbacktype) ? $feedbacktype : "";
$validate = isset($validate) ? $validate : "";
$pwd = isset($pwd) ? $pwd : "";
$comtype = isset($comtype) ? $comtype : "";
$good = isset($good) ? intval($good) : 0;

$cfg_formmember = isset($cfg_formmember) ? true : false;
$ischeck = $cfg_feedbackcheck == 'Y' ? 0 : 1;
$aid = isset($aid) ? intval($aid) : 0;
$fid = isset($fid) ? intval($fid) : 0; // 用来标记回复评论的变量

if (empty($aid) && empty($fid)) {
    echo json_encode(array(
        "code" => -1,
        "msg" => "文档ID不能为空",
        "data" => null,
    ));
    exit();
}

include_once(DEDEINC . "/memberlogin.class.php");
$cfg_ml = new MemberLogin();


//查看评论
/*
function __ViewFeedback(){ }
*/
//-----------------------------------
if ($action == '' || $action == 'show') {
    //读取文档信息
    $arcRow = GetOneArchive($aid);
    if (empty($arcRow['aid'])) {
        echo json_encode(array(
            "code" => -1,
            "msg" => "无法查看未知文档的评论",
            "data" => null,
        ));
        exit();
    }

    $where_sql = "WHERE 1=1";
    if (!empty($fid)) {
        $where_sql .= " AND fb.fid={$fid}";
    }
    if (!empty($aid)) {
        $where_sql .= " AND fb.aid={$aid}";
    }

    // 调用20条热评
    $querystring = "SELECT fb.*,mb.userid,mb.face as mface,mb.spacesta,mb.scores,mb.sex FROM `#@__feedback` fb
    LEFT JOIN `#@__member` mb on mb.mid = fb.mid $where_sql AND fb.ischeck='1' ORDER BY fb.good DESC";

    $dsql->Execute('fb', $querystring . " LIMIT 20 ");

    $data = array();

    while ($row = $dsql->GetArray('fb')) {
        $row['face'] = empty($row['mface']) ? $GLOBALS['cfg_cmspath'] . '/static/img/avatar.png' : $row['mface'];
        $row['dtimestr'] = MyDate('Y-m-d', $row['dtime']);
        unset($row['ip']);
        $data[] = $row;
    }

    echo json_encode(array(
        "code" => 200,
        "msg" => "",
        "data" => $data,
    ));
    exit;
}
//发表评论
//------------------------------------
/*
function __SendFeedback(){ }
*/ else if ($action == 'send') {
    //读取文档信息
    $arcRow = GetOneArchive($aid);
    if ((empty($arcRow['aid']) || $arcRow['notpost'] == '1') && empty($fid)) {
        echo json_encode(array(
            "code" => -1,
            "msg" => "无法对该文档发表评论",
            "data" => null,
        ));
        exit();
    }

    //如果没有登录，则需要检查验证码
    if (!$cfg_ml->IsLogin()) {
        if ($feedbacktype === 'good') {

            // 未登录点good不进行数据库记录
            echo json_encode(array(
                "code" => 200,
                "msg" => "",
                "data" => $good + 1,
            ));
            exit();
        }
        $svali = GetCkVdValue();
        if (strtolower($validate) != $svali || $svali == '') {
            // ResetVdValue();
            echo json_encode(array(
                "code" => -1,
                "msg" => "验证码错误",
                "data" => null,
            ));
            exit();
        }
    }


    //检查用户登录
    if (empty($notuser)) {
        $notuser = 0;
    }

    if ($cfg_feedback_guest == 'N' && $cfg_ml->M_ID < 1) {
        echo json_encode(array(
            "code" => -1,
            "msg" => "管理员禁用了游客评论",
            "data" => null,
        ));
        exit();
    }

    //匿名发表评论
    if ($notuser == 1) {
        $username = $cfg_ml->M_ID > 0 ? '匿名' : '游客';
    }

    //已登录的用户
    else if ($cfg_ml->M_ID > 0) {
        $username = $cfg_ml->M_UserName;
    }

    //用户身份验证
    else {
        if ($username != '' && $pwd != '') {
            $rs = $cfg_ml->CheckUser($username, $pwd);
            if ($rs == 1) {
                $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET logintime='" . time() . "',loginip='" . GetIP() . "' WHERE mid='{$cfg_ml->M_ID}'; ");
            } else {
                $username = '游客';
            }
        } else {
            $username = '游客';
        }
    }
    $ip = GetIP();
    $dtime = time();

    //检查评论间隔时间；
    if (!empty($cfg_feedback_time)) {
        //检查最后发表评论时间，如果未登录判断当前IP最后评论时间
        if ($cfg_ml->M_ID > 0) {
            $where = "WHERE `mid` = '$cfg_ml->M_ID'";
        } else {
            $where = "WHERE `ip` = '$ip'";
        }
        $row = $dsql->GetOne("SELECT dtime FROM `#@__feedback` $where ORDER BY `id` DESC ");
        if (is_array($row) && $dtime - $row['dtime'] < $cfg_feedback_time) {
            ResetVdValue();
            echo json_encode(array(
                "code" => -1,
                "msg" => "管理员设置了评论间隔时间，请稍等休息一下",
                "data" => null,
            ));
            exit();
        }
    }

    if (empty($face)) {
        $face = 0;
    }
    $face = intval($face);
    $typeid = (isset($typeid) && is_numeric($typeid)) ? intval($typeid) : 0;
    extract($arcRow, EXTR_SKIP);
    $msg = cn_substrR(TrimMsg($msg), $cfg_feedback_msglen);
    $username = cn_substrR(HtmlReplace($username, 2), 20);

    if (empty($feedbacktype) || !in_array($feedbacktype, array('good', 'bad'))) {
        $feedbacktype = 'feedback';
    }

    //保存评论内容
    if ($comtype == 'comments' || $comtype == 'reply') {
        $arctitle = empty($title) ? "" : addslashes($title);
        $typeid = intval($typeid);
        $ischeck = intval($ischeck);
        $feedbacktype = preg_replace("#[^0-9a-z]#i", "", $feedbacktype);
        if ($msg != '') {
            $inquery = "INSERT INTO `#@__feedback`(`aid`,`typeid`,`fid`, `username`,`arctitle`,`ip`,`ischeck`,`dtime`, `mid`,`bad`,`good`,`ftype`,`face`,`msg`)
                   VALUES ('$aid','$typeid','$fid','$username','$arctitle','$ip','$ischeck','$dtime', '{$cfg_ml->M_ID}','0','0','$feedbacktype','$face','$msg'); ";
            $rs = $dsql->ExecuteNoneQuery($inquery);
            if (!$rs) {
                echo json_encode(array(
                    "code" => -1,
                    "msg" => "发表评论错误",
                    "data" => null,
                ));
                //echo $dsql->GetError();
                exit();
            }
        }
    }

    if ($feedbacktype == 'bad') {
        $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET scores=scores-{cfg_feedback_sub},badpost=badpost+1,lastpost='$dtime' WHERE id='$aid' ");
    } else if ($feedbacktype == 'good') {
        $row = $dsql->GetOne("SELECT COUNT(*) as dd FROM `#@__feedback_goodbad` WHERE fid={$fid} AND mid={$cfg_ml->M_ID} AND fgtype=0");

        if (intval($row['dd']) <= 0) {
            $dsql->ExecuteNoneQuery("INSERT INTO `#@__feedback_goodbad` (`mid`, `fid`, `fgtype`) VALUES ('$cfg_ml->M_ID', '$fid', '0');");
            $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET scores=scores+{$cfg_feedback_add},goodpost=goodpost+1,lastpost='$dtime' WHERE id='$aid' ");
        } else {
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__feedback_goodbad` WHERE mid='{$cfg_ml->M_ID}' AND fid={$fid} AND fgtype=0");
            $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET scores=scores-{$cfg_feedback_add},goodpost=goodpost-1,lastpost='$dtime' WHERE id='$aid' ");
        }

        $rr = $dsql->GetOne("SELECT COUNT(*) as dd FROM `#@__feedback_goodbad` WHERE fid={$fid}");
        $dsql->ExecuteNoneQuery("UPDATE `#@__feedback` SET good='{$rr['dd']}' WHERE id={$fid}");
        echo json_encode(array(
            "code" => 200,
            "msg" => "",
            "data" => $rr['dd'],
        ));
        exit;
    } else {
        $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET scores=scores+1,lastpost='$dtime' WHERE id='$aid' ");
    }
    if ($cfg_ml->M_ID > 0) {
        $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET scores=scores+{$cfg_sendfb_scores} WHERE mid='{$cfg_ml->M_ID}' ");
    }
    //统计用户发出的评论
    if ($cfg_ml->M_ID > 0) {
        $row = $dsql->GetOne("SELECT COUNT(*) AS nums FROM `#@__feedback` WHERE `mid`='" . $cfg_ml->M_ID . "'");
        $dsql->ExecuteNoneQuery("UPDATE `#@__member_tj` SET `feedback`='$row[nums]' WHERE `mid`='" . $cfg_ml->M_ID . "'");
    }

    $_SESSION['sedtime'] = time();
    if (empty($uid) && isset($cmtuser)) $uid = $cmtuser;
    if ($ischeck == 0) {
        echo json_encode(array(
            "code" => 200,
            "msg" => "成功发表评论，但需审核后才会显示你的评论",
            "data" => "ok",
        ));
    } else {
        echo json_encode(array(
            "code" => 200,
            "msg" => "成功发表评论，现在转到评论页面",
            "data" => "ok",
        ));
    }
    exit();
}
