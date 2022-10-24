<?php
/**
 * SQL命令执行器
 *
 * @version        $Id: sys_sql_query.php 1 22:28 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert(Lang("err_safemode_check"),ALERT_DANGER));
}
if ($cfg_dbtype == 'pgsql') {
    showMsg( Lang('sys_data_pgsql_tip',array('cfg_dbname'=>$cfg_dbname)), 'javascript:;');
    exit();
}
UserLogin::CheckPurview('sys_Data');
if (empty($dopost)) $dopost = "";
//查看表结构
if ($dopost == "viewinfo") {
    CheckCSRF();
    if (empty($tablename)) {
        echo Lang("sys_data_err_table");
    } else {
        $dsql->SetQuery("SHOW CREATE TABLE ".$dsql->dbName.".".$tablename);
        $dsql->Execute('me');
        $row2 = $dsql->GetArray('me', PDO::FETCH_BOTH);
        $ctinfo = $row2[1];
        echo "<xmp>".trim($ctinfo)."</xmp>";
    }
    exit();
}
//优化表
else if ($dopost == "opimize") {
    CheckCSRF();
    if (empty($tablename)) {
        echo Lang("sys_data_err_table");
    } else {
        $rs = $dsql->ExecuteNoneQuery("OPTIMIZE TABLE `$tablename`");
        if ($rs) echo Lang('sys_data_opimize_ok',array('tablename'=>$tablename));
        else echo Lang('sys_data_opimize_err',array('tablename'=>$tablename,'err'=>$dsql->GetError()));
    }
    exit();
}
//优化全部表
else if ($dopost == "opimizeAll") {
    CheckCSRF();
    $dsql->SetQuery("SHOW TABLES");
    $dsql->Execute('t');
    while ($row = $dsql->GetArray('t', PDO::FETCH_BOTH)) {
        $rs = $dsql->ExecuteNoneQuery("OPTIMIZE TABLE `{$row[0]}`");
        if ($rs) {
            echo Lang('sys_data_opimize_ok',array('tablename'=>$row[0]))."<br>\r\n";
        } else {
            echo Lang('sys_data_opimize_err',array('tablename'=>$row[0],'err'=>$dsql->GetError()))."<br>\r\n";
        }
    }
    exit();
}
//修复表
else if ($dopost == "repair") {
    CheckCSRF();
    if (empty($tablename)) {
        echo Lang("sys_data_err_table");
    } else {
        $rs = $dsql->ExecuteNoneQuery("REPAIR TABLE `$tablename`");
        if ($rs) echo Lang('sys_data_repair_ok',array('tablename'=>$tablename));
        else echo Lang('sys_data_repair_err',array('tablename'=>$tablename,'err'=>$dsql->GetError()));
    }
    exit();
}
//修复全部表
else if ($dopost == "repairAll") {
    CheckCSRF();
    $dsql->SetQuery("Show Tables");
    $dsql->Execute('t');
    while ($row = $dsql->GetArray('t', PDO::FETCH_BOTH)) {
        $rs = $dsql->ExecuteNoneQuery("REPAIR TABLE `{$row[0]}`");
        if ($rs) {
            echo Lang('sys_data_repair_ok',array('tablename'=>$row[0]))."<br>\r\n";
        } else {
            echo Lang('sys_data_repair_err',array('tablename'=>$row[0],'err'=>$dsql->GetError()))."<br>\r\n";
        }
    }
    exit();
}
//执行SQL语句
else if ($dopost == "query") {
    CheckCSRF();
    $sqlquery = trim(stripslashes($sqlquery));
    if (preg_match("#drop(.*)table#i", $sqlquery) || preg_match("#drop(.*)database#", $sqlquery)) {
        echo Lang("sys_sql_query_err_drop");
        exit();
    }
    //运行查询语句
    if (preg_match("#^select #i", $sqlquery)) {
        $dsql->SetQuery($sqlquery);
        $dsql->Execute();
        if ($dsql->GetTotalRow() <= 0) {
            echo Lang("sys_sql_query_success_none",array('sqlquery'=>$sqlquery));
        } else {
            echo Lang('sys_sql_query_success_num',array('sqlquery'=>$sqlquery,'num'=>$dsql->GetTotalRow()));
        }
        $j = 0;
        while ($row = $dsql->GetArray()) {
            $j++;
            if ($j > 100) {
                break;
            }
            echo "<hr size=1 width='100%'/>";
            echo Lang('record')."：$j";
            echo "<hr size=1 width='100%'/>";
            foreach ($row as $k => $v) {
                echo "<span class='text-danger'>{$k}：</span>{$v}<br>\r\n";
            }
        }
        exit();
    }
    if ($querytype == 2) {
        //普通的SQL语句
        $sqlquery = str_replace("\r", "", $sqlquery);
        $sqls = preg_split("#;[ \t]{0,}\n#", $sqlquery);
        $nerrCode = "";
        $i = 0;
        foreach ($sqls as $q) {
            $q = trim($q);
            if ($q == "") {
                continue;
            }
            $dsql->ExecuteNoneQuery($q);
            $errCode = trim($dsql->GetError());
            if ($errCode == "") {
                $i++;
            } else {
                $nerrCode .= Lang('query')."：<span style='color:#007bff'>$q</span> ".Lang('sys_sql_query_err_info')."：<span class='text-danger'>".$errCode."</span><br>";
            }
        }
        echo Lang('sys_sql_query_success_query',array('i'=>$i));
        echo $nerrCode;
    } else {
        $dsql->ExecuteNoneQuery($sqlquery);
        $nerrCode = trim($dsql->GetError());
        echo Lang('sys_sql_query_success_query',array('i'=>1));
        echo $nerrCode;
    }
    exit();
}
make_hash();
include DedeInclude('templets/sys_sql_query.htm');
?>