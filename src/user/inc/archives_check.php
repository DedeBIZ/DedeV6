<?php
if (!defined('DEDEMEMBER')) exit('dedebiz');
/**
 * 文档验证
 * 
 * @version        $id:archives_check.php 13:52 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
include_once(DEDEINC.'/image.func.php');
include_once(DEDEINC.'/libraries/oxwindow.class.php');
$svali = GetCkVdValue();
if (strtolower($vdcode) != $svali || $svali == '') {
    ResetVdValue();
    ShowMsg('验证码不正确', '-1');
    exit();
}
//校验CSRF
CheckCSRF();
$flag = '';
$autokey = $remote = $dellink = $autolitpic = 0;
$userip = GetIP();
if ($typeid == 0) {
    ShowMsg('您还没选择栏目，请选择发布文档栏目', '-1');
    exit();
}
$query = "SELECT tp.ispart,tp.channeltype,tp.issend,ch.issend as cissend,ch.sendrank,ch.arcsta,ch.addtable,ch.fieldset,ch.usertype FROM `#@__arctype` tp LEFT JOIN `#@__channeltype` ch on ch.id=tp.channeltype WHERE tp.id='$typeid' ";
$cInfos = $dsql->GetOne($query);
//检测栏目是否有投稿权限
if ($cInfos['issend'] != 1 || $cInfos['ispart'] != 0  || $cInfos['channeltype'] != $channelid || $cInfos['cissend'] != 1) {
    ShowMsg("您所选择的栏目不支持投稿", "-1");
    exit();
}
//检查栏目设定的投稿许可权限
if ($cInfos['sendrank'] > $cfg_ml->M_Rank) {
    $row = $dsql->GetOne("SELECT membername FROM `#@__arcrank` WHERE `rank`='".$cInfos['sendrank']."' ");
    ShowMsg("对不起，需要<span class='text-primary'>".$row['membername']."</span>才能在这个栏目发布文档", "-1", "0", 5000);
    exit();
}
if ($cInfos['usertype'] != '' && $cInfos['usertype'] != $cfg_ml->M_MbType) {
    ShowMsg("对不起，需要<span class='text-primary'>".$cInfos['usertype']."</span>才能在这个栏目发布文档", "-1", "0", 5000);
    exit();
}
//文档的默认状态
if ($cInfos['arcsta'] == 0) {
    $ismake = 0;
    $arcrank = 0;
} else if ($cInfos['arcsta'] == 1) {
    $ismake = -1;
    $arcrank = 0;
} else {
    $ismake = 0;
    $arcrank = -1;
}
//对保存的文档进行处理
$money = 0;
$flag = $shorttitle = $color = $source = '';
$sortrank = $senddate = $pubdate = time();
$title = cn_substrR(HtmlReplace($title, 1), $cfg_title_maxlen);
$writer =  cn_substrR(HtmlReplace($writer, 1), 20);
if (empty($description)) $description = '';
$description = cn_substrR(HtmlReplace($description, 1), 250);
$keywords = cn_substrR(HtmlReplace($tags, 1), 30);
$mid = $cfg_ml->M_ID;
//检测文档是否重复
if ($cfg_mb_cktitle == 'Y') {
    $row = $dsql->GetOne("SELECT * FROM `#@__archives` WHERE title LIKE '$title' ");
    if (is_array($row)) {
        ShowMsg("对不起，请不要发布重复文档", "-1", "0", 5000);
        exit();
    }
}