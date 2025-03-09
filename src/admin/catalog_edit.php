<?php
/**
 * 修改栏目
 *
 * @version        $id:catalog_edit.php 14:31 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/typelink/typelink.class.php");
if (empty($dopost)) $dopost = '';
$id = isset($id) ? intval($id) : 0;
//检查权限许可
CheckPurview('t_Edit,t_AccEdit');
//检查栏目操作许可
CheckCatalog($id, '您无权修改本栏目');
if ($dopost == "save") {
    if ($apienabled == 1 && empty($apikey)) {
        ShowMsg("跨站调用秘钥不能为空", "-1");
        exit();
    }
    $description = Html2Text($description, 1);
    $keywords = Html2Text($keywords, 1);
    $uptopsql = $smalltypes = '';
    if (isset($smalltype) && is_array($smalltype)) $smalltypes = join(',', $smalltype);
    if ($topid == 0) {
        $sitepath = $typedir;
        $uptopsql = " ,siteurl='$siteurl',sitepath='$sitepath',ishidden='$ishidden' ";
    }
    if ($ispart != 0) $cross = 0;
    $upquery = "UPDATE `#@__arctype` SET issend='$issend',sortrank='$sortrank',typename='$typename',cnoverview='$cnoverview',enname='$enname',enoverview='$enoverview',bigpic='$bigpic',litimg='$litimg',typedir='$typedir',isdefault='$isdefault',defaultname='$defaultname',issend='$issend',ishidden='$ishidden',channeltype='$channeltype',tempindex='$tempindex',templist='$templist',temparticle='$temparticle',namerule='$namerule',namerule2='$namerule2',ispart='$ispart',corank='$corank',description='$description',keywords='$keywords',seotitle='$seotitle',moresite='$moresite',`cross`='$cross',`content`='$content',`crossid`='$crossid',`smalltypes`='$smalltypes',`apienabled`='$apienabled',`apikey`='$apikey' $uptopsql WHERE id='$id' ";
    if (!$dsql->ExecuteNoneQuery($upquery)) {
        ShowMsg("保存栏目失败，请您检查栏目字段是否存在问题", "-1");
        exit();
    }
    //如果选择子栏目可投稿，更新顶级栏目为可投稿
    if ($topid > 0 && $issend == 1) {
        $dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET issend='$issend' WHERE id='$topid';");
    }
    $slinks = " id IN (".GetSonIds($id).")";
    //修改顶级栏目时强制修改下级的多站点支持属性
    if ($topid == 0 && preg_match("#,#", $slinks)) {
        $upquery = "UPDATE `#@__arctype` SET moresite='$moresite', siteurl='$siteurl',sitepath='$sitepath',ishidden='$ishidden' WHERE 1=1 AND $slinks";
        $dsql->ExecuteNoneQuery($upquery);
    }
    //修改子栏目属性
    if (!empty($upnext)) {
    $upquery = "UPDATE `#@__arctype` SET issend='$issend',defaultname='$defaultname',channeltype='$channeltype',tempindex='$tempindex',templist='$templist',temparticle='$temparticle',namerule='$namerule',namerule2='$namerule2',ishidden='$ishidden' WHERE 1=1 AND $slinks";
        if (!$dsql->ExecuteNoneQuery($upquery)) {
            ShowMsg("修改栏目成功，但修改下级栏目属性时失败", "-1");
            exit();
        }
    }
    UpDateCatCache();
    ShowMsg("成功修改一个栏目", "catalog_main.php");
    exit();
} else if ($dopost == "savetime") {
    $uptopsql = '';
    $slinks = " id IN (".GetSonIds($id).")";
    //顶级栏目二级域名根目录处理
    if ($topid == 0 && $moresite == 1) {
        $sitepath = $typedir;
        $uptopsql = " ,sitepath='$sitepath' ";
        if (preg_match("#,#", $slinks)) {
            $upquery = "UPDATE `#@__arctype` SET sitepath='$sitepath' WHERE $slinks";
            $dsql->ExecuteNoneQuery($upquery);
        }
    }
    //如果选择子栏目可投稿，更新顶级栏目为可投稿
    if ($topid > 0 && $issend == 1) {
        $dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET issend='$issend' WHERE id='$topid';");
    }
    $upquery = "UPDATE `#@__arctype` SET issend='$issend',sortrank='$sortrank',typedir='$typedir',typename='$typename',isdefault='$isdefault',defaultname='$defaultname',ispart='$ispart',corank='$corank' $uptopsql WHERE id='$id' ";
    if (!$dsql->ExecuteNoneQuery($upquery)) {
        ShowMsg("保存栏目失败，请您检查栏目字段是否存在输入问题", "-1");
        exit();
    }
    UpDateCatCache();
    ShowMsg("成功修改一个栏目", "catalog_main.php");
    exit();
}
//读取栏目信息
$dsql->SetQuery("SELECT tp.*,ch.typename as ctypename FROM `#@__arctype` tp LEFT JOIN `#@__channeltype` ch ON ch.id=tp.channeltype WHERE tp.id=$id");
$myrow = $dsql->GetOne();
$topid = $myrow['topid'];
if ($topid > 0) {
    $toprow = $dsql->GetOne("SELECT moresite,siteurl,sitepath FROM `#@__arctype` WHERE id=$topid");
    foreach ($toprow as $k => $v) {
        if (!preg_match("#[0-9]#", $k)) {
            $myrow[$k] = $v;
        }
    }
}
$myrow['content'] = empty($myrow['content']) ? "&nbsp;" : $myrow['content'];
//读取栏目模型信息
$channelid = $myrow['channeltype'];
$dsql->SetQuery("SELECT id,typename,nid FROM `#@__channeltype` WHERE id<>-1 AND isshow=1 ORDER BY id");
$dsql->Execute();
while ($row = $dsql->GetObject()) {
    $channelArray[$row->id]['typename'] = $row->typename;
    $channelArray[$row->id]['nid'] = $row->nid;
    if ($row->id == $channelid) {
        $nid = $row->nid;
    }
}
PutCookie('lastCid', GetTopid($id), 3600 * 24, "/");
include DedeInclude('templets/catalog_edit.htm');
?>