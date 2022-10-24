<?php
/**
 * 用户管理
 *
 * @version        $Id: sys_admin_user.php 1 16:22 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DataListCP;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('sys_User');
setcookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
if (empty($rank)) $rank = '';
else $rank = " WHERE CONCAT(#@__admin.usertype)='$rank' ";
$dsql->SetQuery("SELECT `rank`,typename FROM `#@__admintype`");
$dsql->Execute();
while ($row = $dsql->GetObject()) {
    $adminRanks[$row->rank] = $row->typename;
}
$query = "SELECT * FROM `#@__admin` $rank";
$dlist = new DataListCP();
$dlist->enableXssClean = false;
$dlist->SetTemplet(DEDEADMIN."/templets/sys_admin_user.htm");
$dlist->SetSource($query);
$dlist->Display();
function GetUserType($trank)
{
    global $adminRanks;
    if (isset($adminRanks[$trank])) return $adminRanks[$trank];
    else return "错误类型";
}
function GetChannel($c)
{
    global $dsql;
    $result = array();
    $dsql->SetQuery("SELECT typename FROM `#@__arctype` where id in ($c)");
    $dsql->Execute('c');
    while ($row = $dsql->GetObject('c')) {
        $result[] = $row->typename;
    }
    if ($c == "" || $c == 0) return "所有频道";
    else return join(',',$result);
}
?>