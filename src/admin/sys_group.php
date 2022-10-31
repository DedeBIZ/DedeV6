<?php
/**
 * 系统权限组
 *
 * @version        $Id: sys_group.php 1 22:28 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_Group');
if (empty($dopost)) $dopost = "";
include DedeInclude('templets/sys_group.htm');
?>