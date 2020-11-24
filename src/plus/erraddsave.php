<?php

/**
 *
 * 错误提交
 *
 * @version        $Id: erraddsave.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2020, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__) . "/../include/common.inc.php");
require_once(DEDEINC . '/memberlogin.class.php');

$dopost = isset($dopost) ? $dopost : "";
$aid = isset($aid) ? intval($aid) : 0;
if (empty($aid)) {
    echo json_encode(array(
        "code" => -1,
        "data" => null,
        "msg" => "请求错误",
    ));
    exit;
}

if ($dopost == "saveedit") {
    $cfg_ml = new MemberLogin();
    $title = HtmlReplace($title);
    $format = isset($format) ? $format : "";
    $type = isset($type) && is_numeric($type) ? $type : 0;
    $mid = isset($cfg_ml->M_ID) ? $cfg_ml->M_ID : 0;
    $err = trimMsg(cn_substr(RemoveXSS($err), 2000), 1);
    $oktxt = trimMsg(cn_substr(RemoveXSS($erradd), 2000), 1);
    if (empty($err) || empty($oktxt)) {
        echo json_encode(array(
            "code" => -1,
            "data" => null,
            "msg" => "错误内容或建议不能为空",
        ));
        exit;
    }

    $time = time();
    $query = "INSERT INTO `#@__erradd`(aid,mid,title,type,errtxt,oktxt,sendtime)
                  VALUES ('$aid','$mid','$title','$type','$err','$oktxt','$time'); ";
    $dsql->ExecuteNoneQuery($query);
    if (!empty($format)) {
        echo json_encode(array(
            "code" => 200,
            "data" => "ok",
        ));
    } else {
        ShowMsg("谢谢您对本网站的支持，我们会尽快处理您的建议！", "javascript:window.close();");
    }

    exit();
} else {
    echo json_encode(array(
        "code" => -1,
        "data" => null,
        "msg" => "未知方法",
    ));
    exit;
}
