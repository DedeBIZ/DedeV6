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
    echo "<script>alert('Save OK!');</script>";
}
//读出
if (empty($allwriter) && filesize($m_file) > 0) {
    $fp = fopen($m_file, 'r');
    $allwriter = fread($fp, filesize($m_file));
    fclose($fp);
}
$wintitle = "文档作者管理";
$wecome_info = "文档作者管理";
$win = new OxWindow();
$win->Init('article_writer_edit.php', 'js/blank.js', 'POST');
$win->AddHidden('dopost', 'save');
$win->AddTitle("把作者姓名用半角逗号“,”分开：");
$win->AddMsgItem("<textarea name='allwriter' id='allwriter'class='biz-textarea'>$allwriter</textarea>");
$winform = $win->GetWindow('ok');
$win->Display();
?>