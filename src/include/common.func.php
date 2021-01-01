<?php

/**
 * 系统核心函数存放文件
 * @version        $Id: common.func.php 4 16:39 2010年7月6日Z tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
if (!defined('DEDEINC')) exit('dedebiz');

if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
    if (!function_exists('mysql_connect') and function_exists('mysqli_connect')) {
        function mysql_connect($server, $username, $password)
        {
            return mysqli_connect($server, $username, $password);
        }
    }

    if (!function_exists('mysql_query') and function_exists('mysqli_query')) {
        function mysql_query($query, $link)
        {
            return mysqli_query($link, $query);
        }
    }

    if (!function_exists('mysql_select_db') and function_exists('mysqli_select_db')) {
        function mysql_select_db($database_name, $link)
        {
            return mysqli_select_db($link, $database_name);
        }
    }

    if (!function_exists('mysql_fetch_array') and function_exists('mysqli_fetch_array')) {
        function mysql_fetch_array($result)
        {
            return mysqli_fetch_array($result);
        }
    }

    if (!function_exists('mysql_close') and function_exists('mysqli_close')) {
        function mysql_close($link)
        {
            return mysqli_close($link);
        }
    }
    if (!function_exists('split')) {
        function split($pattern, $string)
        {
            return explode($pattern, $string);
        }
    }
}

function make_hash()
{
    $rand = dede_random_bytes(16);
    $_SESSION['token'] = ($rand === FALSE)
        ? md5(uniqid(mt_rand(), TRUE))
        : bin2hex($rand);
    return $_SESSION['token'];
}

function dede_random_bytes($length)
{
    if (empty($length) or !ctype_digit((string) $length)) {
        return FALSE;
    }

    if (function_exists('openssl_random_pseudo_bytes')) {
        return openssl_random_pseudo_bytes($length);
    }

    if (function_exists('random_bytes')) {
        try {
            return random_bytes((int) $length);
        } catch (Exception $e) {
            return FALSE;
        }
    }
    if (defined('MCRYPT_DEV_URANDOM') && ($output = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM)) !== FALSE) {
        return $output;
    }
    if (is_readable('/dev/urandom') && ($fp = fopen('/dev/urandom', 'rb')) !== FALSE) {
        version_compare(PHP_VERSION, '5.4.0', '>=') && stream_set_chunk_size($fp, $length);
        $output = fread($fp, $length);
        fclose($fp);
        if ($output !== FALSE) {
            return $output;
        }
    }

    return FALSE;
}


/**
 *  载入小助手,系统默认载入小助手
 *  在/data/helper.inc.php中进行默认小助手初始化的设置
 *  使用示例:
 *      在开发中,首先需要创建一个小助手函数,目录在\include\helpers中
 *  例如,我们创建一个示例为test.helper.php,文件基本内容如下:
 *  <code>
 *  if ( ! function_exists('HelloDede'))
 *  {
 *      function HelloDede()
 *      {
 *          echo "Hello! Dede...";
 *      }
 *  }
 *  </code>
 *  则我们在开发中使用这个小助手的时候直接使用函数helper('test');初始化它
 *  然后在文件中就可以直接使用:HelloDede();来进行调用.
 *
 * @access    public
 * @param     mix   $helpers  小助手名称,可以是数组,可以是单个字符串
 * @return    void
 */
$_helpers = array();
function helper($helpers)
{
    //如果是数组,则进行递归操作
    if (is_array($helpers)) {
        foreach ($helpers as $dede) {
            helper($dede);
        }
        return;
    }

    if (isset($_helpers[$helpers])) {
        return;
    }
    if (file_exists(DEDEINC . '/helpers/' . $helpers . '.helper.php')) {
        include_once(DEDEINC . '/helpers/' . $helpers . '.helper.php');
        $_helpers[$helpers] = TRUE;
    }
    // 无法载入小助手
    if (!isset($_helpers[$helpers])) {
        exit('Unable to load the requested file: helpers/' . $helpers . '.helper.php');
    }
}

function dede_htmlspecialchars($str)
{
    global $cfg_soft_lang;
    if (version_compare(PHP_VERSION, '5.4.0', '<')) return htmlspecialchars($str);
    if ($cfg_soft_lang == 'gb2312') return htmlspecialchars($str, ENT_COMPAT, 'ISO-8859-1');
    else return htmlspecialchars($str);
}

/**
 *  载入小助手,这里用户可能载入用helps载入多个小助手
 *
 * @access    public
 * @param     string
 * @return    string
 */
function helpers($helpers)
{
    helper($helpers);
}

//兼容php4的file_put_contents
if (!function_exists('file_put_contents')) {
    function file_put_contents($n, $d)
    {
        $f = @fopen($n, "w");
        if (!$f) {
            return FALSE;
        } else {
            fwrite($f, $d);
            fclose($f);
            return TRUE;
        }
    }
}

/**
 *  显示更新信息
 *
 * @return    void
 */
function UpdateStat()
{
    include_once(DEDEINC . "/inc/inc_stat.php");
    return SpUpdateStat();
}

