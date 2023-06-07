<?php
/**
 * 防采集工具
 *
 * @version        $id:article_string_mix.php 14:31 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
require_once(DEDEINC.'/libraries/oxwindow.class.php');
CheckPurview('sys_StringMix');
if (empty($dopost)) $dopost = '';
if (empty($allsource)) $allsource = '';
else $allsource = stripslashes($allsource);
$m_file = DEDEDATA."/downmix.data.inc";
//保存
if ($dopost == "save") {
    CheckCSRF();
    $fp = fopen($m_file, 'w');
    flock($fp, 3);
    fwrite($fp, $allsource);
    fclose($fp);
    echo "<script>alert('成功保存字符串混淆');</script>";
}
//读出
if (empty($allsource) && filesize($m_file) > 0) {
    $fp = fopen($m_file, 'r');
    $allsource = fread($fp, filesize($m_file));
    fclose($fp);
}
make_hash();
$wecome_info = "防采集工具";
$win = new OxWindow();
$win->Init('article_string_mix.php', 'js/blank.js', 'POST');
$win->AddHidden('dopost', 'save');
$win->AddHidden('token', $_SESSION['token']);
$win->AddTitle("<div class='alert alert-info mb-0'>启用字符串混淆来防采集功能，文档模板需要的字段加上function='RndString(@me)'属性，如：{dede:field name='body' function='RndString(@me)'/}</div>");
$win->AddMsgItem("<tr><td><textarea name='allsource' id='allsource' class='admin-textarea-xl'>$allsource</textarea></td></tr>");
$winform = $win->GetWindow('ok');
$win->Display();
?>