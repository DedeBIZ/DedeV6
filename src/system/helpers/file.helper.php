<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 文件处理助手
 *
 * @version        $id:file.helper.php 2010-07-05 11:43:09 tianya $
 * @package        DedeBIZ.Helpers
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */

/**
 *  创建所有目录
 *
 * @param     string  $truepath  真实地址
 * @param     string  $mmode   模式
 * @return    bool
 */
if (!function_exists('MkdirAll')) {
    function MkdirAll($truepath, $mmode)
    {
        if (!file_exists($truepath)) {
            mkdir($truepath, $mmode);
            chmod($truepath, $mmode);
            return true;
        } else {
            return true;
        }
    }
}
/**
 *  修改所有模式
 *
 * @access    public
 * @param     string  $truepath  文件路径
 * @param     string  $mmode   模式
 * @return    string
 */
if (!function_exists('ChmodAll')) {
    function ChmodAll($truepath, $mmode)
    {
        return chmod($truepath, '0'.$mmode);
    }
}
/**
 *  创建目录
 *
 * @param     string  $spath  创建的文件夹
 * @return    bool
 */
if (!function_exists('CreateDir')) {
    function CreateDir($spath)
    {
        if (!function_exists('SpCreateDir')) {
            require_once(DEDEINC.'/inc/inc_fun_funAdmin.php');
        }
        return SpCreateDir($spath);
    }
}
/**
 *  写文件
 *
 * @access    public
 * @param     string  $file  文件名
 * @param     string  $content  文档
 * @param     int  $flag   标识
 * @return    string
 */
if (!function_exists('PutFile')) {
    function PutFile($file, $content, $flag = 0)
    {
        $pathinfo = pathinfo($file);
        if (!empty($pathinfo['dirname'])) {
            if (file_exists($pathinfo['dirname']) === FALSE) {
                if (@mkdir($pathinfo['dirname'], 0777, TRUE) === FALSE) {
                    return FALSE;
                }
            }
        }
        if ($flag === FILE_APPEND) {
            return @file_put_contents($file, $content, FILE_APPEND);
        } else {
            return @file_put_contents($file, $content, LOCK_EX);
        }
    }
}
/**
 *  用递归方式删除目录
 *
 * @access    public
 * @param     string    $file   目录文件
 * @return    string
 */
if (!function_exists('RmRecurse')) {
    function RmRecurse($file)
    {
        if (is_dir($file) && !is_link($file)) {
            foreach (glob($file.'/*') as $sf) {
                if (!RmRecurse($sf)) {
                    return false;
                }
            }
            return @rmdir($file);
        } else {
            return @unlink($file);
        }
    }
}
?>