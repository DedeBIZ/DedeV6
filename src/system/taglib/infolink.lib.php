<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 分类信息地区与类型快捷链接标签
 *
 * @version        $id:infolink.lib.php 9:29 2010年7月6日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(DEDEINC.'/enums.func.php');
$cachefile = DEDESTATIC.'/enums/nativeplace.json';
$data = json_decode(file_get_contents($cachefile));
foreach ($data as $key => $value) {
    $GLOBALS['em_nativeplaces'][$key] = $value;
}
$cachefile = DEDESTATIC.'/enums/infotype.json';
$data = json_decode(file_get_contents($cachefile));
foreach ($data as $key => $value) {
    $GLOBALS['em_infotypes'][$key] = $value;
}
function is_str_float($value){
    return ((int)$value != $value) ;
}
function lib_infolink(&$ctag, &$refObj)
{
    global $dsql, $nativeplace, $infotype, $cfg_rewrite, $cfg_cmspath, $cfg_mainsite, $em_nativeplaces, $em_infotypes;
    //属性处理
    //$attlist="row|12,titlelen|30";
    //FillAttsDefault($ctag->CAttribute->Items,$attlist);
    //extract($ctag->CAttribute->Items, EXTR_SKIP);
    $cmspath = ((empty($cfg_cmspath) || !preg_match("#\/$#", $cfg_cmspath)) ? $cfg_cmspath.'/' : $cfg_cmspath);
    $baseurl = preg_replace("#\/$#", '', $cfg_mainsite).$cmspath;
    $smalltypes = '';
    if (!empty($refObj->TypeLink->TypeInfos['smalltypes'])) {
        $smalltypes = explode(',', $refObj->TypeLink->TypeInfos['smalltypes']);
    }
    if (empty($refObj->Fields['typeid'])) {
        $row = $dsql->GetOne("SELECT id FROM `#@__arctype` WHERE channeltype='-8' And reid = '0' ");
        $typeid = (is_array($row) ? $row['id'] : 0);
    } else {
        $typeid = $refObj->Fields['typeid'];
    }
    $innerText = trim($ctag->GetInnerText());
    if (empty($innerText)) $innerText = GetSysTemplets("info_link.htm");
    $ctp = new DedeTagParse();
    $ctp->SetNameSpace('field', '[', ']');
    $ctp->LoadSource($innerText);
    $revalue = $seli = '';
    $channelid = (empty($refObj->TypeLink->TypeInfos['channeltype']) ? -8 : $refObj->TypeLink->TypeInfos['channeltype']);
    $fields = array('nativeplace' => '', 'infotype' => '', 'typeid' => $typeid, 'channelid' => $channelid, 'linkallplace' => '', 'linkalltype' => '');
    $fields['nativeplace'] = $fields['infotype'] = '';
    if ($cfg_rewrite == 'Y') {
        $fields['linkallplace'] = "<a href='{$baseurl}list-{$typeid}?infotype={$infotype}&channelid={$channelid}'>不限</a>";
        $fields['linkalltype'] = "<a href='{$baseurl}list-{$typeid}?nativeplace={$nativeplace}&channelid={$channelid}'>不限</a>";
    } else {
        $fields['linkallplace'] = "<a href='{$baseurl}apps/list.php?channelid={$channelid}&tid={$typeid}&infotype={$infotype}'>不限</a>";
        $fields['linkalltype'] = "<a href='{$baseurl}apps/list.php?channelid={$channelid}&tid={$typeid}&nativeplace={$nativeplace}'>不限</a>";
    }
    //地区
    if (empty($nativeplace)) {
        foreach ($em_nativeplaces as $eid => $em) {
            if ($eid % 500 != 0) continue;
            if ($cfg_rewrite == 'Y') {
                $fields['nativeplace'] .= "<a href='{$baseurl}list-{$typeid}?nativeplace={$eid}&infotype={$infotype}&channelid={$channelid}'>{$em}</a>\r\n";
            } else {
                $fields['nativeplace'] .= "<a href='{$baseurl}apps/list.php?channelid={$channelid}&tid={$typeid}&nativeplace={$eid}&infotype={$infotype}'>{$em}</a>\r\n";
            }
        }
    } else {
        $sontype = (($nativeplace % 500 != 0) ? $nativeplace : 0);//子级联动分类
        $toptype = (($nativeplace % 500 == 0) ? (int)$nativeplace : (int)($nativeplace - ($nativeplace % 500)));//顶级联动分类
        if ($cfg_rewrite == 'Y') {
            $fields['nativeplace'] = "<a href='{$baseurl}list-{$typeid}?nativeplace={$toptype}&infotype={$infotype}&channelid={$channelid}'> {$em_nativeplaces[$toptype]}</a><br>";
        } else {
            $fields['nativeplace'] = "<a href='{$baseurl}apps/list.php?channelid={$channelid}&tid={$typeid}&nativeplace={$toptype}&infotype={$infotype}'> {$em_nativeplaces[$toptype]}</a><br>";
        }
        if ($nativeplace % 500 == 0) {
            //1级分类
            foreach ($em_nativeplaces as $eid => $em) {
                if ($eid < $toptype + 1 || $eid > $toptype + 499) continue;
                if (is_str_float($eid)) continue;//仅显示2级
                if ($eid == $nativeplace) {
                    $fields['nativeplace'] .= "{$em}\r\n";
                } else {
                    if ($cfg_rewrite == 'Y') {
                        $fields['nativeplace'] .= "<a href='{$baseurl}list-{$typeid}?nativeplace={$eid}&infotype={$infotype}&channelid={$channelid}'>{$em}</a>\r\n";
                    } else {
                        $fields['nativeplace'] .= "<a href='{$baseurl}apps/list.php?channelid={$channelid}&tid={$typeid}&nativeplace={$eid}&infotype={$infotype}'>{$em}</a>\r\n";
                    }
                }
            }
        } else if (!is_str_float($nativeplace)) {
            //2级分类
            $fields['nativeplace'] .= "<span>{$em_nativeplaces[$sontype]}</span>";
            $i = 0;
            $ff = "";
            foreach ($em_nativeplaces as $eid => $em) {
                if ($eid < $sontype + 1 && $eid > $sontype) {
                    if (is_str_float($eid)) {
                        $i++;
                    }
                    if ($eid === $nativeplace) {
                        $ff .= " {$em}\r\n";
                    } else {
                        if ($cfg_rewrite == 'Y') {
                            $ff .= "<a href='{$baseurl}list-{$typeid}?nativeplace={$eid}&infotype={$infotype}&channelid={$channelid}'>{$em}</a>\r\n";
                        } else {
                            $ff .= "<a href='{$baseurl}apps/list.php?channelid={$channelid}&tid={$typeid}&nativeplace={$eid}&infotype={$infotype}'>{$em}</a>\r\n";
                        }
                    }
                }
            }
            if ($i > 0) $fields['nativeplace'] .= "<br>";
            $fields['nativeplace'] .= $ff;
        } else {
            //3级分类
            $t = intval($nativeplace);
            if ($cfg_rewrite == 'Y') {
                $fields['nativeplace'] .= "<a href='{$baseurl}list-{$typeid}?nativeplace={$t}&infotype={$infotype}&channelid={$channelid}'> {$em_nativeplaces[$t]}</a><br>";
            } else {
                $fields['nativeplace'] .= "<a href='{$baseurl}apps/list.php?channelid={$channelid}&tid={$typeid}&nativeplace={$t}&infotype={$infotype}'> {$em_nativeplaces[$t]}</a><br>";
            }
            foreach ($em_nativeplaces as $eid => $em) {
                if ($eid < $t + 1 && $eid > $t) {
                    if ($eid === $nativeplace) {
                        $fields['nativeplace'] .= " {$em}\r\n";
                    } else {
                        if ($cfg_rewrite == 'Y') {
                            $fields['nativeplace'] .= "<a href='{$baseurl}list-{$typeid}?nativeplace={$eid}&infotype={$infotype}&channelid={$channelid}'>{$em}</a>\r\n";
                        } else {
                            $fields['nativeplace'] .= "<a href='{$baseurl}apps/list.php?channelid={$channelid}&tid={$typeid}&nativeplace={$eid}&infotype={$infotype}'>{$em}</a>\r\n";
                        }
                    }
                }
            }
        }
    }
    //信息类型
    if (empty($infotype) || !is_array($smalltypes)) {
        foreach ($em_infotypes as $eid => $em) {
            if (!is_array($smalltypes) || $eid % 500 != 0) continue;
            if (is_array($smalltypes) && !in_array($eid, $smalltypes)) continue;
            if ($eid == $infotype) {
                $fields['infotype'] .= " {$em}\r\n";
            } else {
                if ($cfg_rewrite == 'Y') {
                    $fields['infotype'] .= "<a href='{$baseurl}list-{$typeid}?infotype={$eid}&nativeplace={$nativeplace}&channelid={$channelid}'>{$em}</a>\r\n";
                } else {
                    $fields['infotype'] .= "<a href='{$baseurl}apps/list.php?channelid={$channelid}&tid={$typeid}&infotype={$eid}&nativeplace={$nativeplace}'>{$em}</a>\r\n";
                }
            }
        }
    } else {
        $sontype = (($infotype % 500 != 0) ? $infotype : 0);
        $toptype = (($infotype % 500 == 0) ? (int)$infotype : (int)($infotype - ($infotype % 500)));
        if ($cfg_rewrite == 'Y') {
            $fields['infotype'] = "<a href='{$baseurl}list-{$typeid}?infotype={$toptype}&nativeplace={$nativeplace}&channelid={$channelid}'>{$em_infotypes[$toptype]}</a><br>";
        } else {
            $fields['infotype'] = "<a href='{$baseurl}apps/list.php?channelid={$channelid}&tid={$typeid}&infotype={$toptype}&nativeplace={$nativeplace}'>{$em_infotypes[$toptype]}</a><br>";
        }
        if ($infotype % 500 == 0) {
            //1级分类
            foreach ($em_infotypes as $eid => $em) {
                if ($eid < $toptype + 1 || $eid > $toptype + 499) continue;
                if (is_str_float($eid)) continue;//仅显示2级
                if ($eid == $infotype) {
                    $fields['infotype'] .= "{$em}\r\n";
                } else {
                    if ($cfg_rewrite == 'Y') {
                        $fields['infotype'] .= "<a href='{$baseurl}list-{$typeid}?nativeplace={$nativeplace}&infotype={$eid}&channelid={$channelid}'>{$em}</a>\r\n";
                    } else {
                        $fields['infotype'] .= "<a href='{$baseurl}apps/list.php?channelid={$channelid}&tid={$typeid}&nativeplace={$nativeplace}&infotype={$eid}'>{$em}</a>\r\n";
                    }
                }
            }
        } else if (!is_str_float($infotype)) {
            //2级分类
            $fields['infotype'] .= "<span>{$em_infotypes[$sontype]}</span>";
            $i = 0;
            $ff = "";
            foreach ($em_infotypes as $eid => $em) {
                if ($eid < $sontype + 1 && $eid > $sontype) {
                    if (is_str_float($eid)) {
                        $i++;
                    }
                    if ($eid === $infotype) {
                        $ff .= " {$em}\r\n";
                    } else {
                        if ($cfg_rewrite == 'Y') {
                            $ff .= "<a href='{$baseurl}list-{$typeid}?nativeplace={$nativeplace}&infotype={$eid}&channelid={$channelid}'>{$em}</a>\r\n";
                        } else {
                            $ff .= "<a href='{$baseurl}apps/list.php?channelid={$channelid}&tid={$typeid}&nativeplace={$nativeplace}&infotype={$eid}'>{$em}</a>\r\n";
                        }
                    }
                }
            }
            if ($i > 0) $fields['infotype'] .= "<br>";
            $fields['infotype'] .= $ff;
        } else {
            //3级分类
            $t = intval($infotype);
            if ($cfg_rewrite == 'Y') {
                $fields['infotype'] .= "<a href='{$baseurl}list-{$typeid}?nativeplace={$nativeplace}&infotype={$t}&channelid={$channelid}'> {$em_infotypes[$t]}</a><br>";
            } else {
                $fields['infotype'] .= "<a href='{$baseurl}apps/list.php?channelid={$channelid}&tid={$typeid}&nativeplace={$nativeplace}&infotype={$t}'> {$em_infotypes[$t]}</a><br>";
            }
            foreach ($em_infotypes as $eid => $em) {
                if ($eid < $t + 1 && $eid > $t) {
                    if ($eid === $infotype) {
                        $fields['infotype'] .= " {$em}\r\n";
                    } else {
                        if ($cfg_rewrite == 'Y') {
                            $fields['infotype'] .= "<a href='{$baseurl}/list-{$typeid}?nativeplace={$nativeplace}&infotype={$eid}&channelid={$channelid}'>{$em}</a>\r\n";
                        } else {
                            $fields['infotype'] .= "<a href='{$baseurl}apps/list.php?channelid={$channelid}&tid={$typeid}&nativeplace={$nativeplace}&infotype={$eid}'>{$em}</a>\r\n";
                        }
                    }
                }
            }
        }
    }
    if (is_array($ctp->CTags)) {
        foreach ($ctp->CTags as $tagid => $ctag) {
            if (isset($fields[$ctag->GetName()])) {
                $ctp->Assign($tagid, $fields[$ctag->GetName()]);
            }
        }
        $revalue .= $ctp->GetResult();
    }
    return $revalue;
}
?>