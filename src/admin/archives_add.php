<?php
/**
 * 文档发布
 *
 * @version        $Id: archives_add.php 1 8:26 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__).'/config.php');
UserLogin::CheckPurview('a_New,a_AccNew');
require_once(DEDEADMIN.'/inc/inc_archives_functions.php');
if (empty($dopost)) $dopost = '';
if ($dopost != 'save') {
    require_once(DEDEADMIN.'/inc/inc_catalog_options.php');
    UserLogin::ClearMyAddon();
    $channelid = empty($channelid) ? 0 : intval($channelid);
    $cid = empty($cid) ? 0 : intval($cid);
    //获得频道模型id
    if ($cid > 0 && $channelid == 0) {
        $row = $dsql->GetOne("SELECT channeltype FROM `#@__arctype` WHERE id='$cid';");
        $channelid = $row['channeltype'];
    } else {
        if ($channelid == 0) {
            ShowMsg(Lang('content_err_channel_empty'), '-1');
            exit();
        }
    }
    //获得频道模型信息
    $cInfos = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$channelid'");
    $channelid = $cInfos['id'];
    //获取文档最大id+1以确定当前权重
    $maxWright = $dsql->GetOne("SELECT id+1 AS cc FROM `#@__archives` ORDER BY id DESC LIMIT 1");
    $maxWright = empty($maxWright)? array('cc'=>1) :  $maxWright;
    include DedeInclude('templets/archives_add.htm');
    exit();
}
else if ($dopost == 'save') {
    helper('image');
    $flag = isset($flags) ? join(',', $flags) : '';
    $notpost = isset($notpost) && $notpost == 1 ? 1 : 0;
    if (empty($click)) $click = ($cfg_arc_click == '-1' ? mt_rand(50, 200) : $cfg_arc_click);
    if (empty($typeid2)) $typeid2 = 0;
    if (!isset($autokey)) $autokey = 0;
    if (!isset($remote)) $remote = 0;
    if (!isset($dellink)) $dellink = 0;
    if (!isset($autolitpic)) $autolitpic = 0;
    if (empty($click)) $click = ($cfg_arc_click == '-1' ? mt_rand(50, 200) : $cfg_arc_click);
    if ($typeid == 0) {
        ShowMsg(Lang('content_error_typeid_isempty'), '-1');
        exit();
    }
    if (empty($channelid)) {
        ShowMsg(Lang('content_error_channelid_isempty'), '-1');
        exit();
    }
    if (!CheckChannel($typeid, $channelid)) {
        ShowMsg(Lang('content_error_channelid_check_failed'), '-1');
        exit();
    }
    if (!UserLogin::TestPurview('a_New')) {
        UserLogin::CheckCatalog($typeid, Lang('content_error_channelid_check_failed',array('typeid'=>$typeid)));
    }
    //对保存的内容进行处理
    if (empty($writer)) $writer = $cUserLogin->getUserName();
    if (empty($source)) $source = Lang('unknow');
    $pubdate = GetMkTime($pubdate);
    $senddate = time();
    $sortrank = AddDay($pubdate, $sortup);
    $ismake = $ishtml == 0 ? -1 : 0;
    $title = preg_replace("#\"#", '＂', $title);
    $title = cn_substrR($title, $cfg_title_maxlen);
    $shorttitle = cn_substrR($shorttitle, 36);
    $color =  cn_substrR($color, 7);
    $writer =  cn_substrR($writer, 20);
    $source = cn_substrR($source, 30);
    $description = cn_substrR($description, $cfg_auot_description);
    $keywords = cn_substrR($keywords, 60);
    $filename = trim(cn_substrR($filename, 40));
    $userip = GetIP();
    $serviterm = empty($serviterm) ? "" : $serviterm;
    if (!UserLogin::TestPurview('a_Check,a_AccCheck,a_MyCheck')) {
        $arcrank = -1;
    }
    $adminid = $cUserLogin->getUserID();
    //处理上传的缩略图
    if (empty($ddisremote)) {
        $ddisremote = 0;
    }
    $litpic = GetDDImage('none', $picname, $ddisremote);
    //生成文档id
    $arcID = GetIndexKey($arcrank, $typeid, $sortrank, $channelid, $senddate, $adminid);
    if (empty($arcID)) {
        ShowMsg(Lang("content_error_id_is_empty"), "-1");
        exit();
    }
    //分析处理附加表数据
    $inadd_f = $inadd_v = '';
    if (!empty($dede_addonfields)) {
        $addonfields = explode(';', $dede_addonfields);
        if (is_array($addonfields)) {
            foreach ($addonfields as $v) {
                if ($v == '') continue;
                $vs = explode(',', $v);
                if ($vs[1] == 'htmltext' || $vs[1] == 'textdata') {
                    ${$vs[0]} = AnalyseHtmlBody(${$vs[0]}, $description, $litpic, $keywords, $vs[1]);
                } else {
                    if (!isset(${$vs[0]})) ${$vs[0]} = '';
                    ${$vs[0]} = GetFieldValueA(${$vs[0]}, $vs[1], $arcID);
                }
                $inadd_f .= ','.$vs[0];
                $inadd_v .= " ,'".${$vs[0]}."' ";
            }
        }
    }
    //处理图片文档的自定义属性
    if ($litpic != '' && !preg_match("#p#", $flag)) {
        $flag = ($flag == '' ? 'p' : $flag.',p');
    }
    if ($redirecturl != '' && !preg_match("#j#", $flag)) {
        $flag = ($flag == '' ? 'j' : $flag.',j');
    }
    //跳转网址的文档强制为动态
    if (preg_match("#j#", $flag)) $ismake = -1;
    //保存到主表
    $query = "INSERT INTO `#@__archives`(id,typeid,typeid2,sortrank,flag,ismake,channel,arcrank,click,money,title,shorttitle,color,writer,source,litpic,pubdate,senddate,mid,notpost,description,keywords,filename,dutyadmin,weight) VALUES ('$arcID','$typeid','$typeid2','$sortrank','$flag','$ismake','$channelid','$arcrank','$click','$money','$title','$shorttitle','$color','$writer','$source','$litpic','$pubdate','$senddate','$adminid','$notpost','$description','$keywords','$filename','$adminid','$weight');";
    if (!$dsql->ExecuteNoneQuery($query)) {
        $gerr = $dsql->GetError();
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
        ShowMsg(Lang('content_error_archives_save',array('error'=>str_replace('"', '', $gerr))), "javascript:;");
        exit();
    }
    //保存到附加表
    $cts = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$channelid'");
    $addtable = trim($cts['addtable']);
    if (!empty($addtable)) {
        $useip = GetIP();
        $query = "INSERT INTO `{$addtable}`(aid,typeid,redirecturl,userip,templet{$inadd_f}) VALUES ('$arcID','$typeid','$redirecturl','$useip',''{$inadd_v})";
        if (!$dsql->ExecuteNoneQuery($query)) {
            $gerr = $dsql->GetError();
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE id='$arcID'");
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
            ShowMsg(Lang('content_error_addtable_save',array('addtable'=>$addtable, 'error'=>str_replace('"', '', $gerr))), "javascript:;");
            exit();
        }
    }
    //生成网页
    InsertTags($tags, $arcID);
    $artUrl = MakeArt($arcID, true, true);
    if ($artUrl == '') {
        $artUrl = $cfg_phpurl."/view.php?aid=$arcID";
    }
    UserLogin::ClearMyAddon($arcID, $title);
    //自动更新关联内容
    if (isset($automake) && is_array($automake)) {
        foreach ($automake as $key => $value) {
            if (isset(${$key}) && !empty(${$key})) {
                $ids = explode(",", ${$key});
                foreach ($ids as $id) {
                    MakeArt($id, true, true);
                }
            }
        }
    }
    //返回成功信息
    $msg = Lang('more_actions')."：<a href='archives_add.php?cid=$typeid' class='btn btn-success btn-sm'>".Lang('content_continue_publish')."</a><a href='$artUrl' target='_blank' class='btn btn-success btn-sm'>".Lang('content_view')."</a><a href='archives_do.php?aid=".$arcID."&dopost=editArchives' class='btn btn-success btn-sm'>".Lang('content_edit')."</a><a href='catalog_do.php?cid=$typeid&dopost=listArchives' class='btn btn-success btn-sm'>".Lang('content_published_main')."</a>$backurl";
    $msg = "<div>{$msg}</div>".GetUpdateTest();
    $wintitle = Lang("content_success_publish");
    $wecome_info = Lang('content_main')."::".Lang('content_add');
    DedeWin::Instance()->AddTitle(Lang("content_success_publish").'：')->AddMsgItem($msg)->GetWindow('hand', '&nbsp;', false)->Display();
}
?>