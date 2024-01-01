<?php
/**
 * 栏目管理
 *
 * @version        $id:catalog_main.php 14:31 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/typelink/typeunit.class.admin.php");
$userChannel = $cuserLogin->getUserChannel();
include DedeInclude('templets/catalog_main.htm');
?>