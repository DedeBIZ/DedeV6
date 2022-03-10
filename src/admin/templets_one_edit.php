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
require(dirname(__FILE__)."/config.php");
CheckPurview('temp_One');
if (empty($dopost)) $dopost = "";
$aid = isset($aid) && is_numeric($aid) ? $aid : 0;
if ($dopost == "saveedit") {
    include_once(DEDEINC."/archive/sgpage.class.php");
    $uptime = time();
    $body = str_replace('&quot;', '\\"', $body);
    $filename = preg_replace("#^\/#", "", $nfilename);
    if (!preg_match('#\.htm$#i', trim($template))) {
        ShowMsg("您指定的文件名被系统禁止", "javascript:;");
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
     description='$description',
     likeid='$likeid',
     ismake='$ismake',
     filename='$filename',
     template='$template',
     uptime='$uptime',
     body='$body'
     WHERE aid='$aid'; ";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        ShowMsg("更新页面数据时失败，请检查长相是否有问题", "-1");
        exit();
    }
    $sg = new sgpage($aid);
    $sg->SaveToHtml();
    ShowMsg("成功修改一个页面", "templets_one.php");
    exit();
} else if ($dopost == "delete") {
    $row = $dsql->GetOne("SELECT filename FROM `#@__sgpage` WHERE aid='$aid'");
    $filename = preg_replace("#\/{1,}#", "/", $cfg_basedir.$cfg_cmspath."/".$row['filename']);
    $dsql->ExecuteNoneQuery(" DELETE FROM `#@__sgpage` WHERE aid='$aid' ");
    if (is_file($filename)) {
        unlink($filename);
    }
    ShowMsg("成功删除一个页面", "templets_one.php");
    exit();
} else if ($dopost == "make") {
    include_once(DEDEINC."/archive/sgpage.class.php");
    $row = $dsql->GetOne("SELECT filename FROM `#@__sgpage` WHERE aid='$aid'");
    $fileurl = $cfg_cmsurl.'/'.preg_replace("#\/{1,}#", "/", $row['filename']);
    $sg = new sgpage($aid);
    $sg->SaveToHtml();
    ShowMsg("成功更新一个页面", $fileurl);
    exit();
} else if ($dopost == "mkall") {
    include_once(DEDEINC."/archive/sgpage.class.php");
    $dsql->Execute("ex", "SELECT aid FROM `#@__sgpage` ");
    $i = 0;
    while ($row = $dsql->GetArray("ex")) {
        $sg = new sgpage($row['aid']);
        $sg->SaveToHtml();
        $i++;
    }
    ShowMsg("成功更新 $i 个页面", '-1');
    exit();
} else if ($dopost == "mksel") {
    if (empty($ids)) {
        $ids = '';
    }
    include_once(DEDEINC."/archive/sgpage.class.php");
    $i = 0;
    if ($ids == 0) {
        ShowMsg('您没有选择需要更新的文档', '-1');
        exit();
    } else if (is_array($ids)) {
        foreach ($ids as $aid) {
            $sg = new sgpage($aid);
            $sg->SaveToHtml();
            $i++;
        }
        ShowMsg("成功更新 $i 个页面", '-1');
        exit();
    }
} else if ($dopost == "view") {
    if (empty($aid)) {
        ShowMsg('错误的ID', 'javascript:;');
        exit();
    }
    include_once(DEDEINC."/archive/sgpage.class.php");
    $sg = new sgpage($aid);
    $sg->display();
    exit();
}
$row = $dsql->GetOne("SELECT  * FROM `#@__sgpage` WHERE aid='$aid' ");
include(DEDEADMIN."/templets/templets_one_edit.htm");