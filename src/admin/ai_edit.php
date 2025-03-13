<?php
/**
 * 修改大模型
 *
 * @version        $id:ai_edit.php 2025 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2025 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (empty($dopost)) $dopost = '';
$id = isset($id)? intval($id) : 0;
if ($dopost == "delete") {
    CheckPurview('ai_Del');
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__ai` WHERE id='$id'");
    ShowMsg("成功删除一个大模型", $ENV_GOBACK_URL);
    exit();
} else if ($dopost == "saveedit") {
    CheckPurview('ai_Edit');
    $title = isset($title)? HtmlReplace($title, -1) : '';
    $description = isset($description)? HtmlReplace($description, -1) : '';
    $company = isset($company)? HtmlReplace($company, -1) : '';
    $website = isset($website)? HtmlReplace($website, -1) : '';
    $apikey = isset($apikey)? HtmlReplace($apikey, -1) : '';
    $baseurl = isset($baseurl)? HtmlReplace($baseurl, -1) : '';
    $query = "UPDATE `#@__ai` SET title='$title',description='$description',company='$company',website='$website',apikey='$apikey', baseurl='$baseurl' WHERE id='$id' ";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("成功修改一个大模型", $ENV_GOBACK_URL);
    exit();
}
CheckPurview('ai_Edit');
$myAI = $dsql->GetOne("SELECT * FROM `#@__ai` WHERE id=$id");
$sql = "SELECT * FROM `#@__ai_model` WHERE aiid=$id ORDER BY id DESC";
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN.'/templets/ai_edit.htm');
$dlist->SetSource($sql);
$dlist->display();
// include DedeInclude('templets/ai_edit.htm');
?>