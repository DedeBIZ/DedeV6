<?php
/**
 * 修改分类文档模型
 * 
 * @version        $id:archives_sg_add.php 13:52 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/dedetag.class.php");
require_once(DEDEINC."/customfields.func.php");
require_once(DEDEMEMBER."/inc/inc_catalog_options.php");
require_once(DEDEMEMBER."/inc/inc_archives_functions.php");
$channelid = isset($channelid) && is_numeric($channelid) ? $channelid : 1;
$aid = isset($aid) && is_numeric($aid) ? $aid : 0;
$mtypesid = isset($mtypesid) && is_numeric($mtypesid) ? $mtypesid : 0;
$menutype = 'content';
if (empty($dopost)) {
    //读取归档信息
    $arcQuery = "SELECT ch.*,arc.* FROM `#@__arctiny` arc LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel WHERE arc.id='$aid' ";
    $cInfos = $dsql->GetOne($arcQuery);
    if (!is_array($cInfos)) {
        ShowMsg("读取文档信息出错", "index.php");
        exit();
    }
    $addRow = $dsql->GetOne("SELECT * FROM `{$cInfos['addtable']}` WHERE aid='$aid';");
    if ($addRow['mid'] != $cfg_ml->M_ID) {
        ShowMsg("您没有操作此文档权限", "-1");
        exit();
    }
    $addRow['id'] = $addRow['aid'];
    include(DEDEMEMBER."/templets/archives_sg_edit.htm");
    exit();
} else if ($dopost == 'save') {
    require_once(DEDEINC."/image.func.php");
    require_once(DEDEINC."/libraries/webwindow.class.php");
    $flag = '';
    $typeid = isset($typeid) && is_numeric($typeid) ? $typeid : 0;
    $userip = GetIP();
    if ($typeid == 0) {
        ShowMsg('您还没选择栏目，请选择发布文档栏目', '-1');
        exit();
    }
    $query = "SELECT tp.ispart,tp.channeltype,tp.issend,ch.issend AS cissend,ch.sendrank,ch.arcsta,ch.addtable,ch.fieldset,ch.usertype FROM `#@__arctype` tp LEFT JOIN `#@__channeltype` ch ON ch.id=tp.channeltype WHERE tp.id='$typeid' ";
    $cInfos = $dsql->GetOne($query);
    $addtable = $cInfos['addtable'];
    //检测栏目是否有投稿权限
    if ($cInfos['issend'] != 1 || $cInfos['ispart'] != 0 || $cInfos['channeltype'] != $channelid || $cInfos['cissend'] != 1) {
        ShowMsg("您所选择的栏目不支持投稿", "-1");
        exit();
    }
    //校验CSRF
    CheckCSRF();
    //文档的默认状态
    if ($cInfos['arcsta'] == 0) {
        $arcrank = 0;
    } else if ($cInfos['arcsta'] == 1) {
        $arcrank = 0;
    } else {
        $arcrank = -1;
    }
    //对保存的文档进行处理
    $title = cn_substrR(HtmlReplace($title, 1), $cfg_title_maxlen);
    $mid = $cfg_ml->M_ID;
    //分析处理附加表数据
    $inadd_f = $inadd_m = '';
    if (!empty($dede_addonfields)) {
        $addonfields = explode(';', $dede_addonfields);
        if (is_array($addonfields)) {
            foreach ($addonfields as $v) {
                if ($v == '') {
                    continue;
                }
                $vs = explode(',', $v);
                if (!isset(${$vs[0]})) {
                    ${$vs[0]} = '';
                }
                //自动摘要和远程图片本地化
                if ($vs[1] == 'htmltext' || $vs[1] == 'textdata') {
                    ${$vs[0]} = AnalyseHtmlBody(${$vs[0]}, $description, $vs[1]);
                }
                ${$vs[0]} = GetFieldValueA(${$vs[0]}, $vs[1], $aid);
                $inadd_f .= ',`'.$vs[0]."` ='".${$vs[0]}."' ";
                $inadd_m .= ','.$vs[0];
            }
        }
        //这里对前台提交的附加数据进行一次校验
        $fontiterm = PrintAutoFieldsAdd(stripslashes($cInfos['fieldset']), 'autofield', FALSE);
        if ($fontiterm != $inadd_m) {
            ShowMsg("提交的信息有错误，请修改重新提交", "-1");
            exit();
        }
    }
    if ($addtable != '') {
        $litpic = isset($litpic)? HtmlReplace($litpic, 1) : '';
        $upQuery = "UPDATE `$addtable` SET `title`='$title',`typeid`='$typeid',`arcrank`='$arcrank',litpic='$litpic',userip='$userip'{$inadd_f} WHERE aid='$aid' ";
        if (!$dsql->ExecuteNoneQuery($upQuery)) {
            ShowMsg("数据保存到数据库附加表出错，请联系管理员", "javascript:;");
            exit();
        }
    }
    UpIndexKey($aid, 0, $typeid, $sortrank, '');
    $artUrl = MakeArt($aid, true);
    if ($artUrl == '') $artUrl = $cfg_phpurl."/view.php?aid=$aid";
    //返回成功信息
    $msg = "<a href='$artUrl' target='_blank' class='btn btn-success btn-sm'>浏览文档</a><a href='archives_sg_add.php?cid=$typeid' class='btn btn-success btn-sm'>发布文档</a><a href='archives_do.php?channelid=$channelid&aid=".$aid."&dopost=edit' class='btn btn-success btn-sm'>修改文档</a><a href='content_sg_list.php?channelid=$channelid' class='btn btn-success btn-sm'>返回文档列表</a>";
    $wintitle = "成功修改分类文档";
    $win = new WebWindow();
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", false);
    $win->Display(DEDEMEMBER."/templets/win_templet.htm");
}
?>