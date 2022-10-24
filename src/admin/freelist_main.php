<?php
/**
 * 自由列表管理
 *
 * @version        $Id: freelist_main.php 1 8:48 2010年7月13日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('c_FreeList');
require_once DEDEINC.'/channel/channelunit.func.php';
setcookie("ENV_GOBACK_URL",$dedeNowurl,time()+3600,"/");
if (empty($pagesize)) $pagesize = 30;
if (empty($pageno)) $pageno = 1;
if (empty($dopost)) $dopost = '';
if (empty($orderby)) $orderby = 'aid';
if (empty($keyword))
{
    $keyword = '';
    $addget = '';
    $addsql = '';
} else {
    $addget = '&keyword='.urlencode($keyword);
    $addsql = " where title like '%$keyword%' ";
}
//重载列表
if ($dopost=='getlist')
{
    AjaxHead();
    GetTagList($dsql,$pageno,$pagesize,$orderby);
    exit();
}
//删除字段
else if ($dopost=='del')
{
    $aid = preg_replace("#[^0-9]#", "", $aid);
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__freelist` WHERE aid='$aid';");
    AjaxHead();
    GetTagList($dsql,$pageno,$pagesize,$orderby);
    exit();
}
//第一次进入这个页面
if ($dopost=='')
{
    $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__freelist` $addsql");
    $totalRow = $row['dd'];
    include(DEDEADMIN."/templets/freelist_main.htm");
}
/**
 * 获得特定的Tag列表
 *
 * @param object $dsql
 * @param int $pageno
 * @param int $pagesize
 * @param string $orderby
 */
function GetTagList($dsql,$pageno,$pagesize,$orderby='aid')
{
    global $cfg_phpurl,$addsql;
    $start = ($pageno-1) * $pagesize;
    $printhead ="<table width='98%' cellpadding='1' cellspacing='1' align='center' class='table maintable' style='margin-bottom:10px'>
        <tr align='center' bgcolor='#f8fcf2'>
          <td width='5%' class='tbsname'><a href='javascript:;' onclick=\"ReloadPage('aid')\">id</a></td>
          <td width='20%' class='tbsname'>".Lang('title')."</td>
          <td width='20%' class='tbsname'>".Lang('template')."</td>
          <td width='5%' class='tbsname'><a href='javascript:;' onclick=\"ReloadPage('click')\">".Lang('click')."</a></td>
          <td width='15%' class='tbsname'>".Lang('edtime')."</td>
          <td class='tbsname'>".Lang('operation')."</td>
            </tr>\r\n";
    echo $printhead;
    $dsql->SetQuery("SELECT aid,title,templet,click,edtime,namerule,listdir,defaultpage,nodefault FROM `#@__freelist` $addsql ORDER BY $orderby DESC LIMIT $start,$pagesize");
    $dsql->Execute();
    $i = 0;
    while($row = $dsql->GetArray())
    {
        $listurl = GetFreeListUrl($row['aid'],$row['namerule'],$row['listdir'],$row['defaultpage'],$row['nodefault']);
        $line = "<tr align='center' onMouseMove=\"javascript:this.bgColor='#f8fcf2';\" onMouseOut=\"javascript:this.bgColor='#ffffff';\">
        <td>{$row['aid']}</td>
        <td> <a href='$listurl' target='_blank'>{$row['title']}</a> </td>
        <td> {$row['templet']} </td>
        <td> {$row['click']} </td>
        <td>".MyDate("y-m-d",$row['edtime'])."</td>
        <td> <a href='javascript:;' onclick='EditNote({$row['aid']})' class='btn btn-success btn-sm'><i class=\"fa fa-code\" aria-hidden=\"true\"></i> ".Lang("edit")."</a>
        <a href='javascript:;' onclick='CreateNote({$row['aid']})' class='btn btn-success btn-sm'><i class=\"fa fa-refresh\" aria-hidden=\"true\"></i> ".Lang("update")."</a>
         <a href='javascript:;' onclick='DelNote({$row['aid']})' class='btn btn-success btn-sm'><i class=\"fa fa-trash\" aria-hidden=\"true\"></i> ".Lang("delete")."</a>
    </td>
  </tr>";
        $i++;
        echo $line;
    }
    if ($i == 0) {
        echo "<tr><td colspan='6'><center>".Lang('none_result')."</center></td></tr>";
    }
    echo "</table>\r\n";
}
?>