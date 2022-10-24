<?php
/**
 * 单表模型文档发布
 *
 * @version        $Id: archives_sg_add.php 1 8:26 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('a_New,a_AccNew');
require_once(DEDEADMIN."/inc/inc_archives_functions.php");
if (empty($dopost)) $dopost = '';
if ($dopost != 'save') {
    require_once(DEDEADMIN."/inc/inc_catalog_options.php");
    UserLogin::ClearMyAddon();
    $channelid = empty($channelid) ? 0 : intval($channelid);
    $cid = empty($cid) ? 0 : intval($cid);
    //获得频道模型id
    if ($cid > 0 && $channelid == 0) {
        $row = $dsql->GetOne("SELECT channeltype FROM `#@__arctype` WHERE id='$cid';");
        $channelid = $row['channeltype'];
    } else {
        if ($channelid == 0) {
            ShowMsg(Lang('content_err_channel_empty'), "-1");
            exit();
        }
    }
    //获得频道模型信息
    $cInfos = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$channelid'");
    $channelid = $cInfos['id'];
    include DedeInclude("templets/archives_sg_add.htm");
    exit();
}
else if ($dopost == 'save') {
    helper('image');
    if ($typeid == 0) {
        ShowMsg(Lang('content_error_typeid_isempty'), "-1");
        exit();
    }
    if (empty($channelid)) {
        ShowMsg(Lang('content_error_channelid_isempty'), "-1");
        exit();
    }
    if (!CheckChannel($typeid, $channelid)) {
        ShowMsg(Lang('content_error_channelid_check_failed'), "-1");
        exit();
    }
    if (!UserLogin::TestPurview('a_New')) {
        UserLogin::CheckCatalog($typeid, Lang('content_error_channelid_check_failed',array('typeid'=>$typeid)));
    }
    //对保存的内容进行处理
    if (empty($writer)) $writer = $cUserLogin->getUserName();
    if (empty($source)) $source = Lang('unknow');
    if (empty($flags)) $flag = '';
    else $flag = join(',', $flags);
    $senddate = time();
    $title = cn_substrR($title, $cfg_title_maxlen);
    $serviterm = empty($serviterm) ? "" : $serviterm;
    if (!UserLogin::TestPurview('a_Check,a_AccCheck,a_MyCheck')) $arcrank = -1;
    $adminid = $cUserLogin->getUserID();
    $userip = GetIP();
    if (empty($ddisremote)) $ddisremote = 0;
    $litpic = GetDDImage('none', $picname, $ddisremote);
    //生成文档id
    $arcID = GetIndexKey($arcrank, $typeid, $senddate, $channelid, $senddate, $adminid);
    if (empty($arcID)) {
        ShowMsg(Lang("content_error_id_is_empty"), "-1");
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
                }
                $vs = explode(',', $v);
                if ($vs[1] == 'htmltext' || $vs[1] == 'textdata') //网页文本特殊处理
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
    }
    //处理图片文档的自定义属性
    if ($litpic != '' && !preg_match("#p#", $flag)) {
        $flag = ($flag == '' ? 'p' : $flag.',p');
    }
    //保存到附加表
    $cts = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$channelid'");
    $addtable = trim($cts['addtable']);
    if (!empty($addtable)) {
        $query = "INSERT INTO `{$addtable}`(aid,typeid,channel,arcrank,mid,click,title,senddate,flag,litpic,userip{$inadd_f}) VALUES ('$arcID','$typeid','$channelid','$arcrank','$adminid','0','$title','$senddate','$flag','$litpic','$userip'{$inadd_v})";
        if (!$dsql->ExecuteNoneQuery($query)) {
            $gerr = $dsql->GetError();
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
            ShowMsg(Lang('content_error_addtable_save',array('addtable'=>$addtable, 'error'=>str_replace('"', '', $gerr))), "javascript:;");
            exit();
        }
    }
    //生成网页
    $artUrl = MakeArt($arcID, TRUE, TRUE);
    if ($artUrl == '') {
        $artUrl = $cfg_phpurl."/view.php?aid=$arcID";
    }
    UserLogin::ClearMyAddon($arcID, $title);
    //返回成功信息
    $msg = Lang('more_actions')."：<a href='archives_sg_add.php?cid=$typeid' class='btn btn-success btn-sm'>".Lang('content_continue_publish')."</a><a href='$artUrl' target='_blank' class='btn btn-success btn-sm'>".Lang('content_view')."</a><a href='archives_do.php?aid=".$arcID."&dopost=editArchives' class='btn btn-success btn-sm'>".Lang('content_edit')."</a><a href='content_sg_list.php?cid=$typeid&channelid={$channelid}&dopost=listArchives' class='btn btn-success btn-sm'>".Lang('content_published_main')."</a><a href='catalog_main.php' class='btn btn-success btn-sm'>".Lang('catalog_main')."</a>";
    $wintitle = Lang("content_success_publish");
    $wecome_info = Lang('content_main')."::".Lang('content_add');
    DedeWin::Instance()->AddTitle(Lang("content_success_publish")."：")->AddMsgItem($msg)->GetWindow("hand", "&nbsp;", false)->Display();
}
?>