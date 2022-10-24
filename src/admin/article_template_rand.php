<?php
/**
 * 文档随机模板
 *
 * @version        $Id: article_template_rand.php 1 14:31 2010年7月12日Z tianya $
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
$templates = empty($templates) ? '' : stripslashes($templates);
$m_file = DEDEDATA.'/template.rand.txt';
$okmsg = '';
//保存配置
if ($dopost == 'save') {
    CheckCSRF();
    $fp = fopen($m_file, 'w');
    flock($fp, 3);
    fwrite($fp, $templates);
    fclose($fp);
    $okmsg = Lang('article_template_rand_success_save');
}
//对旧文档进行随机模板处理
else if ($dopost == 'makeold') {
    CheckCSRF();
    set_time_limit(3600);
    if (!file_exists($m_file)) {
        AjaxHead();
        echo Lang("article_template_rand_err_filenotexists");
        exit();
    }
    $fileData = file_get_contents($m_file);
    $arrs = preg_split("#[\t\r\n]#", $fileData);
    $cfg_tamplate_arr = array();
    foreach ($arrs as $value) {
        if (trim($value) !== "") {
            $cfg_tamplate_arr[] = trim($value);
        }
    }
    if ($cfg_tamplate_rand == 0) {
        AjaxHead();
        echo Lang("article_template_rand_err_cfg");
        exit();
    }
    $totalTmp = count($cfg_tamplate_arr) - 1;
    if ($totalTmp < 1) {
        AjaxHead();
        echo Lang("article_template_rand_err_tt");
        exit();
    }
    for ($i = 0; $i < 10; $i++) {
        $temp = $cfg_tamplate_arr[mt_rand(0, $totalTmp)];
        $dsql->ExecuteNoneQuery("UPDATE `#@__addonarticle` set templet='$temp' where RIGHT(aid, 1)='$i'");
    }
    AjaxHead();
    echo Lang("article_template_rand_success");
    exit();
}
//清除全部的指定模板
else if ($dopost == 'clearold') {
    CheckCSRF();
    $dsql->ExecuteNoneQuery("UPDATE `#@__addonarticle` set templet=''");
    $dsql->ExecuteNoneQuery(" OPTIMIZE TABLE `#@__addonarticle`");
    AjaxHead();
    echo Lang("article_template_rand_success");
    exit();
}
//读出
if (empty($templates) && filesize($m_file) > 0) {
    $fp = fopen($m_file, 'r');
    $templates = fread($fp, filesize($m_file));
    fclose($fp);
}
$wintitle = Lang("article_template_rand");
$wecome_info = Lang("article_template_rand");
make_hash();
$msg = "
<link rel='stylesheet' href='../static/web/css/admin.min.css'>
<script src='js/main.js'></script>
<script src='../static/web/js/webajax.js'></script>
<script>
function DoRand(jobname)
{
    ChangeFullDiv('show');
    \$DE('loading').style.display = 'block';
    fetch('article_template_rand.php?dopost='+jobname+'&token={$_SESSION['token']}').then(resp=>resp.text()).then((d)=>{
        \$DE('tmpct').innerHTML = d;
        \$DE('loading').style.display = 'none';
        ChangeFullDiv('hide');
    });
}
</script>
<div id='loading' style='position:absolute;top:160;display:none;z-index:3000'>
    <img src='../static/web/img/load.gif'>".Lang('article_template_rand_doing')."
</div>
<table width='100%' align='center'>
<tr>
    <td>
    ".Lang('article_template_rand_tip')."
    <a href='javascript:;' onclick='DoRand(\"makeold\")' class='btn btn-success btn-sm'>".Lang('article_template_rand_makeold')."</a>
    <a href='javascript:;' onclick='DoRand(\"clearold\")' class='btn btn-success btn-sm'>".Lang('article_template_rand_clearold')."</a>
    <span id='tmpct' style='color:#dc3545;font-weight:bold'>$okmsg</span>
    </td>
</tr>
<tr>
    <td><textarea name='templates' id='templates' style='width:100%;height:250px'>$templates</textarea></td>
</tr>
</table>";
DedeWin::Instance()->Init('article_template_rand.php', 'js/blank.js', 'POST')
->AddHidden('dopost', 'save')
->AddHidden('token', $_SESSION['token'])
->AddTitle(Lang("article_template_rand_title"))
->AddMsgItem($msg)
->GetWindow('ok')->Display();
?>