<?php
/**
 * 添加一个模板
 *
 * @version        $Id: templets_one_add.php 1 23:07 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Archive\Sgpage;
use DedeBIZ\Login\UserLogin;
require(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('temp_One');
if (empty($dopost)) $dopost = "";
if ($dopost == "save") {
    $uptime = time();
    $body = str_replace('&quot;', '\\"', $body);
    $filename = preg_replace("#^\/#", "", $nfilename);
    if (DEDEBIZ_SAFE_MODE) $ismake = 0; //安全模式不允许编译
    if (!preg_match('#\.htm$#i', trim($template))) {
        ShowMsg(Lang("media_ext_forbidden"), "javascript:;");
        exit();
    }
    if ($likeid == '') {
        $likeid = $likeidsel;
    }
    $row = $dsql->GetOne("SELECT filename FROM `#@__sgpage` WHERE likeid='$likeid' AND filename LIKE '$filename'");
    if (is_array($row)) {
        ShowMsg(Lang("templets_one_name_exists"), "-1");
        exit();
    }
    $inQuery = "INSERT INTO `#@__sgpage`(title,keywords,description,template,likeid,ismake,filename,uptime,body) VALUES ('$title','$keywords','$description','$template','$likeid','$ismake','$filename','$uptime','$body');";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        ShowMsg(Lang("templets_one_err_add"), "-1");
        exit();
    }
    $id = $dsql->GetLastID();
    $sg = new Sgpage($id);
    $sg->SaveToHtml();
    ShowMsg(Lang("templets_one_add_success"), "templets_one.php");
    exit();
}
$row = $dsql->GetOne("SELECT MAX(aid) AS aid FROM `#@__sgpage`");
$nowid = is_array($row) ? $row['aid'] + 1 : '';
include_once(DEDEADMIN."/templets/templets_one_add.htm");
?>