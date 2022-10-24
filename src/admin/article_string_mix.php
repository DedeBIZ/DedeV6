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
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__).'/config.php');
UserLogin::CheckPurview('sys_StringMix');
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
    echo "<script>alert('".Lang('operation_successful')."');</script>";
}
//读出
if (empty($allsource) && filesize($m_file) > 0) {
    $fp = fopen($m_file, 'r');
    $allsource = fread($fp, filesize($m_file));
    fclose($fp);
}
make_hash();
$wintitle = Lang("article_string_mix");
$wecome_info = Lang("article_string_mix");
DedeWin::Instance()->Init('article_string_mix.php', 'js/blank.js', 'POST')
->AddHidden('dopost', 'save')
->AddHidden('token', $_SESSION['token'])
->AddTitle(Lang("article_string_mix_title"))
->AddMsgItem("<textarea name='allsource' id='allsource' style='width:100%;height:300px'>$allsource</textarea>")
->GetWindow('ok')->Display();
?>