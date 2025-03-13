<?php
/**
 * 添加大模型
 *
 * @version        $id:ai_add.php 2025 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2025 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__)."/config.php");
CheckPurview('ai_New');
if (empty($dopost)) $dopost = '';
if ($dopost == "add") {
    $title = isset($title)? HtmlReplace($title, -1) : '';
    $description = isset($description)? HtmlReplace($description, -1) : '';
    $company = isset($company)? HtmlReplace($company, -1) : '';
    $website = isset($website)? HtmlReplace($website, -1) : '';
    $apikey = isset($apikey)? HtmlReplace($apikey, -1) : '';
    $baseurl = isset($baseurl)? HtmlReplace($baseurl, -1) : '';
    $query = "INSERT INTO `#@__ai` (title,description,company,website,apikey,baseurl) VALUES ('$title','$description','$company','$website','$apikey','$baseurl'); ";
    $rs = $dsql->ExecuteNoneQuery($query);
    $burl = empty($_COOKIE['ENV_GOBACK_URL']) ? "ai_main.php" : $_COOKIE['ENV_GOBACK_URL'];
    if ($rs) {
        ShowMsg("成功添加一个大模型", $burl);
        exit();
    } else {
        ShowMsg("添加大模型时出错，原因：".$dsql->GetError(), "javascript:;");
        exit();
    }
}
include DedeInclude('templets/ai_add.htm');
?>