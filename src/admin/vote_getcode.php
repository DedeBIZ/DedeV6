<?php
/**
 * 获取投票代码
 *
 * @version        $id:vote_getcode.php 23:54 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/dedevote.class.php");
$aid = isset($aid) && is_numeric($aid) ? $aid : 0;
include DedeInclude('templets/vote_getcode.htm');
?>