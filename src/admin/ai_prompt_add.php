<?php
/**
 * 添加提示词
 *
 * @version        $id:ai_prompt_add.php 2025 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2025 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__)."/config.php");
CheckPurview('ai_PromptNew');
if (empty($dopost)) $dopost = '';
$id = isset($id) ? intval($id) : 0;
if ($dopost == "add") {
    $pname = isset($pname)? HtmlReplace($pname, -1) : '';
    $title = isset($title)? HtmlReplace($title, -1) : '';
    $description = isset($description)? HtmlReplace($description, -1) : '';
    $prompt = isset($prompt)? $prompt : '';
    $dfprompt = isset($dfprompt)? $dfprompt : '';
    $query = "INSERT INTO `#@__ai_prompt` (pname,title,prompt,description,dfprompt) VALUES ('$pname','$title','$prompt','$description','$dfprompt');";
    $rs = $dsql->ExecuteNoneQuery($query);
    $burl =  "ai_prompt_main.php";
    if ($rs) {
        ShowMsg("成功添加一个提示词", $burl);
        exit();
    } else {
        ShowMsg("添加提示词时出错，原因：".$dsql->GetError(), "javascript:;");
        exit();
    }
}
include DedeInclude('templets/ai_prompt_add.htm');
?>