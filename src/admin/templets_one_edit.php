<?php
/**
 * 编辑一个模板
 *
 * @version        $Id: templets_one_edit.php 1 23:07 2010年7月20日Z tianya $
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
$aid = isset($aid) && is_numeric($aid) ? $aid : 0;
if ($dopost == "saveedit") {
    $uptime = time();
    $body = str_replace('&quot;', '\\"', $body);
    $filename = preg_replace("#^\/#", "", $nfilename);
    if (DEDEBIZ_SAFE_MODE) $ismake = 0; //安全模式不允许编译
    if (!preg_match('#\.htm$#i', trim($template))) {
        ShowMsg(Lang("media_ext_forbidden"), "javascript:;");
        exit();
    }
    //如果修改了文件名，删除旧文件
    if ($oldfilename != $filename) {
        $oldfilename = $cfg_basedir.$cfg_cmspath."/".$oldfilename;
        if (is_file($oldfilename)) {
            unlink($oldfilename);
        }
    }
    if ($likeidsel != $oldlikeid) {
        $likeid = $likeidsel;
    }
    $inQuery = "
     UPDATE `#@__sgpage` SET
     title='$title',
     keywords='$keywords',
    DESCription='$description',
     likeid='$likeid',
     ismake='$ismake',
     filename='$filename',
     template='$template',
     uptime='$uptime',
     body='$body'
    WHERE aid='$aid'; ";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        ShowMsg(Lang("templets_one_err_update"), "-1");
        exit();
    }
    $sg = new Sgpage($aid);
    $sg->SaveToHtml();
    ShowMsg(Lang("templets_one_update_success"), "templets_one.php");
    exit();
} else if ($dopost == "delete") {
    $row = $dsql->GetOne("SELECT filename FROM `#@__sgpage` WHERE aid='$aid'");
    $filename = preg_replace("#\/{1,}#", "/", $cfg_basedir.$cfg_cmspath."/".$row['filename']);
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__sgpage` WHERE aid='$aid'");
    if (is_file($filename)) {
        unlink($filename);
    }
    ShowMsg(Lang("templets_one_delete_success"), "templets_one.php");
    exit();
} else if ($dopost == "make") {
    $row = $dsql->GetOne("SELECT filename FROM `#@__sgpage` WHERE aid='$aid'");
    $fileurl = $cfg_cmsurl.'/'.preg_replace("#\/{1,}#", "/", $row['filename']);
    $sg = new Sgpage($aid);
    $sg->SaveToHtml();
    ShowMsg(Lang("templets_one_make_success"), $fileurl);
    exit();
} else if ($dopost == "mkall") {
    $dsql->Execute("ex", "SELECT aid FROM `#@__sgpage`");
    $i = 0;
    while ($row = $dsql->GetArray("ex")) {
        $sg = new Sgpage($row['aid']);
        $sg->SaveToHtml();
        $i++;
    }
    ShowMsg(Lang("templets_one_makei_success",array('i'=>$i)), '-1');
    exit();
} else if ($dopost == "mksel") {
    if (empty($ids)) {
        $ids = '';
    }
    $i = 0;
    if ($ids == 0) {
        ShowMsg(Lang('templets_one_err_noselect'), '-1');
        exit();
    } else if (is_array($ids)) {
        foreach ($ids as $aid) {
            $sg = new Sgpage($aid);
            $sg->SaveToHtml();
            $i++;
        }
        ShowMsg(Lang("templets_one_makei_success",array('i'=>$i)), '-1');
        exit();
    }
} else if ($dopost == "view") {
    if (empty($aid)) {
        ShowMsg('错误的ID', 'javascript:;');
        exit();
    }
    $sg = new Sgpage($aid);
    $sg->display();
    exit();
}
$row = $dsql->GetOne("SELECT  * FROM `#@__sgpage` WHERE aid='$aid'");
include(DEDEADMIN."/templets/templets_one_edit.htm");
?>