<?php
if (!defined('DEDEMEMBER')) exit ('dedebiz');
/**
 * 文档修改验证
 * 
 * @version        $id:archives_check_edit.php 13:52 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(DEDEINC."/image.func.php");
require_once(DEDEINC."/libraries/webwindow.class.php");
$flag = '';
$typeid = isset($typeid) && is_numeric($typeid) ? $typeid : 0;
$userip = GetIP();
if (trim($title) == '') {
    ShowMsg("文档标题不能为空", "-1");
    exit();
}
if (empty($typeid)) {
    ShowMsg("请选择文档栏目", "-1");
    exit();
}
//校验CSRF
CheckCSRF();
$query = "SELECT tp.ispart,tp.channeltype,tp.issend,ch.issend as cissend,ch.sendrank,ch.arcsta,ch.addtable,ch.fieldset,ch.usertype FROM `#@__arctype` tp LEFT JOIN `#@__channeltype` ch ON ch.id=tp.channeltype WHERE tp.id='$typeid' ";
$cInfos = $dsql->GetOne($query);
$addtable = $cInfos['addtable'];
//检测栏目是否有投稿权限
if ($cInfos['issend'] != 1 || $cInfos['ispart'] != 0 || $cInfos['channeltype'] != $channelid || $cInfos['cissend'] != 1) {
    ShowMsg("您所选择的栏目不支持投稿", "-1");
    exit();
}
//文档的默认状态
if ($cInfos['arcsta'] == 0) {
    $ismake = 0;
    $arcrank = 0;
} else if ($cInfos['arcsta'] == 1) {
    $ismake = -1;
    $arcrank = 0;
} else {
    $ismake = 0;
    $arcrank = -1;
}
//对保存的文档进行处理
$title = cn_substrR(HtmlReplace($title, 1), $cfg_title_maxlen);
$writer =  cn_substrR(HtmlReplace($writer, 1), 20);
if (empty($description)) $description = '';
$description = cn_substrR(HtmlReplace($description, 1), 250);
$keywords = cn_substrR(HtmlReplace($tags, 1), 30);
$source = isset($source)? HtmlReplace($source, 1) : '';
$mid = $cfg_ml->M_ID;
$midQuery = "SELECT mid FROM `#@__arctiny` WHERE id='$aid'";
$midRow = $dsql->GetOne($midQuery);
if ($midRow['mid'] != $mid) {
    ShowMsg("您暂无权限修改文档", "javascript:;");
    exit;
}
$isadmin = ($cfg_ml->fields['matt'] == 10 ? true : false);
if (empty($oldlitpic)) {
    $oldlitpic = '';
}
?>