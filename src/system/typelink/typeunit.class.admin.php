<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 后台栏目管理
 *
 * @version        $id:typeunit.class.admin.php 15:21 2010年7月5日 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(DEDEINC."/channelunit.func.php");
class TypeUnit
{
    var $dsql;
    var $artDir;
    var $baseDir;
    var $idCounter;
    var $idArray;
    var $shortName;
    var $CatalogNums;
    //php5构造函数
    function __construct()
    {
        $this->idCounter = 0;
        $this->artDir = $GLOBALS['cfg_cmspath'].$GLOBALS['cfg_arcdir'];
        $this->baseDir = $GLOBALS['cfg_basedir'];
        $this->shortName = $GLOBALS['art_shortname'];
        $this->idArray = array();
        $this->dsql = $GLOBALS['dsql'];
    }
    function TypeUnit()
    {
        $this->__construct();
    }
    //清理类
    function Close()
    {
    }
    //获取所有栏目的文档id数
    function UpdateCatalogNum()
    {
        $this->dsql->SetQuery("SELECT typeid,count(typeid) as dd FROM `#@__arctiny` WHERE arcrank <>-3 GROUP BY typeid");
        $this->dsql->Execute();
        while ($row = $this->dsql->GetArray()) {
            $this->CatalogNums[$row['typeid']] = $row['dd'];
        }
    }
    function GetTotalArc($tid)
    {
        if (!is_array($this->CatalogNums)) {
            $this->UpdateCatalogNum();
        }
        if (!isset($this->CatalogNums[$tid])) {
            return 0;
        } else {
            $totalnum = 0;
            $ids = explode(',', GetSonIds($tid));
            foreach ($ids as $tid) {
                if (isset($this->CatalogNums[$tid])) {
                    $totalnum += $this->CatalogNums[$tid];
                }
            }
            return $totalnum;
        }
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
            $this->dsql->SetQuery("SELECT reid FROM `#@__arctype` WHERE id in($admin_catalog) GROUP BY reid ");
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
        $this->dsql->SetQuery("SELECT id,typedir,typename,ispart,sortrank,ishidden,apienabled FROM `#@__arctype` WHERE reid=0 ORDER BY sortrank");
        $this->dsql->Execute(0);
        $i = 0;
        while ($row = $this->dsql->GetObject(0)) {
            if ($cfg_admin_channel == 'array' && !in_array($row->id, $admin_catalogs)) {
                continue;
            }
            $typeDir = $row->typedir;
            $typeName = $row->typename;
            $ispart = $row->ispart;
            $id = $row->id;
            $rank = $row->sortrank;
            if ($row->ishidden == '1') {
                $nss = "<span class='btn btn-secondary btn-sm'>隐藏</span>";
            } else {
                $nss = '';
            }
            if ($ispart == 0) {
                //列表栏目
echo <<<tpl
<div class='d-flex justify-content-between mb-3'>
    <div class='left'>
        <span class='btn btn-light btn-sm'><i id='icon{$id}' onclick="LoadSuns('suns{$id}',{$id});" class='fa fa-plus-square'></i></span>
        <span class='btn btn-success btn-sm'>列表</span>
        {$nss}
        <a href='catalog_edit.php?id={$id}' class='btn btn-sm'>id：{$id}</a>
        <a href='catalog_do.php?cid={$id}&dopost=listArchives' class='btn btn-sm'>{$typeName}</a>
        <span class='btn btn-sm'>文档数：{$this->GetTotalArc($id)}</span>
    </div>
    <div class='right'>
        <a href='{$GLOBALS['cfg_phpurl']}/list.php?tid={$id}' target='_blank' class='btn btn-light btn-sm'><i class='fa fa-eye' title='预览'></i></a>
        <a href='catalog_do.php?cid={$id}&dopost=listArchives' class='btn btn-light btn-sm'><i class='fa fa-file-text' title='文档'></i></a>
        <a href='catalog_add.php?id={$id}' class='btn btn-light btn-sm'><i class='fa fa-plus-circle' title='添加'></i></a>
        <a href='catalog_edit.php?id={$id}' class='btn btn-light btn-sm'><i class='fa fa-pencil-square' title='修改'></i></a>
        <a href='catalog_do.php?dopost=moveCatalog&typeid={$id}' class='btn btn-light btn-sm'><i class='fa fa-share-square' title='移动'></i></a>
        <a href='catalog_del.php?id={$id}&typeoldname=".urlencode({$typeName})."' class='btn btn-danger btn-sm'><i class='fa fa-trash' title='删除'></i></a>
        <input type='text' name='sortrank{$id}' value='{$rank}' class='admin-main-sort'>
    </div>
</div>
tpl;
            } else if ($ispart == 1) {
                //封面栏目
echo <<<tpl
<div class='d-flex justify-content-between mb-3'>
    <div class='left'>
        <span class='btn btn-light btn-sm'><i id='icon{$id}' onclick="LoadSuns('suns{$id}',{$id});" class='fa fa-plus-square'></i></span>
        <span class='btn btn-warning btn-sm'>封面</span>
        {$nss}
        <a href='catalog_edit.php?id={$id}' class='btn btn-sm'>id：{$id}</a>
        <a href='catalog_do.php?cid={$id}&dopost=listArchives' class='btn btn-sm'>{$typeName}</a>
    </div>
    <div class='right'>
        <a href='{$GLOBALS['cfg_phpurl']}/list.php?tid={$id}' target='_blank' class='btn btn-light btn-sm'><i class='fa fa-eye' title='预览'></i></a>
        <a href='catalog_do.php?cid={$id}&dopost=listArchives' class='btn btn-light btn-sm'><i class='fa fa-file-text' title='文档'></i></a>
        <a href='catalog_add.php?id={$id}' class='btn btn-light btn-sm'><i class='fa fa-plus-circle' title='添加'></i></a>
        <a href='catalog_edit.php?id={$id}' class='btn btn-light btn-sm'><i class='fa fa-pencil-square' title='修改'></i></a>
        <a href='catalog_do.php?dopost=moveCatalog&typeid={$id}' class='btn btn-light btn-sm'><i class='fa fa-share-square' title='移动'></i></a>
        <a href='catalog_del.php?id={$id}&typeoldname=".urlencode({$typeName})."' class='btn btn-danger btn-sm'><i class='fa fa-trash' title='删除'></i></a>
        <input type='text' name='sortrank{$id}' value='{$rank}' class='admin-main-sort'>
    </div>
</div>
tpl;
            } else if ($ispart == 2) {
                //外部栏目
echo <<<tpl
<div class='d-flex justify-content-between mb-3'>
    <div class='left'>
        <span class='btn btn-light btn-sm'><i id='icon{$id}' onclick="LoadSuns('suns{$id}',{$id});" class='fa fa-plus-square'></i></span>
        <span class='btn btn-primary btn-sm'>外部</span>
        {$nss}
        <a href='catalog_edit.php?id={$id}' class='btn btn-sm'>id：{$id}<a>
        <a href='{$typeDir}' target='_blank' class='btn btn-sm'>{$typeName}</a>
    </div>
    <div class='right'>
        <a href='{$typeDir}' target='_blank' class='btn btn-light btn-sm'><i class='fa fa-eye' title='预览'></i></a>
        <a href='catalog_add.php?id={$id}' class='btn btn-light btn-sm'><i class='fa fa-plus-circle' title='添加'></i></a>
        <a href='catalog_edit.php?id={$id}' class='btn btn-light btn-sm'><i class='fa fa-pencil-square' title='修改'></i></a>
        <a href='catalog_do.php?dopost=moveCatalog&typeid={$id}' class='btn btn-light btn-sm'><i class='fa fa-share-square' title='移动'></i></a>
        <a href='catalog_del.php?id={$id}&typeoldname=".urlencode({$typeName})."' class='btn btn-danger btn-sm'><i class='fa fa-trash' title='删除'></i></a>
        <input type='text' name='sortrank{$id}' value='{$rank}' class='admin-main-sort'>
    </div>
</div>
tpl;
            }
            echo "<div id='suns".$id."'>";
            $lastid = GetCookie('lastCid');
            if ($channel == $id || $lastid == $id || isset($GLOBALS['exallct']) || $cfg_admin_channel == 'array') {
                $this->LogicListAllSunType($id, "─");
            }
            echo "</div>";
            $i++;
        }
    }
    /**
     *  获得子栏目的递归调用
     *
     * @access    public
     * @param     int  $id  栏目id
     * @param     string  $step  层级标志
     * @return    void
     */
    function LogicListAllSunType($id, $step)
    {
        global $cfg_admin_channel, $admin_catalogs;
        $fid = $id;
        $this->dsql->SetQuery("SELECT id,reid,typedir,typename,ispart,sortrank,ishidden FROM `#@__arctype` WHERE reid='".$id."' ORDER BY sortrank");
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
                if ($step == "　") {
                    $stepdd = 2;
                } else {
                    $stepdd = 3;
                }
                $rank = $row->sortrank;
                if ($row->ishidden == '1') {
                    $nss = "<span class='btn btn-secondary btn-sm'>隐藏</span>";
                } else {
                    $nss = '';
                } if ($ispart == 0) {
                    //列表栏目
echo <<<tpl
<div class='d-flex justify-content-between mb-3'>
    <div class='left'>
        <span>└{$step}</span>
        <span class='btn btn-light btn-sm'><i id='icon{$id}' onclick="LoadSuns('suns{$id}',{$id});" class='fa fa-plus-square'></i></span>
        <span class='btn btn-success btn-sm'>列表</span>
        {$nss}
        <a href='catalog_edit.php?id={$id}' class='btn btn-sm'>id：{$id}</a>
        <a href='catalog_do.php?cid={$id}&dopost=listArchives' class='btn btn-sm'>{$typeName}</a>
        <span class='btn btn-sm'>文档数：{$this->GetTotalArc($id)}</span>
    </div>
    <div class='right'>
        <a href='{$GLOBALS['cfg_phpurl']}/list.php?tid={$id}' target='_blank' class='btn btn-light btn-sm'><i class='fa fa-eye' title='预览'></i></a>
        <a href='catalog_do.php?cid={$id}&dopost=listArchives' class='btn btn-light btn-sm'><i class='fa fa-file-text' title='文档'></i></a>
        <a href='catalog_add.php?id={$id}' class='btn btn-light btn-sm'><i class='fa fa-plus-circle' title='添加'></i></a>
        <a href='catalog_edit.php?id={$id}' class='btn btn-light btn-sm'><i class='fa fa-pencil-square' title='修改'></i></a>
        <a href='catalog_do.php?dopost=moveCatalog&typeid={$id}' class='btn btn-light btn-sm'><i class='fa fa-share-square' title='移动'></i></a>
        <a href='catalog_del.php?id={$id}&typeoldname=".urlencode({$typeName})."' class='btn btn-danger btn-sm'><i class='fa fa-trash' title='删除'></i></a>
        <input type='text' name='sortrank{$id}' value='{$rank}' class='admin-main-sort'>
    </div>
</div>
tpl;
                } else if ($ispart == 1) {
                    //封面栏目
echo <<<tpl
<div class='d-flex justify-content-between mb-3'>
    <div class='left'>
         <span>└{$step}</span>
        <span class='btn btn-light btn-sm'><i id='icon{$id}' onclick="LoadSuns('suns{$id}',{$id});" class='fa fa-plus-square'></i></span>
        <span class='btn btn-warning btn-sm'>封面</span>
        {$nss}
        <a href='catalog_edit.php?id={$id}' class='btn btn-sm'>id：{$id}</a>
        <a href='catalog_do.php?cid={$id}&dopost=listArchives' class='btn btn-sm'>{$typeName}</a>
    </div>
    <div class='right'>
        <a href='{$GLOBALS['cfg_phpurl']}/list.php?tid={$id}' target='_blank' class='btn btn-light btn-sm'><i class='fa fa-eye' title='预览'></i></a>
        <a href='catalog_do.php?cid={$id}&dopost=listArchives' class='btn btn-light btn-sm'><i class='fa fa-file-text' title='文档'></i></a>
        <a href='catalog_add.php?id={$id}' class='btn btn-light btn-sm'><i class='fa fa-plus-circle' title='添加'></i></a>
        <a href='catalog_edit.php?id={$id}' class='btn btn-light btn-sm'><i class='fa fa-pencil-square' title='修改'></i></a>
        <a href='catalog_do.php?dopost=moveCatalog&typeid={$id}' class='btn btn-light btn-sm'><i class='fa fa-share-square' title='移动'></i></a>
        <a href='catalog_del.php?id={$id}&typeoldname=".urlencode({$typeName})."' class='btn btn-danger btn-sm'><i class='fa fa-trash' title='删除'></i></a>
        <input type='text' name='sortrank{$id}' value='{$rank}' class='admin-main-sort'>
    </div>
</div>
tpl;
                } else if ($ispart == 2) {
                    //外部栏目
echo <<<tpl
<div class='d-flex justify-content-between mb-3'>
    <div class='left'>
         <span>└{$step}</span>
        <span class='btn btn-light btn-sm'><i id='icon{$id}' onclick="LoadSuns('suns{$id}',{$id});" class='fa fa-plus-square'></i></span>
        <span class='btn btn-primary btn-sm'>外部</span>
        {$nss}
        <a href='catalog_edit.php?id={$id}' class='btn btn-sm'>id：{$id}</a>
        <a href='{$typeDir}' target='_blank' class='btn btn-sm'>{$typeName}</a>
    </div>
    <div class='right'>
        <a href='{$typeDir}' target='_blank' class='btn btn-light btn-sm'><i class='fa fa-eye' title='预览'></i></a>
        <a href='catalog_edit.php?id={$id}' class='btn btn-light btn-sm'><i class='fa fa-pencil-square' title='修改'></i></a>
        <a href='catalog_do.php?dopost=moveCatalog&typeid={$id}' class='btn btn-light btn-sm'><i class='fa fa-share-square' title='移动'></i></a>
        <a href='catalog_del.php?id={$id}&typeoldname=".urlencode({$typeName})."' class='btn btn-danger btn-sm'><i class='fa fa-trash' title='删除'></i></a>
        <input type='text' name='sortrank{$id}' value='{$rank}' class='admin-main-sort'>
    </div>
</div>
tpl;
                }
                echo "<div id='suns".$id."' style='".(isset($GLOBALS['exallct'])? "" : "display:none")."'>";
                $this->LogicListAllSunType($id, $step."──");
                echo "</div>";
            }
        }
    }
    /**
     *  返回某个相关下级目录的栏目id列表删除栏目或文档时调用
     *
     * @access    public
     * @param     int   $id  栏目id
     * @param     int   $channel  栏目id
     * @return    array
     */
    function GetSunTypes($id, $channel = 0)
    {
        $this->idArray[$this->idCounter] = $id;
        $this->idCounter++;
        $fid = $id;
        if ($channel != 0) {
            $csql = " And channeltype=$channel ";
        } else {
            $csql = '';
        }
        $this->dsql->SetQuery("SELECT id FROM `#@__arctype` WHERE reid=$id $csql");
        $this->dsql->Execute("gs".$fid);
        while ($row = $this->dsql->GetObject("gs".$fid)) {
            $nid = $row->id;
            $this->GetSunTypes($nid, $channel);
        }
        return $this->idArray;
    }
    /**
     *  删除栏目
     *
     * @access    public
     * @param     int   $id  栏目id
     * @param     bool   $isDelFile  是否删除文件
     * @return    string
     */
    function DelType($id, $isDelFile)
    {
        $this->idCounter = 0;
        $this->idArray = array();
        $this->GetSunTypes($id);
        $query = "SELECT `#@__arctype`.*,`#@__channeltype`.typename AS ctypename, `#@__channeltype`.addtable FROM `#@__arctype` LEFT JOIN `#@__channeltype` ON `#@__channeltype`.id=`#@__arctype`.channeltype WHERE `#@__arctype`.id='$id' ";
        $typeinfos = $this->dsql->GetOne($query);
        $topinfos = $this->dsql->GetOne("SELECT moresite,siteurl FROM `#@__arctype` WHERE id='".$typeinfos['topid']."'");
        if (!is_array($typeinfos)) {
            return FALSE;
        }
        $indir = $typeinfos['typedir'];
        $addtable = $typeinfos['addtable'];
        $ispart = $typeinfos['ispart'];
        $defaultname = $typeinfos['defaultname'];
        //删除数据库里的相关记录
        foreach ($this->idArray as $id) {
            $myrow = $this->dsql->GetOne("SELECT * FROM `#@__arctype` WHERE id='$id'");
            if ($myrow['topid'] > 0) {
                $mytoprow = $this->dsql->GetOne("SELECT moresite,siteurl FROM `#@__arctype` WHERE id='".$myrow['topid']."'");
                if (is_array($mytoprow) && !empty($mytoprow)) {
                    foreach ($mytoprow as $k => $v) {
                        if (!preg_match("/[0-9]/", $k)) {
                            $myrow[$k] = $v;
                        }
                    }
                }
            }
            //删除单独页面，删除目录和目录里的所有文件，禁止了此功能
            if ($myrow['ispart'] == 2 && $myrow['typedir'] == '') {
                if (is_file($this->baseDir.'/'.$myrow['defaultname'])) {
                    @unlink($this->baseDir.'/'.$myrow['defaultname']);
                }
            }
            //删除数据库信息
            $this->dsql->ExecuteNoneQuery("DELETE FROM `#@__arctype` WHERE id='$id'");
            $this->dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE typeid='$id'");
            $this->dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE typeid='$id'");
            $this->dsql->ExecuteNoneQuery("DELETE FROM `#@__spec` WHERE typeid='$id'");
            $this->dsql->ExecuteNoneQuery("DELETE FROM `#@__feedback` WHERE typeid='$id'");
            if ($addtable != "") {
                $this->dsql->ExecuteNoneQuery("DELETE FROM $addtable WHERE typeid='$id'");
            }
        }
        //删除单独页面，删除目录和目录里的所有文件，禁止了此功能
        if ($ispart == 2 && $indir == "") {
            if (is_file($this->baseDir."/".$defaultname)) {
                @unlink($this->baseDir."/".$defaultname);
            }
        }
        @reset($this->idArray);
        $this->idCounter = 0;
        return TRUE;
    }
    /**
     *  删除指定目录的所有文件
     *
     * @access    public
     * @param     string  $indir  指定目录
     * @return    int
     */
    function RmDirFile($indir)
    {
        if (!file_exists($indir)) return -1;
        $dh = dir($indir);
        while ($file = $dh->read()) {
            if ($file == "." || $file == "..") {
                continue;
            } else if (is_file("$indir/$file")) {
                @unlink("$indir/$file");
            } else {
                $this->RmDirFile("$indir/$file");
            }
            if (is_dir("$indir/$file")) {
                @rmdir("$indir/$file");
            }
        }
        $dh->close();
        return (1);
    }
}//End Class
?>