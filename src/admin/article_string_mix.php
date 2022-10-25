<?php
/**
 * 防采集混淆字符串管理
 *
 * @version        $Id: article_string_mix.php 1 14:31 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
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
    echo "<script>alert('Save OK!');</script>";
}
//读出
if (empty($allsource) && filesize($m_file) > 0) {
    $fp = fopen($m_file, 'r');
    $allsource = fread($fp, filesize($m_file));
    fclose($fp);
}
make_hash();
$wintitle = "防采集混淆字符串管理";
$wecome_info = "防采集混淆字符串管理";
$win = new OxWindow();
$win->Init('article_string_mix.php', 'js/blank.js', 'POST');
$win->AddHidden('dopost', 'save');
$win->AddHidden('token', $_SESSION['token']);
$win->AddTitle("如果您要启用字符串混淆来防采集，请在文档模板需要的字段加上 function='RndString(@me)' 属性，如：{dede:field name='body' function='RndString(@me)'/}");
$win->AddMsgItem("<textarea name='allsource' id='allsource' class='biz-textarea'>$allsource</textarea>");
$winform = $win->GetWindow('ok');
$win->Display();
?>