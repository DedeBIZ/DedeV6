<?php
/**
 * 数据库备份还原 
 *
 * @version        $id:sys_data.php 17:19 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert("系统已启用安全模式，无法使用当前功能",ALERT_DANGER));
  }
CheckPurview('sys_Data');
if (empty($dopost)) $dopost = '';
if ($cfg_dbtype == 'sqlite') {
    showMsg('备份系统根目录下/data/'.$cfg_dbname.'.db文件即可', 'javascript:;');
    exit();
}
if ($dopost == "viewinfo") //查看表结构
{
    echo "<xmp>";
    if (empty($tablename)) {
        echo "没有指定表名";
    } else {
        $dsql->SetQuery("SHOW CREATE TABLE ".$dsql->dbName.".".$tablename);
        $dsql->Execute('me');
        $row2 = $dsql->GetArray('me', MYSQL_BOTH);
        $ctinfo = $row2[1];
        echo trim($ctinfo);
    }
    echo '</xmp>';
    exit();
} else if ($dopost == "opimize") //优化表
{
    echo "<xmp>";
    if (empty($tablename)) {
        echo "没有指定表名";
    } else {
        $rs = $dsql->ExecuteNoneQuery("OPTIMIZE TABLE `$tablename`");
        if ($rs) {
            echo "执行优化表<span class='text-primary'>$tablename</span>完成<br>";
        } else {
            echo "执行优化表<span class='text-primary'>$tablename</span>失败，原因是：".$dsql->GetError();
        }
    }
    echo '</xmp>';
    exit();
} else if ($dopost == "repair") //修复表
{
    echo "<xmp>";
    if (empty($tablename)) {
        echo "没有指定表名";
    } else {
        $rs = $dsql->ExecuteNoneQuery("REPAIR TABLE `$tablename`");
        if ($rs) {
            echo "修复表<span class='text-primary'>$tablename</span>完成<br>";
        } else {
            echo "修复表<span class='text-primary'>$tablename</span>失败，原因是：".$dsql->GetError();
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
while ($row = $dsql->GetArray('t', MYSQL_BOTH)) {
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