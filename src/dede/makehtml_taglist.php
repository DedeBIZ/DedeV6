<?php
/**
 * 生成Tag
 *
 * @version        $Id: makehtml_taglist.php 1 11:17 2020年8月19日Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2020, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license/v6
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
$tid = isset($tid)? $tid : 0;
include DedeInclude('templets/makehtml_taglist.htm');

?>