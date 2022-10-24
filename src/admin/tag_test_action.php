<?php
/**
 * 标签测试操作
 *
 * @version        $Id: tag_test_action.php 1 23:07 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Archive\PartView;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert(Lang("err_safemode_check"),ALERT_DANGER));
}
UserLogin::CheckPurview('temp_Test');
CheckCSRF();
if (empty($partcode)) {
    ShowMsg(Lang('tag_test_err_submit'), 'javascript:;');
    exit;
}
$partcode = stripslashes($partcode);
if (empty($typeid)) $typeid = 0;
if (empty($showsource)) $showsource = "";
if ($typeid > 0) $pv = new PartView($typeid);
else $pv = new PartView();
$pv->SetTemplet($partcode, "string");
if ($showsource == "" || $showsource == "yes") {
    echo Lang('tag_test_tcode').":";
    echo "<span class='text-danger'><pre>".dede_htmlspecialchars($partcode)."</pre></span>";
    echo Lang('result').":<hr size='1' width='100%'>";
}
$pv->Display();
?>