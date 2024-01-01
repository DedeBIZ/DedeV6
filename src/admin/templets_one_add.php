<?php
/**
 * 添加自定义页面
 *
 * @version        $id:templets_one_add.php 23:07 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__)."/config.php");
CheckPurview('temp_One');
if (empty($dopost)) $dopost = '';
if ($dopost == "save") {
    require_once(DEDEINC."/archive/partview.class.php");
    $uptime = time();
    $body = str_replace('&quot;', '\\"', $body);
    $filename = preg_replace("#^\/#", "", $nfilename);
    if (DEDEBIZ_SAFE_MODE) $ismake = 0; //安全模式不允许编译
    if (!preg_match('#\.htm$#i', trim($template))) {
        ShowMsg("文件扩展名已被系统禁止", "javascript:;");
        exit();
    }
    if (!preg_match('#\.html$#i', trim($filename))) {
        ShowMsg("文件扩展名已被系统禁止", "javascript:;");
        exit();
    }
    if ($likeid == '') {
        $likeid = $likeidsel;
    }
    $row = $dsql->GetOne("SELECT filename FROM `#@__sgpage` WHERE likeid='$likeid' AND filename LIKE '$filename' ");
    if (is_array($row)) {
        ShowMsg("已经存在相同的文件名，请修改为其它文件名", "-1");
        exit();
    }
    $inQuery = "INSERT INTO `#@__sgpage`(title,keywords,description,template,likeid,ismake,filename,uptime,body) VALUES ('$title','$keywords','$description','$template','$likeid','$ismake','$filename','$uptime','$body'); ";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        ShowMsg("添加页面失败，请检文档是否有问题", "-1");
        exit();
    }
    $id = $dsql->GetLastID();
    include_once(DEDEINC."/archive/sgpage.class.php");
    $sg = new sgpage($id);
    $sg->SaveToHtml();
    ShowMsg("成功添加一个单页", "templets_one.php");
    exit();
}
$row = $dsql->GetOne("SELECT MAX(aid) AS aid FROM `#@__sgpage`");
$nowid = is_array($row) ? $row['aid'] + 1 : '';
include_once(DEDEADMIN."/templets/templets_one_add.htm");
?>