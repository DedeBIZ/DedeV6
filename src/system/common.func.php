<?php
if (!defined('DEDEINC')) exit('dedebiz');
//显示类似BS的提示信息
define('ALERT_PRIMARY', 1);
define('ALERT_SECONDARY', 2);
define('ALERT_SUCCESS', 3);
define('ALERT_DANGER', 4);
define('ALERT_WARNING', 5);
define('ALERT_INFO', 6);
define('ALERT_LIGHT', 7);
define('ALERT_DARK', 8);
define('ALERT_COLORS', array(
    ALERT_PRIMARY => array('#cfe2ff','#b6d4fe','#084298'),
    ALERT_SECONDARY => array('#e2e3e5','#d3d6d8','#41464b'),
    ALERT_SUCCESS => array('#d1e7dd','#badbcc','#0f5132'),
    ALERT_DANGER => array('#f8d7da','#f5c2c7','#842029'),
    ALERT_WARNING => array('#fff3cd','#ffecb5','#664d03'),
    ALERT_INFO => array('#cff4fc','#b6effb','#055160'),
    ALERT_LIGHT => array('#fefefe','#fdfdfe','#636464'),
    ALERT_DARK => array('#d3d3d4','#bcbebf','#141619'),
));
define("ALERT_TPL", '<div style="position:relative;padding:.75rem 1.25rem;width:auto;font-size:12px;color:~color~;background:~background~;border-color:~border~;border:1px solid transparent;border-radius:.2rem">~content~</div>');
//$content:文档 $type:alert类型
function DedeAlert($content, $type = ALERT_PRIMARY)
{
    $content = htmlspecialchars($content);
    $colors = isset(ALERT_COLORS[$type])? ALERT_COLORS[$type] : ALERT_COLORS[ALERT_PRIMARY];
    list($background, $border, $color) = $colors;
    return str_replace(array('~color~','~background~','~border~', '~content~'),array($color,$background,$border,$content),ALERT_TPL);
}
/**
 * 系统核心函数存放文件
 * 
 * @version        $id:common.func.php 4 16:39 2010年7月6日 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
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
    if (!function_exists('mysql_free_result') and function_exists('mysqli_free_result')) {
        function mysql_free_result($result)
        {
            return mysqli_free_result($result);
        }
    }
    if (!function_exists('split')) {
        function split($pattern, $string)
        {
            return explode($pattern, $string);
        }
    }
}
//一个支持在PHP Cli Server打印的方法
function var_dump_cli($val,...$values)
{
    ob_start();
    var_dump($val,$values);
    error_log(ob_get_clean(), 4);
}
function get_mime_type($filename)
{
    if (!function_exists('finfo_open')) {
        return 'unknow/octet-stream';
    }
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filename);
    finfo_close($finfo);
    return $mimeType;
}
function is_all_numeric(array $array)
{
    foreach ($array as $item) {
        if (!is_numeric($item)) return false;
    }
    return true;
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
//SQL语句过滤程序，由80sec提供，这里作了适当的修改
if (!function_exists('CheckSql')) {
    function CheckSql($db_string, $querytype = 'select')
    {
        global $cfg_cookie_encode;
        $clean = '';
        $error = '';
        $old_pos = 0;
        $pos = -1;
        $enkey = substr(md5(substr($cfg_cookie_encode.'dedebiz', 0, 5)), 0, 10);
        $log_file = DEDEDATA.'/checksql_'.$enkey.'_safe.txt';
        $userIP = GetIP();
        $getUrl = GetCurUrl();
        //如果是普通查询语句，直接过滤一些特殊语法
        if ($querytype == 'select') {
            $notallow1 = "[^0-9a-z@\._-]{1,}(union|sleep|benchmark|load_file|outfile)[^0-9a-z@\.-]{1,}";
            //$notallow2 = "--|/\*";
            if (preg_match("/".$notallow1."/i", $db_string)) {
                fputs(fopen($log_file, 'a+'), "$userIP||$getUrl||$db_string||SelectBreak\r\n");
                exit("<span>Safe Alert: Request Error step 1 !</span>");
            }
        }
        //完整的SQL检查
        while (TRUE) {
            $pos = strpos($db_string, '\'', $pos + 1);
            if ($pos === FALSE) {
                break;
            }
            $clean .= substr($db_string, $old_pos, $pos - $old_pos);
            while (TRUE) {
                $pos1 = strpos($db_string, '\'', $pos + 1);
                $pos2 = strpos($db_string, '\\', $pos + 1);
                if ($pos1 === FALSE) {
                    break;
                } elseif ($pos2 == FALSE || $pos2 > $pos1) {
                    $pos = $pos1;
                    break;
                }
                $pos = $pos2 + 1;
            }
            $clean .= '$s$';
            $old_pos = $pos + 1;
        }
        $clean .= substr($db_string, $old_pos);
        $clean = trim(strtolower(preg_replace(array('~\s+~s'), array(' '), $clean)));
        if (
            strpos($clean, '@') !== FALSE  or strpos($clean, 'char(') !== FALSE or strpos($clean, '"') !== FALSE
            or strpos($clean, '$s$$s$') !== FALSE
        ) {
            $fail = TRUE;
            if (preg_match("#^create table#i", $clean)) $fail = FALSE;
            $error = "unusual character";
        }
        //老版本的Mysql并不支持union，常用的程序里也不使用union，但是一些黑客使用它，所以检查它
        if (strpos($clean, 'union') !== FALSE && preg_match('~(^|[^a-z])union($|[^[a-z])~s', $clean) != 0) {
            $fail = TRUE;
            $error = "union detect";
        }
        //发布版本的程序可能比较少包括--,#这样的注释，但是黑客经常使用它们
        elseif (strpos($clean, '/*') > 2 || strpos($clean, '--') !== FALSE || strpos($clean, '#') !== FALSE) {
            $fail = TRUE;
            $error = "comment detect";
        }
        //这些函数不会被使用，但是黑客会用它来操作文件，down掉数据库
        elseif (strpos($clean, 'sleep') !== FALSE && preg_match('~(^|[^a-z])sleep($|[^[a-z])~s', $clean) != 0) {
            $fail = TRUE;
            $error = "slown down detect";
        } elseif (strpos($clean, 'benchmark') !== FALSE && preg_match('~(^|[^a-z])benchmark($|[^[a-z])~s', $clean) != 0) {
            $fail = TRUE;
            $error = "slown down detect";
        } elseif (strpos($clean, 'load_file') !== FALSE && preg_match('~(^|[^a-z])load_file($|[^[a-z])~s', $clean) != 0) {
            $fail = TRUE;
            $error = "file fun detect";
        } elseif (strpos($clean, 'into outfile') !== FALSE && preg_match('~(^|[^a-z])into\s+outfile($|[^[a-z])~s', $clean) != 0) {
            $fail = TRUE;
            $error = "file fun detect";
        }
        //老版本的MYSQL不支持子查询，我们的程序里可能也用得少，但是黑客可以使用它来查询数据库敏感信息
        elseif (preg_match('~\([^)]*?select~s', $clean) != 0) {
            $fail = TRUE;
            $error = "sub select detect";
        }
        if (!empty($fail)) {
            fputs(fopen($log_file, 'a+'), "$userIP||$getUrl||$db_string||$error\r\n");
            exit("<span>Safe Alert: Request Error step 2!</span>");
        } else {
            return $db_string;
        }
    }
}
/**
 *  载入小助手,系统默认载入小助手
 *  在/data/helper.inc.php中进行默认小助手初始化的设置，创建一个示例为test.helper.php文件基本文档如下：
 *  <code>
 *  if (!function_exists('HelloDede'))
 *  {
 *      function HelloDede()
 *      {
 *          echo "Hello! Dede";
 *      }
 *  }
 *  </code>
 *  则我们在开发中使用这个小助手的时候直接使用函数helper('test');初始化它，然后在文件中就可以直接使用:HelloDede();来进行调用
 *
 * @access    public
 * @param     mix   $helpers  小助手名称,可以是数组,可以是单个字符串
 * @return    void
 */
