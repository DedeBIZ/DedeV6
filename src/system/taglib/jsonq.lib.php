<?php

/**
 * JSONQ标签
 *
 * @version        $id:jsonq.lib.php 2023年3月20日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(DEDEINC . "/libraries/jsonq/Jsonq.php");
helper('cache');
function lib_jsonq(&$ctag, &$refObj)
{
    $attlist = "url|,path|,cachetime|3600";
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);

    $Innertext = trim($ctag->GetInnerText());

    if ($url == '' || $Innertext == '') return '';
    $key = md5($url);
    try {
        if ($path=='') {
            $jsonq = new Jsonq($url);
            $revalue = GetCache("tagjsonq2", $key);
            if (!empty($revalue)) {
                return $revalue;
            }
            $revalue = "";
            $ctp = new DedeTagParse();
            $ctp->SetNameSpace('field', '[', ']');
            $ctp->LoadSource($Innertext);
            foreach ($ctp->CTags as $tagid => $ctag) {
                $tagname = $ctag->GetName();
                $vv = $jsonq->from($tagname)->get();
                $ctp->Assign($tagid, $vv);
                $jsonq->reset();
            }
            $revalue .= $ctp->GetResult();
            SetCache("tagjsonq2", $key, $revalue, $cachetime);
            return $revalue;
        }
        
        $row = GetCache("tagjsonq", $key);
        if (!is_array($row) || $cachetime == 0) {
            $jsonq = new Jsonq($url);
            $row = $jsonq->from($path)->get();
            SetCache("tagjsonq", $key, $row, $cachetime);
        }
        if (!is_array($row)) {
            return "";
        }

        $ctp = new DedeTagParse();
        $ctp->SetNameSpace('field', '[', ']');
        $ctp->LoadSource($Innertext);

        $GLOBALS['autoindex'] = 0;
        $revalue = "";

        foreach ($row as $key => $value) {
            $GLOBALS['autoindex']++;
            foreach ($ctp->CTags as $tagid => $ctag) {
                if ($ctag->GetName() == 'array') {
                    $ctp->Assign($tagid, $value);
                } else {
                    if (!empty($value[$ctag->GetName()])) {
                        $ctp->Assign($tagid, $value[$ctag->GetName()]);
                    } else {
                        $ctp->Assign($tagid, "");
                    }
                }
            }
            $revalue .= $ctp->GetResult();
        }

        return $revalue;
    } catch (Exception $e) {
        return "";
    }
}
