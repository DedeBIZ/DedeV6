<?php
/**
 * 文档关键词选择
 *
 * @version        $Id: article_keywords_select.php$
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/datalistcp.class.php");
setcookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");

$f = RemoveXSS($f);

if (empty($keywords)) $keywords = "";

$sql = "SELECT * FROM `#@__keywords` ORDER BY `rank` DESC";
$dlist = new DataListCP();
$dlist->SetTemplate(DEDEADMIN."/templets/article_keywords_select.htm");
$dlist->pageSize = 300;
$dlist->SetParameter("f", $f);
$dlist->SetSource($sql);
$dlist->Display();

function GetSta($sta)
{
    if ($sta == 1) return "正常";
    else return "<span style='color:#e74d58'>禁用</span>";
}

function GetMan($sta)
{
    if ($sta == 1) return "禁用";
    else return "启用";
}
