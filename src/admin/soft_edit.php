<?php
/**
 * 软件编辑
 *
 * @version        $Id: soft_edit.php 1 16:09 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
use DedeBIZ\Template\DedeTagParse;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('a_Edit,a_AccEdit,a_MyEdit');
require_once(DEDEADMIN."/inc/inc_archives_functions.php");
if (empty($dopost)) $dopost = '';
if ($dopost != 'save') {
    require_once(DEDEADMIN."/inc/inc_catalog_options.php");
    UserLogin::ClearMyAddon();
    $aid = preg_replace("#[^0-9]#", '', $aid);
    $channelid = "3";
    //读取归档信息
    $arcQuery = "SELECT `#@__channeltype`.typename as channelname,`#@__arcrank`.membername as rankname,`#@__archives`.* FROM `#@__archives` LEFT JOIN `#@__channeltype` ON `#@__channeltype`.id=`#@__archives`.channel LEFT JOIN `#@__arcrank` ON `#@__arcrank`.`rank`=`#@__archives`.arcrank WHERE `#@__archives`.id='$aid'";
    $dsql->SetQuery($arcQuery);
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
    $addQuery = "SELECT * FROM `$addtable` WHERE aid='$aid'";
    $addRow = $dsql->GetOne($addQuery);
    $newRowStart = 1;
    $nForm = '';
    $daccess = $addRow['daccess'];
    $needmoney = $addRow['needmoney'];
    if ($addRow['softlinks'] != '') {
        $dtp = new DedeTagParse();
        $dtp->LoadSource($addRow['softlinks']);
        if (is_array($dtp->CTags)) {
            foreach ($dtp->CTags as $ctag) {
                if ($ctag->GetName() == 'link') {
                    $islocal = $ctag->GetAtt('islocal');
                    if ($islocal != 1) $needmsg = "<label><input type='checkbox' name='del{$newRowStart}' value='1'> ".Lang('delete')."</label>";
                    else $needmsg = '<button name="sel1" class="btn btn-success btn-sm" type="button" id="sel1" onClick="SelectSoft(\'form1.softurl'.$newRowStart.'\')">选取</button>';
                    $nForm .= "<div style='line-height:36px'>软件地址{$newRowStart}：<input type='text' name='softurl{$newRowStart}' value='".trim($ctag->GetInnerText())."' style='width:260px'> 服务器名称：<input type='text' name='servermsg{$newRowStart}' value='".$ctag->GetAtt("text")."' style='width:160px'>
                    <input type='hidden' name='islocal{$newRowStart}' value='{$islocal}'>
                    $needmsg
                    </div>\r\n";
                    $newRowStart++;
                }
            }
        }
        $dtp->Clear();
    }
    $channelid = $arcRow['channel'];
    $tags = GetTags($aid);
    $arcRow = XSSClean($arcRow);
    $addRow = XSSClean($addRow);
    include DedeInclude("templets/soft_edit.htm");
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
    $senddate = time();
    $sortrank = AddDay($pubdate, $sortup);
    if ($ishtml == 0) {
        $ismake = -1;
    } else {
        $ismake = 0;
    }
    $title = cn_substrR($title, $cfg_title_maxlen);
    $shorttitle = cn_substrR($shorttitle, 36);
    $color =  cn_substrR($color, 7);
    $writer =  cn_substrR($writer, 20);
    $source = cn_substrR($source, 30);
    $description = cn_substrR($description, $cfg_auot_description);
    $keywords = cn_substrR($keywords, 60);
    $filename = trim(cn_substrR($filename, 40));
    $serviterm = empty($serviterm) ? "" : $serviterm;
    if (!UserLogin::TestPurview('a_Check,a_AccCheck,a_MyCheck')) {
        $arcrank = -1;
    }
    $adminid = $cUserLogin->getUserID();
    //处理上传的缩略图
    if (empty($ddisremote)) {
        $ddisremote = 0;
    }
    $litpic = GetDDImage('litpic', $picname, $ddisremote);
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
    if ($litpic != '' && !preg_match('#p#', $flag)) {
        $flag = ($flag == '' ? 'p' : $flag.',p');
    }
    if ($redirecturl != '' && !preg_match('#j#', $flag)) {
        $flag = ($flag == '' ? 'j' : $flag.',j');
    }
    //跳转网址的文档强制为动态
    if (preg_match('#j#', $flag)) $ismake = -1;
    //修改主档案表
    $inQuery = "UPDATE `#@__archives` SET typeid='$typeid',typeid2='$typeid2',sortrank='$sortrank',flag='$flag',click='$click',ismake='$ismake',arcrank='$arcrank',`money`='$money',title='$title',color='$color',source='$source',writer='$writer',litpic='$litpic',pubdate='$pubdate',notpost='$notpost',description='$description',keywords='$keywords',shorttitle='$shorttitle',filename='$filename',dutyadmin='$adminid',weight='$weight' WHERE id='$id';";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        ShowMsg(Lang('content_err_update_archive'), "-1");
        exit();
    }
    //软件链接列表
    $urls = '';
    for ($i = 1; $i <= 30; $i++) {
        if (!empty(${'softurl'.$i})) {
            $islocal = empty(${'islocal'.$i}) ? '' : 1;
            $isneed = empty(${'del'.$i}) ? true : false;
            $servermsg = str_replace("'", '', stripslashes(${'servermsg'.$i}));
            $softurl = stripslashes(${'softurl'.$i});
            if ($servermsg == '') {
                $servermsg = Lang('download_url').$i;
            }
            if ($softurl != 'http://') {
                if ($islocal == 1) $urls .= "{dede:link islocal='$islocal' text='{$servermsg}'} $softurl {/dede:link}\r\n";
                else if ($isneed) $urls .= "{dede:link text='$servermsg'} $softurl {/dede:link}\r\n";
                else continue;
            }
        }
    }
    $urls = addslashes($urls);
    //更新附加表
    $cts = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$channelid'");
    $addtable = trim($cts['addtable']);
    if ($addtable != '') {
        $useip = GetIP();
        $inQuery = "UPDATE `$addtable` SET typeid='$typeid',filetype='$filetype',language='$language',softtype='$softtype',accredit='$accredit',os='$os',softrank='$softrank',officialUrl='$officialUrl',officialDemo='$officialDemo',softsize='$softsize',softlinks='$urls',redirecturl='$redirecturl',userip='$useip',daccess='$daccess',needmoney='$needmoney',introduce='$body' {$inadd_f} WHERE aid='$id';";
        if (!$dsql->ExecuteNoneQuery($inQuery)) {
            ShowMsg(Lang('content_err_update_addon',array('addtable'=>'addonsoft')), "-1");
            exit();
        }
    }
    //生成网页
    UpIndexKey($id, $arcrank, $typeid, $sortrank, $tags);
    $arcUrl = MakeArt($id, TRUE, TRUE);
    if ($arcUrl == "") {
        $arcUrl = $cfg_phpurl."/view.php?aid=$id";
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
    $msg = Lang('more_actions')."：<a href='soft_add.php?cid=$typeid' class='btn btn-success btn-sm'>".Lang('content_continue_publish')."</a><a href='archives_do.php?aid=".$id."&dopost=editArchives' class='btn btn-success btn-sm'>修改软件</a><a href='$arcUrl' target='_blank' class='btn btn-success btn-sm'>".Lang('content_view')."</a><a href='catalog_do.php?cid=$typeid&dopost=listArchives' class='btn btn-success btn-sm'>".Lang('content_published_main')."</a><a href='catalog_main.php' class='btn btn-success btn-sm'>".Lang('catalog_main')."</a>";
    $wintitle = Lang("content_success_edit");
    $wecome_info = Lang('content_main')."::".Lang('content_edit');
    DedeWin::Instance()->AddTitle(Lang("content_success_edit")."：")->AddMsgItem($msg)->GetWindow("hand", "&nbsp;", FALSE)->Display();
}
?>