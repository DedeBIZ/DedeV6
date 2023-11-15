<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 扩展函数
 * 
 * @version        $id:extend.func.php 2 20:50 2010年7月7日 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
/*会员中心调用主题模板<?php obtaintheme('head.htm');?>*/
if (!function_exists('obtaintheme')) {
    require_once DEDEINC."/archive/partview.class.php";
    function obtaintheme($path)
    {
        global $cfg_basedir, $cfg_templets_dir, $cfg_df_style;
        $tmpfile = $cfg_basedir.$cfg_templets_dir.'/'.$cfg_df_style.'/'.$path;
        $dtp = new PartView();
        $dtp->SetTemplet($tmpfile);
        $dtp->Display();
    }
}
//标签调用[field:id function='obtaintags(@me,3)'/]3表示调用文档3个标签
if (!function_exists('obtaintags')) {
    function obtaintags($aid, $num = 3)
    {
        global $dsql;
        $tags = '';
        $query = "SELECT * FROM `#@__taglist` WHERE aid='$aid' LIMIT $num";
        $dsql->Execute('tag',$query);
        while($row = $dsql->GetArray('tag')) {
            $link = "/apps/tags.php?/{$row['tid']}";
            $tags .= ($tags == '' ? "<a href='{$link}'>{$row['tag']}</a>":"<a href='{$link}'>{$row['tag']}</a>");
        }
        return $tags;
    }
}
//提取文档多图片[field:body function='obtainimgs(@me,3)'/]3表示调用文档3张图片，则附加字段需添加body字段调用channelid='模型id' addfields='字段1,字段2'
if (!function_exists('obtainimgs')) {
    function obtainimgs($string, $num)
    {
        preg_match_all("/<img([^>]*)\s*src=('|\")([^'\"]+)('|\")/", $string, $matches);
        $imgsrc_arr = array_unique($matches[3]);
        $i = 0;
        $result = '';
        foreach($imgsrc_arr as $imgsrc)
        {
            if ($i == $num) break;
            $result .= "<img src=\"$imgsrc\">";
            $i++;
        }
        return $result;
    }
}
//联动单筛选{dede:php}obtainfilter(模型id,类型,'字段1,字段2');{/dede:php}类型表示前台展现方式对应case值
function obtainfilter($channelid, $type = 1, $fieldsnamef = '', $defaulttid = 0, $toptid = 0, $loadtype = 'autofield')
{
    global $tid, $dsql, $id, $aid;
    $tid = $defaulttid ? $defaulttid : $tid;
    if ($id!="" || $aid!="") {
        $arcid = $id!="" ? $id : $aid;
        $tidsq = $dsql->GetOne("SELECT * FROM `#@__archives` WHERE id='$arcid'");
        $tid = $toptid==0 ? $tidsq["typeid"] : $tidsq["topid"];
    }
    $nofilter = (isset($_REQUEST['TotalResult']) ? "&TotalResult=".$_REQUEST['TotalResult'] : '').(isset($_REQUEST['PageNo']) ? "&PageNo=".$_REQUEST['PageNo'] : '');
    $filterarr = string_filter(stripos($_SERVER['REQUEST_URI'], "list.php?tid=") ? str_replace($nofilter, '', $_SERVER['REQUEST_URI']) : $GLOBALS['cfg_cmsurl']."/apps/list.php?tid=".$tid);
    $cInfos = $dsql->GetOne("SELECT * FROM  `#@__channeltype` WHERE id='$channelid'");
    $fieldset=$cInfos['fieldset'];
    $dtp = new DedeTagParse();
    $dtp->SetNameSpace('field', '<', '>');
    $dtp->LoadSource($fieldset);
    $biz_addonfields = '';
    if (is_array($dtp->CTags)) {
        foreach($dtp->CTags as $tida=>$ctag)
        {
            $fieldsname = $fieldsnamef ? explode(",", $fieldsnamef) : explode(",", $ctag->GetName());
            if (($loadtype!='autofield' || ($loadtype=='autofield' && $ctag->GetAtt('autofield')==1)) && in_array($ctag->GetName(), $fieldsname)) {
                $href1 = explode($ctag->GetName().'=', $filterarr);
                $href2 = explode('&', $href1[1]);
                $fields_value = $href2[0];
                switch ($type) {
                    case 1:
                        $biz_addonfields .= '<p>';
                        $biz_addonfields .= (preg_match("/&".$ctag->GetName()."=/is",$filterarr,$regm) ? '<a href="'.str_replace("&".$ctag->GetName()."=".$fields_value,"",$filterarr).'" class="btn btn-outline-success btn-sm">全部</a>' : '<a href="'.str_replace("&".$ctag->GetName()."=".$fields_value,"",$filterarr).'" class="btn btn-success btn-sm">全部</a>');
                        $addonfields_items = explode(",",$ctag->GetAtt('default'));
                        for ($i=0; $i<count($addonfields_items); $i++)
                        {
                            $href = stripos($filterarr,$ctag->GetName().'=') ? str_replace("=".$fields_value,"=".urlencode($addonfields_items[$i]),$filterarr) : $filterarr.'&'.$ctag->GetName().'='.urlencode($addonfields_items[$i]);
                            $biz_addonfields .= ($fields_value!=urlencode($addonfields_items[$i]) ? '<a title="'.$addonfields_items[$i].'" href="'.$href.'" class="btn btn-outline-success btn-sm">'.$addonfields_items[$i].'</a>' : '<a href="'.$href.'" class="btn btn-success btn-sm">'.$addonfields_items[$i].'</a>');
                        }
                        $biz_addonfields .= '</p>';
                    break;
                    case 2:
                        $biz_addonfields .= '<select name="filter'.$ctag->GetName().'" onchange="window.location=this.options[this.selectedIndex].value" class="form-control w-25 mr-3">
                            '.'<option value="'.str_replace("&".$ctag->GetName()."=".$fields_value,"",$filterarr).'">全部</option>';
                        $addonfields_items = explode(",",$ctag->GetAtt('default'));
                        for ($i=0; $i<count($addonfields_items); $i++)
                        {
                            $href = stripos($filterarr,$ctag->GetName().'=') ? str_replace("=".$fields_value,"=".urlencode($addonfields_items[$i]),$filterarr) : $filterarr.'&'.$ctag->GetName().'='.urlencode($addonfields_items[$i]);
                            $biz_addonfields .= '<option value="'.$href.'"'.($fields_value==urlencode($addonfields_items[$i]) ? ' selected="selected"' : '').'>'.$addonfields_items[$i].'</option>
                            ';
                        }
                        $biz_addonfields .= '</select>';
                    break;
                    case 3:
                        $biz_addonfields .= '<p>';
                        $biz_addonfields .= (preg_match("/&".$ctag->GetName()."=/is",$filterarr,$regm) ? '<a href="'.str_replace("&".$ctag->GetName()."=".$fields_value,"",$filterarr).'"><input type="radio" name="filter'.$ctag->GetName().'" value="'.str_replace("&".$ctag->GetName()."=".$fields_value,"",$filterarr).'" onclick="window.location=this.value">全部</a>' : '<span><input type="radio" name="filter'.$ctag->GetName().'" checked="checked">全部</span>');
                        $addonfields_items = explode(",",$ctag->GetAtt('default'));
                        for ($i=0; $i<count($addonfields_items); $i++)
                        {
                            $href = stripos($filterarr,$ctag->GetName().'=') ? str_replace("=".$fields_value,"=".urlencode($addonfields_items[$i]),$filterarr) : $filterarr.'&'.$ctag->GetName().'='.urlencode($addonfields_items[$i]);
                            $biz_addonfields .= ($fields_value!=urlencode($addonfields_items[$i]) ? '<a title="'.$addonfields_items[$i].'" href="'.$href.'"><input type="radio" name="filter'.$ctag->GetName().'" value="'.$href.'" onclick="window.location=this.value">'.$addonfields_items[$i].'</a>' : '<span><input type="radio" name="filter'.$ctag->GetName().'" checked="checked">'.$addonfields_items[$i].'</span>');
                        }
                        $biz_addonfields .= '</p>';
                    break;
                }
            }
        }
    }
    echo $biz_addonfields;
}
//联动单筛选获取附加表
function litimgurls($imgid = 0)
{
    global  $dsql, $lit_imglist;
    $row = $dsql->GetOne("SELECT c.addtable FROM `#@__archives` AS a LEFT JOIN `#@__channeltype` AS c ON a.channel=c.id WHERE a.id='$imgid'");
    $addtable = trim($row['addtable']);
    $row = $dsql->GetOne("SELECT imgurls FROM `$addtable` WHERE aid='$imgid'");
    $ChannelUnit = new ChannelUnit(2, $imgid);
    $lit_imglist = $ChannelUnit->GetlitImgLinks($row['imgurls']);
    return $lit_imglist;
}
//联动单筛选字符过滤函数
function string_filter($str, $stype = "inject")
{
    if ($stype == "inject") {
        $str = str_replace(
            array("select", "insert", "update", "delete", "alter", "cas", "union", "into", "load_file", "outfile", "create", "join", "where", "like", "drop", "modify", "rename", "'", "/*", "*", "../", "./"),
            array("", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""),
            $str
        );
    } else if ($stype == "xss") {
        $farr = array("/\s+/", "/<(\/?)(script|META|STYLE|HTML|HEAD|BODY|STYLE |i?frame|b|strong|style|html|img|P|o:p|iframe|u|em|strike|BR|div|a|TABLE|TBODY|object|tr|td|st1:chsdate|FONT|span|MARQUEE|body|title|\r\n|link|meta|\?|\%)([^>]*?)>/isU", "/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU",);
        $tarr = array(" ", "", "\\1\\2",);
        $str = preg_replace($farr, $tarr, $str);
        $str = str_replace(
            array("<", ">", "'", "\"", ";", "/*", "*", "../", "./"),
            array("&lt;", "&gt;", "", "", "", "", "", "", ""),
            $str
        );
    }
    return $str;
}
?>