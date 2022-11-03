<?php
/**
 * 搜索关键词管理
 *
 * @version        $id:search_keywords_main.php 15:46 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
setcookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
if (empty($pagesize)) $pagesize = 30;
if (empty($pageno)) $pageno = 1;
if (empty($dopost)) $dopost = '';
if (empty($orderby)) $orderby = 'aid';
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
    $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__search_keywords` ");
    $totalRow = $row['dd'];
    include(DEDEADMIN."/templets/search_keywords_main.htm");
}
//获得特定的关键词列表
function GetKeywordList($dsql, $pageno, $pagesize, $orderby = 'aid')
{
    global $cfg_phpurl;
    $start = ($pageno - 1) * $pagesize;
    $printhead = "<form name='form3' action=\"search_keywords_main.php\" method=\"post\">
    <input name=\"dopost\" type=\"hidden\" value=\"\">
    <table width='98%' align='center' cellpadding='1' cellspacing='1' class='table maintable'>
    <tr align='center' bgcolor='#fbfce2'>
      <td width='6%'>选择</td>
      <td width='6%'><a href='javascript:;' onclick=\"ReloadPage('aid')\">id</a></td>
      <td width='20%'>关键词</td>
      <td width='30%'>分词结果</td>
      <td width='6%'><a href='javascript:;' onclick=\"ReloadPage('count')\">频率</a></td>
      <td width='6%'><a href='javascript:;' onclick=\"ReloadPage('result')\">结果</a></td>
      <td width='12%'><a href='javascript:;' onclick=\"ReloadPage('lasttime')\">最后搜索时间</a></td>
      <td>管理</td>
    </tr>";
    echo $printhead;
    if ($orderby == 'result') $orderby = $orderby." ASC";
    else $orderby = $orderby." DESC";
    $dsql->SetQuery("SELECT * FROM `#@__search_keywords` ORDER BY $orderby LIMIT $start,$pagesize ");
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        $line = "<tr align='center' onMouseMove=\"javascript:this.bgColor='#fbfce2';\" onMouseOut=\"javascript:this.bgColor='#ffffff';\">
      <td><input name=\"aids[]\" type=\"checkbox\" class=\"np\" value=\"{$row['aid']}\" /></td>
      <td>{$row['aid']}</td>
      <td><input name='keyword' type='text' id='keyword{$row['aid']}' value='{$row['keyword']}' style='width:93%;'></td>
      <td><input name='spwords' type='text' id='spwords{$row['aid']}' value='{$row['spwords']}' style='width:95%;'></td>
      <td><input name='count' type='text' id='count{$row['aid']}' value='{$row['count']}' size='5'></td>
      <td><a href='{$cfg_phpurl}/search.php?kwtype=0&keyword=".urlencode($row['keyword'])."&searchtype=titlekeyword' target='_blank'>{$row['result']}</a></td>
      <td>".MyDate("Y-m-d H:i:s", $row['lasttime'])."</td>
      <td>
        <a href='javascript:;' onclick='UpdateNote({$row['aid']})' class='btn btn-success btn-sm'>更新</a>
        <a href='javascript:;' onclick='DelNote({$row['aid']})' class='btn btn-success btn-sm'>删除</a>
      </td>
    </tr>";
        echo $line;
    }
    echo "<tr>
            <td colspan='8'>
            <a href='javascript:selAll()' class='btn btn-success btn-sm'>反选</a>
            <a href='javascript:noselAll()' class='btn btn-success btn-sm'>取消</a>
            <a href='javascript:delall()' class='btn btn-success btn-sm'>删除</a>
           </td>
        </tr>";
    echo "</table></form>";
}