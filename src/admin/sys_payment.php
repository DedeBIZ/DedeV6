<?php
/**
 * 支付接口
 *
 * @version        $id:sys_info_mark.php 22:28 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC.'/datalistcp.class.php');
CheckPurview('sys_Data');
$dopost = (empty($dopost)) ? '' : $dopost;
$pid = (empty($pid)) ? 0 : intval($pid);

$sql = "SELECT * FROM `#@__payment` ORDER BY `rank` ASC";
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN."/templets/sys_payment.htm");
$dlist->SetSource($sql);
$dlist->display();
?>