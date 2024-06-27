<?php
/**
 * 地址跳转
 *
 * @version        $id:jump.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/../system/common.inc.php');
require_once(DEDEINC."/libraries/oxwindow.class.php");
$url = isset($url)? RemoveXSS($url) : '';
if (preg_match("#^http#", $url)) {
    $rur = parse_url($url);
    $loc = parse_url($cfg_basehost);
    if (!$rur || !$loc) {
        ShowMsg("地址错误","javascript:;");
        exit;
    }
    if ($rur['host'] !== $loc['host']) {
        //如果不是本站点的，则需要点击进行跳转
        $wintitle = "将要访问";
        $msg = "<code>$url</code><div class='mt-3'><a href='$url' class='btn btn-success btn-sm'>继续访问</a></div>";
        $win = new OxWindow();
        $win->AddTitle("您将要访问的链接不属于当前站点，请留意账号安全");
        $win->AddMsgItem($msg);
        $winform = $win->GetWindow("hand", false);
        $win->Display();
    } else {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location:'.$url);
    }
} else {
    ShowMsg("地址错误", "javascript:;");
    exit;
}
?>