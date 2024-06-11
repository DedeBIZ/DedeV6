<?php
/**
 * 文件管理器
 *
 * @version        $id:file_manage_main.php 8:48 2010年7月13日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__)."/config.php");
CheckPurview('plus_文件管理器');
if (!isset($activepath)) $activepath = DEDEBIZ_SAFE_MODE? $cfg_medias_dir : $cfg_cmspath;
$inpath = '';
$activepath = str_replace("..", "", $activepath);
$activepath = preg_replace("#^\/{1,}#", "/", $activepath);
if (DEDEBIZ_SAFE_MODE && !preg_match("#^/static#",$activepath)) {
    ShowMsg("安全模式下仅允许查看修改static目录文档", -1);
    exit;
}
if ($activepath == "/") $activepath = '';
if ($activepath == "") $inpath = $cfg_basedir;
else $inpath = $cfg_basedir.$activepath;
$activeurl = $activepath;
if (preg_match("#".$cfg_templets_dir."#i", $activepath)) {
    $istemplets = TRUE;
} else {
    $istemplets = FALSE;
}
include DedeInclude('templets/file_manage_main.htm');
?>