<?php
/**
 * 文档作者管理
 *
 * @version        $Id: article_writer_edit.php 1 14:31 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__).'/config.php');
UserLogin::CheckPurview('sys_Writer');
if (empty($dopost)) $dopost = '';
if (empty($allwriter)) $allwriter = '';
else $allwriter = stripslashes($allwriter);
$m_file = DEDEDATA."/admin/writer.txt";
//保存
if ($dopost == "save") {
    $fp = fopen($m_file, 'w');
    flock($fp, 3);
    fwrite($fp, $allwriter);
    fclose($fp);
    echo "<script>alert('Save OK!');</script>";
}
//读出
if (empty($allwriter) && filesize($m_file) > 0) {
    $fp = fopen($m_file, 'r');
    $allwriter = fread($fp, filesize($m_file));
    fclose($fp);
}
$wintitle = Lang("content_writer_main");
$wecome_info = Lang("content_writer_main");
DedeWin::Instance()->Init('article_writer_edit.php', 'js/blank.js', 'POST')
->AddHidden('dopost', 'save')
->AddTitle(Lang("content_writer_main_title"))
->AddMsgItem("<textarea name='allwriter' id='allwriter' style='width:100%;height:300px'>$allwriter</textarea>")
->GetWindow('ok')->Display();
?>