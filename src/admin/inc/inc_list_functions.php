<?php
/**
 * 列表对应函数
 *
 * @version        $Id: inc_list_functions.php 1 10:32 2010年7月21日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
if (!isset($registerGlobals)) {
    require_once(dirname(__FILE__)."/../../system/common.inc.php");
}
//获取栏目名称
function GetTypename($tid)
{
    global $dsql;
    if (empty($tid)) return '';
    if (file_exists(DEDEDATA.'/cache/inc_catalog_base.inc')) {
        require_once(DEDEDATA.'/cache/inc_catalog_base.inc');
        global $cfg_Cs;
        if (isset($cfg_Cs[$tid])) {
            return base64_decode($cfg_Cs[$tid][3]);
        }
    } else {
        $row = $dsql->GetOne("SELECT typename FROM #@__arctype WHERE id = '{$tid}'");
        unset($dsql);
        unset($cfg_Cs);
        return isset($row['typename']) ? $row['typename'] : '';
    }
    return '';
}
//获得是否推荐的表述
$arcatts = array();
$dsql->Execute('n', 'SELECT * FROM `#@__arcatt` ');
while ($arr = $dsql->GetArray('n')) {
    $arcatts[$arr['att']] = $arr['attname'];
}
function IsCommendArchives($iscommend)
{
    global $arcatts;
    $sn = '';
    foreach ($arcatts as $k => $v) {
        $v = cn_substr($v, 2);
        $sn .= (preg_match("#".$k."#", $iscommend) ? ' '.$v : '');
    }
    $sn = trim($sn);
    if ($sn == '') return '';
    else return "<span style='color:#ff5722'>[$sn]</span>";
}
//获得推荐的标题
function GetCommendTitle($title, $iscommend)
{
    return $title;
}
//更换颜色
$GLOBALS['RndTrunID'] = 1;
function GetColor($color1, $color2)
{
    $GLOBALS['RndTrunID']++;
    if ($GLOBALS['RndTrunID'] % 2 == 0) {
        return $color1;
    } else {
        return $color2;
    }
}
//检查图片是否存在
function CheckPic($picname)
{
    if ($picname != "") {
        return $picname;
    } else {
        return "/static/web/img/defaultpic.jpg";
    }
}
//判断内容是否生成HTML
function IsHtmlArchives($ismake)
{
    if ($ismake == 1) {
        return "已生成";
    } else if ($ismake == -1) {
        return "仅动态";
    } else {
        return "<span style='color:#ff5722'>未生成</span>";
    }
}
//获得内容的限定级别名称
function GetRankName($arcrank)
{
    global $arcArray, $dsql;
    if (!is_array($arcArray)) {
        $dsql->SetQuery("SELECT * FROM `#@__arcrank` ");
        $dsql->Execute();
        while ($row = $dsql->GetObject()) {
            $arcArray[$row->rank] = $row->membername;
        }
    }
    if (isset($arcArray[$arcrank])) {
        return $arcArray[$arcrank];
    } else {
        return "不限";
    }
}
//判断内容是否为图片文章
function IsPicArchives($picname)
{
    if ($picname != '') {
        return "<span style='color:#ff5722'>[图]</span>";
    } else {
        return '';
    }
}