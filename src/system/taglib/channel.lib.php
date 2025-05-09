<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 栏目列表标签
 *
 * @version        $id:channel.lib.php 9:29 2010年7月6日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
function lib_channel(&$ctag, &$refObj)
{
    global $dsql;
    $attlist = "typeid|0,reid|0,row|100,col|1,type|son,currentstyle|,cacheid|";
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $innertext = $ctag->GetInnerText();
    $line = empty($row) ? 100 : $row;
    $likeType = '';
    //读取固定的缓存块
    $cacheid = trim($cacheid);
    if ($cacheid != '') {
        $likeType = GetCacheBlock($cacheid);
        if ($likeType != '') return $likeType;
    }
    $reid = 0;
    $topid = 0;
    //如果属性里没指定栏目id，从引用类里获取栏目信息
    if (empty($typeid)) {
        if (isset($refObj->TypeLink->TypeInfos['id'])) {
            $typeid = $refObj->TypeLink->TypeInfos['id'];
            $reid = $refObj->TypeLink->TypeInfos['reid'];
            $topid = $refObj->TypeLink->TypeInfos['topid'];
        } else {
            $typeid = 0;
        }
    }
    //如果指定了栏目id，从数据库获取栏目信息
    else {
        $row2 = $dsql->GetOne("SELECT * FROM `#@__arctype` WHERE id='$typeid' ");
        if (is_array($row2)) {
            $typeid = $row2['id'];
            $reid = $row2['reid'];
            $topid = $row2['topid'];
            $issetInfos = true;
        }
    }
    if ($type == '' || $type == 'sun') $type = 'son';
    if ($innertext == '') $innertext = GetSysTemplets("channel_list.htm");
    if ($type == 'top') {
        $sql = "SELECT * FROM `#@__arctype` WHERE reid=0 AND ishidden<>1 ORDER BY sortrank ASC LIMIT 0, $line";
    } else if ($type == 'son') {
        if ($typeid == 0) return '';
        $sql = "SELECT * FROM `#@__arctype` WHERE reid='$typeid' AND ishidden<>1 ORDER BY sortrank ASC LIMIT 0, $line";
    } else if ($type == 'self') {
        if ($reid == 0) return '';
        $sql = "SELECT * FROM `#@__arctype` WHERE reid='$reid' AND ishidden<>1 ORDER BY sortrank ASC LIMIT 0, $line";
    }
    $needRel = false;
    $dtp2 = new DedeTagParse();
    $dtp2->SetNameSpace('field', '[', ']');
    $dtp2->LoadSource($innertext);
    //检查是否有子栏目，并返回rel提示用于二级菜单
    if (preg_match('#:rel#', $innertext)) $needRel = true;
    if (empty($sql)) return '';
    $dsql->SetQuery($sql);
    $dsql->Execute();
    $totalRow = $dsql->GetTotalRow();
    //如果用子栏目模式，当没有子栏目时显示同级栏目
    if ($type == 'son' && $reid != 0 && $totalRow == 0) {
        $sql = "SELECT * FROM `#@__arctype` WHERE reid='$reid' AND ishidden<>1 ORDER BY sortrank ASC LIMIT 0, $line";
        $dsql->SetQuery($sql);
        $dsql->Execute();
    }
    $GLOBALS['autoindex'] = 0;
    for ($i = 0; $i < $line; $i++) {
        if ($col > 1) $likeType .= "<dl>\r\n";
        for ($j = 0; $j < $col; $j++) {
            if ($col > 1) $likeType .= "<dd>\r\n";
            if ($row = $dsql->GetArray()) {
                $row['sonids'] = $row['rel'] = '';
                if ($needRel) {
                    $row['sonids'] = GetSonIds($row['id'], 0, false);
                    if ($row['sonids'] == '') $row['rel'] = '';
                    else $row['rel'] = " rel='dropmenu{$row['id']}'";
                }
                //处理同级栏目中，当前栏目的样式
                if (($row['id'] == $typeid || ($topid == $row['id'] && $type == 'top')) && $currentstyle != '') {
                    $linkOkstr = $currentstyle;
                    $row['typelink'] = GetOneTypeUrlA($row);
                    $linkOkstr = str_replace("~rel~", $row['rel'], $linkOkstr);
                    $linkOkstr = str_replace("~id~", $row['id'], $linkOkstr);
                    $linkOkstr = str_replace("~bigpic~", $row['bigpic'], $linkOkstr);
                    $linkOkstr = str_replace("~litimg~", $row['litimg'], $linkOkstr);
                    $linkOkstr = str_replace("~typelink~", $row['typelink'], $linkOkstr);
                    $linkOkstr = str_replace("~typename~", $row['typename'], $linkOkstr);
                    $likeType .= $linkOkstr;
                } else {
                    $row['typelink'] = $row['typeurl'] = GetOneTypeUrlA($row);
                    if (is_array($dtp2->CTags)) {
                        foreach ($dtp2->CTags as $tagid => $ctag) {
                            if (isset($row[$ctag->GetName()])) $dtp2->Assign($tagid, $row[$ctag->GetName()]);
                        }
                    }
                    $likeType .= $dtp2->GetResult();
                }
            }
            if ($col > 1) $likeType .= "</dd>\r\n";
            $GLOBALS['autoindex']++;
        }
        if ($col > 1) {
            $i += $col - 1;
            $likeType .= "</dl>\r\n";
        }
    }
    $dsql->FreeResult();
    if ($cacheid != '') {
        WriteCacheBlock($cacheid, $likeType);
    }
    return $likeType;
}
?>