<?php

/**
 *
 * 自由列表
 *
 * @version        $Id: freelist.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__) . "/../include/common.inc.php");
require_once(DEDEINC . "/arc.freelist.class.php");
if (!empty($lid)) $tid = $lid;

$tid = (isset($tid) && is_numeric($tid) ? $tid : 0);
if ($tid == 0) die(" Request Error! ");

$fl = new FreeList($tid);
$fl->Display();
