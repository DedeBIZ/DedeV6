<?php
/**
 * 投票管理
 *
 * @version        $id:vote_main.php 23:54 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/datalistcp.class.php");
DedeSetCookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
$sql = "SELECT aid,votename,starttime,endtime,totalcount,isenable FROM `#@__vote` ORDER BY aid DESC";
$dlist = new DataListCP();
$issel = isset($issel) ? $issel : 0;
$aid = isset($aid) ? $aid : 0;
if ($issel == 1) {
    $dlist->SetParameter('issel', $issel);
    $dlist->SetTemplet(DEDEADMIN."/templets/vote_select.htm");
} else {
    $dlist->SetTemplet(DEDEADMIN."/templets/vote_main.htm");
}
$dlist->SetSource($sql);
$dlist->display();
?>