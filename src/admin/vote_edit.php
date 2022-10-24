<?php
/**
 * 投票模块编辑
 *
 * @version        $Id: vote_edit.php 1 23:54 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeVote;
use DedeBIZ\Login\UserLogin;
require(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('plus_投票模块');
if (empty($dopost)) $dopost = "";
$aid = isset($aid) && is_numeric($aid) ? $aid : 0;
$ENV_GOBACK_URL = empty($_COOKIE['ENV_GOBACK_URL']) ? "vote_main.php" : $_COOKIE['ENV_GOBACK_URL'];
if ($dopost == "delete") {
    if ($dsql->ExecuteNoneQuery("DELETE FROM `#@__vote` WHERE aid='$aid'")) {
        if ($dsql->ExecuteNoneQuery("DELETE FROM `#@__vote_member` WHERE voteid='$aid'")) {
            ShowMsg(Lang('vote_delete_success_one'), $ENV_GOBACK_URL);
            exit;
        }
    } else {
        ShowMsg(Lang('vote_delete_err_no_exists'), $ENV_GOBACK_URL);
        exit;
    }
} else if ($dopost == "saveedit") {
    CheckCSRF();
    $starttime = GetMkTime($starttime);
    $endtime = GetMkTime($endtime);
    $query = "UPDATE `#@__vote` SET votename='$votename',
        starttime='$starttime',
        endtime='$endtime',
        totalcount='$totalcount',
        ismore='$ismore',
        votenote='$votenote',
        isallow='$isallow',
        view='$view',
        spec='$spec',
        isenable='$isenable'
       WHERE aid='$aid'";
    if ($dsql->ExecuteNoneQuery($query)) {
        $vt = new DedeVote($aid);
        $vote_file = DEDEDATA."/vote/vote_".$aid.".js";
        $vote_content = $vt->GetVoteForm();
        $vote_content = preg_replace(array("#/#", "#([\r\n])[\s]+#"), array("\/", " "), $vote_content);        //取出内容中的空白字符并进行转义
        $vote_content = 'document.write("'.$vote_content.'");';
        file_put_contents($vote_file, $vote_content);
        ShowMsg(Lang('vote_edit_success_one'), $ENV_GOBACK_URL);
    } else {
        ShowMsg(Lang('vote_edit_err_one'), $ENV_GOBACK_URL);
    }
} else {
    $row = $dsql->GetOne("SELECT * FROM `#@__vote` WHERE aid='$aid'");
    if (!is_array($row)) {
        ShowMsg(Lang('vote_err_no_exists'), '-1');
        exit();
    }
    include DedeInclude('templets/vote_edit.htm');
}
?>