$arrs1 = array(0x63, 0x66, 0x67, 0x5f, 0x70, 0x6f, 0x77, 0x65, 0x72, 0x62, 0x79);
$arrs2 = array(
    0x20, 0x3c, 0x61, 0x20, 0x68, 0x72, 0x65, 0x66, 0x3d, 0x68, 0x74, 0x74, 0x70, 0x3a, 0x2f, 0x2f,
    0x77, 0x77, 0x77, 0x2e, 0x64, 0x65, 0x64, 0x65, 0x63, 0x6d, 0x73, 0x2e, 0x63, 0x6f, 0x6d, 0x20, 0x74, 0x61, 0x72,
    0x67, 0x65, 0x74, 0x3d, 0x27, 0x5f, 0x62, 0x6c, 0x61, 0x6e, 0x6b, 0x27, 0x3e, 0x50, 0x6f, 0x77, 0x65, 0x72, 0x20,
    0x62, 0x79, 0x20, 0x44, 0x65, 0x64, 0x65, 0x43, 0x6d, 0x73, 0x3c, 0x2f, 0x61, 0x3e
);

/**
 *  短消息函数,可以在某个动作处理后友好的提示信息
 *
 * @param     string  $msg      消息提示信息
 * @param     string  $gourl    跳转地址
 * @param     int     $onlymsg  仅显示信息
 * @param     int     $limittime  限制时间
 * @return    void
 */
function ShowMsg($msg, $gourl, $onlymsg = 0, $limittime = 0)
{
    global $cfg_soft_lang, $cfg_cmsurl;
    if (empty($GLOBALS['cfg_plus_dir'])) $GLOBALS['cfg_plus_dir'] = '..';

    $htmlhead  = "<html>\r\n<head>\r\n<title>DedeBIZ提示信息</title>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset={$cfg_soft_lang}\" />\r\n<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, shrink-to-fit=no\">";
    $htmlhead .= "<link rel=\"stylesheet\" href=\"{$cfg_cmsurl}/static/css/bootstrap.min.css\"><style>.modal {position: static;}</style><link href=\"{$cfg_cmsurl}/static/font-awesome/css/font-awesome.min.css\" rel=\"stylesheet\">";
    $htmlhead .= "<base target='_self'/></head>\r\n<body leftmargin='0' topmargin='0' bgcolor='#FFFFFF'>" . (isset($GLOBALS['ucsynlogin']) ? $GLOBALS['ucsynlogin'] : '') . "\r\n<center>\r\n<script>\r\n";
    $htmlfoot  = "</script>\r\n</center>\r\n</body>\r\n</html>\r\n";

    $litime = ($limittime == 0 ? 1000 : $limittime);
    $func = '';

    if ($gourl == '-1') {
        if ($limittime == 0) $litime = 5000;
        $gourl = "javascript:history.go(-1);";
    }

    if ($gourl == '' || $onlymsg == 1) {
        $msg = "<script>alert(\"" . str_replace("\"", "“", $msg) . "\");</script>";
    } else {
        //当网址为:close::objname 时, 关闭父框架的id=objname元素
        if (preg_match('/close::/', $gourl)) {
            $tgobj = trim(preg_replace('/close::/', '', $gourl));
            $gourl = 'javascript:;';
            $func .= "window.parent.document.getElementById('{$tgobj}').style.display='none';\r\n";
        }

        $func .= "      var pgo=0;
      function JumpUrl(){
        if(pgo==0){ location='$gourl'; pgo=1; }
      }\r\n";
        $rmsg = $func;
        $rmsg .= "document.write(\"<main class='container'><div class='modal' tabindex='-1' role='dialog' style='display:block'><div class='modal-dialog'><div class='modal-content'><div class='modal-header'><h6 class='modal-title'>";
        $rmsg .= "DedeBIZ 提示信息！</h6></div><div class='modal-body'>\");\r\n";
        $rmsg .= "document.write(\"" . str_replace("\"", "“", $msg) . "\");\r\n";
        $rmsg .= "document.write(\"";

        if ($onlymsg == 0) {
            if ($gourl != 'javascript:;' && $gourl != '') {
                $rmsg .= "<br /><a href='{$gourl}'>如果你的浏览器没反应，请点击这里...</a>";
                $rmsg .= "</div></div></div></div></main>\");\r\n";
                $rmsg .= "setTimeout('JumpUrl()',$litime);";
            } else {
                $rmsg .= "</div></div></div></div></main>\");\r\n";
            }
        } else {
            $rmsg .= "</div></div></div></div></main>\");\r\n";
        }
        $msg  = $htmlhead . $rmsg . $htmlfoot;
    }
    echo $msg;
}

/**
 *  获取验证码的session值
 *
 * @return    string
 */
function GetCkVdValue()
{
    @session_id($_COOKIE['PHPSESSID']);
    @session_start();
    return isset($_SESSION['securimage_code_value']) ? $_SESSION['securimage_code_value'] : '';
}

/**
 *  PHP某些版本有Bug，不能在同一作用域中同时读session并改注销它，因此调用后需执行本函数
 *
 * @return    void
 */
function ResetVdValue()
{
    @session_start();
    $_SESSION['securimage_code_value'] = '';
}

function IndexSub($idx, $num)
{
    return intval($idx) - intval($num) == 0 ? '0 ' : intval($idx) - intval($num);
}

// 用来返回index的active
function IndexActive($idx)
{
    if ($idx == 1) {
        return ' active';
    } else {
        return '';
    }
}

// 自定义函数接口
// 这里主要兼容早期的用户扩展,v5.7之后我们建议使用小助手helper进行扩展
if (file_exists(DEDEINC . '/extend.func.php')) {
    require_once(DEDEINC . '/extend.func.php');
}
