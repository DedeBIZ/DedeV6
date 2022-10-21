<?php
/**
 * 下载次数统计
 *
 * 如果想显示下载次数，即把下面js调用放到文档模板适当位置
 * <script src="{dede:global name='cfg_phpurl'/}/disdls.php?aid={dede:field name='id'/}"></script>
 *
 * @version        $Id: disdls.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../system/common.inc.php");
$aid = (isset($aid) && is_numeric($aid)) ? $aid : 0;
$row = $dsql->GetOne("SELECT SUM(downloads) AS totals FROM `#@__downloads` WHERE id='$aid' ");
if (empty($row['totals'])) $row['totals'] = 0;
echo "document.write('{$row['totals']}');";
exit();