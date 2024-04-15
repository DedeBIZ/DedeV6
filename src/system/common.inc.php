<?php
/**
 * @version        $id:common.inc.php 2024-04-15 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
//系统默认运行模式为安全模式，模板管理、标签管理、数据库管理、模块管理等功能已暂停，如果您需要这些功能，DEDEBIZ_SAFE_MODE后面值`TRUE`改为`FALSE`恢复使用
define('DEDEBIZ_SAFE_MODE', TRUE);
//生产环境使用`production`，如果采用`dev`模式，会有一些php的报错信息提示，用于开发调试
if (!defined('DEDE_ENVIRONMENT')) {
    define('DEDE_ENVIRONMENT', 'production');
}
if (!defined('DEBUG_LEVEL')) {
    if (DEDE_ENVIRONMENT == 'production') {
        define('DEBUG_LEVEL', FALSE);
    } else {
        define('DEBUG_LEVEL', TRUE);
    }
}
if (DEDE_ENVIRONMENT == 'production') {
    ini_set('display_errors', 0);
    if (version_compare(PHP_VERSION, '5.3', '>=')) {
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
    } else {
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
    }
} else {
    error_reporting(-1);
    ini_set('display_errors', 1);
}
define('DEDEINC', str_replace("\\", '/', dirname(__FILE__)));
define('DEDEROOT', str_replace("\\", '/', substr(DEDEINC, 0, -6))); //站点根目录
define('DEDEDATA', substr(DEDEINC, 0, -6).'data');
define('DEDESTATIC', DEDEROOT.'/static');
define('DEDEMEMBER', DEDEROOT.'/user');
define('DEDETEMPLATE', DEDEROOT.'/theme');
define('DEDEBIZURL', "https://www.dedebiz.com");//DedeBiz商业支持
define('DEDEBIZCDN', "https://cdn.dedebiz.com");//DedeBizCDN镜像
define('DEDEVER', 6);//当前系统大版本
define('DEDEPUB', '
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvupO2Lixns34bBqwTzK0
9wA9sfGBdgc03zh1sUacieJBikx08e7xmkJbMF81jb/YfNGW/+iJ3qHULdc9Dtd3
+FsnHG+tUDnzjkPnVVmnrjucQqfHRRVKKAgXOWxtuRKUVF3NDjiJtDAf5Y2BMAhw
oqzeepye5I4mWyO4A8/V2ougO+xDK426MIf1dq+W59NVZj8k+zeZrbPh7+fPFw4u
PwAMpkTJJ9nwNOO6saH2eMGaQ3oxZIQ+SmminDB8miI/+hwIn2hNmaHFuur0OGlB
NQabUzX9JoYtXqPcpZRT7ymHrppU0KFdUSEJiW0utTWJo0HrDOBIT5qWlM0MP9p/
PwIDAQAB
-----END PUBLIC KEY-----
');//DedeBIZ系统公钥
define('DEDECDNURL', 'https://cdn.dedebiz.com');//默认静态资源地址
if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
    if (!function_exists('session_register')) {
        function session_register()
        {
            $args = func_get_args();
            foreach ($args as $key) {
                $_SESSION[$key] = $GLOBALS[$key];
            }
        }
        function session_is_registered($key)
        {
            return isset($_SESSION[$key]);
        }
        function session_unregister($key)
        {
            unset($_SESSION[$key]);
        }
    }
}
//是否启用mb_substr替换cn_substr来提高效率
$cfg_is_mb = $cfg_is_iconv = FALSE;
if (function_exists('mb_substr')) $cfg_is_mb = TRUE;
if (function_exists('iconv_substr')) $cfg_is_iconv = TRUE;
function _RunMagicQuotes(&$svar)
{
    if (is_array($svar)) {
        foreach ($svar as $_k => $_v) {
            if ($_k == 'nvarname') continue;
            _RunMagicQuotes($_k);
            $svar[$_k] = _RunMagicQuotes($_v);
        }
    } else {
        if (strlen($svar) > 0 && preg_match('#^(cfg_|GLOBALS|_GET|_REQUEST|_POST|_COOKIE|_SESSION)#', $svar)) {
            exit('The requested operation is forbidden');
        }
        $svar = addslashes($svar);
    }
    return $svar;
}
foreach (array('_GET', '_POST', '_COOKIE') as $_req) {
    foreach ($$_req as $_k => $_v) {
        if (preg_match('#^(cfg_|GLOBALS|_GET|_REQUEST|_POST|_COOKIE|_SESSION)#', $_k)) {
            exit('The requested operation is forbidden');
        }
        if ($_k == 'nvarname') ${$_k} = $_v;
        else ${$_k} = _RunMagicQuotes($_v);
    }
}
//系统相关变量检测
if (!isset($needFilter)) {
    $needFilter = false;
}
$registerGlobals = @ini_get("register_globals");
$isUrlOpen = @ini_get("allow_url_fopen");
//系统配置参数
if (!file_exists(DEDEDATA."/config.cache.inc.php")) {
    die('DedeBIZ初始化失败，确保系统正确被安装');
}
require_once(DEDEDATA."/config.cache.inc.php");
//Session保存路径
$sessSaveHandler = @ini_get("session.save_handler");
if ($sessSaveHandler !== "files") {
    @ini_set("session.save_handler", "files");
}
$enkey = substr(md5(substr($cfg_cookie_encode, 0, 5)), 0, 10);
$sessSavePath = DEDEDATA."/sessions_{$enkey}";
if (!is_dir($sessSavePath)) mkdir($sessSavePath);
if (is_writeable($sessSavePath) && is_readable($sessSavePath)) {
    @session_save_path($sessSavePath);
}
require_once DEDEINC.'/dedealert.func.php';
//转换上传的文件相关的变量及安全处理，并引用前台通用的上传函数
if ($_FILES) {
    require_once(DEDEINC.'/uploadsafe.inc.php');
}
//数据库配置文件
if (file_exists(DEDEDATA.'/common.inc.php')) {
    require_once(DEDEDATA.'/common.inc.php');
} else {
    $cfg_dbtype = $cfg_dbhost = $cfg_dbname= $cfg_dbuser = $cfg_dbpwd = $cfg_dbprefix = $cfg_db_language ='';//数据库类型
}
if (!isset($cfg_dbtype)) {
    $cfg_dbtype = 'mysql';
}
//Session跨域设置
if (!empty($cfg_domain_cookie)) {
    @session_set_cookie_params(0, '/', $cfg_domain_cookie);
}
//php5.1版本以上时区设置，由于这个函数对于是php5.1以下版本并无意义，因此实际上的时间调用，应该用MyDate函数调用
if (PHP_VERSION > '5.1') {
    $time51 = $cfg_cli_time * -1;
    @date_default_timezone_set('Etc/GMT'.$time51);
}
$cfg_isUrlOpen = @ini_get("allow_url_fopen");
//会员浏览默认网址
if (PHP_SAPI === 'cli') {
    $cfg_clihost = 'https://www.dedebiz.com';
} else {
    $cfg_clihost = 'http://'.$_SERVER['HTTP_HOST'];
}
//站点根目录
$cfg_basedir = preg_replace('#'.'\/system$#i', '', DEDEINC);
if ($cfg_multi_site == 'Y') {
    $cfg_mainsite = $cfg_basehost;
} else {
    $cfg_mainsite = '';
}
//模板存放目录
$cfg_templets_dir = '/theme';
$cfg_templeturl = $cfg_mainsite.$cfg_templets_dir;
$cfg_templets_skin = empty($cfg_df_style) ? $cfg_mainsite.$cfg_templets_dir."/templets" : $cfg_mainsite.$cfg_templets_dir."/$cfg_df_style";
//安装目录网址
$cfg_cmsurl = $cfg_mainsite;
//模块插件目录
$cfg_plus_dir = '/apps';
$cfg_phpurl = $cfg_mainsite.$cfg_plus_dir;
//一些缓存配置数据存放目录
$cfg_data_dir = '/data';
$cfg_dataurl = $cfg_mainsite.$cfg_data_dir;
//专题存放目录
$cfg_special = '/a/special';
$cfg_specialurl = $cfg_mainsite.$cfg_special;
//会员会员目录
$cfg_member_dir = '/user';
$cfg_memberurl = $cfg_mainsite.$cfg_member_dir;
//静态文件存放目录
$cfg_static_dir = '/static';
$cfg_staticurl = $cfg_mainsite.$cfg_static_dir;
//上传图片存放目录，建议按默认
$cfg_image_dir = $cfg_medias_dir.'/allimg';
//会员投稿图片存放目录
$cfg_user_dir = $cfg_medias_dir.'/userup';
//上传软件存放目录
$cfg_soft_dir = $cfg_medias_dir.'/soft';
//上传多媒体文件存放目录
$cfg_other_medias = $cfg_medias_dir.'/media';
//附件目录
$cfg_medias_dir = $cfg_medias_dir;
$cfg_mediasurl = $cfg_mainsite.$cfg_medias_dir;
//程序信息摘要，请不要删除则系统无法接收升级信息
$cfg_version = 'V6';
$cfg_version_detail = '6.3.0';//详细版本号
$cfg_soft_lang = 'utf-8';
$cfg_soft_public = 'base';
$cfg_softname = '得德系统';
$cfg_soft_enname = 'DedeV6';
$cfg_soft_devteam = 'DedeBIZ';
//文档的默认命名规则
$art_shortname = $cfg_df_ext = '.html';
$cfg_df_namerule = '{typedir}/{aid}'.$cfg_df_ext;
//新建目录的权限，如果您使用别的属性，本程不保证程序能顺利在Linux或Unix系统运行
$cfg_dir_purview = 0755;
//Cookie设置
$cfg_cookie_samesite = 'Lax'; //samesite属性（Lax, Strict or None）
$cfg_cookie_secure = false;   //仅当存在安全的HTTPS连接时才会设置Cookie
$cfg_cookie_httponly = false; //只能通过HTTP(S)访问（无法通过JavaScript访问）
//特殊全局变量
$_sys_globals['curfile'] = '';
$_sys_globals['typeid'] = 0;
$_sys_globals['typename'] = '';
$_sys_globals['aid'] = 0;
if (empty($cfg_addon_savetype)) {
    $cfg_addon_savetype = 'Ymd';
}
if ($cfg_sendmail_bysmtp == 'Y' && !empty($cfg_smtp_usermail)) {
    $cfg_adminemail = $cfg_smtp_usermail;
}
//DedeBIZ商业化组件
require_once(DEDEINC.'/libraries/dedebiz.class.php');
//第三方SDKs
require_once(DEDEINC.'/sdks/include.php');
//对全局分页传递参数进行过滤
if (isset($GLOBALS['PageNo'])) {
    $GLOBALS['PageNo'] = intval($GLOBALS['PageNo']);
}
if (isset($GLOBALS['TotalResult'])) {
    $GLOBALS['TotalResult'] = intval($GLOBALS['TotalResult']);
}
if (!isset($cfg_NotPrintHead)) {
    if (PHP_SAPI != 'cli') {
        if (defined('IS_DEDEAPI')) {
            header("Content-Type:text/json; charset={$cfg_soft_lang}");
        } else {
            header("Content-Type:text/html; charset={$cfg_soft_lang}");
        }
    }
}
//自动加载类库处理
if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
    require_once(DEDEINC.'/autoload7.inc.php');
} else {
    require_once(DEDEINC.'/autoload.inc.php');
}
$cfg_biz_helpUrl = DEDEBIZURL."/help";
$cfg_biz_gitUrl = DEDEBIZURL."/git";
$cfg_biz_dedebizUrl = DEDEBIZURL;
//引入数据库类
if (!defined('MYSQL_BOTH')) {
    define('MYSQL_BOTH', MYSQLI_BOTH);
}
if (!defined('MYSQL_ASSOC')) {
    define('MYSQL_ASSOC', MYSQLI_ASSOC);
}
if (!defined('MYSQL_NUM')) {
    define('MYSQL_NUM', MYSQLI_NUM);
}
//全局常用函数
require_once(DEDEINC.'/common.func.php');
if ($GLOBALS['cfg_dbtype'] == 'mysql' || $GLOBALS['cfg_dbtype'] == 'mysqli') {
    require_once(DEDEINC.'/database/dedesqli.class.php');
} else {
    require_once(DEDEINC.'/database/dedesqlite.class.php');
}
//载入助手配置，并对其进行默认初始化
$cfg_helper_autoload = array(
    'charset',    /* 编码助手 */
    'channelunit',/* 模型单元助手 */
    'string',     /* 字符串助手 */
    'time',       /* 日期助手 */
    'file',       /* 文件助手 */
    'util',       /* 单元助手 */
    'validate',   /* 数据验证助手 */
    'filter',     /* 过滤器助手 */
    'cookie',     /* cookies助手 */
    'debug',      /* 调试助手 */
    'archive',    /* 文档助手 */
    'upload',     /* 上传助手 */
    'extend',     /* 扩展助手 */
    'code',       /* 代码助手 */
);
//初始化助手
helper($cfg_helper_autoload);
?>