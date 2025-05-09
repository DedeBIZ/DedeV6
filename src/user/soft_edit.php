<?php
/**
 * 修改软件模型
 * 
 * @version        $id:soft_edit.php 2 14:16 2010-11-11 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/dedetag.class.php");
require_once(DEDEINC."/customfields.func.php");
require_once(DEDEMEMBER."/inc/inc_catalog_options.php");
require_once(DEDEMEMBER."/inc/inc_archives_functions.php");
$channelid = isset($channelid) && is_numeric($channelid) ? $channelid : 3;
$aid = isset($aid) && is_numeric($aid) ? $aid : 0;
$menutype = 'content';
if (empty($dopost)) {
    //读取归档信息
    $arcQuery = "SELECT `#@__channeltype`.typename as channelname,`#@__arcrank`.membername as rankname,`#@__channeltype`.arcsta,`#@__archives`.* FROM `#@__archives` LEFT JOIN `#@__channeltype` ON `#@__channeltype`.id=`#@__archives`.channel LEFT JOIN `#@__arcrank` ON `#@__arcrank`.`rank`=`#@__archives`.arcrank WHERE `#@__archives`.id='$aid'";
    $dsql->SetQuery($arcQuery);
    $row = $dsql->GetOne($arcQuery);
    if (!is_array($row)) {
        ShowMsg("读取文档信息出错", "index.php");
        exit();
    } else if ($row['arcrank'] >= 0) {
        $dtime = time();
        $maxtime = $cfg_mb_editday * 24 * 3600;
        if ($dtime - $row['senddate'] > $maxtime) {
            ShowMsg("这篇文档已锁定，暂时无法修改", "-1");
            exit();
        }
    }
    $query = "SELECT * FROM `#@__channeltype` WHERE id='".$row['channel']."'";
    $cInfos = $dsql->GetOne($query);
    if (!is_array($cInfos)) {
        ShowMsg("读取栏目信息出错", "index.php");
        exit();
    }
    $addtable = $cInfos['addtable'];
    $addQuery = "SELECT * FROM `$addtable` WHERE aid='$aid'";
    $addRow = $dsql->GetOne($addQuery);
    $newRowStart = 1;
    $nForm = '';
    if (isset($addRow['softlinks']) && $addRow['softlinks'] != '') {
        $dtp = new DedeTagParse();
        $dtp->LoadSource($addRow['softlinks']);
        if (is_array($dtp->CTags)) {
            foreach ($dtp->CTags as $ctag) {
                if ($ctag->GetName() == 'link') {
                    $nForm .= "<div class='form-group'><label>下载地址".$newRowStart."：</label><div class='input-group mb-3'><input type='text' name='softurl".$newRowStart."' value='".trim($ctag->GetInnerText())."' class='form-control'><div class='input-group-append'><span class='btn btn-success btn-sm btn-send' onclick=\"SelectSoft('addcontent.softurl".$newRowStart."')\">选择</span></div></div>
                    <label>下载名称：</label><input type='text' name='servermsg".$newRowStart."' value='".$ctag->GetAtt("text")."' class='form-control'></div>";
                    $newRowStart++;
                }
            }
        }
        $dtp->Clear();
    }
    $row = XSSClean($row);
    $addRow = XSSClean($addRow);
    $channelid = $row['channel'];
    $tags = GetTags($aid);
    include(DEDEMEMBER."/templets/soft_edit.htm");
    exit();
} else if ($dopost == 'save') {
    $description = '';
    include(DEDEMEMBER.'/inc/archives_check_edit.php');
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
            }
        }
    }
    $body = AnalyseHtmlBody($body, $description);
    $body = HtmlReplace($body, -1);
    //处理图片文档的自定义属性
    if ($litpic != '') $flag = 'p';
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
                //网页文本特殊处理
                if ($vs[1] == 'htmltext' || $vs[1] == 'textdata') {
                    ${$vs[0]} = AnalyseHtmlBody(${$vs[0]}, $description, $litpic, $keywords, $vs[1]);
                } else {
                    if (!isset(${$vs[0]})) {
                        ${$vs[0]} = '';
                    }
                    ${$vs[0]} = GetFieldValueA(${$vs[0]}, $vs[1], $arcID);
                }
                $inadd_f .= ",`{$vs[0]}` = '".${$vs[0]}."'";
            }
        }
        //这里对前台提交的附加数据进行一次校验
        $fontiterm = PrintAutoFieldsAdd(stripslashes($cInfos['fieldset']), 'autofield', FALSE);
        if ($fontiterm != $inadd_f) {
            ShowMsg("提交的信息有错误，请修改重新提交", "-1");
            exit();
        }
    }
    //修改主文档表
    $litpic = isset($litpic)? HtmlReplace($litpic, 1) : '';
    $upQuery = "UPDATE `#@__archives` SET ismake='$ismake',arcrank='$arcrank',typeid='$typeid',title='$title',litpic='$litpic',description='$description',keywords='$keywords',flag='$flag',source='$source' WHERE id='$aid' AND mid='$mid'; ";
    if (!$dsql->ExecuteNoneQuery($upQuery)) {
        ShowMsg("数据保存到数据库文档主表出错，请联系管理员", "-1");
        exit();
    }
    //软件链接列表
    $urls = '';
    $nums = 2;
    for ($i = 1; $i <= 9; $i++) {
        if (!empty(${'softurl'.$i})) {
            $servermsg = str_replace("'", '', stripslashes(${'servermsg'.$i}));
            $servermsg = str_replace(array("{dede:", "{/dede:", "}"), "#", $servermsg);
            $softurl = stripslashes(${'softurl'.$i});
            $softurl = str_replace(array("{dede:", "{/dede:", "}"), "#", $softurl);
            if ($servermsg == '') {
                $servermsg = '下载地址'.$i;
            }
            if ($softurl != '' && $softurl != 'http://') {
                $urls .= "{dede:link text='$servermsg'} $softurl {/dede:link}\r\n";
            }
            $nums++;
        }
    }
    $urls = addslashes($urls);
    //更新附加表
    $needmoney = @intval($needmoney);
    if ($needmoney > 100) $needmoney = 100;
    $cts = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$channelid' ");
    $addtable = trim($cts['addtable']);
    if ($addtable != '') {
        $inQuery = "UPDATE `$addtable` SET typeid='$typeid',filetype='$filetype',language='$language',softtype='$softtype',accredit='$accredit',os='$os',softrank='$softrank',officialUrl='$officialUrl',officialDemo='$officialDemo',softsize='$softsize',softlinks='$urls',userip='$userip',needmoney='$needmoney',introduce='$body' {$inadd_f} WHERE aid='$aid'; ";
        if (!$dsql->ExecuteNoneQuery($inQuery)) {
            ShowMsg("数据保存到数据库附加表出错，请联系管理员", "-1");
            exit();
        }
    }
    UpIndexKey($aid, $arcrank, $typeid, $sortrank, $tags);
    $artUrl = MakeArt($aid, TRUE);
    if ($artUrl == '') {
        $artUrl = $cfg_phpurl."/view.php?aid=$aid";
    }
    //返回成功信息
    $msg = "<a href='$artUrl' target='_blank' class='btn btn-success btn-sm'>浏览文档</a><a href='soft_add.php?cid=$typeid' class='btn btn-success btn-sm'>发布文档</a><a href='soft_edit.php?channelid=$channelid&aid=".$aid."' class='btn btn-success btn-sm'>修改文档</a><a href='content_list.php?channelid=$channelid' class='btn btn-success btn-sm'>返回文档列表</a>";
    $wintitle = "成功修改软件文档";
    $win = new WebWindow();
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", false);
    $win->Display(DEDEMEMBER."/templets/win_templet.htm");
}
?>