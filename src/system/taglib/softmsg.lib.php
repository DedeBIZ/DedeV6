<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 下载说明标签
 *
 * @version        $id:softmsg.lib.php 9:29 2010年7月6日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
function lib_softmsg(&$ctag, &$refObj)
{
    global $dsql;
    $revalue = '';
    $row = $dsql->GetOne(" SELECT * FROM `#@__softconfig`");
    if (is_array($row)) $revalue = $row['downmsg'];
    return $revalue;
}
?>