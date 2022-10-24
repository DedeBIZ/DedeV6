<?php
/**
 * 文档编辑
 *
 * @version        $Id: archives_edit.php 1 8:26 2010年7月12日Z tianya $
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
    $arcQuery = "SELECT ch.typename as channelname,ar.membername as rankname,arc.* FROM `#@__archives` arc LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel LEFT JOIN `#@__arcrank` ar ON ar.`rank`=arc.arcrank WHERE arc.id='$aid'";
    $arcRow = $dsql->GetOne($arcQuery);
    if (!is_array($arcRow)) {
        ShowMsg(Lang("content_err_archive"), "-1");
        exit();
    }
    $query = "SELECT * FROM `#@__channeltype` WHERE id='".$arcRow['channel']."'";
    $cInfos = $dsql->GetOne($query);
    if (!is_array($cInfos)) {
        ShowMsg(Lang("content_err_channel"), "javascript:;");
        exit();
    }
    $addtable = $cInfos['addtable'];
    $addRow = $dsql->GetOne("SELECT * FROM `$addtable` WHERE aid='$aid'");
    $channelid = $arcRow['channel'];
    $tags = GetTags($aid);
    include DedeInclude("templets/archives_edit.htm");
    exit();
}
else if ($dopost == 'save') {
    helper('image');
    $flag = isset($flags) ? join(',', $flags) : '';
    $notpost = isset($notpost) && $notpost == 1 ? 1 : 0;
    if (empty($typeid2)) $typeid2 = 0;
    if (!isset($autokey)) $autokey = 0;
    if (!isset($remote)) $remote = 0;
    if (!isset($dellink)) $dellink = 0;
    if (!isset($autolitpic)) $autolitpic = 0;
    if (!isset($writer)) $writer = '';
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
    $pubdate = GetMkTime($pubdate);
    $sortrank = AddDay($pubdate, $sortup);
    $ismake = $ishtml == 0 ? -1 : 0;
    $title = cn_substrR($title, $cfg_title_maxlen);
    $shorttitle = cn_substrR($shorttitle, 36);
    $color =  cn_substrR($color, 7);
    $writer =  cn_substrR($writer, 20);
    $source = cn_substrR($source, 30);
    $description = cn_substrR($description, $cfg_auot_description);
    $keywords = trim(cn_substrR($keywords, 60));
    $filename = trim(cn_substrR($filename, 40));
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
    if ($redirecturl != '' && !preg_match("#j#", $flag)) {
        $flag = ($flag == '' ? 'j' : $flag.',j');
    }
    //跳转网址的文档强制为动态
    if (preg_match("#j#", $flag)) $ismake = -1;
    //更新数据库的SQL语句
    $inQuery = "UPDATE `#@__archives` SET
    typeid='$typeid',
    typeid2='$typeid2',
    sortrank='$sortrank',
    flag='$flag',
    notpost='$notpost',
    click='$click',
    ismake='$ismake',
    arcrank='$arcrank',
    money='$money',
    title='$title',
    color='$color',
    writer='$writer',
    source='$source',
    litpic='$litpic',
    pubdate='$pubdate',
   DESCription='$description',
    keywords='$keywords',
    shorttitle='$shorttitle',
    filename='$filename',
    dutyadmin='$adminid',
    weight='$weight'
   WHERE id='$id'; ";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        ShowMsg(Lang('content_err_update_archive'), "-1");
        exit();
    }
    $cts = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$channelid'");
    $addtable = trim($cts['addtable']);
    if ($addtable != '') {
        $useip = GetIP();
        $iquery = "UPDATE `$addtable` SET typeid='$typeid'{$inadd_f},redirecturl='$redirecturl',userip='$useip' WHERE aid='$id'";
        if (!$dsql->ExecuteNoneQuery($iquery)) {
            ShowMsg(Lang('content_err_update_addon',array('addtable'=>$addtable)), "javascript:;");
            exit();
        }
    }
    //生成网页
    UpIndexKey($id, $arcrank, $typeid, $sortrank, $tags);
    $artUrl = MakeArt($id, TRUE, TRUE);
    if ($artUrl == '') {
        $artUrl = $cfg_phpurl."/view.php?aid=$id";
    }
    UserLogin::ClearMyAddon($id, $title);
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
    $msg = Lang('more_actions')."：<a href='archives_add.php?cid=$typeid' class='btn btn-success btn-sm'>".Lang('content_continue_publish')."</a><a href='archives_do.php?aid=".$id."&dopost=editArchives' class='btn btn-success btn-sm'>".Lang('content_edit')."</a><a href='$artUrl' target='_blank' class='btn btn-success btn-sm'>".Lang('content_view')."</a><a href='catalog_do.php?cid=$typeid&dopost=listArchives' class='btn btn-success btn-sm'>".Lang('content_published_main')."</a>$backurl";
    $wintitle = Lang("content_success_edit");
    $wecome_info = Lang('content_main')."::".Lang('content_edit');
    DedeWin::Instance()->AddTitle(Lang("content_success_edit")."：")->AddMsgItem($msg)->GetWindow("hand", "&nbsp;", false)->Display();
}
?>