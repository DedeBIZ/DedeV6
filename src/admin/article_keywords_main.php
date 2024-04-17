<?php
/**
 * 文档关键词维护
 *
 * @version        $id:article_keywords_main.php 14:12 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_Keyword');
require_once(DEDEINC."/datalistcp.class.php");
DedeSetCookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
if (empty($dopost)) $dopost = '';
//保存批量修改
if ($dopost == 'saveall') {
    $ENV_GOBACK_URL = empty($_COOKIE['ENV_GOBACK_URL']) ? "article_keywords_main.php" : $_COOKIE['ENV_GOBACK_URL'];
    if (!isset($aids)) {
        ShowMsg("请选择需要修改的关键词", $ENV_GOBACK_URL);
        exit();
    }
    foreach ($aids as $aid) {
        $rpurl = ${'rpurl_'.$aid};
        $rpurlold = ${'rpurlold_'.$aid};
        $keyword = ${'keyword_'.$aid};
        //删除项目
        if (!empty(${'isdel_'.$aid})) {
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__keywords` WHERE aid='$aid'");
            continue;
        }
        //禁用项目
        $staold = ${'staold_'.$aid};
        $sta = empty(${'isnouse_'.$aid}) ? 1 : 0;
        if ($staold != $sta) {
            $query1 = "UPDATE `#@__keywords` SET sta='$sta',rpurl='$rpurl' WHERE aid='$aid' ";
            $dsql->ExecuteNoneQuery($query1);
            continue;
        }
        //更新链接网址
        if ($rpurl != $rpurlold) {
            $query1 = "UPDATE `#@__keywords` SET rpurl='$rpurl' WHERE aid='$aid' ";
            $dsql->ExecuteNoneQuery($query1);
        }
    }
    ShowMsg("成功修改一则关键词", $ENV_GOBACK_URL);
    exit();
}
//添加关键词
else if ($dopost == 'add') {
    $ENV_GOBACK_URL = empty($_COOKIE['ENV_GOBACK_URL']) ? "-1" : $_COOKIE['ENV_GOBACK_URL'];
    $keyword = trim($keyword);
    $rank = preg_replace("#[^0-9]#", '', $rank);
    if ($keyword == '') {
        ShowMsg("关键词不能为空", -1);
        exit();
    }
    $row = $dsql->GetOne("SELECT * FROM `#@__keywords` WHERE keyword LIKE '$keyword'");
    if (is_array($row)) {
        ShowMsg("关键词已存在库中", "-1");
        exit();
    }
    $inquery = "INSERT INTO `#@__keywords`(keyword,`rank`,sta,rpurl) VALUES ('$keyword','$rank','1','$rpurl');";
    $dsql->ExecuteNoneQuery($inquery);
    ShowMsg("成功添加一个关键词", $ENV_GOBACK_URL);
    exit();
}
if (empty($keyword)) {
    $keyword = '';
    $addquery = '';
} else {
    $addquery = " WHERE keyword LIKE '%$keyword%' ";
}
$sql = "SELECT * FROM `#@__keywords` $addquery ORDER BY `rank` DESC";
$dlist = new DataListCP();
$dlist->pagesize = 30;
$dlist->SetParameter("keyword", $keyword);
$dlist->SetTemplate(DEDEADMIN."/templets/article_keywords_main.htm");
$dlist->SetSource($sql);
$dlist->Display();
function GetSta($sta)
{
    if ($sta == 1) return '';
    else return 'checked';
}
?>