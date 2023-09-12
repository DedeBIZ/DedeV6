<?php
/**
 * 发布文档模型
 * 
 * @version        $id:article_add.php 8:38 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckRank(0, 0);//禁止游客操作
require_once(DEDEINC."/dedetag.class.php");
require_once(DEDEINC."/userlogin.class.php");
require_once(DEDEINC."/customfields.func.php");
require_once(DEDEMEMBER."/inc/inc_catalog_options.php");
require_once(DEDEMEMBER."/inc/inc_archives_functions.php");
$channelid = isset($channelid) && is_numeric($channelid) ? $channelid : 1;
$typeid = isset($typeid) && is_numeric($typeid) ? $typeid : 0;
$mtypesid = isset($mtypesid) && is_numeric($mtypesid) ? $mtypesid : 0;
$menutype = 'content';
if ($cfg_ml->IsSendLimited()) {
    ShowMsg("投稿失败，每日投稿次数{$cfg_ml->M_SendMax}次，剩余0次，需要增加次数，请联系网站管理员", "index.php", "0", 5000);
    exit();
}
if (empty($dopost)) {
    $cInfos = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$channelid';");
    //检查会员等级和类型限制
    if ($cInfos['sendrank'] > $cfg_ml->M_Rank) {
        $row = $dsql->GetOne("SELECT membername FROM `#@__arcrank` WHERE `rank`='".$cInfos['sendrank']."' ");
        ShowMsg("需要".$row['membername']."才能在这个栏目发布文档", "-1", "0", 5000);
        exit();
    }
    if ($cInfos['usertype'] != '' && $cInfos['usertype'] != $cfg_ml->M_MbType) {
        ShowMsg("需要".$cInfos['usertype']."帐号才能在这个栏目发布文档", "-1", "0", 5000);
        exit();
    }
    include(DEDEMEMBER."/templets/article_add.htm");
    exit();
} else if ($dopost == 'save') {
    include(DEDEMEMBER.'/inc/archives_check.php');
    //分析处理附加表数据
    $inadd_f = $inadd_v = '';
    if (!empty($dede_addonfields)) {
        $addonfields = explode(';', $dede_addonfields);
        $inadd_f = '';
        $inadd_v = '';
        if (is_array($addonfields)) {
            foreach ($addonfields as $v) {
                if ($v == '') {
                    continue;
                }
                $vs = explode(',', $v);
                if (!isset(${$vs[0]})) {
                    ${$vs[0]} = '';
                }
                ${$vs[0]} = GetFieldValueA(${$vs[0]}, $vs[1], 0);
                $inadd_f .= ','.$vs[0];
                $inadd_v .= " ,'".${$vs[0]}."' ";
            }
        }
    }
    //这里对前台提交的附加数据进行一次校验
    $fontiterm = PrintAutoFieldsAdd(stripslashes($cInfos['fieldset']), 'autofield', FALSE);
    if ($fontiterm != $inadd_f) {
        ShowMsg("提交的信息有错误，请修改重新提交", "-1");
        exit();
    }
    $body = AnalyseHtmlBody($body, $description);
    $body = HtmlReplace($body, -1);
    $litpic = isset($litpic)? HtmlReplace($litpic, 1) : '';
    //生成文档id
    $arcID = GetIndexKey($arcrank, $typeid, $sortrank, $channelid, $senddate, $mid);
    if (empty($arcID)) {
        ShowMsg("获取主键失败，无法进行后续操作", "-1");
        exit();
    }
    //保存到主表
    $inQuery = "INSERT INTO `#@__archives` (id,typeid,sortrank,flag,ismake,channel,arcrank,click,`money`,title,shorttitle,color,writer,source,litpic,pubdate,senddate,mid,description,keywords,mtype)
    VALUES ('$arcID','$typeid','$sortrank','$flag','$ismake','$channelid','$arcrank','0','$money','$title','$shorttitle','$color','$writer','$source','$litpic','$pubdate','$senddate','$mid','$description','$keywords','$mtypesid'); ";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        $gerr = $dsql->GetError();
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID' ");
        ShowMsg("数据保存到数据库文档主表出错，请联系管理员", "javascript:;");
        exit();
    }
    //保存到附加表
    $addtable = trim($cInfos['addtable']);
    if (empty($addtable)) {
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE id='$arcID'");
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
        ShowMsg("没找到模型{$channelid}主表信息，无法完成操作", "javascript:;");
        exit();
    } else {
        $inquery = "INSERT INTO `{$addtable}` (aid,typeid,userip,redirecturl,templet,body{$inadd_f}) VALUES ('$arcID','$typeid','$userip','','','$body'{$inadd_v})";
        if (!$dsql->ExecuteNoneQuery($inquery)) {
            $gerr = $dsql->GetError();
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE id='$arcID'");
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
            ShowMsg("数据保存到数据库附加表出错，请联系管理员", "javascript:;");
            exit();
        }
    }
    //增加积分
    $dsql->ExecuteNoneQuery("UPDATE `#@__member` set scores=scores+{$cfg_sendarc_scores} WHERE mid='".$cfg_ml->M_ID."' ;");
    //更新统计
    countArchives($channelid);
    //生成网页
    InsertTags($tags, $arcID);
    $artUrl = MakeArt($arcID, TRUE);
    if ($artUrl == '') $artUrl = $cfg_phpurl."/view.php?aid=$arcID";
    ClearMyAddon($arcID, $title);
    //返回成功信息
    $msg = "<a href='$artUrl' target='_blank' class='btn btn-success btn-sm'>浏览文档</a><a href='article_add.php?cid=$typeid' class='btn btn-success btn-sm'>发布文档</a><a href='article_edit.php?channelid=$channelid&aid=$arcID' class='btn btn-success btn-sm'>修改文档</a><a href='content_list.php?channelid={$channelid}' class='btn btn-success btn-sm'>管理文档</a>";
    $wintitle = "成功发布文档";
    $wecome_info = "文档管理 - 发布文档";
    $win = new OxWindow();
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", false);
    $win->Display(DEDEMEMBER."/templets/win_templet.htm");
}
?>