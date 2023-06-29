<?php
/**
 * 会员登录
 * 
 * @version        $id:login.php 8:38 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
$gourl = RemoveXSS($gourl);
if ($cfg_ml->IsLogin()) {
    ShowMsg('正在登录会员中心，请稍等', 'index.php');
    exit();
}
require_once(dirname(__FILE__)."/templets/login.htm");
?>