<?php
/**
 * 回收站
 *
 * @version        $Id: recycling.php 1 15:46 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DataListCP;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__).'/config.php');
UserLogin::CheckPurview('a_List,a_AccList,a_MyList');
if (empty($cid)) {
    $cid = '0';
    $whereSql = '';
}
if ($cid != 0) {
    require_once(DEDEINC.'/channel/channelunit.func.php');
    $whereSql = " AND arc.typeid IN (".GetSonIds($cid).")";
}
$query = "SELECT arc.*,tp.typename FROM `#@__archives` AS arc LEFT JOIN `#@__arctype` AS tp ON arc.typeid = tp.id WHERE arc.arcrank = '-2' $whereSql ORDER BY arc.id DESC";
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN."/templets/recycling.htm");
$dlist->SetSource($query);
$dlist->display();
?>