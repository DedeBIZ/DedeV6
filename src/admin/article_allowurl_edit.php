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
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('sys_Source');
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
$wecome_info = Lang("content_allowurl_edit");
DedeWin::Instance()->Init('article_allowurl_edit.php', 'js/blank.js', 'POST')->AddHidden('dopost', 'save')
->AddTitle(Lang("content_allowurl_edit_title"))->AddMsgItem("<textarea name='allurls' id='allurls' style='width:100%;height:300px'>$allurls</textarea>")->GetWindow('ok')->Display();
?>