<?php
/**
 * 栏目管理
 *
 * @version        $Id: catalog_main.php 1 14:31 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/typeunit.class.admin.php");
$userChannel = $cuserLogin->getUserChannel();
include DedeInclude('templets/catalog_main.htm');