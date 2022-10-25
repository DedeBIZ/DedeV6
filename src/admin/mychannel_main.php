<?php
/**
 * 自定义模型管理
 *
 * @version        $Id: mychannel_main.php 1 15:26 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('c_List');
require_once(DEDEINC.'/datalistcp.class.php');
setcookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
$sql = "SELECT id,nid,typename,addtable,isshow,issystem FROM `#@__channeltype` ORDER BY id DESC";
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN."/templets/mychannel_main.htm");
$dlist->SetSource($sql);
$dlist->display();
function GetSta($sta, $id)
{
    if ($sta == 1) {
        return ($id != -1 ? "已启用 <a href='mychannel_edit.php?dopost=hide&id=$id' class='btn btn-outline-danger btn-sm'>禁用</a>" : "固定项目");
    } else {
        return "已禁用 <a href='mychannel_edit.php?dopost=show&id=$id' class='btn btn-outline-success btn-sm'>启用</a>";
    }
}
function IsSystem($s)
{
    return $s == 1 ? "系统" : "自动";
}
?>