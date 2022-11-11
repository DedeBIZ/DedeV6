<?php
/**
 * 标签测试操作
 *
 * @version        $id:tag_test_action.php 23:07 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert("系统已启用安全模式，无法使用当前功能",ALERT_DANGER));
}
CheckPurview('temp_Test');
require_once(DEDEINC."/archive/partview.class.php");
CheckCSRF();
if (empty($partcode)) {
    ShowMsg('错误请求', 'javascript:;');
    exit;
}
$partcode = stripslashes($partcode);
if (empty($typeid)) $typeid = 0;
if (empty($showsource)) $showsource = "";
if ($typeid > 0) $pv = new PartView($typeid);
else $pv = new PartView();
$pv->SetTemplet($partcode, "string");
if ($showsource == "" || $showsource == "yes") {
    echo "模板代码:";
    echo "<span class='text-primary'><pre>".dede_htmlspecialchars($partcode)."</pre></span>";
    echo "结果:<hr size='1' width='100%'>";
}
$pv->Display();
?>