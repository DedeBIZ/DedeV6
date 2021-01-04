<?php

/**
 * 管理目录配置文件
 *
 * @version        $Id: config.php 1 14:31 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
define('DEDEADMIN', str_replace("\\", '/', dirname(__FILE__)));
require_once(DEDEADMIN . '/../include/common.inc.php');
require_once(DEDEINC . '/userlogin.class.php');
header('Cache-Control:private');
$dsql->safeCheck = FALSE;
$dsql->SetLongLink();
$cfg_admin_skin = 1; // 后台管理风格

if (file_exists(DEDEDATA . '/admin/skin.txt')) {
    $skin = file_get_contents(DEDEDATA . '/admin/skin.txt');
    $cfg_admin_skin = !in_array($skin, array(1, 2, 3, 4)) ? 1 : $skin;
}

// 检查CSRF
function CheckCSRF()
{
    $cc_csrf_token_check = GetCookie("dede_csrf_token");
    if (
        !(isset($_POST['_csrf_token'], $cc_csrf_token_check)
            && is_string($_POST['_csrf_token']) && is_string($cc_csrf_token_check)
            && hash_equals($_POST['_csrf_token'], $cc_csrf_token_check))
    ) {
        ShowMsg('CSRF校验失败，请刷新页面重新提交', '-1');
        exit();
    }

    DropCookie("dede_csrf_token");
}

// 生成CSRF校验token，在比较重要的表单中应该要加上这个token校验
$cc_csrf_token = GetCookie("dede_csrf_token");
if (!isset($GLOBALS['csrf_token']) || $GLOBALS['csrf_token'] === null) {
    if (
        isset($cc_csrf_token) && is_string($cc_csrf_token)
        && preg_match('#^[0-9a-f]{32}$#iS', $cc_csrf_token) === 1
    ) {
        $GLOBALS['csrf_token'] = $cc_csrf_token;
    } else {
        $GLOBALS['csrf_token'] = md5(uniqid(mt_rand(), TRUE));
    }
}

if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
    PutCookie('dede_csrf_token', $GLOBALS['csrf_token'], 7200, '/');
}


//获得当前脚本名称，如果你的系统被禁用了$_SERVER变量，请自行更改这个选项
$dedeNowurl = $s_scriptName = '';
$isUrlOpen = @ini_get('allow_url_fopen');
$dedeNowurl = GetCurUrl();
$dedeNowurls = explode('?', $dedeNowurl);
$s_scriptName = $dedeNowurls[0];

//检验用户登录状态
$cuserLogin = new userLogin();

if ($cuserLogin->getUserID() == -1) {
    if (preg_match("#PHP (.*) Development Server#", $_SERVER['SERVER_SOFTWARE'])) {
        $dirname = dirname($_SERVER['SCRIPT_NAME']);
        header("location:{$dirname}/login.php?gotopage=" . urlencode($dedeNowurl));
    } else {
        header("location:login.php?gotopage=" . urlencode($dedeNowurl));
    }
    exit();
}

function XSSClean($val)
{
    if (is_array($val)) {
        foreach ($val as $key => $v) {
            if (in_array($key, array('tags', 'body', 'dede_fields', 'dede_addonfields', 'dopost', 'introduce'))) continue;
            $val[$key] = XSSClean($val[$key]);
        }
        return $val;
    }
    return RemoveXss($val);
}

if ($cfg_dede_log == 'Y') {
    $s_nologfile = '_main|_list';
    $s_needlogfile = 'sys_|file_';
    $s_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
    $s_query = isset($dedeNowurls[1]) ? $dedeNowurls[1] : '';
    $s_scriptNames = explode('/', $s_scriptName);
    $s_scriptNames = $s_scriptNames[count($s_scriptNames) - 1];
    $s_userip = GetIP();
    if ($s_method == 'POST' || (!preg_match("#" . $s_nologfile . "#i", $s_scriptNames) && $s_query != '') || preg_match("#" . $s_needlogfile . "#i", $s_scriptNames)) {
        $inquery = "INSERT INTO `#@__log`(adminid,filename,method,query,cip,dtime)
             VALUES ('" . $cuserLogin->getUserID() . "','{$s_scriptNames}','{$s_method}','" . addslashes($s_query) . "','{$s_userip}','" . time() . "');";
        $dsql->ExecuteNoneQuery($inquery);
    }
}

//管理缓存、管理员频道缓存
$cache1 = DEDEDATA . '/cache/inc_catalog_base.inc';
if (!file_exists($cache1)) UpDateCatCache();
$cacheFile = DEDEDATA . '/cache/admincat_' . $cuserLogin->userID . '.inc';
if (file_exists($cacheFile)) require_once($cacheFile);

//更新服务器
require_once(DEDEDATA . '/admin/config_update.php');

if (strlen($cfg_cookie_encode) <= 10) {
    $chars = 'abcdefghigklmnopqrstuvwxwyABCDEFGHIGKLMNOPQRSTUVWXWY0123456789';
    $hash = '';
    $length = rand(28, 32);
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    $dsql->ExecuteNoneQuery("UPDATE `#@__sysconfig` SET `value`='{$hash}' WHERE varname='cfg_cookie_encode' ");
    $configfile = DEDEDATA . '/config.cache.inc.php';
    if (!is_writeable($configfile)) {
        echo "配置文件'{$configfile}'不支持写入，无法修改系统配置参数！";
        exit();
    }
    $fp = fopen($configfile, 'w');
    flock($fp, 3);
    fwrite($fp, "<" . "?php\r\n");
    $dsql->SetQuery("SELECT `varname`,`type`,`value`,`groupid` FROM `#@__sysconfig` ORDER BY aid ASC ");
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        if ($row['type'] == 'number') {
            if ($row['value'] == '') $row['value'] = 0;
            fwrite($fp, "\${$row['varname']} = " . $row['value'] . ";\r\n");
        } else {
            fwrite($fp, "\${$row['varname']} = '" . str_replace("'", '', $row['value']) . "';\r\n");
        }
    }
    fwrite($fp, "?" . ">");
    fclose($fp);
}

/**
 *  更新栏目缓存
 *
 * @access    public
 * @return    void
 */
