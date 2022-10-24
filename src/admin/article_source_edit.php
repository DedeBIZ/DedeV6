<?php
/**
 * 文档来源编辑
 *
 * @version        $Id: archives_add.php 1 14:30 2010年7月12日Z tianya $
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
if (empty($allsource)) $allsource = '';
else $allsource = stripslashes($allsource);
$m_file = DEDEDATA."/admin/source.txt";
//保存
if ($dopost == 'save') {
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
$wintitle = Lang("content_source_main");
$wecome_info = Lang("content_source_main");
DedeWin::Instance()->Init('article_source_edit.php', 'js/blank.js', 'POST')->AddHidden('dopost', 'save')
->AddTitle(Lang("content_source_main_title"))->AddMsgItem("<textarea name='allsource' id='allsource' style='width:100%;height:300px'>$allsource</textarea>")->GetWindow('ok')->Display();
?>