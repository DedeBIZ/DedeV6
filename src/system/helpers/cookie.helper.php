<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * Cookie处理助手
 *
 * @version        $id:cookie.helper.php 2024-04-11 tianya $
 * @package        DedeBIZ.Helpers
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
function DedeSetCookie($key, $value = "", $expires = 0, $path = ""){
    global $cfg_domain_cookie,$cfg_cookie_samesite,$cfg_cookie_secure,$cfg_cookie_httponly;
    if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
        $options = array(
            "expires" => $expires,
            'path' => $path,
            'domain' => $cfg_domain_cookie,
            'samesite' => $cfg_cookie_samesite,
            'secure' => $cfg_cookie_secure,
            'httponly' => $cfg_cookie_httponly,
        );
        setcookie($key, $value, $options);
    } else {
        $cookie_header = 'Set-Cookie: '.$key.'='.rawurlencode($value);
        $cookie_header .= ($expires === 0 ? '' : '; Expires='.gmdate('D, d-M-Y H:i:s T', $expires)).';';
        $cookie_header .= '; Path='.$path.($cfg_domain_cookie !== '' ? '; Domain='.$cfg_domain_cookie : '');
        $cookie_header .= ($cfg_cookie_secure ? '; Secure' : '').($cfg_cookie_httponly ? '; HttpOnly' : '').'; SameSite='.$cfg_cookie_samesite;
        header($cookie_header);
        return;
    }
}
/**
 *  设置Cookie记录
 *
 * @param     string  $key    键
 * @param     string  $value  值
 * @param     string  $kptime  保持时间
 * @param     string  $pa     保存路径
 * @return    void
 */
if (!function_exists('PutCookie')) {
    function PutCookie($key, $value, $kptime = 0, $pa = "/")
    {
        global $cfg_cookie_encode;
        DedeSetCookie($key, $value, time() + $kptime, $pa);
        DedeSetCookie($key.'__ckMd5', substr(md5($cfg_cookie_encode.$value), 0, 16), time() + $kptime, $pa);
    }
}
/**
 *  清除Cookie记录
 *
 * @param     $key   键名
 * @return    void
 */
if (!function_exists('DropCookie')) {
    function DropCookie($key)
    {
        DedeSetCookie($key, '', time() - 360000, "/");
        DedeSetCookie($key.'__ckMd5', '', time() - 360000, "/");
    }
}
/**
 *  获取Cookie记录
 *
 * @param     $key   键名
 * @return    string
 */
if (!function_exists('GetCookie')) {
    function GetCookie($key)
    {
        global $cfg_cookie_encode;
        if (!isset($_COOKIE[$key]) || !isset($_COOKIE[$key.'__ckMd5'])) {
            return '';
        } else {
            if ($_COOKIE[$key.'__ckMd5'] != substr(md5($cfg_cookie_encode.$_COOKIE[$key]), 0, 16)) {
                return '';
            } else {
                return $_COOKIE[$key];
            }
        }
    }
}
?>