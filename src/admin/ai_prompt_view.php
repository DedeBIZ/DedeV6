<?php
/**
 * 预览提示词
 *
 * @version        $id:ai_prompt_view.php 2025 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2025 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('ai_PromptView');
if (empty($dopost)) $dopost = '';
$id = isset($id)? intval($id) : 0;
$myPrompt = $dsql->GetOne("SELECT * FROM `#@__ai_prompt` WHERE id=$id");
include DedeInclude('templets/ai_prompt_view.htm');
?>