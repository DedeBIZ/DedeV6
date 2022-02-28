<?php
/**
 * 软件添加
 * 
 * @version        $Id: soft_add.php 2 14:16 2010-11-11 tianya $
 * @package        DedeBIZ.Member
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
//考虑安全原因不管是否开启游客投稿功能，都不允许用户投稿
CheckRank(0, 0);
if ($cfg_mb_lit == 'Y') {
    ShowMsg("由于系统开启了精简版会员空间，您访问的功能不可用", "-1");
    exit();
}
require_once(DEDEINC."/dedetag.class.php");
require_once(DEDEINC."/userlogin.class.php");
require_once(DEDEINC."/customfields.func.php");
require_once(DEDEMEMBER."/inc/inc_catalog_options.php");
require_once(DEDEMEMBER."/inc/inc_archives_functions.php");
$channelid = isset($channelid) && is_numeric($channelid) ? $channelid : 3;
$typeid = isset($typeid) && is_numeric($typeid) ? $typeid : 0;
$menutype = 'content';

/*-------------
function _ShowForm(){  }
--------------*/
if (empty($dopost)) {
    $cInfos = $dsql->GetOne("SELECT * FROM `#@__channeltype`  WHERE id='$channelid'; ");
    if (!is_array($cInfos)) {
        ShowMsg('模型不正确', '-1');
        exit();
    }

    //如果限制了会员级别或类型，则允许游客投稿选项无效
    if ($cInfos['sendrank'] > 0 || $cInfos['usertype'] != '') {
        CheckRank(0, 0);
    }

    //检查会员等级和类型限制
    if ($cInfos['sendrank'] > $cfg_ml->M_Rank) {
        $row = $dsql->GetOne("Select membername From `#@__arcrank` where rank='".$cInfos['sendrank']."' ");
        ShowMsg("对不起，需要[".$row['membername']."]才能在这个频道发布文档", "-1", "0", 5000);
        exit();
    }
    if ($cInfos['usertype'] != '' && $cInfos['usertype'] != $cfg_ml->M_MbType) {
        ShowMsg("对不起，需要[".$cInfos['usertype']."帐号]才能在这个频道发布文档", "-1", "0", 5000);
        exit();
    }
    include(DEDEMEMBER."/templets/soft_add.htm");
    exit();
}

