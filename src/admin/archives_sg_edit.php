<?php
/**
 * 单表模型文档编辑
 *
 * @version        $Id: archives_sg_edit.php 1 8:26 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('a_Edit,a_AccEdit,a_MyEdit');
require_once(DEDEADMIN."/inc/inc_archives_functions.php");
if (empty($dopost)) $dopost = '';
if ($dopost != 'save') {
    require_once(DEDEADMIN."/inc/inc_catalog_options.php");
    UserLogin::ClearMyAddon();
    $aid = intval($aid);
    //读取归档信息
    $arcQuery = "SELECT ch.*,arc.* FROM `#@__arctiny` arc LEFT JOIN `#@__channeltype` ch on ch.id=arc.channel WHERE arc.id='$aid'";
    $cInfos = $dsql->GetOne($arcQuery);
    if (!is_array($cInfos)) {
        ShowMsg(Lang("content_err_channel"), "-1");
        exit();
    }
    $addtable = $cInfos['addtable'];
    $addRow = $dsql->GetOne("SELECT arc.*,ar.membername as rankname FROM `$addtable` arc LEFT JOIN `#@__arcrank` ar on ar.`rank`=arc.arcrank WHERE arc.aid='$aid'");
    $channelid = $cInfos['channel'];
    $tags = GetTags($aid);
    include DedeInclude('templets/archives_sg_edit.htm');
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
    if (!UserLogin::TestPurview('a_Edit')) {
        if (UserLogin::TestPurview('a_AccEdit')) {
            UserLogin::CheckCatalog($typeid, Lang('content_error_channelid_check_failed',array('typeid'=>$typeid)));
        } else {
            CheckArcAdmin($id, $cUserLogin->getUserID());
        }
    }
    //对保存的内容进行处理
    if (empty($flags)) $flag = '';
    else $flag = join(',', $flags);
    $title = cn_substrR($title, $cfg_title_maxlen);
    $serviterm = empty($serviterm) ? "" : $serviterm;
    if (!UserLogin::TestPurview('a_Check,a_AccCheck,a_MyCheck')) $arcrank = -1;
    $adminid = $cUserLogin->getUserID();
    //处理上传的缩略图
    if (empty($ddisremote)) $ddisremote = 0;
    $litpic = GetDDImage('none', $picname, $ddisremote);
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
                    ${$vs[0]} = GetFieldValueA(${$vs[0]}, $vs[1], $id);
                }
                $inadd_f .= ",`{$vs[0]}` = '".${$vs[0]}."'";
            }
        }
    }
    //处理图片文档的自定义属性
    if ($litpic != '' && !preg_match("#p#", $flag)) {
        $flag = ($flag == '' ? 'p' : $flag.',p');
    }
    $cts = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$channelid'");
    $addtable = trim($cts['addtable']);
    if ($addtable != '') {
        $iquery = "UPDATE `$addtable` SET typeid='$typeid',arcrank='$arcrank',title='$title',flag='$flag',litpic='$litpic'{$inadd_f} WHERE aid='$id'";
        if (!$dsql->ExecuteNoneQuery($iquery)) {
            ShowMsg(Lang('content_err_update_addon',array('addtable'=>$addtable)), "javascript:;");
            exit();
        }
    }
    //生成网页
    UpIndexKey($id, $arcrank, $typeid, $sortrank, '');
    $artUrl = MakeArt($id, TRUE, TRUE);
    if ($artUrl == '') $artUrl = $cfg_phpurl."/view.php?aid=$id";
    UserLogin::ClearMyAddon($id, $title);
    //返回成功信息
    $msg = Lang('more_actions')."：<a href='archives_sg_add.php?cid=$typeid' class='btn btn-success btn-sm'>".Lang('content_continue_publish')."</a><a href='archives_do.php?aid=".$id."&dopost=editArchives' class='btn btn-success btn-sm'>".Lang('content_view')."</a><a href='$artUrl' target='_blank' class='btn btn-success btn-sm'>".Lang('content_edit')."</a><a href='catalog_do.php?cid=$typeid&channelid={$channelid}&dopost=listArchives' class='btn btn-success btn-sm'>".Lang('content_published_main')."</a><a href='catalog_main.php' class='btn btn-success btn-sm'>".Lang('catalog_main')."</a>";
    $wintitle = Lang("content_success_edit");
    $wecome_info = Lang('content_main')."::".Lang('content_edit');
    DedeWin::Instance()->AddTitle(Lang("content_success_edit")."：")->AddMsgItem($msg)->GetWindow("hand", "&nbsp;", false)->Display();
}
?>