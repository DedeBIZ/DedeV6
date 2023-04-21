<?php
/**
 * 发布文档模型
 * 
 * @version        $id:article_add.php 8:38 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckRank(0, 0);
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
    ShowMsg("投稿失败，投稿限定次数<span class='text-primary'>{$cfg_ml->M_SendMax}次</span>（剩余0次），需要增加次数，请联系网站管理员", "index.php", "0", 5000);
    exit();
}
if (empty($dopost)) {
    $cInfos = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$channelid';");
    //如果限制了会员级别或类型，则允许游客投稿选项无效
    if ($cInfos['sendrank'] > 0 || $cInfos['usertype'] != '') CheckRank(0, 0);
    //检查会员等级和类型限制
    if ($cInfos['sendrank'] > $cfg_ml->M_Rank) {
        $row = $dsql->GetOne("SELECT membername FROM `#@__arcrank` WHERE `rank`='".$cInfos['sendrank']."' ");
        ShowMsg("需要<span class='text-primary'>".$row['membername']."</span>才能在这个栏目发布文档", "-1", "0", 5000);
        exit();
    }
    if ($cInfos['usertype'] != '' && $cInfos['usertype'] != $cfg_ml->M_MbType) {
        ShowMsg("需要<span class='text-primary'>".$cInfos['usertype']."</span>帐号才能在这个栏目发布文档", "-1", "0", 5000);
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
        ShowMsg("提交表单同系统配置不相符，请重新提交", "-1");
        exit();
    }
    $body = AnalyseHtmlBody($body, $description);
    $body = HtmlReplace($body, -1);
    $litpic = isset($litpic)? HtmlReplace($litpic, 1) : '';
    
    //生成文档id
    $arcID = GetIndexKey($arcrank, $typeid, $sortrank, $channelid, $senddate, $mid);
    if (empty($arcID)) {
        ShowMsg("无法获得主键，因此无法进行后续操作", "-1");
        exit();
    }
    //保存到主表
    $inQuery = "INSERT INTO `#@__archives` (id,typeid,sortrank,flag,ismake,channel,arcrank,click,`money`,title,shorttitle,color,writer,source,litpic,pubdate,senddate,mid,description,keywords,mtype)
    VALUES ('$arcID','$typeid','$sortrank','$flag','$ismake','$channelid','$arcrank','0','$money','$title','$shorttitle','$color','$writer','$source','$litpic','$pubdate','$senddate','$mid','$description','$keywords','$mtypesid'); ";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        $gerr = $dsql->GetError();
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID' ");
        ShowMsg("数据保存到数据库主表`#@__archives`时出错，请联系管理员", "javascript:;");
        exit();
    }
    //保存到附加表
    $addtable = trim($cInfos['addtable']);
    if (empty($addtable)) {
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE id='$arcID'");
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
        ShowMsg("没找到当前模型<span class='text-primary'>{$channelid}</span>主表信息，无法完成操作", "javascript:;");
        exit();
    } else {
        $inquery = "INSERT INTO `{$addtable}` (aid,typeid,userip,redirecturl,templet,body{$inadd_f}) VALUES ('$arcID','$typeid','$userip','','','$body'{$inadd_v})";
        if (!$dsql->ExecuteNoneQuery($inquery)) {
            $gerr = $dsql->GetError();
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE id='$arcID'");
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
            ShowMsg("数据保存到数据库附加表时出错，请联系管理员", "javascript:;");
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
    $msg = "请选择您的后续操作：<a href='article_add.php?cid=$typeid' class='btn btn-success btn-sm'>发布文档</a><a href='article_edit.php?channelid=$channelid&aid=$arcID' class='btn btn-success btn-sm'>修改文档</a><a href='$artUrl' target='_blank' class='btn btn-success btn-sm'>浏览文档</a><a href='content_list.php?channelid={$channelid}' class='btn btn-success btn-sm'>管理文档</a>";
    $wintitle = "成功发布文档";
    $wecome_info = "文档管理::发布文档";
    $win = new OxWindow();
    $win->AddTitle("成功发布文档");
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", false);
    $win->Display(DEDEMEMBER."/templets/win_templet.htm");
}
?>