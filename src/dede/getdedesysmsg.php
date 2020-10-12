<?php
/**
 * 获取dede系统提示信息
 *
 * @version        $Id: getdedesysmsg.php 1 11:06 2010年7月13日Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2020, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license/v6
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
require_once(DEDEINC.'/dedehttpdown.class.php');
AjaxHead();
$dhd = new DedeHttpDown();
$dhd->OpenUrl('http://www.dedecms.com/officialinfo.html');
$str = trim($dhd->GetHtml());
$dhd->Close();
if($cfg_soft_lang=='utf-8') $str = gb2utf8($str);
echo $str;
