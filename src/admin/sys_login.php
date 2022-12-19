<?php
/**
 * 系统登录配置
 *
 * @version        $id:sys_info.php 22:28 2022年12月5日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_Edit');

include DedeInclude("templets/sys_login.htm");