<?php
/**
 * 单页文档相同标识调用标签
 *
 * @version        $Id: likepage.lib.php 1 9:29 2010年7月6日Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2020, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
 

if(!defined('DEDEINC')) exit('Request Error!');
require_once(dirname(__FILE__).'/likesgpage.lib.php');

function lib_likepage(&$ctag,&$refObj)
{
    return lib_likesgpage($ctag, $refObj);
}
