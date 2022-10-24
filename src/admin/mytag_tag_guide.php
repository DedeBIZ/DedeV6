<?php
/**
 * 自定义标记向导
 *
 * @version        $Id: mytag_tag_guide.php 1 15:39 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert(Lang("err_safemode_check"),ALERT_DANGER));
}
include DedeInclude('templets/mytag_tag_guide.htm');
?>