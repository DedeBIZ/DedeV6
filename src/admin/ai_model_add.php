<?php
/**
 * 添加模型版本
 *
 * @version        $id:ai_model_add.php 2025 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2025 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__)."/config.php");
CheckPurview('ai_ModelNew');
if (empty($dopost)) $dopost = '';
$aiid = isset($aiid) ? intval($aiid) : 0;
if ($dopost == "add") {
    $model = isset($model)? HtmlReplace($model, -1) : '';
    $title = isset($title)? HtmlReplace($title, -1) : '';
    $description = isset($description)? HtmlReplace($description, -1) : '';
    $sortrank = isset($sortrank)? intval($sortrank) : 50;
    $query = "INSERT INTO `#@__ai_model` (aiid,title,model,description,sortrank) VALUES ('$aiid','$title','$model','$description','$sortrank'); ";
    $rs = $dsql->ExecuteNoneQuery($query);
    $burl =  "ai_edit.php?id=".$aiid;
    if ($rs) {
        ShowMsg("成功添加一个模型版本", $burl);
        exit();
    } else {
        ShowMsg("添加模型版本时出错，原因：".$dsql->GetError(), "javascript:;");
        exit();
    }
}
if($aiid > 0) {
    $ai = $dsql->GetOne("SELECT * FROM `#@__ai` WHERE id=$aiid");
}
include DedeInclude('templets/ai_model_add.htm');
?>