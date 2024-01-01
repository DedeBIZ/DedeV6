<?php
/**
 * 广告js
 *
 * @version        $id:ad_js.php 20:30 2010年7月8日 tianya $
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../system/common.inc.php");
if (isset($arcID)) $aid = $arcID;
$arcID = $aid = (isset($aid) && is_numeric($aid)) ? $aid : 0;
if ($aid == 0) die('dedebiz');
$cacheFile = DEDEDATA.'/cache/myad-'.$aid.'.htm';
if (isset($nocache) || !file_exists($cacheFile) || time() - filemtime($cacheFile) > $cfg_puccache_time) {
    $row = $dsql->GetOne("SELECT * FROM `#@__myad` WHERE aid='$aid' ");
    $adbody = '';
    if ($row['timeset'] == 0) {
        $adbody = $row['normbody'];
    } else {
        $ntime = time();
        if ($ntime > $row['endtime'] || $ntime < $row['starttime']) {
            $adbody = $row['expbody'];
        } else {
            $adbody = $row['normbody'];
        }
    }
    $adbody = str_replace('"', '\"', $adbody);
    $adbody = str_replace("\r", "\\r", $adbody);
    $adbody = str_replace("\n", "\\n", $adbody);
    $adbody = "<!--document.write(\"{$adbody}\");-->";
    $fp = fopen($cacheFile, 'w');
    fwrite($fp, $adbody);
    fclose($fp);
}
include $cacheFile;
?>