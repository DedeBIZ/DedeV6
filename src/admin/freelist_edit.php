<?php
/**
 * 编辑自由列表
 *
 * @version        $Id: freelist_edit.php 1 8:48 2010年7月13日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (empty($dopost)) {
    require_once DEDEINC.'/typelink/typelink.class.php';
    require_once DEDEINC.'/dedetag.class.php';
    $aid = isset($aid) && is_numeric($aid) ? $aid : 0;
    $row = $dsql->GetOne("Select * From `#@__freelist` where aid='$aid' ");
    $dtp = new DedeTagParse();
    $dtp->SetNameSpace("dede", "{", "}");
    $dtp->LoadSource("--".$row['listtag']."--");
    $ctag = $dtp->GetTag('list');
    include DedeInclude('templets/freelist_edit.htm');
    exit();
} else if ($dopost == 'save') {
    if (!isset($types)) $types = '';
    if (!isset($nodefault)) $nodefault = '0';
    $atts = " pagesize='$pagesize' col='$col' titlelen='$titlelen' orderby='$orderby' orderway='$order' \r\n";
    $ntype = '';
    $edtime = time();
    if (is_array($types)) {
        foreach ($types as $v) $ntype .= $v.' ';
    }
    if ($ntype != '') $atts .= " type='".trim($ntype)."' ";
    if (!empty($typeid)) $atts .= " typeid='$typeid' ";
    if (!empty($channel)) $atts .= " channel='$channel' ";
    if (!empty($subday)) $atts .= " subday='$subday' ";
    if (!empty($keywordarc)) $atts .= " keyword='$keywordarc' ";
    if (!empty($att)) $atts .= " att='$att' ";
    $innertext = trim($innertext);
    if (!empty($innertext)) $innertext = stripslashes($innertext);
    $listTag = "{dede:list $atts}$innertext{/dede:list}";
    $listTag = addslashes($listTag);
    $inquery = "
        UPDATE `#@__freelist` set
        title='$title', namerule='$namerule',
        listdir='$listdir', defaultpage='$defaultpage',
        nodefault='$nodefault', templet='$templet',
        edtime='$edtime', `maxpage`='$maxpage', listtag='$listTag', keywords='$keywords',
        description='$description' WHERE aid='$aid';
    ";
    $dsql->ExecuteNoneQuery($inquery);
    ShowMsg("成功修改一个自由列表", "freelist_main.php");
    exit();
}