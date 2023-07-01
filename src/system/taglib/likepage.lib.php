<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 单页文档相同标识标签
 *
 * @version        $id:likepage.lib.php 9:29 2010年7月6日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/likesgpage.lib.php');
function lib_likepage(&$ctag, &$refObj)
{
    return lib_likesgpage($ctag, $refObj);
}
?>