function UpDateCatCache()
{
    global $dsql, $cache1, $cuserLogin;
    $cache2 = DEDEDATA . '/cache/channelsonlist.inc';
    $cache3 = DEDEDATA . '/cache/channeltoplist.inc';
    $dsql->SetQuery("SELECT id,reid,channeltype,issend,typename FROM `#@__arctype`");
    $dsql->Execute();
    $fp1 = fopen($cache1, 'w');
    $phph = '?';
    $fp1Header = "<{$phph}php\r\nglobal \$cfg_Cs;\r\n\$cfg_Cs=array();\r\n";
    fwrite($fp1, $fp1Header);
    while ($row = $dsql->GetObject()) {
        // 将typename缓存起来
        $row->typename = base64_encode($row->typename);
        fwrite($fp1, "\$cfg_Cs[{$row->id}]=array({$row->reid},{$row->channeltype},{$row->issend},'{$row->typename}');\r\n");
    }
    fwrite($fp1, "{$phph}>");
    fclose($fp1);
    $cuserLogin->ReWriteAdminChannel();
    @unlink($cache2);
    @unlink($cache3);
}

// 清空选项缓存
function ClearOptCache()
{
    $tplCache = DEDEDATA . '/tplcache/';
    $fileArray = glob($tplCache . "inc_option_*.inc");
    if (count($fileArray) > 1) {
        foreach ($fileArray as $key => $value) {
            if (file_exists($value)) unlink($value);
            else continue;
        }
        return TRUE;
    }
    return FALSE;
}

/**
 *  引入模板文件
 *
 * @access    public
 * @param     string  $filename  文件名称
 * @param     bool  $isabs  是否为管理目录
 * @return    string
 */
function DedeInclude($filename, $isabs = FALSE)
{
    return $isabs ? $filename : DEDEADMIN . '/' . $filename;
}

/**
 *  根据用户mid获取用户名称
 *
 * @access    public
 * @param     int  $mid   用户ID
 * @return    string
 */
if (!function_exists('GetMemberName')) {
    function GetMemberName($mid = 0)
    {
        global $dsql;
        if (empty($mid)) {
            return "管理员";
        }
        $rs = $dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='{$mid}' ");
        return $rs['uname'];
    }
}
