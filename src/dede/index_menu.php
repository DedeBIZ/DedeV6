<?php
/**
 * 菜单项
 *
 * @version        $Id: index_menu.php 1 11:06 2010年7月13日Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2018, DesDev, Inc.
 * @copyright      Copyright (c) 2020, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license/v6
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__).'/config.php');
require(DEDEADMIN.'/inc/inc_menu.php');
require(DEDEADMIN.'/inc/inc_menu_func.php');
$openitem = (empty($openitem) ? 1 : $openitem);
include DedeInclude('templets/index_menu2.htm');
