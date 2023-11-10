<?php
/**
 * 下载次数
 *
 * 显示下载次数：<script src="{dede:global name='cfg_phpurl'/}/disdls.php?aid={dede:field name='id'/}"></script>
 *
 * @version        $id:disdls.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../system/common.inc.php");
$aid = (isset($aid) && is_numeric($aid)) ? $aid : 0;
$row = $dsql->GetOne("SELECT SUM(downloads) AS totals FROM `#@__downloads` WHERE id='$aid' ");
if (empty($row['totals'])) $row['totals'] = 0;
echo "document.write('{$row['totals']}');";
exit();
?>