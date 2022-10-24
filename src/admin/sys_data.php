<?php
/**
 * 数据库备份还原 
 *
 * @version        $Id: sys_data.php 1 17:19 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert(Lang("err_safemode_check"),ALERT_DANGER));
}
UserLogin::CheckPurview('sys_Data');
if (empty($dopost)) $dopost = '';
if ($cfg_dbtype == 'sqlite') {
    showMsg( Lang('sys_data_sqlite_tip',array('cfg_dbname'=>$cfg_dbname)), 'javascript:;');
    exit();
}
if ($cfg_dbtype == 'pgsql') {
    showMsg( Lang('sys_data_pgsql_tip',array('cfg_dbname'=>$cfg_dbname)), 'javascript:;');
    exit();
}
if ($dopost == "viewinfo") //查看表结构
{
    echo "<xmp>";
    if (empty($tablename)) {
        echo Lang("sys_data_err_table");
    } else {
        $dsql->SetQuery("SHOW CREATE TABLE ".$dsql->dbName.".".$tablename);
        $dsql->Execute('me');
        $row2 = $dsql->GetArray('me', PDO::FETCH_BOTH);
        $ctinfo = $row2[1];
        echo trim($ctinfo);
    }
    echo '</xmp>';
    exit();
} else if ($dopost == "opimize") //优化表
{
    echo "<xmp>";
    if (empty($tablename)) {
        echo Lang("sys_data_err_table");
    } else {
        $rs = $dsql->ExecuteNoneQuery("OPTIMIZE TABLE `$tablename`");
        if ($rs) {
            echo Lang('sys_data_opimize_ok',array('tablename'=>$tablename));
        } else {
            echo Lang('sys_data_opimize_err',array('tablename'=>$tablename,'err'=>$dsql->GetError()));
        }
    }
    echo '</xmp>';
    exit();
} else if ($dopost == "repair") //修复表
{
    echo "<xmp>";
    if (empty($tablename)) {
        echo Lang("sys_data_err_table");
    } else {
        $rs = $dsql->ExecuteNoneQuery("REPAIR TABLE `$tablename`");
        if ($rs) {
            echo Lang('sys_data_repair_ok',array('tablename'=>$tablename));
        } else {
            echo Lang('sys_data_repair_err',array('tablename'=>$tablename,'err'=>$dsql->GetError()));
        }
    }
    echo '</xmp>';
    exit();
}
//获取系统存在的表信息
$otherTables = array();
$dedeSysTables = array();
$channelTables = array();
$dsql->SetQuery("SELECT addtable FROM `#@__channeltype`");
$dsql->Execute();
while ($row = $dsql->GetObject()) {
    $channelTables[] = $row->addtable;
}
$dsql->SetQuery("SHOW TABLES");
$dsql->Execute('t');
while ($row = $dsql->GetArray('t', PDO::FETCH_BOTH)) {
    if (preg_match("#^{$cfg_dbprefix}#", $row[0]) || in_array($row[0], $channelTables)) {
        $dedeSysTables[] = $row[0];
    } else {
        $otherTables[] = $row[0];
    }
}
$mysql_version = $dsql->GetVersion();
include DedeInclude('templets/sys_data.htm');
function TjCount($tbname, &$dsql)
{
    $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM $tbname");
    return $row['dd'];
}
?>