$_helpers = array();
function helper($helpers)
{
    //如果是数组，则进行递归操作
    if (is_array($helpers)) {
        foreach ($helpers as $dede) {
            helper($dede);
        }
        return;
    }
    if (isset($_helpers[$helpers])) {
        return;
    }
    if (file_exists(DEDEINC.'/helpers/'.$helpers.'.helper.php')) {
        include_once(DEDEINC.'/helpers/'.$helpers.'.helper.php');
        $_helpers[$helpers] = TRUE;
    }
    //无法载入小助手
    if (!isset($_helpers[$helpers])) {
        exit('Unable to load the requested file: helpers/'.$helpers.'.helper.php');
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
 *  载入小助手，这里用户可能载入用helps载入多个小助手
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
    include_once(DEDEINC."/inc/inc_stat.php");
    return SpUpdateStat();
}
$arrs1 = array();
$arrs2 = array();
/**
 *  短消息函数，可以在某个动作处理后友好的提示信息
 *
 * @param     string  $msg       消息提示信息
 * @param     string  $gourl     跳转地址
 * @param     int     $onlymsg   仅显示信息
 * @param     int     $limittime 限制时间
 * @return    void
 */
function ShowMsg($msg, $gourl, $onlymsg = 0, $limittime = 0)
{
    global $cfg_soft_lang, $cfg_cmsurl;
    if (empty($GLOBALS['cfg_plus_dir'])) $GLOBALS['cfg_plus_dir'] = '..';
    $htmlhead  = "<!DOCTYPE html><html><head><meta charset='utf-8'><meta http-equiv='X-UA-Compatible' content='IE=Edge,chrome=1'><title>提示信息</title><base target='_self'></head>";
    $htmlhead .= "<body><center><script>";
    $htmlfoot  = "</script></center></body></html>";
    $litime = ($limittime == 0 ? 1000 : $limittime);
    $func = '';
    if ($gourl == '-1') {
        if ($limittime == 0) $litime = 5000;
        $gourl = "javascript:history.go(-1);";
    }
    if ($gourl == '' || $onlymsg == 1) {
        $msg = "<script>alert(\"".str_replace("\"", "“", $msg)."\");</script>";
    } else {
        //当网址为:close::objname时，关闭父框架的id=objname元素
        if (preg_match('/close::/', $gourl)) {
            $tgobj = trim(preg_replace('/close::/', '', $gourl));
            $gourl = 'javascript:;';
            $func .= "window.parent.document.getElementById('{$tgobj}').style.display='none';\r\n";
        }
        $func .= "var pgo=0;function JumpUrl(){if (pgo==0){location='$gourl'; pgo=1;}}";
        $rmsg = $func;
        $rmsg .= "document.write(\"<style>body{margin:0;line-height:1.5;font:14px Helvetica Neue,Helvetica,PingFang SC,Tahoma,Arial,sans-serif;color:#545b62;background:#f5f5f5}a{color:#1eb867;text-decoration:none}.tips{margin:70px auto 0;padding:0;width:500px;height:auto;background:#fff;border-radius:.2rem;box-shadow:0 .125rem .25rem rgba(0,0,0,.075)}.tips-head{margin:0 20px;padding:16px 0;border-bottom:1px solid #f5f5f5}.tips-head p{margin:0;padding-left:10px;line-height:16px;text-align:left;border-left:3px solid #dc3545}.tips-box{padding:20px;min-height:130px;color:#545b62}.btn a{display:inline-block;margin:20px auto 0;padding:.375rem .75rem;font-size:12px;color:#fff;background:#1eb867;border-radius:.2rem;text-align:center;transition:all .6s}.btn a:focus{background:#006829;border-color:#005b24;box-shadow:0 0 0 0.2rem rgba(38,159,86,.5)}.text-primary{color:#007bff!important}@media (max-width:768px){body{padding:0 15px}.tips{width:100%}}</style>\");";
        $rmsg .= "document.write(\"<div class='tips'>";
        $rmsg .= "<div class='tips-head'><p>提示信息</p></div>\");";
        $rmsg .= "document.write(\"<div class='tips-box'>\");";
        $rmsg .= "document.write(\"".str_replace("\"", "“", $msg)."\");";
        $rmsg .= "document.write(\"";
        if ($onlymsg == 0) {
            if ($gourl != 'javascript:;' && $gourl != '') {
                $rmsg .= "<div class='btn'><a href='{$gourl}'>点击反应</a></div>\");";
                $rmsg .= "setTimeout('JumpUrl()',$litime);";
            } else {
                $rmsg .= "</div>\");";
            }
        } else {
            $rmsg .= "</div>\");";
        }
        $msg  = $htmlhead.$rmsg.$htmlfoot;
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
//用来返回index的active
function IndexActive($idx)
{
    if ($idx == 1) {
        return ' active';
    } else {
        return '';
    }
}
//是否是HTTPS
function IsSSL()
{
    if ($_SERVER['HTTPS'] && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
        return true;
    } elseif ('https' == $_SERVER['REQUEST_SCHEME']) {
        return true;
    } elseif ('443' == $_SERVER['SERVER_PORT']) {
        return true;
    } elseif ('https' == $_SERVER['HTTP_X_FORWARDED_PROTO']) {
        return true;
    }
    return false;
}
//用户名称标签{dede:field.mid function="GetMemberInfos('uname',@me)"/}和[field:mid function="GetMemberInfos('uname',@me)"/]
function GetMemberInfos($fields, $mid)
{
    global $dsql;
    if ($mid <= 0) {
        $revalue = "Error";
    } else {
        $row=$dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='{$mid}'");
        if (!is_array($row)) {
            $revalue = "Not user";
        } else {
            $revalue = $row[$fields];
        }
    }
    return $revalue;
}
//用户头像标签{dede:field.mid function='face(@me)'/}和[field:mid function='face(@me)'/]
function face($mid)
{
    global $dsql;
    if ($mid <> 0) {
        $row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='$mid'");
        if ($row['face'] == "") {
            $face = "/static/web/img/avatar.png";
        } else {
            $face = $row['face'];
            $face = "$face";
        }
    }
    return $face;
}
//调用标签[field:id function='GetMyTags(@me,2)'/]2表示输出2个文档
if (!function_exists('GetMyTags')) {
    function GetMyTags($aid, $num=3) {
        global $dsql,$cfg_cmspath;
        $tags = '';
        $query = "SELECT * FROM `#@__taglist` WHERE aid='$aid' LIMIT $num";
        $dsql->Execute('tag',$query);
        while($row = $dsql->GetArray('tag')) {
            $link = $cfg_cmspath."/apps/tags.php?/{$row['tid']}";
            $tags.= ($tags==''?"<a href='{$link}'>{$row['tag']}</a>" : "<a href='{$link}'>{$row['tag']}</a>");
        }
        return $tags;
    }
}
/*调用主题模板<?php pasterTempletDiy('header.htm');?>*/
if (!function_exists('pasterTempletDiy')) {
    function pasterTempletDiy($path) {
        global $cfg_basedir,$cfg_templets_dir,$cfg_df_style;
        $tmpfile = $cfg_basedir.$cfg_templets_dir.'/'.$cfg_df_style.'/'.$path;
        $dtp = new PartView();
        $dtp->SetTemplet($tmpfile);
        $dtp->Display();
    }
}
//联动单筛选标签{dede:php}AddFilter(模型id,类型,'字段1,字段2');{/dede:php}
function litimgurls($imgid = 0)
{
    global $lit_imglist, $dsql;
    $row = $dsql->GetOne("SELECT c.addtable FROM `#@__archives` AS a LEFT JOIN `#@__channeltype` AS c ON a.channel=c.id WHERE a.id='$imgid'");
    $addtable = trim($row['addtable']);
    $row = $dsql->GetOne("SELECT imgurls FROM `$addtable` WHERE aid='$imgid'");
    $ChannelUnit = new ChannelUnit(2, $imgid);
    $lit_imglist = $ChannelUnit->GetlitImgLinks($row['imgurls']);
    return $lit_imglist;
}
//字符过滤函数安全
function string_filter($str, $stype = "inject")
{
    if ($stype == "inject") {
        $str = str_replace(
            array("select", "insert", "update", "delete", "alter", "cas", "union", "into", "load_file", "outfile", "create", "join", "where", "like", "drop", "modify", "rename", "'", "/*", "*", "../", "./"),
            array("", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""),
            $str
        );
    } else if ($stype == "xss") {
        $farr = array("/\s+/", "/<(\/?)(script|META|STYLE|HTML|HEAD|BODY|STYLE |i?frame|b|strong|style|html|img|P|o:p|iframe|u|em|strike|BR|div|a|TABLE|TBODY|object|tr|td|st1:chsdate|FONT|span|MARQUEE|body|title|\r\n|link|meta|\?|\%)([^>]*?)>/isU", "/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU",);
        $tarr = array(" ", "", "\\1\\2",);
        $str = preg_replace($farr, $tarr, $str);
        $str = str_replace(
            array("<", ">", "'", "\"", ";", "/*", "*", "../", "./"),
            array("&lt;", "&gt;", "", "", "", "", "", "", ""),
            $str
        );
    }
    return $str;
}
//联动单筛选自定义表单发布
function AddFilter($channelid, $type=1, $fieldsnamef=array(), $defaulttid=0, $toptid=0, $loadtype='autofield')
{
    global $tid, $dsql, $id, $aid;
    $tid = $defaulttid ? $defaulttid : $tid;
    if ($id!="" || $aid!="") {
        $arcid = $id!="" ? $id : $aid;
        $tidsq = $dsql->GetOne("SELECT * FROM `#@__archives` WHERE id='$arcid'");
        $tid = $toptid==0 ? $tidsq["typeid"] : $tidsq["topid"];
    }
    $fieldsnamef = count($fieldsnamef) > 0 ? implode(",", $fieldsnamef) : $fieldsnamef;
    $nofilter = (isset($_REQUEST['TotalResult']) ? "&TotalResult=".$_REQUEST['TotalResult'] : '').(isset($_REQUEST['PageNo']) ? "&PageNo=".$_REQUEST['PageNo'] : '');
    $filterarr = string_filter(stripos($_SERVER['REQUEST_URI'], "list.php?tid=") ? str_replace($nofilter, '', $_SERVER['REQUEST_URI']) : $GLOBALS['cfg_cmsurl']."/plus/list.php?tid=".$tid);
    $cInfos = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$channelid'");
    $fieldset=$cInfos['fieldset'];
    $dtp = new DedeTagParse();
    $dtp->SetNameSpace('field', '<', '>');
    $dtp->LoadSource($fieldset);
    $dede_addonfields = '';
    if(is_array($dtp->CTags)) {
        foreach($dtp->CTags as $tida=>$ctag)
        {
            $fieldsname = $fieldsnamef ? explode(",", $fieldsnamef) : explode(",", $ctag->GetName());
            if(($loadtype!='autofield' || ($loadtype=='autofield' && $ctag->GetAtt('autofield')==1)) && in_array($ctag->GetName(), $fieldsname) ) {
                $href1 = explode($ctag->GetName().'=', $filterarr);
                $href2 = explode('&', $href1[1]);
                $fields_value = $href2[0];
                switch ($type) {
                    case 1:
                    $dede_addonfields .= (preg_match("/&".$ctag->GetName()."=/is",$filterarr,$regm) ? '<a href="'.str_replace("&".$ctag->GetName()."=".$fields_value,"",$filterarr).'" class="btn btn-outline-success btn-sm">全部</a>' : '<a href="'.str_replace("&".$ctag->GetName()."=".$fields_value,"",$filterarr).'" class="btn btn-outline-success btn-sm">全部</a>');
                    $addonfields_items = explode(",",$ctag->GetAtt('default'));
                    for ($i=0; $i<count($addonfields_items); $i++)
                    {
                        $href = stripos($filterarr,$ctag->GetName().'=') ? str_replace("=".$fields_value,"=".urlencode($addonfields_items[$i]),$filterarr) : $filterarr.'&'.$ctag->GetName().'='.urlencode($addonfields_items[$i]);
                        $dede_addonfields .= ($fields_value!=urlencode($addonfields_items[$i]) ? '<a href="'.$href.'" title="'.$addonfields_items[$i].'" class="btn btn-success btn-sm">'.$addonfields_items[$i].'</a>' : '<a href="'.$href.'" title="'.$addonfields_items[$i].'" class="btn btn-success btn-sm">'.$addonfields_items[$i].'</a>');
                    }
                    break;
                    case 2:
                    $dede_addonfields .= '<select name="filter'.$ctag->GetName().'" onchange="window.location=this.options[this.selectedIndex].value">
                        '.'<option value="'.str_replace("&".$ctag->GetName()."=".$fields_value,"",$filterarr).'">全部</option>';
                    $addonfields_items = explode(",",$ctag->GetAtt('default'));
                    for ($i=0; $i<count($addonfields_items); $i++)
                    {
                        $href = stripos($filterarr,$ctag->GetName().'=') ? str_replace("=".$fields_value,"=".urlencode($addonfields_items[$i]),$filterarr) : $filterarr.'&'.$ctag->GetName().'='.urlencode($addonfields_items[$i]);
                        $dede_addonfields .= '<option value="'.$href.'"'.($fields_value==urlencode($addonfields_items[$i]) ? ' selected="selected"' : '').'>'.$addonfields_items[$i].'</option>
                        ';
                    }
                    $dede_addonfields .= '</select>';
                    break;
                    case 3:
                    $dede_addonfields .= (preg_match("/&".$ctag->GetName()."=/is",$filterarr,$regm) ? '<a href="'.str_replace("&".$ctag->GetName()."=".$fields_value,"",$filterarr).'" title="全部"><input type="radio" name="filter'.$ctag->GetName().'" value="'.str_replace("&".$ctag->GetName()."=".$fields_value,"",$filterarr).'" onclick="window.location=this.value">全部</a>' : '<span><input type="radio" name="filter'.$ctag->GetName().'" checked="checked">全部</span>');
                    $addonfields_items = explode(",",$ctag->GetAtt('default'));
                    for ($i=0; $i<count($addonfields_items); $i++)
                    {
                        $href = stripos($filterarr,$ctag->GetName().'=') ? str_replace("=".$fields_value,"=".urlencode($addonfields_items[$i]),$filterarr) : $filterarr.'&'.$ctag->GetName().'='.urlencode($addonfields_items[$i]);
                        $dede_addonfields .= ($fields_value!=urlencode($addonfields_items[$i]) ? '<a href="'.$href.'" title="'.$addonfields_items[$i].'"><input type="radio" name="filter'.$ctag->GetName().'" value="'.$href.'" onclick="window.location=this.value">'.$addonfields_items[$i].'</a>' : '<span><input type="radio" name="filter'.$ctag->GetName().'" checked="checked">'.$addonfields_items[$i].'</span>');
                    }
                    break;
                }
            }
        }
    }
    echo $dede_addonfields;
}
/**
 * 用于检测系统版本
 *
 * @return string
 */
function CheckDedeVer()
{
    global $dsql;
    $ver = '1.0.0';
    $dsql->GetTableFields('#@__tagindex');
    while ($fields = $dsql->GetFieldObject()) {
        if ($fields->name === 'tag_pinyin') {
            $ver = '6.0.2';
        }
    }
    return $ver;
}
//自定义函数接口
if (file_exists(DEDEINC.'/extend.func.php')) {
    require_once(DEDEINC.'/extend.func.php');
}
