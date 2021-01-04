<?php
/**
 * @version        $Id: index.php 2021-01-03 tianya $
 * @package        DedeBIZ.Install
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
@set_time_limit(0);
//error_reporting(E_ALL);
error_reporting(E_ALL || ~E_NOTICE);

$verMsg = 'V6';
$dfDbname = 'dedecmsv6';
$cfg_version_detail = '6.0.3'; // 详细版本号
$errmsg = '';

define('INSLOCKFILE', dirname(__FILE__).'/install_lock.txt');

$moduleCacheFile = dirname(__FILE__).'/modules.tmp.inc';

define('DEDEINC',dirname(__FILE__).'/../include');
define('DEDEDATA',dirname(__FILE__).'/../data');
define('DEDEROOT',preg_replace("#[\\\\\/]install#", '', dirname(__FILE__)));
header("Content-Type: text/html; charset=utf-8");

require_once(DEDEROOT.'/install/install.inc.php');
require_once(DEDEINC.'/zip.class.php');

foreach(Array('_GET','_POST','_COOKIE') as $_request)
{
    foreach($$_request as $_k => $_v) ${$_k} = RunMagicQuotes($_v);
}

require_once(DEDEINC.'/common.func.php');

if(file_exists(INSLOCKFILE))
{
    exit(" 程序已运行安装，如果你确定要重新安装，请先从FTP中删除 install/install_lock.txt！");
}

if(empty($step))
{
    $step = 1;
}
/*------------------------
使用协议书
function _1_Agreement()
------------------------*/
if($step==1)
{
    $arrMsg = array();
    if (!extension_loaded("openssl")) {
        $arrMsg[] = "OpenSSL未开启，将无法完成<a href='https://www.dedebiz.com' target='_blank'>DedeBIZ商业支持</a>";
    }
    if (!extension_loaded("sockets")) {
        $arrMsg[] = "Sockets未开启，将无法安装<a href='https://www.dedebiz.com/download#dedebiz' target='_blank'>DedeBIZ商业组件</a>";
    }
    if (!function_exists('mysqli_connect')) {
        $arrMsg[] = "MySQL不支持，将无法使用本系统";
    }
    if (!extension_loaded("gd")) {
        $arrMsg[] = "GD未开启，将无法使用验证码、二维码、图片水印等功能";
    }

    if(!empty($_SERVER['REQUEST_URI']))
    $scriptName = $_SERVER['REQUEST_URI'];
    else
    $scriptName = $_SERVER['PHP_SELF'];

    $basepath = preg_replace("#\/install(.*)$#i", '', $scriptName);

    if(!empty($_SERVER['HTTP_HOST']))
        $baseurl = 'http://'.$_SERVER['HTTP_HOST'];
    else
        $baseurl = "http://".$_SERVER['SERVER_NAME'];

    $chars='abcdefghigklmnopqrstuvwxwyABCDEFGHIGKLMNOPQRSTUVWXWY0123456789';
    $rnd_cookieEncode='';
    $length = rand(28,32);
    $max = strlen($chars) - 1;
    for($i = 0; $i < $length; $i++) {
        $rnd_cookieEncode .= $chars[mt_rand(0, $max)];
    }
    $module_local = DEDEDATA.'/module/';

    include('./templates/step-1.html');
    exit();
}
/*------------------------
普通安装
function _2_Setup()
------------------------*/
else if($step==2)
{
    $dbtype = empty($dbtype)? "mysql" : $dbtype;
    $dblang = "utf8";
    if (!in_array($dbtype,array("mysql", "sqlite"))) {
        die("当前数据库类型不支持");
    }
    if(!empty($_SERVER['HTTP_HOST']))
        $dfbaseurl = 'http://'.$_SERVER['HTTP_HOST'];
    else
        $dfbaseurl = "http://".$_SERVER['SERVER_NAME'];
    $dfbasepath = preg_replace("#\/install(.*)$#i", '', $scriptName);

    $dbhost = empty($dbhost)? "localhost" : $dbhost;
    $dbuser = empty($dbuser)? "root" : $dbuser;
    $dbuser = empty($dbuser)? "root" : $dbuser;
    $dbprefix = empty($dbprefix)? "dede_" : $dbprefix;
    $dbname = empty($dbname)? $dfDbname : $dbname;
    $adminuser = empty($adminuser)? "admin" : $adminuser;
    $adminpwd = empty($adminpwd)? "admin" : $adminpwd;
    $webname = empty($webname)? "我的网站" : $webname;
    $baseurl = empty($baseurl)? $dfbaseurl : $baseurl;
    $cmspath = empty($cmspath)? $dfbasepath : $cmspath;

    if ( $dbtype == 'sqlite' )
    {
        $db = new SQLite3(DEDEDATA.'/'.$dbname.'.db');
    } else {
        $dbtype = 'mysql';
        $conn = mysql_connect($dbhost,$dbuser,$dbpwd) or die("<script>alert('数据库服务器或登录密码无效，\\n\\n无法连接数据库，请重新设定！');history.go(-1);</script>");

        mysql_query("CREATE DATABASE IF NOT EXISTS `".$dbname."`;",$conn);

        mysql_select_db($dbname, $conn) or die("<script>alert('选择数据库失败，可能是你没权限，请预先创建一个数据库！');history.go(-1);</script>");

        //获得数据库版本信息
        $rs = mysql_query("SELECT VERSION();",$conn);
        $row = mysql_fetch_array($rs);
        $mysqlVersions = explode('.',trim($row[0]));
        $mysqlVersion = $mysqlVersions[0].".".$mysqlVersions[1];

        mysql_query("SET NAMES '$dblang',character_set_client=binary,sql_mode='';",$conn);
    }
    

    $fp = fopen(dirname(__FILE__)."/common.inc.php","r");
    $configStr1 = fread($fp,filesize(dirname(__FILE__)."/common.inc.php"));
    fclose($fp);

    $fp = fopen(dirname(__FILE__)."/config.cache.inc.php","r");
    $configStr2 = fread($fp,filesize(dirname(__FILE__)."/config.cache.inc.php"));
    fclose($fp);

    //common.inc.php
    $configStr1 = str_replace("~dbtype~",$dbtype,$configStr1);
    $configStr1 = str_replace("~dbhost~",$dbhost,$configStr1);
    $configStr1 = str_replace("~dbname~",$dbname,$configStr1);
    $configStr1 = str_replace("~dbuser~",$dbuser,$configStr1);
    $configStr1 = str_replace("~dbpwd~",$dbpwd,$configStr1);
    $configStr1 = str_replace("~dbprefix~",$dbprefix,$configStr1);
    $configStr1 = str_replace("~dblang~",$dblang,$configStr1);

    @chmod(DEDEDATA,0777);
    $fp = fopen(DEDEDATA."/common.inc.php","w") or die("<script>alert('写入配置失败，请检查../data目录是否可写入！');history.go(-1);</script>");
    fwrite($fp,$configStr1);
    fclose($fp);

    //config.cache.inc.php
    $cmspath = trim(preg_replace("#\/{1,}#", '/', $cmspath));
    if($cmspath!='' && !preg_match("#^\/#", $cmspath)) $cmspath = '/'.$cmspath;

    if($cmspath=='') $indexUrl = '/';
    else $indexUrl = $cmspath;

    $configStr2 = str_replace("~baseurl~",$baseurl,$configStr2);
    $configStr2 = str_replace("~basepath~",$cmspath,$configStr2);
    $configStr2 = str_replace("~indexurl~",$indexUrl,$configStr2);
    $configStr2 = str_replace("~cookieEncode~",$cookieencode,$configStr2);
    $configStr2 = str_replace("~webname~",$webname,$configStr2);
    $configStr2 = str_replace("~adminmail~",$adminmail,$configStr2);

    $fp = fopen(DEDEDATA.'/config.cache.inc.php','w');
    fwrite($fp,$configStr2);
    fclose($fp);

    $fp = fopen(DEDEDATA.'/config.cache.bak.php','w');
    fwrite($fp,$configStr2);
    fclose($fp);

    if($mysqlVersion >= 4.1)
    {
        $sql4tmp = "ENGINE=MyISAM DEFAULT CHARSET=".$dblang;
    }

    //创建数据表
    $query = '';
    $fp = fopen(dirname(__FILE__).'/sql-dftables.txt','r');
    while(!feof($fp))
    {
        $line = rtrim(fgets($fp,1024));
        if(preg_match("#;$#", $line))
        {
            $query .= $line."\n";
            $query = str_replace('#@__',$dbprefix,$query);
            if ( $dbtype == 'sqlite' )
            {
                $query = preg_replace('/character set (.*?) /i','',$query);
                $query = str_replace('unsigned','',$query);
                $query = str_replace('TYPE=MyISAM','',$query);
                
                $query = preg_replace ('/TINYINT\(([\d]+)\)/i','INTEGER',$query);
                $query = preg_replace ('/mediumint\(([\d]+)\)/i','INTEGER',$query);
                $query = preg_replace ('/smallint\(([\d]+)\)/i','INTEGER',$query);
                $query = preg_replace('/int\(([\d]+)\)/i','INTEGER',$query);
                $query = preg_replace('/auto_increment/i','PRIMARY KEY AUTOINCREMENT',$query);
                $query = preg_replace('/,  KEY(.*?)MyISAM;/','',$query);
                $query = preg_replace('/,  KEY(.*?);/',');',$query);
                $query = preg_replace('/,  UNIQUE KEY(.*?);/',');',$query);
                $query = preg_replace('/set\(([^\)]*?)\)/','varchar',$query);
                $query = preg_replace('/enum\(([^\)]*?)\)/','varchar',$query);
                if ( preg_match("/PRIMARY KEY AUTOINCREMENT/",$query) )
                {
                    $query = preg_replace('/,([\t\s ]+)PRIMARY KEY  \(`([0-9a-zA-Z]+)`\)/i','',$query);
                    $query = str_replace(',	PRIMARY KEY (`id`)','',$query);
                }
                $db->exec($query);
            } else {
                if($mysqlVersion < 4.1)
                {
                    $rs = mysql_query($query,$conn);
                } else {
                    if(preg_match('#CREATE#i', $query))
                    {
                        $rs = mysql_query(preg_replace("#TYPE=MyISAM#i",$sql4tmp,$query),$conn);
                    }
                    else
                    {
                        $rs = mysql_query($query,$conn);
                    }
                }
            }
    
            $query='';
        } else if(!preg_match("#^(\/\/|--)#", $line))
        {
            $query .= $line;
        }
    }
    fclose($fp);

    //导入默认数据
    $query = '';
    $fp = fopen(dirname(__FILE__).'/sql-dfdata.txt','r');
    while(!feof($fp))
    {
        $line = rtrim(fgets($fp, 1024));
        if(preg_match("#;$#", $line))
        {
            if ( $dbtype == 'sqlite' )
            {
                $query .= $line;
                $query = str_replace('#@__',$dbprefix,$query);
                $query = str_replace("\'","\"",$query);
                $db->exec($query);
            } else {
                $query .= $line;
                $query = str_replace('#@__',$dbprefix,$query);
                if($mysqlVersion < 4.1) $rs = mysql_query($query,$conn);
                else $rs = mysql_query(str_replace('#~lang~#',$dblang,$query),$conn);
            }
    
            $query='';
        } else if(!preg_match("#^(\/\/|--)#", $line))
        {
            $query .= $line;
        }
    }
    fclose($fp);

    //更新配置
    $cquery = "UPDATE `{$dbprefix}sysconfig` SET value='{$baseurl}' WHERE varname='cfg_basehost';";
    $dbtype == 'sqlite'?  $db->exec($cquery) : mysql_query($cquery,$conn);
    $cquery = "UPDATE `{$dbprefix}sysconfig` SET value='{$cmspath}' WHERE varname='cfg_cmspath';";
    $dbtype == 'sqlite'?  $db->exec($cquery) : mysql_query($cquery,$conn);
    $cquery = "UPDATE `{$dbprefix}sysconfig` SET value='{$indexUrl}' WHERE varname='cfg_indexurl';";
    $dbtype == 'sqlite'?  $db->exec($cquery) : mysql_query($cquery,$conn);
    $cquery = "UPDATE `{$dbprefix}sysconfig` SET value='{$cookieencode}' WHERE varname='cfg_cookie_encode';";
    $dbtype == 'sqlite'?  $db->exec($cquery) : mysql_query($cquery,$conn);
    $cquery = "UPDATE `{$dbprefix}sysconfig` SET value='{$webname}' WHERE varname='cfg_webname';";
    $dbtype == 'sqlite'?  $db->exec($cquery) : mysql_query($cquery,$conn);
    $cquery = "UPDATE `{$dbprefix}sysconfig` SET value='{$adminmail}' WHERE varname='cfg_adminemail';";
    $dbtype == 'sqlite'?  $db->exec($cquery) : mysql_query($cquery,$conn);

    //增加管理员帐号
    $adminquery = "INSERT INTO `{$dbprefix}admin` VALUES (1, 10, '$adminuser', '".substr(md5($adminpwd),5,20)."', 'admin', '', '', 0, '".time()."', '127.0.0.1');";
    $dbtype == 'sqlite'?  $db->exec($adminquery) : mysql_query($adminquery,$conn);

    //关连前台会员帐号
    $adminquery = "INSERT INTO `{$dbprefix}member` (`mid`,`mtype`,`userid`,`pwd`,`uname`,`sex`,`rank`,`money`,`email`,
                   `scores` ,`matt` ,`face`,`safequestion`,`safeanswer` ,`jointime` ,`joinip` ,`logintime` ,`loginip` )
               VALUES ('1','个人','$adminuser','".md5($adminpwd)."','$adminuser','男','100','0','','10000','10','','0','','".time()."','','0',''); ";
    $dbtype == 'sqlite'?  $db->exec($adminquery) : mysql_query($adminquery,$conn);

    $adminquery = "INSERT INTO `{$dbprefix}member_person` (`mid`,`onlynet`,`sex`,`uname`,`qq`,`msn`,`tel`,`mobile`,`place`,`oldplace`,`birthday`,`star`,
                   `income` , `education` , `height` , `bodytype` , `blood` , `vocation` , `smoke` , `marital` , `house` ,`drink` , `datingtype` , `language` , `nature` , `lovemsg` , `address`,`uptime`)
                VALUES ('1', '1', '男', '{$adminuser}', '', '', '', '', '0', '0','1980-01-01', '1', '0', '0', '160', '0', '0', '0', '0', '0', '0','0', '0', '', '', '', '','0'); ";
    $dbtype == 'sqlite'?  $db->exec($adminquery) : mysql_query($adminquery,$conn);

    $adminquery = "INSERT INTO `{$dbprefix}member_tj` (`mid`,`article`,`album`,`archives`,`homecount`,`pagecount`,`feedback`,`friend`,`stow`)
                     VALUES ('1','0','0','0','0','0','0','0','0'); ";
    $dbtype == 'sqlite'?  $db->exec($adminquery): mysql_query($adminquery,$conn);

    $adminquery = "INSERT INTO `{$dbprefix}member_space`(`mid` ,`pagesize` ,`matt` ,`spacename` ,`spacelogo` ,`spacestyle`, `sign` ,`spacenews`)
                VALUES('1','10','0','{$adminuser}的空间','','person','',''); ";
    $dbtype == 'sqlite'?  $db->exec($adminquery) : mysql_query($adminquery,$conn);

    //锁定安装程序
    $fp = fopen($insLockfile,'w');
    fwrite($fp,'ok');
    fclose($fp);
    header('Location:../dede/index.php');
    exit();
}
/*------------------------
检测数据库是否有效
function _10_TestDbPwd()
------------------------*/
else if($step==10)
{
    header("Pragma:no-cache\r\n");
    header("Cache-Control:no-cache\r\n");
    header("Expires:0\r\n");
    $conn = @mysql_connect($dbhost,$dbuser,$dbpwd);
    $info = "";
    if($conn)
    {
		if(empty($dbname)){
			$info = "信息正确";
		}else{
			$info = mysql_select_db($dbname,$conn)? "数据库已经存在，系统将覆盖数据库": "数据库不存在,系统将自动创建";
        }
        $result = array(
            "code" => 200,
            "data" => $info,
        );
        echo json_encode($result);
    }
    else
    {
        $result = array(
            "code" => -1,
            "data" => "数据库连接失败！",
        );
        echo json_encode($result);
    }
    @mysql_close($conn);
    exit();
}
