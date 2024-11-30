<?php
/**
 * 栏目选项函数
 *
 * @version        $id:inc_catalog_options.php 10:32 2010年7月21日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
/**
 *  获取选项列表
 *
 * @access    public
 * @param     string  $selid  选择id
 * @param     string  $userCatalog  会员栏目
 * @param     string  $channeltype  栏目类型
 * @return    string
 */
function GetOptionList($selid = 0, $userCatalog = 0, $channeltype = 0)
{
    global $OptionArrayList, $channels, $dsql, $cfg_admin_channel, $admin_catalogs;
    $dsql->SetQuery("SELECT id,typename FROM `#@__channeltype`");
    $dsql->Execute('dd');
    $channels = array();
    while ($row = $dsql->GetObject('dd')) $channels[$row->id] = $row->typename;
    $OptionArrayList = '';
    //当前选中的栏目
    if ($selid > 0) {
        $row = $dsql->GetOne("SELECT id,typename,ispart,channeltype FROM `#@__arctype` WHERE id='$selid'");
        if ($row['ispart'] == 1) $OptionArrayList .= "<option value='".$row['id']."' class='opt-bg1' selected='selected'>".$row['typename']."(封面栏目)</option>";
        else $OptionArrayList .= "<option value='".$row['id']."' selected='selected'>".$row['typename']."</option>";
    }
    //是否限定会员管理的栏目
    if ($cfg_admin_channel == 'array') {
        if (count($admin_catalogs) == 0) {
            $query = "SELECT id,typename,ispart,channeltype FROM `#@__arctype` WHERE 1=2 ";
        } else {
            $admin_catalog = join(',', $admin_catalogs);
            $dsql->SetQuery("SELECT reid FROM `#@__arctype` WHERE id IN($admin_catalog) GROUP BY reid ");
            $dsql->Execute('qq');
            $topidstr = '';
            while ($row = $dsql->GetObject('qq')) {
                if ($row->reid == 0) continue;
                $topidstr .= ($topidstr == '' ? $row->reid : ','.$row->reid);
            }
            $admin_catalog .= ','.$topidstr;
            $admin_catalogs = explode(',', $admin_catalog);
            $admin_catalogs = array_unique($admin_catalogs);
            $admin_catalog = join(',', $admin_catalogs);
            $admin_catalog = preg_replace("#,$#", '', $admin_catalog);
            $query = "SELECT id,typename,ispart,channeltype FROM `#@__arctype` WHERE id in($admin_catalog) And reid=0";
        }
    } else {
        $query = "SELECT id,typename,ispart,channeltype FROM `#@__arctype` WHERE reid=0 ORDER BY sortrank ASC";
    }
    $dsql->SetQuery($query);
    $dsql->Execute('cc');
    while ($row = $dsql->GetObject('cc')) {
        $sonCats = '';
        LogicGetOptionArray($row->id, '─', $channeltype, $dsql, $sonCats);
        if ($sonCats != '') {
            if ($row->ispart == 1) $OptionArrayList .= "<option value='".$row->id."' class='opt-bg1'>".$row->typename."</option>";
            else if ($row->ispart == 2) $OptionArrayList .= "<option value='".$row->id."' class='opt-bg1'>".$row->typename."</option>";
            else if (empty($channeltype) && $row->ispart != 0) $OptionArrayList .= "<option value='".$row->id."' class='opt-bg2'>".$row->typename."-".$channels[$row->channeltype]."</option>";
            else $OptionArrayList .= "<option value='".$row->id."' class='opt-bg3'>".$row->typename."</option>";
            $OptionArrayList .= $sonCats;
        } else {
            if ($row->ispart == 0 && (!empty($channeltype) && $row->channeltype == $channeltype)) {
                $OptionArrayList .= "<option value='".$row->id."' class='opt-bg3'>".$row->typename."</option>";
            } else if ($row->ispart == 0 && empty($channeltype)) {
                $OptionArrayList .= "<option value='".$row->id."' class='opt-bg3'>".$row->typename."</option>";
            }
        }
    }
    return $OptionArrayList;
}
function LogicGetOptionArray($id, $step, $channeltype, &$dsql, &$sonCats)
{
    global $OptionArrayList, $channels, $cfg_admin_channel, $admin_catalogs;
    $dsql->SetQuery("SELECT id,typename,ispart,channeltype FROM `#@__arctype` WHERE reid='".$id."' ORDER BY sortrank ASC");
    $dsql->Execute($id);
    while ($row = $dsql->GetObject($id)) {
        if ($cfg_admin_channel != 'all' && !in_array($row->id, $admin_catalogs)) {
            continue;
        }
        if ($row->channeltype == $channeltype && $row->ispart == 1) {
            $sonCats .= "<option value='".$row->id."' class='opt-bg1'>└$step ".$row->typename."</option>";
        } else if (($row->channeltype == $channeltype && $row->ispart == 0) || empty($channeltype)) {
            $sonCats .= "<option value='".$row->id."' class='opt-bg3'>└$step ".$row->typename."</option>";
        }
        LogicGetOptionArray($row->id, $step.'─', $channeltype, $dsql, $sonCats);
    }
}
?>