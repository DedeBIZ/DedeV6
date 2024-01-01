<?php
/**
 * 自由列表
 *
 * @version        $id:freelist.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../system/common.inc.php");
require_once(DEDEINC."/archive/freelist.class.php");
if (!empty($lid)) $tid = $lid;
$tid = (isset($tid) && is_numeric($tid) ? $tid : 0);
if ($tid == 0) die("dedebiz");
$fl = new FreeList($tid);
$fl->Display();
?>