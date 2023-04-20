<?php
/**
 * 修改图片模型
 * 
 * @version        $id:album_edit.php 13:52 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
//考虑安全原因不管是否开启游客投稿功能，都不允许会员对图片投稿
CheckRank(0, 0);
if ($cfg_mb_lit == 'Y') {
    ShowMsg("由于系统开启会员空间精简版，您浏览的功能不可用", "-1");
    exit();
}
if ($cfg_mb_album == 'N') {
    ShowMsg("由于系统关闭了图片功能，您浏览的功能不可用", "-1");
    exit();
}
require_once(DEDEINC."/dedetag.class.php");
require_once(DEDEINC."/customfields.func.php");
require_once(DEDEMEMBER."/inc/inc_catalog_options.php");
require_once(DEDEMEMBER."/inc/inc_archives_functions.php");
$channelid = isset($channelid) && is_numeric($channelid) ? $channelid : 2;
$aid = isset($aid) && is_numeric($aid) ? $aid : 0;
$menutype = 'content';
if (empty($formhtml)) $formhtml = 0;
if ($cfg_ml->IsSendLimited()) {
    ShowMsg("投稿失败，投稿限定次数<span class='text-primary'>{$cfg_ml->M_SendMax}次</span>（剩余0次），需要增加次数，请联系网站管理员", "-1", "0", 5000);
    exit();
}
if (empty($dopost)) {
    //读取归档信息
    $arcQuery = "SELECT arc.*,ch.addtable,ch.fieldset,ch.arcsta FROM `#@__archives` arc LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel WHERE arc.id='$aid' AND arc.mid='".$cfg_ml->M_ID."'; ";
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
    $dtp = new DedeTagParse();
    $dtp->LoadSource($addRow['imgurls']);
    $abinfo = $dtp->GetTagByName('pagestyle');
    $row = XSSClean($row);
    $addRow = XSSClean($addRow);
    include(DEDEMEMBER."/templets/album_edit.htm");
    exit();
} else if ($dopost == 'save') {
    $cInfos = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$channelid';");
    $maxwidth = isset($maxwidth) && is_numeric($maxwidth) ? $maxwidth : 800;
    $pagepicnum = isset($pagepicnum) && is_numeric($pagepicnum) ? $pagepicnum : 12;
    $ddmaxwidth = isset($ddmaxwidth) && is_numeric($ddmaxwidth) ? $ddmaxwidth : 200;
    $prow = isset($prow) && is_numeric($prow) ? $prow : 3;
    $pcol = isset($pcol) && is_numeric($pcol) ? $pcol : 3;
    $pagestyle = in_array($pagestyle, array('1', '2', '3')) ? $pagestyle : 2;
    include(DEDEMEMBER.'/inc/archives_check_edit.php');
    $imgurls = "{dede:pagestyle maxwidth='$maxwidth' pagepicnum='$pagepicnum'
    ddmaxwidth='$ddmaxwidth' row='$prow' col='$pcol' value='$pagestyle'/}\r\n";
    $hasone = false;
    $ddisfirst = 1;
    //只支持填写地址
    for ($i = 1; $i <= 120; $i++) {
        if (!isset(${'imgfile'.$i})) {
            continue;
        }
        $f = ${'imgfile'.$i};
        $msg = isset(${'imgmsg'.$i}) ? ${'imgmsg'.$i} : "";
        if (!empty($f) && filter_var($f, FILTER_VALIDATE_URL)) {
            $u = str_replace(array("\"", "'"), "`", $f);
            $info = str_replace(array("\"", "'"), "`", $msg);
            $imgurls .= "{dede:img ddimg='' text='$info'} $u {/dede:img}\r\n";
        }
    } //循环结束
    $imgurls = addslashes($imgurls);
    //分析处理附加表数据
    $inadd_f = '';
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
    $description = HtmlReplace($description, -1);
    $body = HtmlReplace($body, -1);
    //更新数据库的SQL语句
    $litpic = isset($litpic)? HtmlReplace($litpic, 1) : '';
    $upQuery = "UPDATE `#@__archives` SET ismake='$ismake',arcrank='$arcrank',typeid='$typeid',title='$title',description='$description',keywords='$keywords',mtype='$mtypesid',flag='$flag',litpic='$litpic' WHERE id='$aid' AND mid='$mid'; ";
    if (!$dsql->ExecuteNoneQuery($upQuery)) {
        ShowMsg("数据保存到数据库主表`#@__archives`时出错，请联系管理员".$dsql->GetError(), "-1");
        exit();
    }
    $isrm = 0;
    if ($addtable != '') {
        $query = "UPDATE `$addtable` SET typeid='$typeid',pagestyle='$pagestyle',maxwidth='$maxwidth',ddmaxwidth='$ddmaxwidth',pagepicnum='$pagepicnum',imgurls='$imgurls',`row`='$prow',col='$pcol',userip='$userip',isrm='$isrm',body='$body' {$inadd_f} WHERE aid='$aid'; ";
        if (!$dsql->ExecuteNoneQuery($query)) {
            ShowMsg("数据保存到数据库附加表时出错，请联系管理员".$dsql->GetError(), "javascript:;");
            exit();
        }
    }
    UpIndexKey($aid, $arcrank, $typeid, $sortrank, $tags);
    $artUrl = MakeArt($aid, TRUE);
    if ($artUrl == '') $artUrl = $cfg_phpurl."/view.php?aid=$aid";
    //返回成功信息
    $msg = "请选择您的后续操作：<a href='album_add.php?cid=$typeid' class='btn btn-success btn-sm'>发布图片文档</a><a href='archives_do.php?channelid=$channelid&aid=".$aid."&dopost=edit' class='btn btn-success btn-sm'>修改图片文档</a><a href='$artUrl' target='_blank' class='btn btn-success btn-sm'>浏览图片文档</a><a href='content_list.php?channelid=$channelid' class='btn btn-success btn-sm'>管理图片文档</a> ";
    //提交后返回提交页面
    $wintitle = "成功修改图片文档";
    $wecome_info = "图片管理::修改图片文档";
    $win = new OxWindow();
    $win->AddTitle("成功修改图片文档");
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", "&nbsp;", false);
    $win->Display(DEDEMEMBER."/templets/win_templet.htm");
}
?>