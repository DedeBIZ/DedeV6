<?php
/**
 * 修改投票
 *
 * @version        $id:vote_edit.php 23:54 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__)."/config.php");
CheckPurview('plus_投票管理');
require_once(DEDEINC."/dedetag.class.php");
if (empty($dopost)) $dopost = '';
$aid = isset($aid) && is_numeric($aid) ? $aid : 0;
$ENV_GOBACK_URL = empty($_COOKIE['ENV_GOBACK_URL']) ? "vote_main.php" : $_COOKIE['ENV_GOBACK_URL'];
if ($dopost == "delete") {
    if ($dsql->ExecuteNoneQuery("DELETE FROM `#@__vote` WHERE aid='$aid'")) {
        if ($dsql->ExecuteNoneQuery("DELETE FROM `#@__vote_member` WHERE voteid='$aid'")) {
            ShowMsg('成功删除一组投票', $ENV_GOBACK_URL);
            exit;
        }
    } else {
        ShowMsg('指定删除投票不存在', $ENV_GOBACK_URL);
        exit;
    }
} else if ($dopost == "saveedit") {
    CheckCSRF();
    $starttime = GetMkTime($starttime);
    $endtime = GetMkTime($endtime);
    $query = "UPDATE `#@__vote` SET votename='$votename',starttime='$starttime',endtime='$endtime',totalcount='$totalcount',ismore='$ismore',votenote='$votenote',isallow='$isallow',view='$view',spec='$spec',isenable='$isenable' WHERE aid='$aid'";
    if ($dsql->ExecuteNoneQuery($query)) {
        $vt = new DedeVote($aid);
        $vote_file = DEDEDATA."/vote/vote_".$aid.".js";
        $vote_content = $vt->GetVoteForm();
        $vote_content = preg_replace(array("#/#", "#([\r\n])[\s]+#"), array("\/", " "), $vote_content);        //取出文档中的空白字符并进行转义
        $vote_content = 'document.write("'.$vote_content.'");';
        file_put_contents($vote_file, $vote_content);
        ShowMsg('成功修改一组投票', $ENV_GOBACK_URL);
    } else {
        ShowMsg('修改一组投票失败', $ENV_GOBACK_URL);
    }
} else {
    $row = $dsql->GetOne("SELECT * FROM `#@__vote` WHERE aid='$aid'");
    if (!is_array($row)) {
        ShowMsg('指定投票不存在', '-1');
        exit();
    }
    include DedeInclude('templets/vote_edit.htm');
}
?>