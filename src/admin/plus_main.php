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
use DedeBIZ\libraries\DataListCP;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('sys_plus');
setcookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
$sql = "SELECT aid,plusname,writer,isshow FROM `#@__plus` ORDER BY aid ASC";
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN."/templets/plus_main.htm");
$dlist->SetSource($sql);
$dlist->display();
function GetSta($sta, $id, $title)
{
    if ($sta == 1) {
        return Lang('enable')." &gt; <a href='plus_edit.php?dopost=hide&aid=$id' class='text-danger'>".Lang('disable')."</a> <a href='plus_edit.php?dopost=edit&aid=$id'>".Lang('edit')."</a> <a href='plus_edit.php?dopost=delete&aid=$id&title=".urlencode($title)."'>".Lang('delete')."</a>";
    } else {
        return Lang('disable')." &gt; <a href='plus_edit.php?dopost=show&aid=$id' class='text-success'>".Lang('enable')."</a> <a href='plus_edit.php?aid=$id'>".Lang('edit')."</a> <a href='plus_edit.php?dopost=delete&aid=$id&title=".urlencode($title)."'>".Lang('delete')."</a>";
    }
}
?>