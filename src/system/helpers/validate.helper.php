<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 验证助手
 *
 * @version        $id:validate.helper.php 2010-07-05 11:43:09 tianya $
 * @package        DedeBIZ.Helpers
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
//邮箱格式检查
if (!function_exists('CheckEmail')) {
    function CheckEmail($email)
    {
        if (!empty($email)) {
            return preg_match('/^[a-z0-9]+([\+_\-\.]?[a-z0-9]+)*@([a-z0-9]+[\-]?[a-z0-9]+\.)+[a-z]{2,6}$/i', $email);
        }
        return FALSE;
    }
}
?>