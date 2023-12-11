<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * Cookie处理助手
 *
 * @version        $id:cookie.helper.php 2023年11月24日 tianya $
 * @package        DedeBIZ.Helpers
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
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
        global $cfg_cookie_encode, $cfg_domain_cookie;
        if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
            $options = array(
                "expires"=>time() + $kptime,
                'path' => $pa,
                'domain' => $cfg_domain_cookie,
                'samesite' => 'None',
                'secure' => true,
            );
            setcookie($key, $value, $options);
            setcookie($key.'__ckMd5', substr(md5($cfg_cookie_encode.$value), 0, 16), $options);
        } else {
            setcookie($key, $value, time() + $kptime, $pa.'; SameSite=None; Secure', $cfg_domain_cookie);
            setcookie($key.'__ckMd5', substr(md5($cfg_cookie_encode.$value), 0, 16), time() + $kptime, $pa.'; SameSite=None; Secure', $cfg_domain_cookie);
        }
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
        global $cfg_domain_cookie;
        if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
            $options = array(
                "expires"=>time() - 360000,
                'path' => "/",
                'domain' => $cfg_domain_cookie,
                'samesite' => 'None',
                'secure' => true,
            );
            setcookie($key, "", $options);
            setcookie($key.'__ckMd5', "", $options);
        } else {
            setcookie($key, '', time() - 360000, "/; SameSite=None; Secure", $cfg_domain_cookie);
            setcookie($key.'__ckMd5', '', time() - 360000, "/; SameSite=None; Secure", $cfg_domain_cookie);
        }
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