<?php
/**
 * 智能标记向导
 *
 * @version        $id:mytag_tag_guide.php 15:39 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert("系统已启用安全模式，无法使用当前功能",ALERT_DANGER));
}
require_once(DEDEINC."/typelink/typelink.class.php");
include DedeInclude('templets/mytag_tag_guide.htm');
?>