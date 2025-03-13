<?php
/**
 * 修改提示词
 *
 * @version        $id:ai_prompt_edit.php 2025 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2025 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (empty($dopost)) $dopost = '';
$id = isset($id)? intval($id) : 0;
$myPrompt = $dsql->GetOne("SELECT * FROM `#@__ai_prompt` WHERE id=$id");
if ($dopost == "delete") {
    CheckPurview('ai_PromptDel');
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__ai_prompt` WHERE id='$id'");
    ShowMsg("成功删除一个提示词", "ai_prompt_main.php");
    exit();
} else if ($dopost == "saveedit") {
    CheckPurview('ai_PromptEdit');
    $title = isset($title)? HtmlReplace($title, -1) : '';
    $description = isset($description)? HtmlReplace($description, -1) : '';
    $prompt = isset($prompt)? $prompt : '';
    $dfprompt = isset($dfprompt)? $dfprompt : '';
    $query = "UPDATE `#@__ai_prompt` SET title='$title',description='$description',prompt='$prompt',dfprompt='$dfprompt' WHERE id='$id' ";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("成功修改一个提示词", "ai_prompt_edit.php?id={$myPrompt['id']}");
    exit();
}
CheckPurview('ai_PromptEdit');
include DedeInclude('templets/ai_prompt_edit.htm');
?>