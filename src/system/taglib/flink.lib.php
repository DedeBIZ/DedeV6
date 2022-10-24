<?php
if (!defined('DEDEINC')) exit('dedebiz');
/**
 * 友情链接
 *
 * @version        $Id: flink.lib.php 1 9:29 2010年7月6日Z tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
helper('cache');
function lib_flink(&$ctag, &$refObj)
{

    global $dsql, $cfg_soft_lang;
    $attlist = "type|textall,row|24,titlelen|24,linktype|1,typeid|0";
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
    $dsql->Execute();
    while ($dbrow = $dsql->GetObject()) {
        if ($type == 'text' || $type == 'textall') {
            $link = "<a href='".$dbrow->url."' target='_blank'>".cn_substr($dbrow->webname, $titlelen)."</a> ";
        } else if ($type == 'image') {
            $link = "<a href='".$dbrow->url."' target='_blank'><img src='".$dbrow->logo."' style='max-width:80px;max-height:60px'></a> ";
        } else {
            if ($dbrow->logo == '') {
                $link = "<a href='".$dbrow->url."' target='_blank'>".cn_substr($dbrow->webname, $titlelen)."</a> ";
            } else {
                $link = "<a href='".$dbrow->url."' target='_blank'><img src='".$dbrow->logo."' style='max-width:80px;max-height:60px'></a> ";
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