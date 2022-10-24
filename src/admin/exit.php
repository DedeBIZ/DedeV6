<?php
/**
 * 退出
 *
 * @version        $Id: exit.php 1 19:09 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__).'/../system/common.inc.php');
$cUserLogin = new UserLogin();
$cUserLogin->exitUser();
if (empty($needclose)) {
    header('location:index.php');
} else {
    $msg = "<script>
    if (document.all) window.opener=true;
    window.close();
    </script>";
    echo $msg;
}
?>