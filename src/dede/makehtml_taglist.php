<?php
/**
 * 生成Tag
 *
 * @version        $Id: makehtml_taglist.php 1 11:17 2020年8月19日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
$tid = isset($tid)? $tid : 0;
include DedeInclude('templets/makehtml_taglist.htm');
