<?php

/**
 * 检测重复文档
 *
 * @version        $Id: article_test_same.php 1 14:31 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
AjaxHead();
if (empty($t) || $cfg_check_title == 'N') exit;

$row = $dsql->GetOne("SELECT id FROM `#@__archives` WHERE title LIKE '$t' ");
if (is_array($row)) {
    echo "提示：系统已经存在标题为 '<a href='../plus/view.php?aid={$row['id']}' style='color:red' target='_blank'><u>$t</u></a>' 的文档。[<a href='#' onclick='javascript:HideObj(\"mytitle\")'>关闭</a>]";
}
