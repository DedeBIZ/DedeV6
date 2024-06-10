<?php
/**
 * 文档作者管理
 *
 * @version        $id:article_writer_edit.php 14:31 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
require_once(DEDEINC.'/libraries/oxwindow.class.php');
CheckPurview('sys_Writer');
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
    echo "<script>alert('成功保存文档作者');</script>";
}
//读出
if (empty($allwriter) && filesize($m_file) > 0) {
    $fp = fopen($m_file, 'r');
    $allwriter = fread($fp, filesize($m_file));
    fclose($fp);
}
$wintitle = "文档作者管理";
$win = new OxWindow();
$win->Init('article_writer_edit.php', '/static/web/js/admin.blank.js', 'POST');
$win->AddHidden('dopost', 'save');
$win->AddTitle("作者姓名用英文逗号隔开");
$win->AddMsgItem("<tr><td><textarea name='allwriter' id='allwriter' class='admin-textarea-xl'>$allwriter</textarea></td></tr>");
$winform = $win->GetWindow('ok');
$win->Display();
?>