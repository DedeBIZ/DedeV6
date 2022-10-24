<?php
namespace DedeBIZ\API;
/**
 * 默认控制器
 *
 * @version        $Id: api.php$
 * @package        DedeBIZ.API
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\Control;
if (!defined('DEDEAPI')) exit('dedebiz');
class ApiControl extends Control {
    public function ping()
    {
        $this->message(0, "pong");
    }
}
?>