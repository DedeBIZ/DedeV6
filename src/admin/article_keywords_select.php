<?php
/**
 * 文档关键词选择
 *
 * @version        $id:article_keywords_select.php$
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/datalistcp.class.php");
DedeSetCookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
$f = RemoveXSS($f);
if (empty($keywords)) $keywords = '';
$sql = "SELECT * FROM `#@__keywords` ORDER BY `rank` DESC";
$dlist = new DataListCP();
$dlist->SetTemplate(DEDEADMIN."/templets/article_keywords_select.htm");
$dlist->pagesize = 30;
$dlist->SetParameter("f", $f);
$dlist->SetSource($sql);
$dlist->Display();
function GetSta($sta)
{
    if ($sta == 1) return " <span class='btn btn-success btn-sm'>已启用</span>";
    else return " <span class='btn btn-outline-warning btn-sm'>禁用</span>";
}
function GetMan($sta)
{
    if ($sta == 1) return " <span class='btn btn-warning btn-sm'>已禁用</span>";
    else return " <span class='btn btn-outline-success btn-sm'>启用</span>";
}
?>