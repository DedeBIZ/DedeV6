<?php
if (!defined('DEDEINC')) exit('dedebiz');
/**
 * 系统底层数据库核心类
 * 调用这个类前，请先设定这些外部变量
 * $GLOBALS['cfg_dbhost'];
 * $GLOBALS['cfg_dbuser'];
 * $GLOBALS['cfg_dbpwd'];
 * $GLOBALS['cfg_dbname'];
 * $GLOBALS['cfg_dbprefix'];
 *
 * @version        $id:dedesqlite.class.php 15:00 2011-1-21 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
@set_time_limit(0);
if (!extension_loaded("sqlite3")) {
    ShowMsg("尚未发现开启sqlite3模块，请在php.ini中启用`extension=sqlite3`","javasctipt:;",-1) ;
    exit;
}
//在工程所有文件中均不需要单独初始化这个类，可直接用 $dsql或$db进行操作
//为了防止错误，操作完后不必关闭数据库
$dsql = $dsqlitete = $db = new DedeSqlite(FALSE);
/**
 * Dede SQLite3数据库类
 *
 * @package        DedeSqli
 * @subpackage     DedeBIZ.Libraries
 * @link           https://www.dedebiz.com
 */
if (!defined('MYSQL_BOTH')) {
    define('MYSQL_BOTH', MYSQLI_BOTH);
}
if (!defined('MYSQL_ASSOC')) {
    define('MYSQL_ASSOC', SQLITE3_ASSOC);
}
if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}
class DedeSqlite
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
    var $_fixObject;
    var $_fieldIdx = 1; //这里最好是数组，对应id，但由于用的地方不多，暂时先这样处理
    //用外部定义的变量初始类，并连接数据库
    function __construct($pconnect = FALSE, $nconnect = FALSE)
    {
        $this->isClose = FALSE;
        $this->safeCheck = TRUE;
        $this->pconnect = $pconnect;
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
        $this->dbHost   =  $GLOBALS['cfg_dbhost'];
        $this->dbUser   =  $GLOBALS['cfg_dbuser'];
        $this->dbPwd    =  $GLOBALS['cfg_dbpwd'];
        $this->dbName   =  $GLOBALS['cfg_dbname'];
        $this->dbPrefix =  $GLOBALS['cfg_dbprefix'];
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
    //设置SQL里的参数
    function SetParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }
    //连接数据库
    function Open($pconnect = FALSE)
    {
        global $dsqlite;
        //连接数据库
        if ($dsqlite && !$dsqlite->isClose && $dsqlite->isInit) {
            $this->linkID = $dsqlite->linkID;
        } else {
            $this->linkID = new SQLite3(DEDEDATA.'/'.$this->dbName.'.db');
            //复制一个对象副本
            CopySQLiPoint($this);
        }
        //处理错误，成功连接则选择数据库
        if (!$this->linkID) {
            $this->DisplayError("系统提示：<span class='text-primary'>连接数据库失败，可能数据库密码不对或数据库服务器出错</span>");
            exit();
        }
        $this->isInit = TRUE;
        return TRUE;
    }
    //为了防止采集等需要较长运行时间的程序超时，在运行这类程序时设置系统等待和交互时间
    function SetLongLink()
    {
        //@mysqli_query("SET interactive_timeout=3600, wait_timeout=3600 ;", $this->linkID);
    }
    //获得错误描述
    function GetError()
    {
        return $this->linkID->lastErrorMsg();
    }
    //关闭数据库
    //mysql能自动管理非持久连接的连接池
    //实际上关闭并无意义并且容易出错，所以取消这函数
    function Close($isok = FALSE)
    {
        $this->FreeResultAll();
        if ($isok) {
            $this->linkID->close();
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
    }
    function Esc($_str)
    {
        global $dsqlite;
        if (!$dsqlite->isInit) {
            $this->Init($this->pconnect);
        }
        return $this->linkID->escapeString($_str);
    }
    //执行一个不返回结果的SQL语句，如update,delete,insert等
    function ExecuteNoneQuery($sql = '')
    {
        global $dsqlite;
        if (!@$dsqlite->isInit) {
            $this->Init($this->pconnect);
        }
        if ($dsqlite->isClose) {
            $this->Open(FALSE);
            $dsqlite->isClose = FALSE;
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
        $rs = $this->linkID->exec($this->queryString);
        if ($rs === false) {
            var_dump_cli("Error in fetch ".$this->linkID->lastErrorMsg().",SQL:{$this->queryString}");
        }
        //查询性能测试
        if ($this->recordLog) {
            $queryTime = ExecTime() - $t1;
            $this->RecordLog($queryTime);
            //echo $this->queryString."--{$queryTime}<hr/>\r\n";
        }
        return $rs;
    }
    //执行一个返回影响记录条数的SQL语句，如update,delete,insert等
    function ExecuteNoneQuery2($sql = '')
    {
        global $dsqlite;
        if (!$dsqlite->isInit) {
            $this->Init($this->pconnect);
        }
        if ($dsqlite->isClose) {
            $this->Open(FALSE);
            $dsqlite->isClose = FALSE;
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
        $this->linkID->exec($this->queryString);
        //查询性能测试
        if ($this->recordLog) {
            $queryTime = ExecTime() - $t1;
            $this->RecordLog($queryTime);
            //echo $this->queryString."--{$queryTime}<hr/>\r\n";
        }
        return $this->linkID->changes();
    }
    function ExecNoneQuery($sql = '')
    {
        return $this->ExecuteNoneQuery($sql);
    }
    function GetFetchRow($id = 'me')
    {
        return $this->result[$id]->numColumns();
    }
    function GetAffectedRows()
    {
        return $this->linkID->changes();
    }
    //执行一个带返回结果的SQL语句，如SELECT，SHOW等
    function Execute($id = "me", $sql = '')
    {
        global $dsqlite;
        if (!@$dsqlite->isInit) {
            $this->Init($this->pconnect);
        }
        if ($dsqlite->isClose) {
            $this->Open(FALSE);
            $dsqlite->isClose = FALSE;
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
        $this->result[$id] = $this->linkID->query($this->queryString);
        if (!$this->result[$id]) {
            $this->DisplayError("执行SQL错误:{$this->linkID->lastErrorMsg()}");
            exit;
        }
        //var_dump(mysql_error());
        //查询性能测试
        if ($this->recordLog) {
            $queryTime = ExecTime() - $t1;
            $this->RecordLog($queryTime);
            //echo $this->queryString."--{$queryTime}<hr/>\r\n";
        }
        if ($this->result[$id] === FALSE) {
            $this->DisplayError($this->linkID->lastErrorMsg()." <br>Error sql:<span class='text-primary'>".$this->queryString."</span>");
        }
    }
    function Query($id = "me", $sql = '')
    {
        $this->Execute($id, $sql);
    }
    //执行一个SQL语句,返回前一条记录或仅返回一条记录
    function GetOne($sql = '', $acctype = SQLITE3_ASSOC)
    {
        global $dsqlite;
        if (!@$dsqlite->isInit) {
            $this->Init($this->pconnect);
        }
        if ($dsqlite->isClose) {
            $this->Open(FALSE);
            $dsqlite->isClose = FALSE;
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
            $this->result["one"]->reset();
            return ($arr);
        }
    }
    //执行一个不与任何表名有关的SQL语句,Create等
    function ExecuteSafeQuery($sql, $id = "me")
    {
        global $dsqlite;
        if (!$dsqlite->isInit) {
            $this->Init($this->pconnect);
        }
        if ($dsqlite->isClose) {
            $this->Open(FALSE);
            $dsqlite->isClose = FALSE;
        }
        $this->result[$id] = $this->linkID->query($sql);
    }
    //返回当前的一条记录并把游标移向下一记录
    //SQLITE3_ASSOC、SQLITE3_NUM、SQLITE3_BOTH
    function GetArray($id = "me", $acctype = SQLITE3_ASSOC)
    {
        switch ($acctype) {
            case MYSQL_ASSOC:
                $acctype = SQLITE3_ASSOC;
                break;
            case MYSQL_NUM:
                $acctype = SQLITE3_NUM;
                break;
            default:
                $acctype = SQLITE3_BOTH;
                break;
        }
        if ($this->result[$id] === 0) {
            return FALSE;
        } else {
            $rs = $this->result[$id]->fetchArray($acctype);
            if (!$rs) {
                $this->result[$id] = 0;
                return false;
            }
            return $rs;
        }
    }
    function GetObject($id = "me")
    {
        if (!isset($this->_fixObject[$id])) {
            $this->_fixObject[$id] = array();
            while ($row = $this->result[$id]->fetchArray(SQLITE3_ASSOC)) {
                $this->_fixObject[$id][] = (object)$row;
            }
            $this->result[$id]->reset();
        }
        return array_shift($this->_fixObject[$id]);
    }
    //检测是否存在某数据表
    function IsTable($tbname)
    {
        global $dsqlite;
        if (!$dsqlite->isInit) {
            $this->Init($this->pconnect);
        }
        $prefix = "#@__";
        $tbname = str_replace($prefix, $GLOBALS['cfg_dbprefix'], $tbname);
        $row = $this->linkID->querySingle("PRAGMA table_info({$tbname});");
        if ($row !== null) {
            return TRUE;
        }
        return FALSE;
    }
    //获得MySql的版本号
    function GetVersion($isformat = TRUE)
    {
        global $dsqlite;
        if (!@$dsqlite->isInit) {
            $this->Init($this->pconnect);
        }
        if ($dsqlite->isClose) {
            $this->Open(FALSE);
            $dsqlite->isClose = FALSE;
        }
        $rs = $this->linkID->querySingle("select sqlite_version();");
        $sqlite_version = $rs;
        if ($isformat) {
            $sqlite_versions = explode(".", trim($sqlite_version));
            $sqlite_version = number_format($sqlite_versions[0].".".$sqlite_versions[1], 2);
        }
        return $sqlite_version;
    }
    //获取特定表的信息
    function GetTableFields($tbname, $id = "me")
    {
        global $dsqlite;
        if (!$dsqlite->isInit) {
            $this->Init($this->pconnect);
        }
        $prefix = "#@__";
        $tbname = str_replace($prefix, $GLOBALS['cfg_dbprefix'], $tbname);
        $query = "SELECT * FROM {$tbname} LIMIT 1";
        $this->result[$id] = $this->linkID->query($query);
    }
    //获取字段详细信息
    function GetFieldObject($id = "me")
    {
        if(!$this->result[$id]) {
            return false;
        }
        $cols = $this->result[$id]->numColumns();
        if ($this->_fieldIdx >= $cols) {
            $this->_fieldIdx = 1;
            return false;
        }
        for ($i = 1; $i <= $cols; $i++) {
            $field = new stdClass;
            $n = $this->result[$id]->columnName($i);
            $field->name = $n;
            if ($this->_fieldIdx === $i) {
                $this->_fieldIdx++;
                return $field;
            }
        }
        return false;
    }
    //获得查询的总记录数
    function GetTotalRow($id = "me")
    {
        $queryString = preg_replace("/SELECT(.*)FROM/isU", 'SELECT count(*) as dd FROM', $this->queryString);
        $rs = $this->linkID->query($queryString);
        $row = $rs->fetchArray();
        return $row['dd'];
    }
    //获取上一步INSERT操作产生的id
    function GetLastID()
    {
        //如果 AUTO_INCREMENT 的列的类型是 BIGINT，则 mysqli_insert_id() 返回的值不正确
        //可以在 SQL 查询中用 MySQL 内部的 SQL 函数 LAST_INSERT_ID() 来替代
        //$rs = mysqli_query($this->linkID, "Select LAST_INSERT_ID() as lid");
        //$row = mysqli_fetch_array($rs);
        //return $row["lid"];
        return $this->linkID->lastInsertRowID();
    }
    //释放记录集占用的资源
    function FreeResult($id = "me")
    {
        if ($this->result[$id]) {
            @$this->result[$id]->reset();
        }
    }
    function FreeResultAll()
    {
        if (!is_array($this->result)) {
            return '';
        }
        foreach ($this->result as $kk => $vv) {
            if ($vv) {
                @$vv->reset();
            }
        }
    }
    //设置SQL语句，会自动把SQL语句里的#@__替换为$this->dbPrefix(在配置文件中为$cfg_dbprefix)
    function SetQuery($sql)
    {
        $prefix = "#@__";
        $sql = str_replace($prefix, $GLOBALS['cfg_dbprefix'], $sql);
        $this->queryString = $sql;
        //$this->queryString = preg_replace("/CONCAT\(',', arc.typeid2, ','\)/i","printf(',%s,', arc.typeid2)",$this->queryString);
        if (preg_match("/CONCAT\(([^\)]*?)\)/i", $this->queryString, $matches)) {
            $this->queryString = preg_replace("/CONCAT\(([^\)]*?)\)/i", str_replace(",", "||", $matches[1]), $this->queryString);
            $this->queryString = str_replace("'||'", "','", $this->queryString);
        }
        $this->queryString = preg_replace("/FIND_IN_SET\('([\w]+)', arc.flag\)>0/i", "(',' || arc.flag || ',') LIKE '%,\\1,%'", $this->queryString);
        $this->queryString = preg_replace("/FIND_IN_SET\('([\w]+)', arc.flag\)<1/i", "(',' || arc.flag || ',') NOT LIKE '%,\\1,%'", $this->queryString);
        if (preg_match("/CREATE TABLE/i", $this->queryString)) {
            $this->queryString = preg_replace("/[\r\n]/", '', $this->queryString);
            $this->queryString = preg_replace('/character set (.*?) /i', '', $this->queryString);
            $this->queryString = preg_replace('/unsigned/i', '', $this->queryString);
            $this->queryString = str_replace('TYPE=MyISAM', '', $this->queryString);
            $this->queryString = preg_replace('/TINYINT\(([\d]+)\)/i', 'INTEGER', $this->queryString);
            $this->queryString = preg_replace('/mediumint\(([\d]+)\)/i', 'INTEGER', $this->queryString);
            $this->queryString = preg_replace('/smallint\(([\d]+)\)/i', 'INTEGER', $this->queryString);
            $this->queryString = preg_replace('/int\(([\d]+)\)/i', 'INTEGER', $this->queryString);
            $this->queryString = preg_replace('/auto_increment/i', 'PRIMARY KEY AUTOINCREMENT', $this->queryString);
            $this->queryString = preg_replace('/, KEY(.*?)MyISAM;/i', '', $this->queryString);
            $this->queryString = preg_replace('/, KEY(.*?);/i', ');', $this->queryString);
            $this->queryString = preg_replace('/, UNIQUE KEY(.*?);/i', ');', $this->queryString);
            $this->queryString = preg_replace('/set\(([^\)]*?)\)/', 'varchar', $this->queryString);
            $this->queryString = preg_replace('/enum\(([^\)]*?)\)/', 'varchar', $this->queryString);
            if (preg_match("/PRIMARY KEY AUTOINCREMENT/", $this->queryString)) {
                $this->queryString = preg_replace('/,([\t\s ]+)PRIMARY KEY  \(`([0-9a-zA-Z]+)`\)/i', '', $this->queryString);
                $this->queryString = str_replace(',	PRIMARY KEY (`id`)', '', $this->queryString);
            }
        }
        $this->queryString = preg_replace("/SHOW fields FROM `([\w]+)`/i", "PRAGMA table_info('\\1') ", $this->queryString);
        $this->queryString = preg_replace("/SHOW CREATE TABLE .([\w]+)/i", "SELECT 0,sql FROM sqlite_master WHERE name='\\1'; ", $this->queryString);
        //var_dump($this->queryString);
        $this->queryString = preg_replace("/Show Tables/i", "SELECT name FROM sqlite_master WHERE type = \"table\"", $this->queryString);
        $this->queryString = str_replace("\'", "\"", $this->queryString);
        $this->queryString = str_replace('\t\n', "", $this->queryString);
        //var_dump($this->queryString);
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
        $errorTrackFile = DEDEDATA.'/sqlite_error_trace_'.$enkey.'.inc';
        if ($this->showError) {
            $msg = str_replace(array("\r","\n"),"",addslashes($msg));
            ShowMsg("{$msg}", "javascript:;", -1);
            exit;
        }
        $savemsg = 'Page: '.$this->GetCurUrl()."\r\nError: ".$msg."\r\nTime".date('Y-m-d H:i:s');
        //保存SQLite错误日志
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
    $GLOBALS['dsqlite'] = $ndsql;
}
