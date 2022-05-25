<?php
/**
 * 插件管理
 *
 * @version        $Id: plus_main.php 1 15:46 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_plus');
require_once(DEDEINC."/datalistcp.class.php");
setcookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
$sql = "SELECT aid,plusname,writer,isshow FROM `#@__plus` ORDER BY aid ASC";
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN."/templets/plus_main.htm");
$dlist->SetSource($sql);
$dlist->display();
function GetSta($sta, $id, $title)
{
    if ($sta == 1) {
        return "启用 &gt; <a href='plus_edit.php?dopost=hide&aid=$id' class='text-danger'>禁用</a> <a href='plus_edit.php?dopost=edit&aid=$id'>修改</a> <a href='plus_edit.php?dopost=delete&aid=$id&title=".urlencode($title)."'>删除</a>";
    } else {
        return "禁用 &gt; <a href='plus_edit.php?dopost=show&aid=$id' class='text-success'>启用</a> <a href='plus_edit.php?aid=$id'>修改</a> <a href='plus_edit.php?dopost=delete&aid=$id&title=".urlencode($title)."'>册除</a>";
    }
}