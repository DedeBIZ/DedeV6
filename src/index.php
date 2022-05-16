<?php
/**
 * @version        $Id: index.php 1 9:23 2022-05-16 tianya $
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
if (!file_exists(dirname(__FILE__).'/data/common.inc.php')) {
    header('Location:install/index.php');
    exit();
}
//自动生成网页
if (isset($_GET['upcache']) || !file_exists('index.html')) {
    require_once(dirname(__FILE__)."/system/common.inc.php");
    require_once DEDEINC."/archive/partview.class.php";
    $GLOBALS['_arclistEnv'] = 'index';
    $row = $dsql->GetOne("SELECT * FROM `#@__homepageset`");
    $row['templet'] = MfTemplet($row['templet']);
    $pv = new PartView();
    $pv->SetTemplet($cfg_basedir.$cfg_templets_dir."/".$row['templet']);
    $row['showmod'] = isset($row['showmod']) ? $row['showmod'] : 0;
    if ($row['showmod'] == 1) {
        $pv->SaveToHtml(dirname(__FILE__).'/index.html');
        include(dirname(__FILE__).'/index.html');
        exit();
    } else {
        $pv->Display();
        exit();
    }
} else {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location:index.html');
}