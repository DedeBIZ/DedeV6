<?php
/**
 * 修改自定义文档模型
 * 
 * @version        $id:archives_edit.php 13:52 2010年7月9日 tianya $
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
if ($cfg_ml->IsSendLimited()) {
    ShowMsg("投稿失败，每天次数<span class='text-primary'>{$cfg_ml->M_SendMax}次</span>，需要增加次数，请联系网站管理员", "-1", "0", 5000);
    exit();
}
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
            ShowMsg("这篇文档已经锁定，暂时无法修改", "-1");
            exit();
        }
    }
    $addRow = $dsql->GetOne("SELECT * FROM `{$row['addtable']}` WHERE aid='$aid';");
    $cInfos = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='{$row['channel']}';");
    include(DEDEMEMBER."/templets/archives_edit.htm");
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
                //自动摘要和远程图片本地化
                if ($vs[1] == 'htmltext' || $vs[1] == 'textdata') {
                    ${$vs[0]} = AnalyseHtmlBody(${$vs[0]}, $description, $vs[1]);
                }
                ${$vs[0]} = GetFieldValueA(${$vs[0]}, $vs[1], $aid);
                $inadd_m .= ','.$vs[0];
                $inadd_f .= ','.$vs[0]." ='".${$vs[0]}."' ";
            }
        }
        //这里对前台提交的附加数据进行一次校验
        $fontiterm = PrintAutoFieldsAdd(stripslashes($cInfos['fieldset']), 'autofield', FALSE);
        if ($fontiterm != $inadd_m) {
            ShowMsg("提交表单同系统配置不相符，请重新提交", "-1");
            exit();
        }
    }
    //处理图片文档的自定义属性
    if ($litpic != '') $flag = 'p';
    //更新数据库的SQL语句
    $litpic = isset($litpic)? HtmlReplace($litpic, 1) : '';
    $upQuery = "UPDATE `#@__archives` SET ismake='$ismake',arcrank='$arcrank',typeid='$typeid',title='$title',litpic='$litpic',description='$description',keywords='$keywords',mtype='$mtypesid',flag='$flag' WHERE id='$aid' And mid='$mid'; ";
    if (!$dsql->ExecuteNoneQuery($upQuery)) {
        ShowMsg("数据保存到数据库主表`#@__archives`时出错，请联系管理员".$dsql->GetError(), "-1");
        exit();
    }
    if ($addtable != '') {
        $upQuery = "UPDATE `$addtable` SET typeid='$typeid'{$inadd_f}, userip='$userip' WHERE aid='$aid' ";
        if (!$dsql->ExecuteNoneQuery($upQuery)) {
            ShowMsg("数据保存到数据库附加表时出错，请联系管理员", "javascript:;");
            exit();
        }
    }
    $arcrank = empty($arcrank) ? 0 : $arcrank;
    $sortrank = empty($sortrank) ? 0 : $sortrank;
    UpIndexKey($aid, $arcrank, $typeid, $sortrank, $tags);
    $artUrl = MakeArt($aid, TRUE);
    if ($artUrl == '') $artUrl = $cfg_phpurl."/view.php?aid=$aid";
    //返回成功信息
    $msg = "请选择您的后续操作：<a href='archives_add.php?cid=$typeid&channelid=$channelid' class='btn btn-success btn-sm'>发布自定义文档</a><a href='archives_edit.php?channelid=$channelid&aid=".$aid."' class='btn btn-success btn-sm'>修改自定义文档</a><a class='btn btn-success btn-sm' href='$artUrl' target='_blank'>浏览自定义文档</a><a href='content_list.php?channelid=$channelid' class='btn btn-success btn-sm'>管理自定义文档</a>";
    $wintitle = "成功修改自定义文档";
    $wecome_info = "文档管理::修改自定义文档";
    $win = new OxWindow();
    $win->AddTitle("成功修改自定义文档");
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", "&nbsp;", false);
    $win->Display(DEDEMEMBER."/templets/win_templet.htm");
}
?>