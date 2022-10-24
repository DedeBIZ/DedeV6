<?php
/**
 * 栏目操作
 *
 * @version        $Id: catalog_do.php 1 14:31 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Archive\ListView;
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
use DedeBIZ\TypeLink\TypeLink;
use DedeBIZ\TypeLink\TypeUnitAdmin;
use DedeBIZ\TypeLink\TypeUnitMenu;
require_once(dirname(__FILE__).'/config.php');
if (empty($dopost)) {
    ShowMsg(Lang("dopost_error_noparms"), "catalog_main.php");
    exit();
}
$cid = empty($cid) ? 0 : intval($cid);
$unittype = empty($unittype) ? 0 : intval($unittype);
$channelid = empty($channelid) ? 0 : intval($channelid);
//增加文档
if ($dopost == "addArchives") {
    //默认文档调用发布表单
    if (empty($cid) && empty($channelid)) {
        header("location:article_add.php");
        exit();
    }
    if (!empty($channelid)) {
        //根据模型调用发布表单
        $row = $dsql->GetOne("SELECT addcon FROM `#@__channeltype` WHERE id='$channelid'");
    } else {
        //根据栏目调用发布表单
        $row = $dsql->GetOne("SELECT ch.addcon FROM `#@__arctype` tp LEFT JOIN `#@__channeltype` ch ON ch.id=tp.channeltype WHERE tp.id='$cid'");
    }
    $gurl = $row["addcon"];
    if ($gurl == "") {
        ShowMsg(Lang("dopost_error_typelink"), "catalog_main.php");
        exit();
    }
    //跳转并传递参数
    header("location:{$gurl}?channelid={$channelid}&cid={$cid}");
    exit();
}
//管理文档
else if ($dopost == "listArchives") {
    if (!empty($gurl)) {
        if (empty($arcrank)) {
            $arcrank = '';
        }
        $gurl = str_replace('..', '', $gurl);
        header("location:{$gurl}?arcrank={$arcrank}&cid={$cid}");
        exit();
    }
    if ($cid > 0) {
        $row = $dsql->GetOne("SELECT `#@__arctype`.typename,`#@__channeltype`.typename AS channelname,`#@__channeltype`.id,`#@__channeltype`.mancon FROM `#@__arctype` LEFT JOIN `#@__channeltype` on `#@__channeltype`.id=`#@__arctype`.channeltype WHERE `#@__arctype`.id='$cid'");
        $gurl = $row["mancon"];
        $channelid = $row["id"];
        $typename = $row["typename"];
        $channelname = $row["channelname"];
        if ($gurl == "") {
            ShowMsg(Lang("dopost_error_typelink"), "catalog_main.php");
            exit();
        }
    } else if ($channelid > 0) {
        $row = $dsql->GetOne("SELECT typename,id,mancon FROM `#@__channeltype` WHERE id='$channelid'");
        $gurl = $row["mancon"];
        $channelid = $row["id"];
        $typename = "";
        $channelname = $row["typename"];
    }

    if (empty($gurl)) $gurl = 'content_list.php';
    header("location:{$gurl}?channelid={$channelid}&cid={$cid}");
    exit();
}
//浏览通用模板目录
else if ($dopost == "viewTemplet") {
    header("location:tpl.php?path=/".$cfg_df_style);
    exit();
}
//浏览单个页面的栏目
else if ($dopost == "viewSgPage") {
    $lv = new ListView($cid);
    $pageurl = $lv->MakeHtml();
    ShowMsg(Lang("catalog_upcache_wait"), $pageurl);
    exit();
}
//修改栏目排列顺序
else if ($dopost == "upRank") {
    //检查权限许可
    UserLogin::CheckPurview('t_Edit,t_AccEdit');
    //检查栏目操作许可
    UserLogin::CheckCatalog($cid, Lang("catalog_error_noedit_purview"));
    $row = $dsql->GetOne("SELECT reid,sortrank FROM `#@__arctype` WHERE id='$cid'");
    $reid = $row['reid'];
    $sortrank = $row['sortrank'];
    $row = $dsql->GetOne("SELECT sortrank FROM `#@__arctype` WHERE sortrank<=$sortrank AND reid=$reid ORDER BY sortrank DESC");
    if (is_array($row)) {
        $sortrank = $row['sortrank'] - 1;
        $dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET sortrank='$sortrank' WHERE id='$cid'");
    }
    UpDateCatCache();
    ShowMsg(Lang('dopost_success_back'), "catalog_main.php");
    exit();
} else if ($dopost == "upRankAll") {
    //检查权限许可
    UserLogin::CheckPurview('t_Edit');
    $row = $dsql->GetOne("SELECT id FROM `#@__arctype` ORDER BY id DESC");
    if (is_array($row)) {
        $maxID = $row['id'];
        for ($i = 1; $i <= $maxID; $i++) {
            if (isset(${'sortrank'.$i})) {
                $dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET sortrank='".(${'sortrank'.$i})."' WHERE id='{$i}';");
            }
        }
    }
    UpDateCatCache();
    ShowMsg(Lang('dopost_success_back'), "catalog_main.php");
    exit();
}
//更新栏目缓存
else if ($dopost == "upcatcache") {
    UpDateCatCache();
    $sql = " TRUNCATE TABLE `#@__arctiny`";
    $dsql->ExecuteNoneQuery($sql);
    //导入普通模型微数据
    $sql = "INSERT INTO `#@__arctiny`(id, typeid, typeid2, arcrank, channel, senddate, sortrank, mid) SELECT id, typeid, typeid2, arcrank, channel, senddate, sortrank, mid FROM `#@__archives`";
    $dsql->ExecuteNoneQuery($sql);
    //导入单表模型微数据
    $dsql->SetQuery("SELECT id,addtable FROM `#@__channeltype` WHERE id < -1 ");
    $dsql->Execute();
    $doarray = array();
    while ($row = $dsql->GetArray()) {
        $tb = str_replace('#@__', $cfg_dbprefix, $row['addtable']);
        if (empty($tb) || isset($doarray[$tb])) {
            continue;
        } else {
            $sql = "INSERT INTO `#@__arctiny`(id, typeid, typeid2, arcrank, channel, senddate, sortrank, mid) SELECT aid, typeid, 0, arcrank, channel, senddate, 0, mid FROM `$tb`";
            $rs = $dsql->executenonequery($sql);
            $doarray[$tb]  = 1;
        }
    }
    ShowMsg(Lang('dopost_success_back'), "catalog_main.php");
    exit();
}
//获取js文件
else if ($dopost == "GetJs") {
    header("location:makehtml_js.php");
    exit();
}
//获得子类菜单
else if ($dopost == "GetSunListsMenu") {
    $userChannel = $cUserLogin->getUserChannel();
    AjaxHead();
    PutCookie('lastCidMenu', $cid, 3600 * 24, "/");
    $tu = new TypeUnitMenu($userChannel);
    $tu->LogicListAllSunType($cid, "　");
}
//获得子类内容
else if ($dopost == "GetSunLists") {
    AjaxHead();
    PutCookie('lastCid', $cid, 3600 * 24, "/");
    $tu = new TypeUnitAdmin();
    $tu->dsql = $dsql;
    echo "<table width='100%' cellspacing='0' cellpadding='0'>\r\n";
    $tu->LogicListAllSunType($cid, "　");
    echo "</table>\r\n";
    $tu->Close();
}
//合并栏目
else if ($dopost == 'unitCatalog') {
    UserLogin::CheckPurview('t_Move');
    require_once(DEDEINC.'/channel/channelunit.func.php');
    if (empty($nextjob)) {
        $typeid = isset($typeid) ? intval($typeid) : 0;
        $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__arctype` WHERE reid='$typeid'");
        $tl = new TypeLink($typeid);
        $typename = $tl->TypeInfos['typename'];
        $reid = $tl->TypeInfos['reid'];
        $channelid = $tl->TypeInfos['channeltype'];
        if (!empty($row['dd'])) {
            ShowMsg(Lang("catalog_merge_err_hasson", array('typename'=>$typename)), '-1');
            exit();
        }
        $typeOptions = $tl->GetOptionArray(0, 0, $channelid);
        $wintitle = Lang('catalog_merge');
        $wecome_info = "<a href='catalog_main.php'>".Lang('catalog_main')."</a> &gt; ".Lang('catalog_merge');
        DedeWin::Instance()->Init('catalog_do.php', 'js/blank.js', 'POST')
        ->AddHidden('dopost', 'unitCatalog')
        ->AddHidden('typeid', $typeid)
        ->AddHidden('channelid', $channelid)
        ->AddHidden('nextjob', 'unitok')
        ->AddTitle(Lang('catalog_merge_tip1'))
        ->AddItem(Lang('catalog_merge_select_typename'), Lang('catalog_merge_tip2',array('typename'=>$typename)))
        ->AddItem(Lang('catalog_merge_to'), "<select name='unittype'>\r\n{$typeOptions}\r\n</select>")
        ->AddItem(Lang('care'), Lang('catalog_merge_tip3'))
        ->GetWindow('ok')
        ->Display();
        exit();
    } else {
        if ($typeid == $unittype) {
            ShowMsg(Lang('catalog_merge_err_same'), '-1');
            exit();
        }
        if (IsParent($unittype, $typeid)) {
            ShowMsg(Lang('catalog_merge_err_parent2son'), 'catalog_main.php');
            exit();
        }
        $row = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$channelid'");
        $addtable = (empty($row['addtable']) ? '#@__addonarticle' : $row['addtable']);
        $dsql->ExecuteNoneQuery("UPDATE `#@__arctiny` SET typeid='$unittype' WHERE typeid='$typeid'");
        $dsql->ExecuteNoneQuery("UPDATE `#@__feedback` SET typeid='$unittype' WHERE typeid='$typeid'");
        $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET typeid='$unittype' WHERE typeid='$typeid'");
        $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET typeid2='$unittype' WHERE typeid2='$typeid'");
        $dsql->ExecuteNoneQuery("UPDATE `#@__addonspec` SET typeid='$unittype' WHERE typeid='$typeid'");
        $dsql->ExecuteNoneQuery("UPDATE `$addtable` SET typeid='$unittype' WHERE typeid='$typeid'");
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctype` WHERE id='$typeid'");
        UpDateCatCache();
        ShowMsg(Lang('catalog_merge_success'), 'catalog_main.php');
        exit();
    }
}
//移动栏目
else if ($dopost == 'moveCatalog') {
    UserLogin::CheckPurview('t_Move');
    require_once(DEDEINC.'/channel/channelunit.func.php');
    if (empty($nextjob)) {
        $tl = new TypeLink($typeid);
        $typename = $tl->TypeInfos['typename'];
        $reid = $tl->TypeInfos['reid'];
        $channelid = $tl->TypeInfos['channeltype'];
        $typeOptions = $tl->GetOptionArray(0, 0, $channelid);
        $wintitle = Lang('catalog_move');
        $wecome_info = "<a href='catalog_main.php'>".Lang('catalog_main')."</a> &gt; ".Lang('catalog_move');
        DedeWin::Instance()->Init('catalog_do.php', 'js/blank.js', 'POST')
        ->AddHidden('dopost', 'moveCatalog')
        ->AddHidden('typeid', $typeid)
        ->AddHidden('channelid', $channelid)
        ->AddHidden('nextjob', 'unitok')
        ->AddTitle(Lang("catalog_move_tip"))
        ->AddItem(Lang('catalog').'：', "$typename($typeid)")
        ->AddItem(Lang('select_catalog'), "<select name='movetype'>\r\n<option value='0'>".Lang('catalog_top')."</option>\r\n$typeOptions\r\n</select>")
        ->AddItem(Lang('care').'：', Lang('catalog_move_tip2'))
        ->GetWindow('ok')
        ->Display();
        exit();
    } else {
        if ($typeid == $movetype) {
            ShowMsg(Lang('catalog_move_err_same'), 'catalog_main.php');
            exit();
        }
        if (IsParent($movetype, $typeid)) {
            ShowMsg(Lang('catalog_move_err_parent2son'), 'catalog_main.php');
            exit();
        }
        $topid = GetTopid($movetype);
        $dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET reid='$movetype',topid='$topid' WHERE id='$typeid'");
        UpDateCatCache();
        ShowMsg(Lang('catalog_move_success'), 'catalog_main.php');
        exit();
    }
}
?>