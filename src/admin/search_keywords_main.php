<?php
/**
 * 搜索关键词管理
 *
 * @version        $id:search_keywords_main.php 15:46 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
DedeSetCookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
if (empty($pagesize)) $pagesize = 30;
if (empty($pageno)) $pageno = 1;
if (empty($dopost)) $dopost = '';
if (empty($orderby)) $orderby = 'aid';
$orderby = HtmlReplace($orderby, -1);
$pageno = intval($pageno);
$pagesize = intval($pagesize);
//重载列表
if ($dopost == 'getlist') {
    AjaxHead();
    GetKeywordList($dsql, $pageno, $pagesize, $orderby);
    exit();
}
//更新字段
else if ($dopost == 'update') {
    $aid = preg_replace("#[^0-9]#", "", $aid);
    $count = preg_replace("#[^0-9]#", "", $count);
    $keyword = trim($keyword);
    $spwords = trim($spwords);
    $dsql->ExecuteNoneQuery("UPDATE `#@__search_keywords` SET keyword='$keyword',spwords='$spwords',count='$count' WHERE aid='$aid';");
    AjaxHead();
    GetKeywordList($dsql, $pageno, $pagesize, $orderby);
    exit();
}
//删除字段
else if ($dopost == 'del') {
    $aid = preg_replace("#[^0-9]#", "", $aid);
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__search_keywords` WHERE aid='$aid';");
    AjaxHead();
    GetKeywordList($dsql, $pageno, $pagesize, $orderby);
    exit();
}
//批量删除字段
else if ($dopost == 'delall') {
    foreach ($aids as $aid) {
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__search_keywords` WHERE aid='$aid';");
    }
    ShowMsg("删除成功", $ENV_GOBACK_URL);
    exit();
}
//第一次进入这个页面
if ($dopost == '') {
    $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__search_keywords`");
    $totalRow = $row['dd'];
    include(DEDEADMIN."/templets/search_keywords_main.htm");
}
//获得特定的关键词列表
function GetKeywordList($dsql, $pageno, $pagesize, $orderby = 'aid')
{
    global $cfg_phpurl;
    $start = ($pageno - 1) * $pagesize;
    $printhead = "<form name='form3' action='search_keywords_main.php' method='post'>
    <input name='dopost' type='hidden' value=''>
    <div class='table-responsive'>
    <table class='table table-borderless table-hover'>
    <thead>
        <tr>
            <td scope='col'>选择</td>
            <td scope='col'><a href=\"javascript:ReloadPage('aid');\">id</a></td>
            <td scope='col'>关键词</td>
            <td scope='col'>关键词调整</td>
            <td scope='col'>分词调整</td>
            <td scope='col'><a href=\"javascript:ReloadPage('count');\">频率调整</a></td>
            <td scope='col'><a href=\"javascript:ReloadPage('result');\">索引</a></td>
            <td scope='col'><a href=\"javascript:ReloadPage('lasttime');\">搜索时间</a></td>
            <td scope='col'>操作</td>
        </tr>
    </thead>";
    echo $printhead;
    if ($orderby == 'result') $orderby = $orderby." ASC";
    else $orderby = $orderby." DESC";
    $dsql->SetQuery("SELECT * FROM `#@__search_keywords` ORDER BY $orderby LIMIT $start,$pagesize ");
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        $line = "<tbody>
    <tr>
        <td><input name='aids[]' type='checkbox' value=\"{$row['aid']}\"></td>
        <td>{$row['aid']}</td>
        <td><a href='{$cfg_phpurl}/search.php?keyword=".urlencode($row['keyword'])."' target='_blank'>{$row['keyword']}</a></td>
        <td><input type='text' name='keyword' id='keyword{$row['aid']}' value='{$row['keyword']}' class='admin-input-sm'></td>
        <td><input type='text' name='spwords' id='spwords{$row['aid']}' value='{$row['spwords']}' class='admin-input-md'></td>
        <td><input type='text' name='count' id='count{$row['aid']}' value='{$row['count']}' class='admin-input-sm'></td>
        <td>{$row['result']}</td>
        <td>".MyDate("Y-m-d H:i:s", $row['lasttime'])."</td>
        <td>
            <a href='javascript:UpdateNote({$row['aid']});' class='btn btn-light btn-sm'><i class='fa fa-repeat' title='更新'></i></a>
            <a href='javascript:DelNote({$row['aid']});' class='btn btn-danger btn-sm'><i class='fa fa-trash' title='删除'></i></a>
        </td>
    </tr>";
        echo $line;
    }
    echo "<tr>
            <td colspan='9'>
            <a href=\"javascript:selAll();\" class='btn btn-success btn-sm'>反选</a>
            <a href=\"javascript:noselAll();\" class='btn btn-success btn-sm'>取消</a>
            <a href=\"javascript:delall();\" class='btn btn-danger btn-sm'>删除</a>
           </td>
        </tr>
    </tbody>";
    echo "</table></div></form>";
}
?>