<?php
if (!defined('DEDEINC')) exit('dedebiz');
/**
 * 缓存小助手,支持文件和dedebiz cache
 *
 * @version        $Id: cache.helper.php 1 10:46 2011-3-2 tianya $
 * @package        DedeBIZ.Helpers
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
/**
 *  读缓存
 *
 * @access    public
 * @param     string  $prefix  前缀
 * @param     string  $key  键
 * @return    string
 */
if (!function_exists('GetCache')) {
    function GetCache($prefix, $key)
    {
        global $cfg_bizcore_appid, $cfg_bizcore_key, $cfg_bizcore_hostname, $cfg_bizcore_port;

        $key = md5($key);
        // 商业组件缓存
        if (!empty($cfg_bizcore_appid) && !empty($cfg_bizcore_key)) {
            $client = new DedeBizClient($cfg_bizcore_hostname, $cfg_bizcore_port);
            $client->appid = $cfg_bizcore_appid;
            $client->key = $cfg_bizcore_key;
            $key = trim($prefix.'_'.$key);
            $data = $client->CacheGet($key);
            $result = unserialize($data->data);
            $client->Close();
            return $result;
        }
        $key = substr($key, 0, 2).'/'.substr($key, 2, 2).'/'.substr($key, 4, 2).'/'.$key;
        $result = @file_get_contents(DEDEDATA."/cache/$prefix/$key.php");
        if ($result === false) {
            return false;
        }
        $result = str_replace("<?php exit('dedebiz');?>\n\r", "", $result);
        $result = @unserialize($result);
        if ($result['timeout'] != 0 && $result['timeout'] < time()) {
            return false;
        }
        return $result['data'];
    }
}
/**
 *  写缓存
 *
 * @access    public
 * @param     string  $prefix  前缀
 * @param     string  $key  键
 * @param     string  $value  值
 * @param     string  $timeout  缓存时间
 * @return    int
 */
if (!function_exists('SetCache')) {
    function SetCache($prefix, $key, $value, $timeout = 3600)
    {
        global $cfg_bizcore_appid, $cfg_bizcore_key, $cfg_bizcore_hostname, $cfg_bizcore_port;
        $key = md5($key);

        // 商业组件缓存
        if (!empty($cfg_bizcore_appid) && !empty($cfg_bizcore_key)) {
            $client = new DedeBizClient($cfg_bizcore_hostname, $cfg_bizcore_port);
            $client->appid = $cfg_bizcore_appid;
            $client->key = $cfg_bizcore_key;
            $key = trim($prefix.'_'.$key);
            $data = $client->CacheSet($key,serialize($value),$timeout);
            $result = unserialize($data->data);
            $client->Close();
            return $result;
        }
        $key = substr($key, 0, 2).'/'.substr($key, 2, 2).'/'.substr($key, 4, 2).'/'.$key;
        $tmp['data'] = $value;
        $tmp['timeout'] = $timeout != 0 ? time() + (int) $timeout : 0;
        $cache_data = "<?php exit('dedebiz');?>\n\r".@serialize($tmp);
        return @PutFile(DEDEDATA."/cache/$prefix/$key.php",  $cache_data);
    }
}
/**
 *  删除缓存
 *
 * @access    public
 * @param     string  $prefix  前缀
 * @param     string  $key  键
 * @return    string
 */
if (!function_exists('DelCache')) {
    //删缓存
    function DelCache($prefix, $key)
    {
        global $cfg_bizcore_appid, $cfg_bizcore_key, $cfg_bizcore_hostname, $cfg_bizcore_port;

        $key = md5($key);

        // 商业组件缓存
        if (!empty($cfg_bizcore_appid) && !empty($cfg_bizcore_key)) {
            $client = new DedeBizClient($cfg_bizcore_hostname, $cfg_bizcore_port);
            $client->appid = $cfg_bizcore_appid;
            $client->key = $cfg_bizcore_key;
            $key = trim($prefix.'_'.$key);
            $data = $client->CacheDel($key);
            $client->Close();
            return true;
        }
        $key = substr($key, 0, 2).'/'.substr($key, 2, 2).'/'.substr($key, 4, 2).'/'.$key;
        return @unlink(DEDEDATA."/cache/$prefix/$key.php");
    }
}