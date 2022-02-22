<?php
/**
 * 文件管理器
 *
 * @version        $Id: file_manage_main.php 1 8:48 2010年7月13日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__)."/config.php");
CheckPurview('plus_文件管理器');
if (!isset($activepath)) $activepath = $cfg_cmspath;

$inpath = "";
$activepath = str_replace("..", "", $activepath);
$activepath = preg_replace("#^\/{1,}#", "/", $activepath);
if ($activepath == "/") $activepath = "";

if ($activepath == "") $inpath = $cfg_basedir;
else $inpath = $cfg_basedir.$activepath;

$activeurl = $activepath;
if (preg_match("#".$cfg_templets_dir."#i", $activepath)) {
    $istemplets = TRUE;
} else {
    $istemplets = FALSE;
}
include DedeInclude('templets/file_manage_main.htm');
