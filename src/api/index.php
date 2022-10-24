<?php
namespace DedeBIZ\API;
use DedeBIZ\libraries\Control;
/**
 * API接口
 *
 * @version        $Id: api.php$
 * @package        DedeBIZ.API
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
//define('LANGSECTION', 'api');
define('DEDEAPI', dirname(__FILE__));
define('DEDEAPI_DEBUG', TRUE);
require_once(dirname(__FILE__)."/../system/common.inc.php");
$ct = isset($ct)? $ct : 'api';
$ac = isset($ac)? $ac : 'index';
RunAPI($ct, $ac);
?>