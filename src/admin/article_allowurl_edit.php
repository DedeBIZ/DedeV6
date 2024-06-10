<?php
/**
 * 允许站内链接
 *
 * @version        $id:article_allowurl_edit.php 11:36 2010年10月8日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/libraries/oxwindow.class.php");
CheckPurview('sys_Source');
if (empty($dopost)) $dopost = '';
if (empty($allurls)) $allsource = '';
else $allurls = stripslashes($allurls);
$m_file = DEDEDATA."/admin/allowurl.txt";
//保存
if ($dopost == 'save') {
    $fp = fopen($m_file, 'w');
    flock($fp, 3);
    fwrite($fp, $allurls);
    fclose($fp);
    echo "<script>alert('成功保存站内链接');</script>";
}
//读出
if (empty($allurls) && filesize($m_file) > 0) {
    $fp = fopen($m_file, 'r');
    $allurls = fread($fp, filesize($m_file));
    fclose($fp);
}
$wintitle = "允许站内链接";
$win = new OxWindow();
$win->Init('article_allowurl_edit.php', '/static/web/js/admin.blank.js', 'POST');
$win->AddHidden('dopost', 'save');
$win->AddTitle("每行保存一个超链接");
$win->AddMsgItem("<tr><td><textarea name='allurls' id='allurls' class='admin-textarea-xl'>$allurls</textarea></td></tr>");
$winform = $win->GetWindow('ok');
$win->Display();
?>