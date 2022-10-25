<?php
/**
 * 允许的站内链接
 *
 * @version        $Id: article_allowurl_edit.php 1 11:36 2010年10月8日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
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
    echo "<script>alert('Save OK!');</script>";
}
//读出
if (empty($allurls) && filesize($m_file) > 0) {
    $fp = fopen($m_file, 'r');
    $allurls = fread($fp, filesize($m_file));
    fclose($fp);
}
$wintitle = "";
$wecome_info = "允许的超链接";
$win = new OxWindow();
$win->Init('article_allowurl_edit.php', 'js/blank.js', 'POST');
$win->AddHidden('dopost', 'save');
$win->AddTitle("每行保存一个超链接：");
$win->AddMsgItem("<textarea name='allurls' id='allurls'class='biz-textarea'>$allurls</textarea>");
$winform = $win->GetWindow('ok');
$win->Display();
?>