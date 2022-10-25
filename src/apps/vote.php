<?php
/**
 * 投票
 *
 * @version        $Id: vote.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__)."/../system/common.inc.php");
require(DEDEINC."/dedevote.class.php");
require(DEDEINC."/memberlogin.class.php");
require(DEDEINC."/userlogin.class.php");
$member = new MemberLogin;
$memberID = $member->M_LoginID;
$loginurl = $cfg_basehost."/user";
$ENV_GOBACK_URL = empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];
if (empty($dopost)) $dopost = '';
$aid = (isset($aid) && is_numeric($aid)) ? $aid : 0;
if ($aid == 0) die("dedebiz");
if ($aid == 0) {
    ShowMsg("没指定投票项目的id", "-1");
    exit();
}
$vo = new DedeVote($aid);
$rsmsg = '';
$row = $dsql->GetOne("SELECT * FROM `#@__vote` WHERE aid='$aid'");
//判断是否允许游客进行投票
if ($row['isallow'] == 1) {
    if (!$member->IsLogin()) {
        ShowMsg('请先登录再进行投票', $loginurl);
        exit();
    }
}
if ($dopost == 'send') {
    if (!empty($voteitem)) {
        $rsmsg = "<br>您方才的投票状态：".$vo->SaveVote($voteitem)."<br>";
    } else {
        $rsmsg = "<br>您刚才没选择任何投票项目<br>";
    }
    if ($row['isenable'] == 1) {
        ShowMsg('此投票项未启用,暂时不能进行投票', $ENV_GOBACK_URL);
        exit();
    }
}
$voname = $vo->VoteInfos['votename'];
$totalcount = $vo->VoteInfos['totalcount'];
$starttime = GetDateMk($vo->VoteInfos['starttime']);
$endtime = GetDateMk($vo->VoteInfos['endtime']);
$votelist = $vo->GetVoteResult("98%", 30, "30%");
//判断是否允许被查看
$admin = new userLogin;
if ($dopost == 'view') {
    if ($row['view'] == 1 && empty($admin->userName)) {
        ShowMsg('此投票项不允许查看结果', $ENV_GOBACK_URL);
        exit();
    }
}
//显示模板简单PHP文件
include(DEDETEMPLATE.'/plus/vote.htm');
?>