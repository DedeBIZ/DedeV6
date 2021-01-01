<?php if (!defined('DEDEINC')) exit('Request Error!');
/**
 * 关联内容标签
 *
 * @version        $Id: relation.lib.php 1 9:29 2020年9月23日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */

// 关联内容
function lib_relation(&$ctag, &$refObj)
{
    global $dsql;

    //属性处理
    $attlist = "row|12,titlelen|28,infolen|150,name|default,orderby|";
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);

    if (get_class($refObj) != "Archives") {
        return "暂无相关内容";
    }

    if (empty($refObj->Fields[$name])) {
        return "暂无相关内容";
    }

    if (!isset($refObj->ChannelUnit->ChannelFields[$name])) {
        return "暂无相关内容";
    }

    if (empty($tablewidth)) $tablewidth = 100;
    if (empty($col)) $col = 1;
    $colWidth = ceil(100 / $col);
    $tablewidth = $tablewidth . "%";
    $colWidth = $colWidth . "%";

    $ids = array();
    $channelid = $refObj->ChannelUnit->ChannelFields[$name]["channel"];

    $odb = "";
    if ($channelid > 0) {
        $odb = " ORDER BY arc.sortrank DESC";
    } else {
        $odb = " ORDER BY arc.senddate DESC";
    }
    if ($orderby == "click") {
        $odb = " ORDER BY arc.click DESC";
    }

    if ($channelid > 0) {
        $query = "SELECT arc.*,tp.typedir,tp.typename,tp.corank,tp.isdefault,tp.defaultname,tp.namerule,
    tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath
    FROM `#@__archives` arc LEFT JOIN `#@__arctype` tp ON arc.typeid=tp.id
    where arc.arcrank>-1 AND arc.id IN (" . $refObj->Fields[$name] . ") $odb";
    } else {
        $gquery = "SELECT addtable,listfields FROM `#@__channeltype` WHERE id='$channelid' ";
        $grow = $dsql->GetOne($gquery);
        $maintable = trim($grow['addtable']);
        $query = "SELECT arc.*,tp.typedir,tp.typename,tp.corank,tp.isdefault,tp.defaultname,tp.namerule,
    tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath
    FROM `{$maintable}` arc LEFT JOIN `#@__arctype` tp ON arc.typeid=tp.id
    where arc.arcrank>-1 AND arc.aid IN (" . $refObj->Fields[$name] . ") $odb";
    }

    $innertext = trim($ctag->GetInnerText());
    if ($innertext == '') $innertext = GetSysTemplets('part_arclist.htm');

    $dsql->SetQuery($query);
    $dsql->Execute('al');
    $artlist = '';
    if ($col > 1) {
        $artlist = "<table width='$tablewidth' border='0' cellspacing='0' cellpadding='0'>\r\n";
    }
    $dtp2 = new DedeTagParse();
    $dtp2->SetNameSpace('field', '[', ']');
    $dtp2->LoadString($innertext);
    $GLOBALS['autoindex'] = 0;
    $line = $row;
    for ($i = 0; $i < $line; $i++) {
        if ($col > 1) $artlist .= "<tr>\r\n";
        for ($j = 0; $j < $col; $j++) {
            if ($col > 1) $artlist .= "    <td width='$colWidth'>\r\n";
            if ($row = $dsql->GetArray("al")) {
                if ($channelid > 0) {
                    $row['id'] =  $row['id'];
                } else {
                    $row['id'] =  $row['aid'];
                }
                $ids[] = $row['id'];
                $row['description'] = isset($row['description']) ? $row['description'] : "";
                $row['filename'] = isset($row['filename']) ? $row['filename'] : "";
                $row['money'] = isset($row['money']) ? $row['money'] : 0;
                $row['ismake'] = isset($row['ismake']) ? $row['ismake'] : 0;
                //处理一些特殊字段
                $row['info'] = $row['infos'] = cn_substr($row['description'], $infolen);

                if ($row['corank'] > 0 && $row['arcrank'] == 0) {
                    $row['arcrank'] = $row['corank'];
                }

                $row['filename'] = $row['arcurl'] = GetFileUrl(
                    $row['id'],
                    $row['typeid'],
                    $row['senddate'],
                    $row['title'],
                    $row['ismake'],
                    $row['arcrank'],
                    $row['namerule'],
                    $row['typedir'],
                    $row['money'],
                    $row['filename'],
                    $row['moresite'],
                    $row['siteurl'],
                    $row['sitepath']
                );

                $row['typeurl'] = GetTypeUrl(
                    $row['typeid'],
                    $row['typedir'],
                    $row['isdefault'],
                    $row['defaultname'],
                    $row['ispart'],
                    $row['namerule2'],
                    $row['moresite'],
                    $row['siteurl'],
                    $row['sitepath']
                );

                if ($row['litpic'] == '-' || $row['litpic'] == '') {
                    $row['litpic'] = $GLOBALS['cfg_cmspath'] . '/images/defaultpic.gif';
                }
                if (!preg_match("#^http:\/\/#i", $row['litpic']) && $GLOBALS['cfg_multi_site'] == 'Y') {
                    $row['litpic'] = $GLOBALS['cfg_mainsite'] . $row['litpic'];
                }
                $row['picname'] = $row['litpic'];
                $row['pubdate'] = isset($row['pubdate']) ? $row['pubdate'] : $row['senddate'];
                $row['stime'] = GetDateMK($row['pubdate']);
                $row['typelink'] = "<a href='" . $row['typeurl'] . "'>" . $row['typename'] . "</a>";
                $row['image'] = "<img src='" . $row['picname'] . "' border='0' alt='" . preg_replace("#['><]#", "", $row['title']) . "'>";
                $row['imglink'] = "<a href='" . $row['filename'] . "'>" . $row['image'] . "</a>";
                $row['fulltitle'] = $row['title'];
                $row['title'] = cn_substr($row['title'], $titlelen);
                if (isset($row['color']) && $row['color'] != '') $row['title'] = "<font color='" . $row['color'] . "'>" . $row['title'] . "</font>";
                if (preg_match('#b#', $row['flag'])) $row['title'] = "<strong>" . $row['title'] . "</strong>";
                $row['textlink'] = "<a href='" . $row['filename'] . "'>" . $row['title'] . "</a>";
                $row['plusurl'] = $row['phpurl'] = $GLOBALS['cfg_phpurl'];
                $row['memberurl'] = $GLOBALS['cfg_memberurl'];
                $row['templeturl'] = $GLOBALS['cfg_templeturl'];

                if (is_array($dtp2->CTags)) {
                    foreach ($dtp2->CTags as $k => $ctag) {
                        if ($ctag->GetName() == 'array') {
                            $dtp2->Assign($k, $row);
                        } else {
                            if (isset($row[$ctag->GetName()])) $dtp2->Assign($k, $row[$ctag->GetName()]);
                            else $dtp2->Assign($k, '');
                        }
                    }
                    $GLOBALS['autoindex']++;
                }

                $artlist .= $dtp2->GetResult() . "\r\n";
            }
            //if hasRow
            else {
                $artlist .= '';
            }
            if ($col > 1) $artlist .= "    </td>\r\n";
        }
        //Loop Col
        if ($col > 1) $i += $col - 1;
        if ($col > 1) $artlist .= "    </tr>\r\n";
    }
    //loop line
    if ($col > 1) $artlist .= "    </table>\r\n";
    $dsql->FreeResult("al");
    return $artlist;
}
