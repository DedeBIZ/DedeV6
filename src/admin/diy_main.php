<?php
/**
 * 自定义表单管理
 *
 * @version        $id:diy_main.php 18:31 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('c_List');
require_once(DEDEINC."/datalistcp.class.php");
require_once(DEDEINC."/common.func.php");
DedeSetCookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
$sql = "SELECT `diyid`,`name`,`table` FROM `#@__diyforms` ORDER BY diyid ASC";
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN."/templets/diy_main.htm");
$dlist->SetSource($sql);
$dlist->display();
$dlist->Close();
?>