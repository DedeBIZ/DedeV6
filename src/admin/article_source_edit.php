<?php
/**
 * 文档来源管理
 *
 * @version        $id:archives_add.php 14:30 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/libraries/oxwindow.class.php");
CheckPurview('sys_Source');
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
    echo "<script>alert('成功保存文档来源');</script>";
}
//读出
if (empty($allsource) && filesize($m_file) > 0) {
    $fp = fopen($m_file, 'r');
    $allsource = fread($fp, filesize($m_file));
    fclose($fp);
}
$wintitle = "文档来源管理";
$win = new OxWindow();
$win->Init('article_source_edit.php', '/static/web/js/admin.blank.js', 'POST');
$win->AddHidden('dopost', 'save');
$win->AddTitle("一行填写一个地址");
$win->AddMsgItem("<tr><td><textarea name='allsource' id='allsource' class='admin-textarea-xl'>$allsource</textarea></td></tr>");
$winform = $win->GetWindow('ok');
$win->Display();
?>