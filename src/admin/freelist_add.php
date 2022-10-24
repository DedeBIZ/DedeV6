<?php
/**
 * 增加自由列表
 *
 * @version        $Id: freelist_add.php 1 8:48 2010年7月13日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('c_FreeList');
if (empty($dopost)) {
    include DedeInclude('templets/freelist_add.htm');
    exit();
} else if ($dopost == 'save') {
    if (!isset($types)) $types = '';
    if (!isset($nodefault)) $nodefault = '0';
    $atts = " pagesize='$pagesize' col='$col' titlelen='$titlelen' orderby='$orderby' orderway='$order' ";
    $ntype = '';
    $edtime = time();
    if (empty($channel)) {
        showmsg(Lang('freelist_err_channel_isempty'), '-1');
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
    $inquery = "INSERT INTO `#@__freelist` (`title`,`namerule`,`listdir`,`defaultpage`,`nodefault`,`templet`,`edtime`, `maxpage`,`click`,`listtag`,`keywords`,`description`) VALUES ('$title','$namerule','$listdir','$defaultpage','$nodefault','$templet','$edtime', '$maxpage','0','$listTag','$keywords','$description');
    ";
    $dsql->ExecuteNoneQuery($inquery);
    ShowMsg(Lang("freelist_add_success"), "freelist_main.php");
    exit();
}
?>