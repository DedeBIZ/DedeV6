<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 后台栏目管理
 *
 * @version        $id:typeunit.class.menu.php 15:21 2010年7月5日 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(DEDEDATA."/cache/inc_catalog_base.inc");
class TypeUnit
{
    var $dsql;
    var $aChannels;
    var $isAdminAll;
    //php5构造函数
    function __construct($catlogs = '')
    {
        global $cfg_Cs;
        $this->dsql = $GLOBALS['dsql'];
        $this->aChannels = array();
        $this->isAdminAll = false;
        if (!empty($catlogs) && $catlogs != '-1') {
            $this->aChannels = explode(',', $catlogs);
            foreach ($this->aChannels as $cid) {
                if ($cfg_Cs[$cid][0] == 0) {
                    $this->dsql->SetQuery("SELECT id,ispart FROM `#@__arctype` WHERE reid=$cid");
                    $this->dsql->Execute();
                    while ($row = $this->dsql->GetObject()) {
                        //if ($row->ispart==1)
                        $this->aChannels[] = $row->id;
                    }
                }
            }
        } else {
            $this->isAdminAll = true;
        }
    }
    function TypeUnit($catlogs = '')
    {
        $this->__construct($catlogs);
    }
    //清理类
    function Close()
    {
    }
    /**
     *  读出所有分类，在栏目管理页list_type中使用
     *
     * @access    public
     * @param     int   $channel  栏目id
     * @param     int   $nowdir  当前操作ID
     * @return    string
     */
    function ListAllType($channel = 0, $nowdir = 0)
    {

        global $cfg_admin_channel, $admin_catalogs;
        //检测会员有权限的顶级栏目
        if ($cfg_admin_channel == 'array') {
            $admin_catalog = join(',', $admin_catalogs);
            $this->dsql->SetQuery("SELECT reid FROM `#@__arctype` WHERE id IN($admin_catalog) GROUP BY reid ");
            $this->dsql->Execute();
            $topidstr = '';
            while ($row = $this->dsql->GetObject()) {
                if ($row->reid == 0) continue;
                $topidstr .= ($topidstr == '' ? $row->reid : ','.$row->reid);
            }
            $admin_catalog .= ','.$topidstr;
            $admin_catalogs = explode(',', $admin_catalog);
            $admin_catalogs = array_unique($admin_catalogs);
        }
        $this->dsql->SetQuery("SELECT id,typedir,typename,ispart,channeltype FROM `#@__arctype` WHERE reid=0 ORDER BY sortrank");
        $this->dsql->Execute(0);
        $lastid = GetCookie('lastCidMenu');
        while ($row = $this->dsql->GetObject(0)) {
            if ($cfg_admin_channel == 'array' && !in_array($row->id, $admin_catalogs)) {
                continue;
            }
            $typeDir = $row->typedir;
            $typeName = $row->typename;
            $ispart = $row->ispart;
            $id = $row->id;
            $channeltype = $row->channeltype;
            //普通栏目
            if ($ispart == 0) {
            }
            //封面栏目
            else if ($ispart == 1) {
            }
            //独立页面
            else if ($ispart==2) {
            }
            //跳转网址
            else {
                continue;
            }
            echo "<dl>\r\n";
            echo "<dd><img onclick=\"LoadSuns('suns{$id}',{$id});\" style='cursor:pointer'></dd>\r\n";
            echo "<dd><a href='catalog_do.php?cid=".$id."&dopost=listArchives'>".$typeName."</a></dd>\r\n";
            echo "</dl>\r\n";
            echo "<div id='suns".$id."'>";
            if ($lastid == $id || $cfg_admin_channel == 'array') {
                $this->LogicListAllSunType($id, "　");
            }
            echo "</div>\r\n";
        }
    }
    /**
     *  获得子栏目的递归调用
     *
     * @access    public
     * @param     int  $id  栏目id
     * @param     string  $step  层级标志
     * @param     bool  $needcheck  权限
     * @return    string
     */
    function LogicListAllSunType($id, $step, $needcheck = true)
    {
        global $cfg_admin_channel, $admin_catalogs;
        $fid = $id;
        $this->dsql->SetQuery("SELECT id,reid,typedir,typename,ispart,channeltype FROM `#@__arctype` WHERE reid='".$id."' ORDER BY sortrank");
        $this->dsql->Execute($fid);
        if ($this->dsql->GetTotalRow($fid) > 0) {
            while ($row = $this->dsql->GetObject($fid)) {
                if ($cfg_admin_channel == 'array' && !in_array($row->id, $admin_catalogs)) {
                    continue;
                }
                $typeDir = $row->typedir;
                $typeName = $row->typename;
                $reid = $row->reid;
                $id = $row->id;
                $ispart = $row->ispart;
                $channeltype = $row->channeltype;
                if ($step == "　") {
                    $stepdd = 2;
                } else {
                    $stepdd = 3;
                }
                //有权限栏目
                if (in_array($id, $this->aChannels) || $needcheck === false || $this->isAdminAll === true) {
                    //普通列表
                    if ($ispart == 0 || empty($ispart)) {
                    }
                    //封面栏目
                    else if ($ispart == 1) {
                    }
                    //独立页面
                    else if ($ispart==2) {
                    }
                    //跳转网址
                    else {
                        continue;
                    }
                    echo "<table>\r\n";
                    echo "<tr>\r\n";
                    echo "<td align='left'>".$step."<a href='catalog_do.php?cid=".$id."&dopost=listArchives'>".$typeName."</a></td>\r\n";
                    echo "</tr>\r\n";
                    echo "</table>\r\n";
                    $this->LogicListAllSunType($id, $step."　", false);
                }
            }
        }
    }
}
?>