/*------------------------------
function _SaveArticle(){  }
------------------------------*/
else if ($dopost == 'save') {
    $description = '';
    include(DEDEMEMBER.'/inc/archives_check.php');

    //生成文档ID
    $arcID = GetIndexKey($arcrank, $typeid, $sortrank, $channelid, $senddate, $mid);
    if (empty($arcID)) {
        ShowMsg("无法获得主键，因此无法进行后续操作", "-1");
        exit();
    }

    //分析处理附加表数据
    $inadd_f = '';
    $inadd_v = '';
    if (!empty($dede_addonfields)) {
        $addonfields = explode(';', $dede_addonfields);
        $inadd_f = '';
        $inadd_v = '';
        if (is_array($addonfields)) {
            foreach ($addonfields as $v) {
                if ($v == '') {
                    continue;
                } else if ($v == 'templet') {
                    ShowMsg("您保存的字段有误,请检查", "-1");
                    exit();
                }
                $vs = explode(',', $v);
                if (!isset(${$vs[0]})) {
                    ${$vs[0]} = '';
                } else if ($vs[1] == 'htmltext' || $vs[1] == 'textdata')

                //HTML文本特殊处理
                {
                    ${$vs[0]} = AnalyseHtmlBody(${$vs[0]}, $description, $litpic, $keywords, $vs[1]);
                } else {
                    if (!isset(${$vs[0]})) {
                        ${$vs[0]} = '';
                    }
                    ${$vs[0]} = GetFieldValueA(${$vs[0]}, $vs[1], $arcID);
                }
                $inadd_f .= ','.$vs[0];
                $inadd_v .= " ,'".${$vs[0]}."' ";
            }
        }

        //这里对前台提交的附加数据进行一次校验
        $fontiterm = PrintAutoFieldsAdd($cInfos['fieldset'], 'autofield', FALSE);
        if ($fontiterm != $inadd_f) {
            ShowMsg("提交表单同系统配置不相符,请重新提交", "-1");
            exit();
        }
    }

    //处理图片文档的自定义属性
    if ($litpic != '') {
        $flag = 'p';
    }
    $body = HtmlReplace($body, -1);

    //保存到主表
    $inQuery = "INSERT INTO `#@__archives`(id,typeid,sortrank,flag,ismake,channel,arcrank,click,money,title,shorttitle,
color,writer,source,litpic,pubdate,senddate,mid,description,keywords)
VALUES ('$arcID','$typeid','$sortrank','$flag','$ismake','$channelid','$arcrank','0','$money','$title','$shorttitle',
'$color','$writer','$source','$litpic','$pubdate','$senddate','$mid','$description','$keywords'); ";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        $gerr = $dsql->GetError();
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID' ");
        ShowMsg("把数据保存到数据库主表 `#@__archives` 时出错，请联系管理员", "javascript:;");
        exit();
    }

    //软件链接列表
    $softurl1 = stripslashes($softurl1);
    $softurl1 = str_replace(array("{dede:", "{/dede:", "}"), "#", $softurl1);
    $urls = '';
    if ($softurl1 != '') {
        if (preg_match("#}(.*?){/dede:link}{dede:#sim", $servermsg1) != 1) {
            $urls .= "{dede:link islocal='1' text='{$servermsg1}'} $softurl1 {/dede:link}\r\n";
        }
    }
    for ($i = 2; $i <= 12; $i++) {
        if (!empty(${'softurl'.$i})) {
            $servermsg = str_replace("'", "", stripslashes(${'servermsg'.$i}));
            $servermsg = str_replace(array("{dede:", "{/dede:", "}"), "#", $servermsg);
            $softurl = stripslashes(${'softurl'.$i});
            $softurl = str_replace(array("{dede:", "{/dede:", "}"), "#", $softurl);
            if ($servermsg == '') {
                $servermsg = '下载地址'.$i;
            }
            if ($softurl != '' && $softurl != 'http://') {
                $urls .= "{dede:link text='$servermsg'} $softurl {/dede:link}\r\n";
            }
        }
    }
    $urls = addslashes($urls);
    $softsize = $softsize.$unit;

    //保存到附加表
    $needmoney = @intval($needmoney);
    if ($needmoney > 100) $needmoney = 100;
    $cts = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$channelid' ");
    $addtable = trim($cts['addtable']);
    if (empty($addtable)) {
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE id='$arcID'");
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
        ShowMsg("没找到当前模型[{$channelid}]的主表信息，无法完成操作", "javascript:;");
        exit();
    }
    $inQuery = "INSERT INTO `$addtable`(aid,typeid,filetype,language,softtype,accredit,
    os,softrank,officialUrl,officialDemo,softsize,softlinks,introduce,userip,templet,redirecturl,daccess,needmoney{$inadd_f})
    VALUES ('$arcID','$typeid','$filetype','$language','$softtype','$accredit',
    '$os','$softrank','$officialUrl','$officialDemo','$softsize','$urls','$body','$userip','','','0','$needmoney'{$inadd_v});";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        $gerr = $dsql->GetError();
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE id='$arcID'");
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
        echo $inQuery;
        exit();
        ShowMsg("把数据保存到数据库附加表 `{$addtable}` 时出错，请把相关信息提交给DedeCMS官方".str_replace('"', '', $gerr), "javascript:;");
        exit();
    }

    //增加积分
    $cfg_sendarc_scores = intval($cfg_sendarc_scores);
    $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET scores=scores+{$cfg_sendarc_scores} WHERE mid='".$cfg_ml->M_ID."' ; ");
    //更新统计
    countArchives($channelid);

    //生成HTML
    InsertTags($tags, $arcID);
    $artUrl = MakeArt($arcID, TRUE);
    if ($artUrl == '') {
        $artUrl = $cfg_phpurl."/view.php?aid=$arcID";
    }

    ClearMyAddon($arcID, $title);

    //返回成功信息
    $msg = "
        请选择您的后续操作：
        <a href='soft_add.php?cid=$typeid' class='btn btn-secondary btn-sm'>继续发布软件</a>
        &nbsp;&nbsp;
        <a href='$artUrl' target='_blank' class='btn btn-secondary btn-sm'>查看软件</a>
        &nbsp;&nbsp;
        <a href='soft_edit.php?channelid=$channelid&aid=$arcID' class='btn btn-secondary btn-sm'>修改软件</a>
        &nbsp;&nbsp;
        <a href='content_list.php?channelid={$channelid}' class='btn btn-secondary btn-sm'>已发布软件管理</a>
        ";
    $wintitle = "成功发布文章";
    $wecome_info = "软件管理::发布软件";
    $win = new OxWindow();
    $win->AddTitle("成功发布软件：");
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", "&nbsp;", FALSE);
    $win->Display();
}
