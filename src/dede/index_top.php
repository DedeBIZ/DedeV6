<?php

/**
 * 管理后台顶部
 *
 * @version        $Id: index_top.php 1 8:48 2010年7月13日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__) . "/config.php");
if ($cuserLogin->adminStyle == 'dedecms') {
    include DedeInclude('templets/index_top1.htm');
} else {
    include DedeInclude('templets/index_top2.htm');
}
