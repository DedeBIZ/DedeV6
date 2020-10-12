<?php
/**
 * 退出
 *
 * @version        $Id: exit.php 1 19:09 2010年7月12日Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2020, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license/v6
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/../include/common.inc.php');
require_once(DEDEINC.'/userlogin.class.php');
$cuserLogin = new userLogin();
$cuserLogin->exitUser();
if(empty($needclose))
{
    header('location:index.php');
}
else
{
    $msg = "<script language='javascript'>
    if(document.all) window.opener=true;
    window.close();
    </script>";
    echo $msg;
}