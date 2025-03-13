<?php
/**
 * 修改模型版本
 *
 * @version        $id:ai_model_edit.php 2025 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2025 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");

if (empty($dopost)) $dopost = '';
$id = isset($id)? intval($id) : 0;
$myModel = $dsql->GetOne("SELECT * FROM `#@__ai_model` WHERE id=$id");
if ($dopost == "delete") {
    CheckPurview('ai_ModelDel');
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__ai_model` WHERE id='$id'");
    ShowMsg("成功删除一个模型版本", "ai_model_main.php");
    exit();
} else if ($dopost == "saveedit") {
    CheckPurview('ai_ModelEdit');
    $title = isset($title)? HtmlReplace($title, -1) : '';
    $description = isset($description)? HtmlReplace($description, -1) : '';
    $model = isset($model)? HtmlReplace($model, -1) : '';
    $sortrank = isset($sortrank)? intval($sortrank) : 50;
    $query = "UPDATE `#@__ai_model` SET title='$title',description='$description',model='$model',sortrank='$sortrank' WHERE id='$id' ";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("成功修改一个模型版本", "ai_edit.php?id={$myModel['aiid']}&dopost=getedit");
    exit();
}
CheckPurview('ai_ModelEdit');
$ai = $dsql->GetOne("SELECT * FROM `#@__ai` WHERE id=".$myModel['aiid']);
include DedeInclude('templets/ai_model_edit.htm');
?>