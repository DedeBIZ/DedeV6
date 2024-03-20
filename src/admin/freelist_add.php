<?php
/**
 * 添加自由列表
 *
 * @version        $id:freelist_add.php 8:48 2010年7月13日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('c_FreeList');
if (empty($dopost)) {
    require_once DEDEINC.'/typelink/typelink.class.php';
    include DedeInclude('templets/freelist_add.htm');
    exit();
} else if ($dopost == 'save') {
    if (!isset($types)) $types = '';
    if (!isset($nodefault)) $nodefault = '0';
    $atts = " pagesize='$pagesize' col='$col' titlelen='$titlelen' orderby='$orderby' orderway='$order' ";
    $ntype = '';
    $edtime = time();
    if (trim($title) == '') {
        ShowMsg("请输入自由列表标题", "-1");
        exit();
    }
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
    $inquery = "INSERT INTO `#@__freelist` (`title`,`namerule`,`listdir`,`defaultpage`,`nodefault`,`templet`,`edtime`, `maxpage`,`click`,`listtag`,`keywords`,`description`) VALUES ('$title','$namerule','$listdir','$defaultpage','$nodefault','$templet','$edtime','$maxpage','0','$listTag','$keywords','$description');";
    $dsql->ExecuteNoneQuery($inquery);
    ShowMsg("成功添加一个自由列表", "freelist_main.php");
    exit();
}
?>