<?php
/**
 * 获取投票代码
 *
 * @version        $Id: vote_getcode.php 1 23:54 2010年7月20日Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2018, DesDev, Inc.
 * @copyright      Copyright (c) 2020, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license/v6
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/dedevote.class.php");
$aid = isset($aid) && is_numeric($aid) ? $aid : 0;
include DedeInclude('templets/vote_getcode.htm');