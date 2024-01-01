<?php
/**
 * 文档回收站
 *
 * @version        $id:recycling.php 15:46 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
CheckPurview('a_Recycling');
require_once(DEDEINC.'/datalistcp.class.php');
if (empty($cid)) {
    $cid = '0';
    $whereSql = '';
}
$cid = intval($cid);
if ($cid != 0) {
    require_once(DEDEINC.'/channelunit.func.php');
    $whereSql = " AND arc.typeid IN (".GetSonIds($cid).")";
}
$query = "SELECT arc.*,tp.typename FROM `#@__archives` AS arc LEFT JOIN `#@__arctype` AS tp ON arc.typeid = tp.id WHERE arc.arcrank = '-2' $whereSql ORDER BY arc.id DESC";
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN."/templets/recycling.htm");
$dlist->SetSource($query);
$dlist->display();
?>