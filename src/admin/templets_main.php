<?php
/**
 * 主题模板管理
 *
 * @version        $id:templets_main.php 23:07 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert("系统已启用安全模式，无法使用当前功能",ALERT_DANGER));
}
CheckPurview('plus_文件管理器');
if (empty($acdir)) $acdir = $cfg_df_style;
$templetdir = $cfg_basedir.$cfg_templets_dir;
$templetdird = $templetdir.'/'.$acdir;
$templeturld = $cfg_templeturl.'/'.$acdir;
if (preg_match("#\.#", $acdir)) {
    ShowMsg('Not Allow dir '.$acdir.'!', '-1');
    exit();
}
//获取默认文件说明信息
function GetInfoArray($filename)
{
    $arrs = array();
    $dlist = file($filename);
    foreach ($dlist as $d) {
        $d = trim($d);
        if ($d != '') {
            list($dname, $info) = explode(',', $d);
            $arrs[$dname] = $info;
        }
    }
    return $arrs;
}
$dirlists  = GetInfoArray($templetdir.'/templet-dirlist.inc');
$filelists = GetInfoArray($templetdir.'/templet-filelist.inc');
$pluslists = GetInfoArray($templetdir.'/templet-appslist.inc');
$fileinfos = ($acdir == 'plus' ? $pluslists : $filelists);
include DedeInclude('templets/templets_default.htm');
?>