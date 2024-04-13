<?php
/**
 * 我的收藏
 * 
 * @version        $id:mystow.php 8:38 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckRank(0, 0);//禁止游客操作
require_once(DEDEINC."/datalistcp.class.php");
DedeSetCookie("ENV_GOBACK_URL", GetCurUrl(), time() + 3600, "/");
$type = empty($type) ? "sys" : trim($type);
$tpl = '';
$menutype = 'mydede';
$rank = empty($rank) ? "" : $rank;
if ($rank == 'top') {
    $sql = "SELECT s.*,COUNT(s.aid) AS num,t.* FROM `#@__member_stow` AS s LEFT JOIN `#@__member_stowtype` AS t on t.stowname=s.type GROUP BY s.aid ORDER BY num DESC";
    $tpl = 'mystowtop';
} else {
    $sql = "SELECT s.*,t.* FROM `#@__member_stow` AS s LEFT JOIN `#@__member_stowtype` AS t on t.stowname=s.type WHERE s.mid='".$cfg_ml->M_ID."' ORDER BY s.id DESC";
    $tpl = 'mystow';
}
$dsql->Execute('nn', 'SELECT indexname,stowname FROM `#@__member_stowtype`');
while ($row = $dsql->GetArray('nn')) {
    $rows[] = $row;
}
$dlist = new DataListCP();
$dlist->pagesize = 10;
$dlist->SetTemplate(DEDEMEMBER."/templets/mystow.htm");
$dlist->SetSource($sql);
$dlist->Display();
?>