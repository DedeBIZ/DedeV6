<?php
/**
 * 模型使用管理
 *
 * @version        $id:mychannel_main.php 15:26 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert("系统已启用安全模式，无法使用当前功能",ALERT_DANGER));
}
CheckPurview('c_List');
require_once(DEDEINC.'/datalistcp.class.php');
DedeSetCookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
$sql = "SELECT id,nid,typename,addtable,isshow,issystem FROM `#@__channeltype` ORDER BY id DESC";
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN."/templets/mychannel_main.htm");
$dlist->SetSource($sql);
$dlist->display();
function GetSta($sta, $id)
{
    if ($sta == 1) {
        return ($id != -1 ? "<span class='btn btn-success btn-sm'>启用</span><a href='mychannel_edit.php?dopost=hide&id=$id' class='btn btn-outline-warning btn-sm'>隐藏</a>" : "<span class='btn btn-success btn-sm'>固定</span>");
    } else {
        return "<a href='mychannel_edit.php?dopost=show&id=$id' class='btn btn-outline-success btn-sm'>启用</a><span class='btn btn-warning btn-sm'>隐藏</span>";
    }
}
function IsSystem($s)
{
    return $s == 1 ? "系统" : "自动";
}
?>