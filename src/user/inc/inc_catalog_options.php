<?php
/**
 * 单表模型发布器
 * 
 * @version        $id:archives_sg_add.php 13:52 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
if (!defined('DEDEMEMBER')) exit('dedebiz');
/**
 *  获取选项列表
 *
 * @param     string  $selid  当前选择id
 * @param     string  $channeltype  频道类型
 * @return    string
 */
function GetOptionList($selid = 0, $channeltype = 0)
{
    global $OptionArrayList, $channels, $dsql;
    $dsql->SetQuery("SELECT id,typename FROM `#@__channeltype` ");
    $dsql->Execute();
    $channels = array();
    while ($row = $dsql->GetObject()) {
        $channels[$row->id] = $row->typename;
    }
    $OptionArrayList = "";
    $query = "SELECT id,typename,ispart,channeltype,issend FROM `#@__arctype` WHERE ispart<2 AND reid=0 ORDER BY sortrank ASC ";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $selected = '';
    while ($row = $dsql->GetObject()) {
        if ($selid == $row->id) {
            $selected = " selected='$selected'";
        }
        if ($row->channeltype == $channeltype && $row->issend == 1) {
            if ($row->ispart == 0) {
                $OptionArrayList .= "<option value='".$row->id."' class='option3'{$selected}>".$row->typename."</option>\r\n";
            } else if ($row->ispart == 1) {
                $OptionArrayList .= "<option value='".$row->id."' class='option2'{$selected}>".$row->typename."</option>\r\n";
            }
        }
        $selected = '';
        LogicGetOptionArray($row->id, "─", $channeltype, $selid);
    }
    return $OptionArrayList;
}
/**
 *  逻辑递归
 *
 * @access    public
 * @param     int  $id
 * @param     string  $step
 * @param     string  $channeltype
 * @param     int  $selid
 * @return    string
 */
function LogicGetOptionArray($id, $step, $channeltype, $selid = 0)
{
    global $OptionArrayList, $channels, $dsql;
    $selected = '';
    $dsql->SetQuery("Select id,typename,ispart,channeltype,issend From `#@__arctype` where reid='".$id."' And ispart<2 order by sortrank asc");
    $dsql->Execute($id);
    while ($row = $dsql->GetObject($id)) {
        if ($selid == $row->id) {
            $selected = " selected='$selected'";
        }
        if ($row->channeltype == $channeltype && $row->issend == 1) {
            if ($row->ispart == 0) {
                $OptionArrayList .= "<option value='".$row->id."' class='option3'{$selected}>$step".$row->typename."</option>\r\n";
            } else if ($row->ispart == 1) {
                $OptionArrayList .= "<option value='".$row->id."' class='option2'{$selected}>$step".$row->typename."</option>\r\n";
            }
        }
        $selected = '';
        LogicGetOptionArray($row->id, $step."─", $channeltype, $selid);
    }
}
/**
 *  自定义类型
 *
 * @param     int  $mid  会员id
 * @param     int  $mtypeid  自定义类别id
 * @param     int  $channelid  频道id
 * @return    string
 */
function classification($mid, $mtypeid = 0, $channelid = 1)
{
    global $dsql;
    $list = $selected = '';
    $quey = "SELECT * FROM `#@__mtypes` WHERE mid = '$mid' And channelid='$channelid' ;";
    $dsql->SetQuery($quey);
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        if ($mtypeid != 0) {
            if ($mtypeid == $row['mtypeid']) {
                $selected = " selected";
            }
        }
        $list .= "<option value='".$row['mtypeid']."' class='option3'{$selected}>".$row['mtypename']."</option>\r\n";
        $selected = '';
    }
    return $list;
}