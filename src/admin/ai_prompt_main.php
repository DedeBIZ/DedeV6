<?php
/**
 * 提示词管理
 *
 * @version        $id:ai_prompt_main.php 2025 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2025 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
require_once(DEDEINC.'/datalistcp.class.php');
CheckPurview('ai_PromptList');
$sql = "SELECT * FROM `#@__ai_prompt` ORDER BY id DESC";
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN.'/templets/ai_prompt_main.htm');
$dlist->SetSource($sql);
$dlist->display();
?>