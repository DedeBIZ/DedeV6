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
require(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert("系统已启用安全模式，无法使用当前功能",ALERT_DANGER));
}
CheckPurview('sys_Data');
if (empty($dopost)) $dopost = "";
//查看表结构
if ($dopost == "viewinfo") {
    CheckCSRF();
    if (empty($tablename)) {
        echo "没有指定表名";
    } else {
        $dsql->SetQuery("SHOW CREATE TABLE ".$dsql->dbName.".".$tablename);
        $dsql->Execute('me');
        $row2 = $dsql->GetArray('me', MYSQL_BOTH);
        $ctinfo = $row2[1];
        echo "<xmp>".trim($ctinfo)."</xmp>";
    }
    exit();
}
//优化表
else if ($dopost == "opimize") {
    CheckCSRF();
    if (empty($tablename)) {
        echo "没有指定表名";
    } else {
        $rs = $dsql->ExecuteNoneQuery("OPTIMIZE TABLE `$tablename` ");
        if ($rs)  echo "执行优化表：$tablename  OK";
        else echo "执行优化表：$tablename  失败，原因是：".$dsql->GetError();
    }
    exit();
}
//优化全部表
else if ($dopost == "opimizeAll") {
    CheckCSRF();
    $dsql->SetQuery("SHOW TABLES");
    $dsql->Execute('t');
    while ($row = $dsql->GetArray('t', MYSQL_BOTH)) {
        $rs = $dsql->ExecuteNoneQuery("OPTIMIZE TABLE `{$row[0]}` ");
        if ($rs) {
            echo "优化表: {$row[0]} ok!<br>\r\n";
        } else {
            echo "优化表: {$row[0]} 失败! 原因是: ".$dsql->GetError()."<br>\r\n";
        }
    }
    exit();
}
//修复表
else if ($dopost == "repair") {
    CheckCSRF();
    if (empty($tablename)) {
        echo "没有指定表名";
    } else {
        $rs = $dsql->ExecuteNoneQuery("REPAIR TABLE `$tablename` ");
        if ($rs) echo "修复表：$tablename  OK";
        else echo "修复表：$tablename 失败，原因是：".$dsql->GetError();
    }
    exit();
}
//修复全部表
else if ($dopost == "repairAll") {
    CheckCSRF();
    $dsql->SetQuery("Show Tables");
    $dsql->Execute('t');
    while ($row = $dsql->GetArray('t', MYSQL_BOTH)) {
        $rs = $dsql->ExecuteNoneQuery("REPAIR TABLE `{$row[0]}` ");
        if ($rs) {
            echo "修复表: {$row[0]} ok!<br>\r\n";
        } else {
            echo "修复表: {$row[0]} 失败，原因是: ".$dsql->GetError()."<br>\r\n";
        }
    }
    exit();
}
//执行SQL语句
else if ($dopost == "query") {
    CheckCSRF();
    $sqlquery = trim(stripslashes($sqlquery));
    if (preg_match("#drop(.*)table#i", $sqlquery) || preg_match("#drop(.*)database#", $sqlquery)) {
        echo "<span>删除数据表或数据库的语句不允许在这里执行</span>";
        exit();
    }
    //运行查询语句
    if (preg_match("#^select #i", $sqlquery)) {
        $dsql->SetQuery($sqlquery);
        $dsql->Execute();
        if ($dsql->GetTotalRow() <= 0) {
            echo "运行SQL：{$sqlquery}，无返回记录";
        } else {
            echo "运行SQL：{$sqlquery}，共有".$dsql->GetTotalRow()."条记录，最大返回100条";
        }
        $j = 0;
        while ($row = $dsql->GetArray()) {
            $j++;
            if ($j > 100) {
                break;
            }
            echo "<hr size=1 width='100%'/>";
            echo "记录：$j";
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
                $nerrCode .= "执行：<span style='color:#007bff'>$q</span> 出错，错误提示：<span class='text-danger'>".$errCode."</span><br>";
            }
        }
        echo "成功执行{$i}个SQL语句<br><br>";
        echo $nerrCode;
    } else {
        $dsql->ExecuteNoneQuery($sqlquery);
        $nerrCode = trim($dsql->GetError());
        echo "成功执行1个SQL语句<br><br>";
        echo $nerrCode;
    }
    exit();
}
make_hash();
include DedeInclude('templets/sys_sql_query.htm');