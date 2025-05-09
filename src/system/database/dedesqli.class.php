<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 系统底层数据库核心，调用这个类前，请先设定这些外部变量
 * 
 * $GLOBALS['cfg_dbhost'];
 * $GLOBALS['cfg_dbuser'];
 * $GLOBALS['cfg_dbpwd'];
 * $GLOBALS['cfg_dbname'];
 * $GLOBALS['cfg_dbprefix'];
 *
 * @version        $id:dedesqli.class.php 15:00 2011-1-21 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
@set_time_limit(0);
//在工程所有文件中均不需要单独初始化这个类，可直接用$dsql或$db进行操作，为了防止错误，操作完后不必关闭数据库
if (!function_exists("mysqli_init")) {
    ShowMsg("尚未发现开启mysqli模块，请在php.ini中启用`extension=mysqli`","javasctipt:;",-1) ;
    exit;
}
$dsql = $dsqli = $db = new DedeSqli(FALSE);
/**
 * Dede MySQLi数据库类
 *
 * @package        DedeSqli
 * @subpackage     DedeBIZ.Libraries
 * @link           https://www.dedebiz.com
 */
class DedeSqli
{
    var $linkID;
    var $dbHost;
    var $dbUser;
    var $dbPwd;
    var $dbName;
    var $dbPrefix;
    var $result;
    var $queryString;
    var $parameters;
    var $isClose;
    var $safeCheck;
    var $showError = true;
    var $recordLog = false; //记录日志到data/mysqli_record_log.inc便于进行调试
    var $isInit = false;
    var $pconnect = false;
    //用外部定义的变量初始类，并连接数据库
    function __construct($pconnect = FALSE, $nconnect = FALSE)
    {
        $this->isClose = FALSE;
        $this->safeCheck = TRUE;
        $this->pconnect = $pconnect;
        $this->dbHost   =  $GLOBALS['cfg_dbhost'];
        $this->dbUser   =  $GLOBALS['cfg_dbuser'];
        $this->dbPwd    =  $GLOBALS['cfg_dbpwd'];
        $this->dbName   =  $GLOBALS['cfg_dbname'];
        $this->dbPrefix =  $GLOBALS['cfg_dbprefix'];
        if ($nconnect) {
            $this->Init($pconnect);
        }
    }
    function DedeSql($pconnect = FALSE, $nconnect = TRUE)
    {
        $this->__construct($pconnect, $nconnect);
    }
    function Init($pconnect = FALSE)
    {
        $this->linkID = 0;
        //$this->queryString = '';
        //$this->parameters = Array();

        $this->result["me"] = 0;
        $this->Open($pconnect);
    }
    //用指定参数初始数据库信息
    function SetSource($host, $username, $pwd, $dbname, $dbprefix = "dede_")
    {
        $this->dbHost = $host;
        $this->dbUser = $username;
        $this->dbPwd = $pwd;
        $this->dbName = $dbname;
        $this->dbPrefix = $dbprefix;
        $this->result["me"] = 0;
    }
    function SelectDB($dbname)
    {
        mysqli_select_db($this->linkID, $dbname);
    }
    //设置SQL里的参数
    function SetParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }
    //连接数据库
    function Open($pconnect = FALSE)
    {
        global $dsqli;
        //连接数据库
        if ($dsqli && !$dsqli->isClose && $dsqli->isInit) {
            $this->linkID = $dsqli->linkID;
        } else {
            $i = 0;
            @list($dbhost, $dbport) = explode(':', $this->dbHost);
            !$dbport && $dbport = 3306;
            $this->linkID = mysqli_init();
            try {
                mysqli_real_connect($this->linkID, $dbhost, $this->dbUser, $this->dbPwd, false, $dbport);
                mysqli_errno($this->linkID) != 0 && $this->DisplayError('链接（'.$this->pconnect.'）到MySQL发生错误');
            } catch (Exception $e) {
                $this->DisplayError("连接数据库失败，数据库密码不对或数据库服务器出错");
                exit;
            }
            
            //复制一个对象副本
            CopySQLiPoint($this);
        }
        //处理错误，成功连接则选择数据库
        if (!$this->linkID) {
            $this->DisplayError("连接数据库失败，数据库密码不对或数据库服务器出错");
            exit();
        }
        $this->isInit = TRUE;
        $serverinfo = mysqli_get_server_info($this->linkID);
        if (version_compare($serverinfo, '4.1', ">=") && $GLOBALS['cfg_db_language']) {
            mysqli_query($this->linkID, "SET character_set_connection=".$GLOBALS['cfg_db_language'].",character_set_results=".$GLOBALS['cfg_db_language'].",character_set_client=binary");
        }
        if (version_compare($serverinfo, '5.0', ">")) {
            mysqli_query($this->linkID, "SET sql_mode=''");
        }
        if ($this->dbName && !@mysqli_select_db($this->linkID, $this->dbName)) {
            $this->DisplayError('连接数据库失败，数据库服务器奔溃');
        }
        return TRUE;
    }
    //为了防止采集等需要较长运行时间的程序超时，在运行这类程序时设置系统等待和交互时间
    function SetLongLink()
    {
        if ($this->linkID) {
            @mysqli_query($this->linkID, "SET interactive_timeout=3600, wait_timeout=3600 ;");
        }
    }
    //获得错误描述
    function GetError()
    {
        $str = mysqli_error($this->linkID);
        return $str;
    }
    //关闭数据库
    //mysql能自动管理非持久连接的连接池
    //实际上关闭并无意义并且容易出错，所以取消这函数
    function Close($isok = FALSE)
    {
        $this->FreeResultAll();
        if ($isok) {
            @mysqli_close($this->linkID);
            $this->isClose = TRUE;
            $GLOBALS['dsql'] = NULL;
        }
    }
    //定期清理死连接
    function ClearErrLink()
    {
    }
    //关闭指定的数据库连接
    function CloseLink($dblink)
    {
        @mysqli_close($dblink);
    }
    function Esc($_str)
    {
        global $dsqli;
        if (!$dsqli->isInit) {
            $this->Init($this->pconnect);
        }
        if (version_compare(phpversion(), '4.3.0', '>=')) {
            return @mysqli_real_escape_string($this->linkID, $_str);
        } else {
            return @mysqli_escape_string($this->linkID, $_str);
        }
    }
    //执行一个不返回结果的SQL语句，如update,delete,insert等
    function ExecuteNoneQuery($sql = '')
    {
        global $dsqli;
        if (!$dsqli->isInit) {
            $this->Init($this->pconnect);
        }
        if ($dsqli->isClose) {
            $this->Open(FALSE);
            $dsqli->isClose = FALSE;
        }
        if (!empty($sql)) {
            $this->SetQuery($sql);
        } else {
            return FALSE;
        }
        if (is_array($this->parameters)) {
            foreach ($this->parameters as $key => $value) {
                $this->queryString = str_replace("@".$key, "'$value'", $this->queryString);
            }
        }
        //SQL语句安全检查
        if ($this->safeCheck) CheckSql($this->queryString, 'update');
        $t1 = ExecTime();
        $rs = mysqli_query($this->linkID, $this->queryString);
        //查询性能测试
        if ($this->recordLog) {
            $queryTime = ExecTime() - $t1;
            $this->RecordLog($queryTime);
        }
        if (DEBUG_LEVEL === TRUE) {
            $queryTime = ExecTime() - $t1;
            if (PHP_SAPI === 'cli') {
                echo "执行SQL：".$this->queryString."，执行时间：{$queryTime}\r\n";
            } else {
                echo DedeAlert("执行SQL：".$this->queryString."，执行时间：{$queryTime}", ALERT_SUCCESS);
            }
        }
        return $rs;
    }
    //执行一个返回影响记录条数的SQL语句，如update,delete,insert等
    function ExecuteNoneQuery2($sql = '')
    {
        global $dsqli;
        if (!$dsqli->isInit) {
            $this->Init($this->pconnect);
        }
        if ($dsqli->isClose) {
            $this->Open(FALSE);
            $dsqli->isClose = FALSE;
        }
        if (!empty($sql)) {
            $this->SetQuery($sql);
        }
        if (is_array($this->parameters)) {
            foreach ($this->parameters as $key => $value) {
                $this->queryString = str_replace("@".$key, "'$value'", $this->queryString);
            }
        }
        $t1 = ExecTime();
        mysqli_query($this->linkID, $this->queryString);
        //查询性能测试
        if ($this->recordLog) {
            $queryTime = ExecTime() - $t1;
            $this->RecordLog($queryTime);
            //echo $this->queryString."--{$queryTime}<hr/>\r\n";
        }
        if (DEBUG_LEVEL === TRUE) {
            $queryTime = ExecTime() - $t1;
            if (PHP_SAPI === 'cli') {
                echo "执行SQL：".$this->queryString."，执行时间：{$queryTime}\r\n";
            } else {
                echo DedeAlert("执行SQL：".$this->queryString."，执行时间：{$queryTime}", ALERT_SUCCESS);
            }
        }
        return mysqli_affected_rows($this->linkID);
    }
    function ExecNoneQuery($sql = '')
    {
        return $this->ExecuteNoneQuery($sql);
    }
    function GetFetchRow($id = 'me')
    {
        return @mysqli_fetch_row($this->result[$id]);
    }
    function GetAffectedRows()
    {
        return mysqli_affected_rows($this->linkID);
    }
    //执行一个带返回结果的SQL语句，如SELECT，SHOW等
    function Execute($id = "me", $sql = '')
    {
        global $dsqli;
        if (!$dsqli->isInit) {
            $this->Init($this->pconnect);
        }
        if ($dsqli->isClose) {
            $this->Open(FALSE);
            $dsqli->isClose = FALSE;
        }
        if (!empty($sql)) {
            $this->SetQuery($sql);
        }
        //SQL语句安全检查
        if ($this->safeCheck) {
            CheckSql($this->queryString);
        }
        $t1 = ExecTime();
        //var_dump($this->queryString);
        $this->result[$id] = mysqli_query($this->linkID, $this->queryString);
        //var_dump(mysql_error());
        //查询性能测试
        if ($this->recordLog) {
            $queryTime = ExecTime() - $t1;
            $this->RecordLog($queryTime);
            //echo $this->queryString."--{$queryTime}<hr/>\r\n";
        }
        if (DEBUG_LEVEL === TRUE) {
            $queryTime = ExecTime() - $t1;
            if (PHP_SAPI === 'cli') {
                echo "执行SQL：".$this->queryString."，执行时间：{$queryTime}\r\n";
            } else {
                echo DedeAlert("执行SQL：".$this->queryString."，执行时间：{$queryTime}", ALERT_SUCCESS);
            }
        }
        if ($this->result[$id] === FALSE) {
            $this->DisplayError(mysqli_error($this->linkID)." <br>Error sql:".$this->queryString."");
        }
    }
    function Query($id = "me", $sql = '')
    {
        $this->Execute($id, $sql);
    }
    //执行一个SQL语句,返回前一条记录或仅返回一条记录
    function GetOne($sql = '', $acctype = MYSQLI_ASSOC)
    {
        global $dsqli;
        //$t1 = ExecTime();
        if (!$dsqli->isInit) {
            $this->Init($this->pconnect);
        }
        //echo ExecTime() - $t1;
        if ($dsqli->isClose) {
            $this->Open(FALSE);
            $dsqli->isClose = FALSE;
        }
        if (!empty($sql)) {
            if (!preg_match("/LIMIT/i", $sql)) $this->SetQuery(preg_replace("/[,;]$/i", '', trim($sql))." LIMIT 0,1;");
            else $this->SetQuery($sql);
        }
        $this->Execute("one");
        $arr = $this->GetArray("one", $acctype);
        if (!is_array($arr)) {
            return '';
        } else {
            @mysqli_free_result($this->result["one"]);
            return ($arr);
        }
    }
    //执行一个不与任何表名有关的SQL语句,Create等
    function ExecuteSafeQuery($sql, $id = "me")
    {
        global $dsqli;
        if (!$dsqli->isInit) {
            $this->Init($this->pconnect);
        }
        if ($dsqli->isClose) {
            $this->Open(FALSE);
            $dsqli->isClose = FALSE;
        }
        $this->result[$id] = @mysqli_query($sql, $this->linkID);
    }
    //返回当前的一条记录并把游标移向下一记录
    //MYSQLI_ASSOC、MYSQLI_NUM、MYSQLI_BOTH
    function GetArray($id = "me", $acctype = MYSQLI_ASSOC)
    {
        //var_dump($this->result);
        if ($this->result[$id] === 0) {
            return FALSE;
        } else {
            return @mysqli_fetch_array($this->result[$id], $acctype);
        }
    }
    function GetObject($id = "me")
    {
        if ($this->result[$id] === 0) {
            return FALSE;
        } else {
            return mysqli_fetch_object($this->result[$id]);
        }
    }
    //检测是否存在某数据表
    function IsTable($tbname)
    {
        global $dsqli;
        $prefix = "#@__";
        $tbname = str_replace($prefix, $GLOBALS['cfg_dbprefix'], $tbname);
        if (!preg_match('/^[\p{L}_][\p{L}\p{N}@$#\-_]*$/u', $tbname)) {
            return FALSE;
        }
        if (!$dsqli->isInit) {
            $this->Init($this->pconnect);
        }
        if (mysqli_num_rows(@mysqli_query($this->linkID, "SHOW TABLES LIKE '".$tbname."'"))) {
            return TRUE;
        }
        return FALSE;
    }
    //获得MySql的版本号
    function GetVersion($isformat = TRUE)
    {
        global $dsqli;
        if (!$dsqli->isInit) {
            $this->Init($this->pconnect);
        }
        if ($dsqli->isClose) {
            $this->Open(FALSE);
            $dsqli->isClose = FALSE;
        }
        $rs = mysqli_query($this->linkID, "SELECT VERSION();");
        $row = mysqli_fetch_array($rs);
        $mysql_version = $row[0];
        mysqli_free_result($rs);
        if ($isformat) {
            $mysql_versions = explode(".", trim($mysql_version));
            $mysql_version = number_format($mysql_versions[0].".".$mysql_versions[1], 2);
        }
        return $mysql_version;
    }
    //获取特定表的信息
    function GetTableFields($tbname, $id = "me")
    {
        global $dsqli;
        if (!$dsqli->isInit) {
            $this->Init($this->pconnect);
        }
        $prefix = "#@__";
        $tbname = str_replace($prefix, $GLOBALS['cfg_dbprefix'], $tbname);
        $query = "SELECT * FROM {$tbname} LIMIT 0,1";
        $this->result[$id] = mysqli_query($this->linkID, $query);
    }
    //获取字段详细信息
    function GetFieldObject($id = "me")
    {
        return mysqli_fetch_field($this->result[$id]);
    }
    //获得查询的总记录数
    function GetTotalRow($id = "me")
    {
        if ($this->result[$id] === 0) {
            return -1;
        } else {
            return @mysqli_num_rows($this->result[$id]);
        }
    }
    //获取上一步INSERT操作产生的id
    function GetLastID()
    {
        //如果 AUTO_INCREMENT 的列的类型是 BIGINT，则 mysqli_insert_id() 返回的值不正确
        //可以在 SQL 查询中用 MySQL 内部的 SQL 函数 LAST_INSERT_ID() 来替代
        //$rs = mysqli_query($this->linkID, "Select LAST_INSERT_ID() as lid");
        //$row = mysqli_fetch_array($rs);
        //return $row["lid"];
        return mysqli_insert_id($this->linkID);
    }
    //释放记录集占用的资源
    function FreeResult($id = "me")
    {
        @mysqli_free_result($this->result[$id]);
    }
    function FreeResultAll()
    {
        if (!is_array($this->result)) {
            return '';
        }
        foreach ($this->result as $vv) {
            if ($vv) {
                @mysqli_free_result($vv);
            }
        }
    }
    //设置SQL语句，会自动把SQL语句里的#@__替换为$this->dbPrefix(在配置文件中为$cfg_dbprefix)
    function SetQuery($sql)
    {
        $prefix = "#@__";
        $sql = str_replace($prefix, $GLOBALS['cfg_dbprefix'], $sql);
        $this->queryString = $sql;
    }
    function SetSql($sql)
    {
        $this->SetQuery($sql);
    }
    function RecordLog($runtime = 0)
    {
        global $cfg_cookie_encode;
        $enkey = substr(md5(substr($cfg_cookie_encode.'dedebiz', 0, 5)), 0, 10);
        $RecordLogFile = DEDEDATA.'/mysqli_record_log_'.$enkey.'.inc';
        $url = $this->GetCurUrl();
        $savemsg = <<<EOT

------------------------------------------
SQL:{$this->queryString}
Page:$url
Runtime:$runtime
EOT;
        $fp = @fopen($RecordLogFile, 'a');
        @fwrite($fp, $savemsg);
        @fclose($fp);
    }
    //显示数据链接错误信息
    function DisplayError($msg)
    {
        global $cfg_cookie_encode;
        $enkey = substr(md5(substr($cfg_cookie_encode.'dedebiz', 0, 5)), 0, 10);
        $errorTrackFile = DEDEDATA.'/mysqli_error_trace_'.$enkey.'.inc';
        if ($this->showError) {
            $msg = str_replace(array("\r","\n"),"",addslashes($msg));
            ShowMsg("{$msg}", "javascript:;", -1);
            exit;
        }
        $savemsg = 'Page: '.$this->GetCurUrl()."\r\nError: ".$msg."\r\nTime".date('Y-m-d H:i:s');
        //保存MySql错误日志
        $fp = @fopen($errorTrackFile, 'a');
        @fwrite($fp, '<'.'?php  exit();'."\r\n/*\r\n{$savemsg}\r\n*/\r\n?".">\r\n");
        @fclose($fp);
    }
    //获得当前的脚本网址
    function GetCurUrl()
    {
        if (!empty($_SERVER["REQUEST_URI"])) {
            $scriptName = $_SERVER["REQUEST_URI"];
            $nowurl = $scriptName;
        } else {
            $scriptName = $_SERVER["PHP_SELF"];
            if (empty($_SERVER["QUERY_STRING"])) {
                $nowurl = $scriptName;
            } else {
                $nowurl = $scriptName."?".$_SERVER["QUERY_STRING"];
            }
        }
        return $nowurl;
    }
}
//复制一个对象副本
function CopySQLiPoint(&$ndsql)
{
    $GLOBALS['dsqli'] = $ndsql;
}
?>