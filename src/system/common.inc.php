<?php
/**
 * @version        $Id: common.inc.php 3 17:44 2010-11-23 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
//生产环境使用production，如果采用dev模式，会有一些php的报错信息提示，便于开发调试
if (!defined('DEDE_ENVIRONMENT')) {
    define('DEDE_ENVIRONMENT', 'production');
}
if (!defined('DEBUG_LEVEL')) {
    define('DEBUG_LEVEL', FALSE);//如果设置为TRUE则会打印执行SQL的时间和标签加载时间方便调试
}
if (DEDE_ENVIRONMENT == 'production') {
    ini_set('display_errors', 0);
    if (version_compare(PHP_VERSION, '5.3', '>='))
    {
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
            exit('Request var not allow!');
        }
        $svar = addslashes($svar);
    }
    return $svar;
}
foreach (array('_GET', '_POST', '_COOKIE') as $_req) {
    foreach ($$_req as $_k => $_v) {
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
$isSafeMode = @ini_get("safe_mode");
if (preg_match('/windows/i', @getenv('OS'))) {
    $isSafeMode = false;
}
//系统配置参数
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
    session_save_path($sessSavePath);
}
//转换上传的文件相关的变量及安全处理、并引用前台通用的上传函数
if ($_FILES) {
    require_once(DEDEINC.'/uploadsafe.inc.php');
}
//数据库配置文件
require_once(DEDEDATA.'/common.inc.php');
if (!isset($cfg_dbtype)) {
    $cfg_dbtype = 'mysql';
}
//载入系统验证安全配置
if (file_exists(DEDEDATA.'/safe/inc_safe_config.php')) {
    require_once(DEDEDATA.'/safe/inc_safe_config.php');
    if (!empty($safe_faqs)) $safefaqs = unserialize($safe_faqs);
}
//Session跨域设置
if (!empty($cfg_domain_cookie)) {
    @session_set_cookie_params(0, '/', $cfg_domain_cookie);
}
//php5.1版本以上时区设置
//由于这个函数对于是php5.1以下版本并无意义，因此实际上的时间调用，应该用MyDate函数调用
if (PHP_VERSION > '5.1') {
    $time51 = $cfg_cli_time * -1;
    @date_default_timezone_set('Etc/GMT'.$time51);
}
$cfg_isUrlOpen = @ini_get("allow_url_fopen");
//用户访问的网站host
if (PHP_SAPI === 'cli') {
    $cfg_clihost = 'https://www.dedebiz.com';
} else {
    $cfg_clihost = 'http://'.$_SERVER['HTTP_HOST'];
}
//站点根目录
$cfg_basedir = preg_replace('#'.$cfg_cmspath.'\/system$#i', '', DEDEINC);
if ($cfg_multi_site == 'Y') {
    $cfg_mainsite = $cfg_basehost;
} else {
    $cfg_mainsite = '';
}
//模板的存放目录
$cfg_templets_dir = $cfg_cmspath.'/theme';
$cfg_templeturl = $cfg_mainsite.$cfg_templets_dir;
$cfg_templets_skin = empty($cfg_df_style) ? $cfg_mainsite.$cfg_templets_dir."/templets" : $cfg_mainsite.$cfg_templets_dir."/$cfg_df_style";
//cms安装目录的网址
$cfg_cmsurl = $cfg_mainsite.$cfg_cmspath;
//插件目录，这个目录是用于存放计数器、投票、评论等程序的必要动态程序
$cfg_plus_dir = $cfg_cmspath.'/apps';
$cfg_phpurl = $cfg_mainsite.$cfg_plus_dir;
$cfg_static_dir = $cfg_cmspath.'/static';
$cfg_staticurl = $cfg_mainsite.$cfg_static_dir;
$cfg_mobile_dir = $cfg_cmspath.'/m';
$cfg_mobileurl = $cfg_mainsite.$cfg_mobile_dir;
$cfg_data_dir = $cfg_cmspath.'/data';
$cfg_dataurl = $cfg_mainsite.$cfg_data_dir;
//会员目录
$cfg_member_dir = $cfg_cmspath.'/user';
$cfg_memberurl = $cfg_mainsite.$cfg_member_dir;
//专题列表的存放路径
$cfg_special = $cfg_cmspath.'/a/special';
$cfg_specialurl = $cfg_mainsite.$cfg_special;
//附件目录
$cfg_medias_dir = $cfg_cmspath.$cfg_medias_dir;
$cfg_mediasurl = $cfg_mainsite.$cfg_medias_dir;
//上传的普通图片的路径,建议按默认
$cfg_image_dir = $cfg_medias_dir.'/allimg';
//用户投稿图片存放目录
$cfg_user_dir = $cfg_medias_dir.'/userup';
//上传的软件目录
$cfg_soft_dir = $cfg_medias_dir.'/soft';
//上传的多媒体文件目录
$cfg_other_medias = $cfg_medias_dir.'/media';
//软件摘要信息，请不要删除，否则系统无法正确接收系统漏洞或升级信息
$cfg_version = 'V6';
$cfg_version_detail = '6.1.10beta'; //详细版本号
$cfg_soft_lang = 'utf-8';
$cfg_soft_public = 'base';
$cfg_softname = '织梦内容管理系统';
$cfg_soft_enname = 'DedeCMSV6';
$cfg_soft_devteam = 'DedeBIZ';
//文档的默认命名规则
$art_shortname = $cfg_df_ext = '.html';
$cfg_df_namerule = '{typedir}/{aid}'.$cfg_df_ext;
//新建目录的权限，如果您使用别的属性，本程不保证程序能顺利在Linux或Unix系统运行
if (isset($cfg_ftp_mkdir) && $cfg_ftp_mkdir == 'Y') {
    $cfg_dir_purview = '0755';
} else {
    $cfg_dir_purview = 0755;
}
//会员是否使用精简模式
$cfg_mb_lit = 'N';
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
//对全局分页传递参数进行过滤
if (isset($GLOBALS['PageNo'])) {
    $GLOBALS['PageNo'] = intval($GLOBALS['PageNo']);
}
if (isset($GLOBALS['TotalResult'])) {
    $GLOBALS['TotalResult'] = intval($GLOBALS['TotalResult']);
}
if (!isset($cfg_NotPrintHead)) {
    if (PHP_SAPI != 'cli') {
        header("Content-Type: text/html; charset={$cfg_soft_lang}");
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
//载入小助手配置,并对其进行默认初始化
$cfg_helper_autoload = array(
    'charset',    /* 编码小助手 */
    'channelunit',/* 模型单元小助手 */
    'string',     /* 字符串小助手 */
    'time',       /* 日期小助手 */
    'file',       /* 文件小助手 */
    'util',       /* 单元小助手 */
    'validate',   /* 数据验证小助手 */
    'filter',     /* 过滤器小助手 */
    'cookie',     /* cookies小助手 */
    'debug',      /* 调试小助手 */
    'archive',    /* 文档小助手 */
    'upload',     /* 上传小助手 */
    'extend',     /* 扩展小助手 */
);
//初始化小助手
helper($cfg_helper_autoload);