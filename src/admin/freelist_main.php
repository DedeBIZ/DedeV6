<?php
/**
 * 自由列表管理
 *
 * @version        $id:freelist_main.php 8:48 2010年7月13日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('c_FreeList');
require_once DEDEINC.'/channelunit.func.php';
DedeSetCookie("ENV_GOBACK_URL",$dedeNowurl,time()+3600,"/");
if (empty($pagesize)) $pagesize = 30;
if (empty($pageno)) $pageno = 1;
if (empty($dopost)) $dopost = '';
if (empty($orderby)) $orderby = 'aid';
if (empty($keyword)) {
    $keyword = '';
    $addget = '';
    $addsql = '';
} else {
    $addget = '&keyword='.urlencode($keyword);
    $addsql = " where title like '%$keyword%' ";
}
//重载列表
if ($dopost=='getlist') {
    AjaxHead();
    GetTagList($dsql,$pageno,$pagesize,$orderby);
    exit();
}
//删除字段
else if ($dopost=='del') {
    $aid = preg_replace("#[^0-9]#", "", $aid);
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__freelist` WHERE aid='$aid';");
    AjaxHead();
    GetTagList($dsql,$pageno,$pagesize,$orderby);
    exit();
}
//第一次进入这个页面
if ($dopost=='') {
    $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__freelist` $addsql ");
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
    global $cfg_phpurl, $addsql;
    $start = ($pageno-1) * $pagesize;
    $printhead ="<table class='table shadow-sm my-3'>
        <tr>
            <td colspan='6'>自由列表管理</td>
        </tr>
        <tr align='center'>
            <td width='6%'><a href=\"javascript:ReloadPage('aid');\">id</a></td>
            <td width='20%'>列表名称</td>
            <td width='20%'>模板文件</td>
            <td width='6%'><a href=\"javascript:ReloadPage('click');\">点击</a></td>
            <td width='12%'>创建时间</td>
            <td>操作</td>
        </tr>";
    echo $printhead;
    $dsql->SetQuery("SELECT aid,title,templet,click,edtime,namerule,listdir,defaultpage,nodefault FROM `#@__freelist` $addsql ORDER BY $orderby DESC LIMIT $start,$pagesize");
    $dsql->Execute();
    while($row = $dsql->GetArray())
    {
        $listurl = GetFreeListUrl($row['aid'],$row['namerule'],$row['listdir'],$row['defaultpage'],$row['nodefault']);
        $line = "<tr align='center'>
        <td>{$row['aid']}</td>
        <td><a href='$listurl' target='_blank'>{$row['title']}</a></td>
        <td>{$row['templet']}</td>
        <td>{$row['click']}</td>
        <td>".MyDate("y-m-d",$row['edtime'])."</td>
        <td>
            <a href=\"javascript:CreateNote({$row['aid']});\" class='btn btn-light btn-sm'><i class='fa fa-repeat'></i> 更新</a>
            <a href=\"javascript:EditNote({$row['aid']});\" class='btn btn-light btn-sm'><i class='fa fa-pencil-square'></i> 修改</a>
            <a href='$listurl' target='_blank' class='btn btn-light btn-sm'><i class='fa fa-eye'></i> 预览</a>
            <a href=\"javascript:DelNote({$row['aid']});\" class='btn btn-danger btn-sm'><i class='fa fa-trash'></i> 删除</a>
        </td>
    </tr>";
        echo $line;
    }
    echo "</table>";
}