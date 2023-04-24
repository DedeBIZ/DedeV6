<?php
/**
 * 修改文档模型
 * 
 * @version        $id:article_edit.php 13:52 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckRank(0, 0);
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
    $arcQuery = "SELECT arc.*,ch.addtable,ch.fieldset,arc.mtype as mtypeid,ch.arcsta FROM `#@__archives` arc LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel WHERE arc.id='$aid' And arc.mid='".$cfg_ml->M_ID."'; ";
    $row = $dsql->GetOne($arcQuery);
    if (!is_array($row)) {
        ShowMsg("读取文档信息出错", "-1");
        exit();
    } else if ($row['arcrank'] >= 0) {
        $dtime = time();
        $maxtime = $cfg_mb_editday * 24 * 3600;
        if ($dtime - $row['senddate'] > $maxtime) {
            ShowMsg("这篇文档已锁定，暂时无法修改", "-1");
            exit();
        }
    }
    $addRow = $dsql->GetOne("SELECT * FROM `{$row['addtable']}` WHERE aid='$aid';");
    include(DEDEMEMBER."/templets/article_edit.htm");
    exit();
} else if ($dopost == 'save') {
    include(DEDEMEMBER.'/inc/archives_check_edit.php');
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
                ${$vs[0]} = GetFieldValueA(${$vs[0]}, $vs[1], $aid);
                $inadd_f .= ','.$vs[0]." ='".${$vs[0]}."' ";
                $inadd_m .= ','.$vs[0];
            }
        }
        //这里对前台提交的附加数据进行一次校验
        $fontiterm = PrintAutoFieldsAdd(stripslashes($cInfos['fieldset']), 'autofield', FALSE);
        if ($fontiterm != $inadd_m) {
            ShowMsg("提交表单同系统配置不相符，请重新提交", "-1");
            exit();
        }
    }
    $body = AnalyseHtmlBody($body, $description);
    $body = HtmlReplace($body, -1);
    $litpic = isset($litpic)? HtmlReplace($litpic, 1) : '';
    //更新数据库的SQL语句
    $upQuery = "UPDATE `#@__archives` SET ismake='$ismake',arcrank='$arcrank',typeid='$typeid',title='$title',description='$description',mtype='$mtypesid',keywords='$keywords',flag='$flag',litpic='$litpic' WHERE id='$aid' AND mid='$mid'; ";
    if (!$dsql->ExecuteNoneQuery($upQuery)) {
        ShowMsg("数据保存到数据库主表`#@__archives`时出错，请联系管理员".$dsql->GetError(), "-1");
        exit();
    }
    if ($addtable != '') {
        $upQuery = "UPDATE `$addtable` SET typeid='$typeid',body='$body'{$inadd_f},userip='$userip' WHERE aid='$aid' ";
        if (!$dsql->ExecuteNoneQuery($upQuery)) {
            ShowMsg("数据保存到数据库附加表时出错，请联系管理员", "javascript:;");
            exit();
        }
    }
    UpIndexKey($aid, $arcrank, $typeid, $sortrank, $tags);
    $artUrl = MakeArt($aid, true);
    if ($artUrl == '') {
        $artUrl = $cfg_phpurl."/view.php?aid=$aid";
    }
    //返回成功信息
    $msg = "请选择您的后续操作：<a href='article_add.php?cid=$typeid' class='btn btn-success btn-sm'>发布文档</a><a href='archives_do.php?channelid=$channelid&aid=".$aid."&dopost=edit' class='btn btn-success btn-sm'>修改文档</a><a href='$artUrl' target='_blank' class='btn btn-success btn-sm'>浏览文档</a><a href='content_list.php?channelid=$channelid' class='btn btn-success btn-sm'>管理文档</a>";
    $wintitle = "成功修改文档";
    $wecome_info = "文档管理::修改文档";
    $win = new OxWindow();
    $win->AddTitle("成功修改文档");
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", false);
    $win->Display(DEDEMEMBER."/templets/win_templet.htm");
}
?>