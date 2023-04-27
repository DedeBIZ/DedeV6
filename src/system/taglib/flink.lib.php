<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 友情链接标签
 *
 * @version        $id:flink.lib.php 9:29 2010年7月6日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
helper('cache');
function lib_flink(&$ctag, &$refObj)
{
    global $dsql, $cfg_soft_lang;
    $attlist = "type|textall,row|30,titlelen|30,linktype|1,typeid|0";
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $totalrow = $row;
    $revalue = '';
    if (isset($GLOBALS['envs']['flinkid'])) {
        $typeid = $GLOBALS['envs']['flinkid'];
    }
    $wsql = " where ischeck >= '$linktype' ";
    if ($typeid == 0) {
        $wsql .= '';
    } else {
        $wsql .= "And typeid = '$typeid'";
    }
    if ($type == 'image') {
        $wsql .= " And logo<>'' ";
    } else if ($type == 'text') {
        $wsql .= " And logo='' ";
    }
    $equery = "SELECT * FROM `#@__flink` $wsql order by sortrank asc limit 0,$totalrow";
    if (trim($ctag->GetInnerText()) == '') $innertext = "[field:link/] ";
    else $innertext = $ctag->GetInnerText();
    $dsql->SetQuery($equery);
    $dsql->Execute('fl');
    while ($dbrow = $dsql->GetObject('fl')) {
        if ($type == 'text' || $type == 'textall') {
            $link = "<a href='".$dbrow->url."' target='_blank'>".cn_substr($dbrow->webname, $titlelen)."</a> ";
        } else if ($type == 'image') {
            $link = "<a href='".$dbrow->url."' target='_blank'><img src='".$dbrow->logo."' class='thumbnail-sm'></a> ";
        } else {
            if ($dbrow->logo == '') {
                $link = "<a href='".$dbrow->url."' target='_blank'>".cn_substr($dbrow->webname, $titlelen)."</a> ";
            } else {
                $link = "<a href='".$dbrow->url."' target='_blank'><img src='".$dbrow->logo."' class='thumbnail-sm'></a> ";
            }
        }
        $rbtext = preg_replace("/\[field:url([\/\s]{0,})\]/isU", $dbrow->url, $innertext);
        $rbtext = preg_replace("/\[field:webname([\/\s]{0,})\]/isU", $dbrow->webname, $rbtext);
        $rbtext = preg_replace("/\[field:logo([\/\s]{0,})\]/isU", $dbrow->logo, $rbtext);
        $rbtext = preg_replace("/\[field:link([\/\s]{0,})\]/isU", $link, $rbtext);
        $revalue .= $rbtext;
    }
    return $revalue;
}
?>