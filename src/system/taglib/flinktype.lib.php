<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 友情链接标签
 *
 * @version        $id:flinktype.lib.php 15:57 2011年2月18日Z niap $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(DEDEINC."/taglib/flink.lib.php");
function lib_flinktype(&$ctag, &$refObj)
{
    global $dsql;
    $attlist = "row|30,titlelen|30";
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $totalrow = $row;
    $revalue = '';
    $equery = "SELECT * FROM `#@__flinktype` ORDER BY id ASC LIMIT 0,$totalrow";
    if (trim($ctag->GetInnerText()) == '') $innertext = "[field:typename/]";
    else $innertext = $ctag->GetInnerText();
    if (!isset($type)) $type = '';
    $dtp = new DedeTagParse();
    $dtp->SetNameSpace("dede", "{", "}");
    $dtp->LoadString($innertext);
    $dsql->SetQuery($equery);
    $dsql->Execute();
    $rs = '';
    $row = array();
    while ($dbrow = $dsql->GetObject()) {
        $row[] = $dbrow;
    }
    $DedeBIZ = new stdClass;
    $DedeBIZ->id = 999;
    $DedeBIZ->typename = 'DedeBIZ';
    if ($type == 'DedeBIZ') $row[] = $DedeBIZ;
    foreach ($row as $key => $value) {
        if (is_array($dtp->CTags)) {
            $GLOBALS['envs']['flinkid'] = $value->id;
            foreach ($dtp->CTags as $tagid => $ctag) {
                $tagname = $ctag->GetName();
                if ($tagname == "flink") $dtp->Assign($tagid, lib_flink($ctag, $refObj));
            }
        }
        $rs = $dtp->GetResult();
        $rs = preg_replace("/\[field:id([\/\s]{0,})\]/isU", $value->id, $rs);
        $rs = preg_replace("/\[field:typename([\/\s]{0,})\]/isU", $value->typename, $rs);
        $revalue .= $rs;
    }
    return $revalue;
}
?>