<?php
/**
 * 生成网站地图
 *
 * @version        $Id: makehtml_map.php 1 11:17 2010年7月19日Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2020, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license/v6
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/sitemap.class.php");
require_once(DEDEINC."/dedetag.class.php");

if(empty($dopost))
{
    ShowMsg("参数错误!","-1");
    exit();
}

$serviterm=empty($serviterm)? "" : $serviterm;
$sm = new SiteMap();
$maplist = $sm->GetSiteMap($dopost);
if($dopost=="site")
{
    $murl = $cfg_cmspath."/data/sitemap.html";
    $tmpfile = $cfg_basedir.$cfg_templets_dir."/plus/sitemap.htm";
}
else
{
    $murl = $cfg_cmspath."/data/rssmap.html";
    $tmpfile = $cfg_basedir.$cfg_templets_dir."/plus/rssmap.htm";
}
$dtp = new DedeTagParse();
$dtp->LoadTemplet($tmpfile);
$dtp->SaveTo($cfg_basedir.$murl);
$dtp->Clear();
echo "<link rel=\"stylesheet\" href=\"{$cfg_cmsurl}/static/css/bootstrap.min.css\"><style>.modal {position: static;}</style>";
echo "<div class=\"alert alert-success\" role=\"alert\">成功更新文件: $murl <a href='$murl' target='_blank' class='btn btn-secondary'>浏览...</a></div>";
exit();