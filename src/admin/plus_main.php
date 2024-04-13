<?php
/**
 * 插件管理
 *
 * @version        $id:plus_main.php 15:46 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_plus');
require_once(DEDEINC."/datalistcp.class.php");
DedeSetCookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
$sql = "SELECT aid,plusname,writer,isshow FROM `#@__plus` ORDER BY aid ASC";
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN."/templets/plus_main.htm");
$dlist->SetSource($sql);
$dlist->display();
function GetSta($sta, $id, $title)
{
    if ($sta == 1) {
        return "<span class='btn btn-success btn-sm'>启用</span><a href='plus_edit.php?dopost=hide&aid=$id' class='btn btn-outline-warning btn-sm'>隐藏</a><a href='plus_edit.php?dopost=edit&aid=$id' class='btn btn-light btn-sm'>修改</a><a href='plus_edit.php?dopost=delete&aid=$id&title=".urlencode($title)."' class='btn btn-danger btn-sm'>删除</a>";
    } else {
        return "<a href='plus_edit.php?dopost=show&aid=$id' class='btn btn-outline-success btn-sm'>启用</a><span class='btn btn-warning btn-sm'>隐藏</span><a href='plus_edit.php?aid=$id' class='btn btn-light btn-sm' class='btn btn-outline-warning btn-sm'>修改</a><a href='plus_edit.php?dopost=delete&aid=$id&title=".urlencode($title)."' class='btn btn-danger btn-sm'>册除</a>";
    }
}
?>