<?php
/**
 * 智能标记向导预览
 *
 * @version        $id:mytag_tag_guide_ok.php 15:39 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert("系统已启用安全模式，无法使用当前功能",ALERT_DANGER));
}
CheckPurview('temp_Other');
//根据生成条件标记
$attlist = "";
$attlist .= " row='".$row."'";
$attlist .= " titlelen='".$titlelen."'";
if ($orderby != 'senddate') $attlist .= " orderby='".$orderby."'";
if ($order != 'desc') $attlist .= " order='".$order."'";
if ($typeid > 0) $attlist .= " typeid='".$typeid."'";
if (isset($arcid)) $attlist .= " idlist='".$arcid."'";
if ($channel > 0) $attlist .= " channelid='".$channel."'";
if ($att > 0) $attlist .= " att='".$att."'";
if ($col > 1) $attlist .= " col='".$col."'";
if ($subday > 0) $attlist .= " subday='".$subday."'";
if (!empty($types)) {
    $attlist .= " type='";
    foreach ($types as $v) {
        $attlist .= $v.'.';
    }
    $attlist .= "'";
}
$innertext = stripslashes($innertext);
if ($keyword != "") {
    $attlist .= " keyword='$keyword'";
}
$fulltag = "{dede:arclist$attlist}
$innertext
{/dede:arclist}\r\n";
if ($dopost == 'savetag') {
    $fulltag = addslashes($fulltag);
    $tagname = "auto";
    $inQuery = "INSERT INTO `#@__mytag` (typeid,tagname,timeset,starttime,endtime,normbody,expbody) VALUES ('0','$tagname','0','0','0','$fulltag','');";
    $dsql->ExecuteNoneQuery($inQuery);
    $id = $dsql->GetLastID();
    $dsql->ExecuteNoneQuery("UPDATE `#@__mytag` SET tagname='{$tagname}_{$id}' WHERE aid='$id'");
    $fulltag = "{dede:mytag name='{$tagname}_{$id}' ismake='yes'/}";
}
include DedeInclude('templets/mytag_tag_guide_ok.htm');
?>