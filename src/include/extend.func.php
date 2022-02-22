<?php
/**
 *  添加联动多筛选
 *
 * @param     string  $str  字符
 * @param     string  $stype  类型
 * @param     string  $fieldset  字段列表
 * @param     string  $loadtype  载入类型
 */
function litimgurls($imgid=0)
{
    global $lit_imglist,$dsql;
    $row = $dsql->GetOne("SELECT c.addtable FROM #@__archives AS a LEFT JOIN #@__channeltype AS c ON a.channel=c.id where a.id='$imgid'");
    $addtable = trim($row['addtable']);
    $row = $dsql->GetOne("Select imgurls From `$addtable` where aid='$imgid'");
    $ChannelUnit = new ChannelUnit(2,$imgid);
    $lit_imglist = $ChannelUnit->GetlitImgLinks($row['imgurls']);
    return $lit_imglist;
}
//字符过滤函数，用于安全
function string_filter($str,$stype="inject") {
	if ($stype=="inject") {
		$str = str_replace (
		    array ("select", "insert", "update", "delete", "alter", "cas", "union", "into", "load_file", "outfile", "create", "join", "where", "like", "drop", "modify", "rename", "'", "/*", "*", "../", "./"),
			array ("","","","","","","","","","","","","","","","","","","","","",""),
		$str);
	} else if ($stype=="xss") {
		$farr = array ("/\s+/" , "/<(\/?)(script|META|STYLE|HTML|HEAD|BODY|STYLE |i?frame|b|strong|style|html|img|P|o:p|iframe|u|em|strike|BR|div|a|TABLE|TBODY|object|tr|td|st1:chsdate|FONT|span|MARQUEE|body|title|\r\n|link|meta|\?|\%)([^>]*?)>/isU", "/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU",);
		$tarr = array (" ","","\\1\\2",); 
		$str = preg_replace ($farr, $tarr, $str);
		$str = str_replace (
			array( "<", ">", "'", "\"", ";", "/*", "*", "../", "./"),
			array("&lt;","&gt;","","","","","","",""),
		$str);
	}
	return $str;
}
//载入自定义表单，用于发布
function AddFilter($channelid, $type=1, $fieldsnamef=array(), $defaulttid=0, $loadtype='autofield')
{
	global $tid,$dsql,$id;
	$tid = $defaulttid ? $defaulttid : $tid;
	if ($id!="")
	{
		$tidsq = $dsql->GetOne(" Select typeid From `#@__archives` where id='$id' ");
		$tid = $tidsq["typeid"];
	}
	$nofilter = (isset($_REQUEST['TotalResult']) ? "&TotalResult=".$_REQUEST['TotalResult'] : '').(isset($_REQUEST['PageNo']) ? "&PageNo=".$_REQUEST['PageNo'] : '');
	$filterarr = string_filter(stripos($_SERVER['REQUEST_URI'], "list.php?tid=") ? str_replace($nofilter, '', $_SERVER['REQUEST_URI']) : $GLOBALS['cfg_cmsurl']."/plus/list.php?tid=".$tid);
    $cInfos = $dsql->GetOne(" Select * From  `#@__channeltype` where id='$channelid' ");
	$fieldset=$cInfos['fieldset'];
	$dtp = new DedeTagParse();
    $dtp->SetNameSpace('field','<','>');
    $dtp->LoadSource($fieldset);
    $dede_addonfields = '';
    if(is_array($dtp->CTags))
    {
        foreach($dtp->CTags as $tida=>$ctag)
        {
            $fieldsname = $fieldsnamef ? explode(",", $fieldsnamef) : explode(",", $ctag->GetName());
			if(($loadtype!='autofield' || ($loadtype=='autofield' && $ctag->GetAtt('autofield')==1)) && in_array($ctag->GetName(), $fieldsname) )
            {
                $href1 = explode($ctag->GetName().'=', $filterarr);
				$href2 = explode('&', $href1[1]);
				$fields_value = $href2[0];
				$fields_value1 = explode('|', $fields_value);
				$dede_addonfields .= '<b>'.$ctag->GetAtt('itemname').'：</b>';
				switch ($type) {
					case 1:
						$dede_addonfields .= (preg_match("/&".$ctag->GetName()."=/is",$filterarr,$regm) ? '<a href="'.str_replace("&".$ctag->GetName()."=".$fields_value,"",$filterarr).'" style="display:inline-block;padding:.25rem .5rem;line-height:1.5;color:#fff;background:#008e38;border-color:#008e38;border-radius:.2rem">全部</a>' : '<span style="display:inline-block;padding:.25rem .5rem;line-height:1.5;color:#fff;background:#dc3545;border-color:#dc3545;border-radius:.2rem">全部</span>').'&nbsp;';
						$addonfields_items = explode(",",$ctag->GetAtt('default'));
						for ($i=0; $i<count($addonfields_items); $i++)
						{
							$href = stripos($filterarr,$ctag->GetName().'=') ? str_replace("=".$fields_value,"=".$fields_value."|".urlencode($addonfields_items[$i]),$filterarr) : $filterarr.'&'.$ctag->GetName().'='.urlencode($addonfields_items[$i]);
							$is_select = in_array(urlencode($addonfields_items[$i]), $fields_value1) ? 1 : 0;
							$fields_value2 = "";
							for ($j=0; $j<count($fields_value1); $j++)
							{
								$fields_value2 .= $fields_value1[$j] != urlencode($addonfields_items[$i]) ? $fields_value1[$j].($j<count($fields_value1)-1 ? "|" : "") : "";
							}
							$fields_value2 = rtrim($fields_value2, "|");
							$href3 = str_replace(array("&".$ctag->GetName()."=".$fields_value,$ctag->GetName()."=".$fields_value, "&".$ctag->GetName()."=&"), array("&".$ctag->GetName()."=".$fields_value2,$ctag->GetName()."=".$fields_value2, "&"), $filterarr);
							$href3 = !end(explode("=", $href3)) ? str_replace("&".end(explode("&", $href3)), "", $href3) : $href3;
							
							$dede_addonfields .= ($fields_value!=urlencode($addonfields_items[$i]) && $is_select!=1 ? '<a title="'.$addonfields_items[$i].'" href="'.$href.'" style="display:inline-block;padding:.25rem .5rem;line-height:1.5;color:#fff;background:#008e38;border-color:#008e38;border-radius:.2rem">'.$addonfields_items[$i].'</a>' : '<a title="'.$addonfields_items[$i].'" href="'.$href3.'" style="display:inline-block;padding:.25rem .5rem;line-height:1.5;color:#fff;background:#dc3545;border-color:#dc3545;border-radius:.2rem">'.$addonfields_items[$i].'<span style="margin-left:6px;color:#fff">×</span></a>')."&nbsp;";
						}
						$dede_addonfields .= '<br><br>';
					break;
					case 2:
						$dede_addonfields .= (preg_match("/&".$ctag->GetName()."=/is",$filterarr,$regm) ? '<a href="'.str_replace("&".$ctag->GetName()."=".$fields_value,"",$filterarr).'">全部</a>' : '<span>全部</span>').'&nbsp;';
						$addonfields_items = explode(",",$ctag->GetAtt('default'));
						for ($i=0; $i<count($addonfields_items); $i++)
						{
							$href = stripos($filterarr,$ctag->GetName().'=') ? str_replace("=".$fields_value,"=".$fields_value."|".urlencode($addonfields_items[$i]),$filterarr) : $filterarr.'&'.$ctag->GetName().'='.urlencode($addonfields_items[$i]);
							$is_select = in_array(urlencode($addonfields_items[$i]), $fields_value1) ? 1 : 0;
							$fields_value2 = "";
							for ($j=0; $j<count($fields_value1); $j++)
							{
								$fields_value2 .= $fields_value1[$j] != urlencode($addonfields_items[$i]) ? $fields_value1[$j].($j<count($fields_value1)-1 ? "|" : "") : "";
							}
							$fields_value2 = rtrim($fields_value2, "|");
							$href3 = str_replace(array("&".$ctag->GetName()."=".$fields_value,$ctag->GetName()."=".$fields_value, "&".$ctag->GetName()."=&"), array("&".$ctag->GetName()."=".$fields_value2,$ctag->GetName()."=".$fields_value2, "&"), $filterarr);
							$href3 = !end(explode("=", $href3)) ? str_replace("&".end(explode("&", $href3)), "", $href3) : $href3;
							
							$dede_addonfields .= ($fields_value!=urlencode($addonfields_items[$i]) && $is_select!=1 ? '<input type="checkbox" title="'.$addonfields_items[$i].'" value="'.$href.'" onclick="window.location=this.value">&nbsp;<a title="'.$addonfields_items[$i].'" href="'.$href.'">'.$addonfields_items[$i].'</a>' : '<input type="checkbox" checked="checked" title="'.$addonfields_items[$i].'" value="'.$href3.'" onclick="window.location=this.value">&nbsp;<a title="'.$addonfields_items[$i].'" href="'.$href3.'" class="cur">'.$addonfields_items[$i].'</a>')."&nbsp;";
						}
						$dede_addonfields .= '<br><br>';
					break;
				}
            }
        }
    }
	echo $dede_addonfields;
}