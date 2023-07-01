<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 自动关连文档标签
 *
 * @version        $id:likearticle.lib.php 9:29 2010年7月6日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
function lib_likearticle(&$ctag, &$refObj)
{
    global $dsql;
    //属性处理
    $attlist = "row|12,titlelen|28,infolen|150,col|1,tablewidth|100,mytypeid|0,byabs|0,imgwidth|120,imgheight|90";
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $revalue = '';
    if (empty($tablewidth)) $tablewidth = 100;
    if (empty($col)) $col = 1;
    $colWidth = ceil(100 / $col);
    $tablewidth = $tablewidth."%";
    $colWidth = $colWidth."%";
    $ids = array();
    $tids = array();
    if (!empty($refObj->Fields['tags'])) {
        $keyword = $refObj->Fields['tags'];
    } else {
        $keyword = (!empty($refObj->Fields['keywords']) ? $refObj->Fields['keywords'] : '');
    }
    $typeid = (!empty($mytypeid) ? $mytypeid : 0);
    if (empty($typeid)) {
        if (!empty($refObj->Typelink->TypeInfos['reid'])) {
            $typeid = $refObj->Typelink->TypeInfos['reid'];
        } else {
            if (!empty($refObj->Fields['typeid'])) $typeid = $refObj->Fields['typeid'];
        }
    }
    if (!empty($typeid) && !preg_match('#,#', $typeid)) {
        $typeid = GetSonIds($typeid);
    }
    $limitRow = $row - count($ids);
    $keyword = '';
    if (!empty($refObj->Fields['keywords'])) {
        $keywords = explode(',', trim($refObj->Fields['keywords']));
        $keyword = '';
        $n = 1;
        foreach ($keywords as $k) {
            if ($n > 3)  break;

            if (trim($k) == '') continue;
            else $k = addslashes($k);

            $keyword .= ($keyword == '' ? " CONCAT(arc.keywords,' ',arc.title) LIKE '%$k%' " : " OR CONCAT(arc.keywords,' ',arc.title) LIKE '%$k%' ");
            $n++;
        }
    }
    $arcid = (!empty($refObj->Fields['id']) ? $refObj->Fields['id'] : 0);
    if (empty($arcid) || $byabs == 0) {
        $orderquery = " ORDER BY arc.id DESC ";
    } else {
        $orderquery = " ORDER BY ABS(arc.id - ".$arcid.") ";
    }
    if ($keyword != '') {
        if (!empty($typeid)) {
            $typeid = " AND arc.typeid IN($typeid) AND arc.id<>$arcid ";
        }
        $query = "SELECT arc.*,tp.typedir,tp.typename,tp.corank,tp.isdefault,tp.defaultname,tp.namerule,tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath FROM `#@__archives` arc LEFT JOIN `#@__arctype` tp ON arc.typeid=tp.id WHERE arc.arcrank>-1 AND ($keyword)  $typeid $orderquery limit 0, $row";
    } else {
        if (!empty($typeid)) {
            $typeid = " arc.typeid IN($typeid) AND arc.id<>$arcid ";
        }
        $query = "SELECT arc.*,tp.typedir,tp.typename,tp.corank,tp.isdefault,tp.defaultname,tp.namerule,tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath FROM `#@__archives` arc LEFT JOIN `#@__arctype` tp ON arc.typeid=tp.id WHERE arc.arcrank>-1 AND  $typeid $orderquery limit 0, $row";
    }
    $innertext = trim($ctag->GetInnerText());
    if ($innertext == '') $innertext = GetSysTemplets('part_arclist.htm');
    $dsql->SetQuery($query);
    $dsql->Execute('al');
    $artlist = '';
    if ($col > 1) {
        $artlist = "<table width='$tablewidth'>\r\n";
    }
    $dtp2 = new DedeTagParse();
    $dtp2->SetNameSpace('field', '[', ']');
    $dtp2->LoadString($innertext);
    $GLOBALS['autoindex'] = 0;
    $line = $row;
    for ($i = 0; $i < $line; $i++) {
        if ($col > 1) $artlist .= "<tr>\r\n";
        for ($j = 0; $j < $col; $j++) {
            if ($col > 1) $artlist .= "<td width='$colWidth'>\r\n";
            if ($row = $dsql->GetArray("al")) {
                $ids[] = $row['id'];
                //处理一些特殊字段
                $row['info'] = $row['infos'] = cn_substr($row['description'], $infolen);
                $row['id'] =  $row['id'];
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
                    $row['litpic'] = $GLOBALS['cfg_cmspath'].'/static/web/img/thumbnail.jpg';
                }
                if (!preg_match("#^(http|https):\/\/#i", $row['litpic']) && $GLOBALS['cfg_multi_site'] == 'Y') {
                    $row['litpic'] = $GLOBALS['cfg_mainsite'].$row['litpic'];
                }
                $row['picname'] = $row['litpic'];
                $row['stime'] = GetDateMK($row['pubdate']);
                $row['typelink'] = "<a href='".$row['typeurl']."'>".$row['typename']."</a>";
                $row['image'] = "<img src='".$row['picname']."' width='$imgwidth' height='$imgheight' title='".preg_replace("#['><]#", "", $row['title'])."'>";
                $row['imglink'] = "<a href='".$row['filename']."'>".$row['image']."</a>";
                $row['fulltitle'] = $row['title'];
                $row['title'] = cn_substr($row['title'], $titlelen);
                if ($row['color'] != '') $row['title'] = "<span style='color:".$row['color']."'>".$row['title']."</span>";
                if (preg_match('#b#', $row['flag'])) $row['title'] = "".$row['title']."";
                $row['textlink'] = "<a href='".$row['filename']."'>".$row['title']."</a>";
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
                $artlist .= $dtp2->GetResult()."\r\n";
            }
            //if hasRow
            else {
                $artlist .= '';
            }
            if ($col > 1) $artlist .= "</td>\r\n";
        }
        //Loop Col
        if ($col > 1) $i += $col - 1;
        if ($col > 1) $artlist .= "</tr>\r\n";
    }
    //loop line
    if ($col > 1) $artlist .= "</table>\r\n";
    $dsql->FreeResult("al");
    return $artlist;
}
?>