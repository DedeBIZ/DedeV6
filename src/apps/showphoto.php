<?php
/**
 * 图片操作
 *
 * @version        $id:showphoto.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../system/common.inc.php");
require_once(DEDEINC."/channelunit.class.php");
if (isset($arcID)) $aid = $arcID;
$arcID = $aid = (isset($aid) && is_numeric($aid)) ? $aid : 0;
if ($aid == 0) die("dedebiz");
//读取文档信息
$arctitle = '';
$arcurl = '';
$topid = 0;
$arcRow = $dsql->GetOne("SELECT arc.title,arc.senddate,arc.arcrank,arc.ismake,arc.money,arc.typeid,tp.topid,tp.typedir,tp.namerule,tp.moresite,tp.siteurl,tp.sitepath FROM `#@__archives` arc LEFT JOIN `#@__arctype` tp ON tp.id=arc.typeid WHERE arc.id='$aid'");
if (is_array($arcRow)) {
    $arctitle = $arcRow['title'];
    $topid = $arcRow['topid'];
    $arcurl = @GetFileUrl(
        $aid,
        $arcRow['typeid'],
        $arcRow['senddate'],
        $arctitle,
        $arcRow['ismake'],
        $arcRow['arcrank'],
        $arcRow['namerule'],
        $arcRow['typedir'],
        $arcRow['money'],
        $arcRow['filename'],
        $arcRow['moresite'],
        $arcRow['siteurl'],
        $arcRow['sitepath']
    );
} else {
    ShowMsg('无法浏览未知文档', '-1');
    exit();
}
if (empty($mx)) $mx = $cfg_album_width;
$pageGuide = '';
//获取上下幅图片链接
$row = $dsql->GetOne("SELECT imgurls FROM `#@__addonimages` WHERE aid='{$aid}'");
$i = 0;
$nextSrc = '';
$preSrc = '';
$dtp = new DedeTagParse();
$dtp->LoadSource($row['imgurls']);
foreach ($dtp->CTags as $ctag) {
    if ($ctag->GetName() == "img") {
        if ($i == ($npos - 1)) $preSrc = trim($ctag->GetInnerText());
        if ($i == ($npos + 1)) $nextSrc = trim($ctag->GetInnerText());
        $i++;
    }
}
unset($dtp);
if ($cfg_multi_site == 'Y') {
    if (!preg_match("/^(http|https):/i", $preSrc) && !empty($preSrc)) $preSrc = $cfg_basehost.$preSrc;
    if (!preg_match("/^(http|https):/i", $nextSrc) && !empty($nextSrc)) $nextSrc = $cfg_basehost.$nextSrc;
}
if ($preSrc != '') {
    $pageGuide .= "<a href='showphoto.php?aid={$aid}&src=".urlencode($preSrc)."&npos=".($npos - 1)."'>上一幅图片</a> ";
} else {
    $pageGuide .= "开始";
}
$nextlink = 'javascript:;';
if ($nextSrc != '') {
    $nextlink = "showphoto.php?aid={$aid}&src=".urlencode($nextSrc)."&npos=".($npos + 1);
    if ($pageGuide != "") $pageGuide .= " | ";
    $pageGuide .= "<a href='showphoto.php?aid={$aid}&src=".urlencode($nextSrc)."&npos=".($npos + 1)."'>下一幅图片</a> ";
} else {
    $pageGuide .= "结束";
}
require_once(DEDETEMPLATE.'/apps/showphoto.htm');
exit();
?>