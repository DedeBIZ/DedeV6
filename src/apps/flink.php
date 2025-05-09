<?php
/**
 * 友情链接
 *
 * @version        $id:flink.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../system/common.inc.php");
if (empty($dopost)) $dopost = '';
if ($dopost == 'save') {
    $validate = isset($validate) ? strtolower(trim($validate)) : '';
    $svali = GetCkVdValue();
    if ($validate == '' || $validate != $svali) {
        ShowMsg('验证码不正确', '-1');
        exit();
    }
    $msg = RemoveXSS(dede_htmlspecialchars($msg));
    $email = RemoveXSS(dede_htmlspecialchars($email));
    $webname = RemoveXSS(dede_htmlspecialchars($webname));
    $url = RemoveXSS(dede_htmlspecialchars($url));
    $logo = RemoveXSS(dede_htmlspecialchars($logo));
    $typeid = intval($typeid);
    $dtime = time();
    $query = "INSERT INTO `#@__flink` (sortrank,url,webname,logo,msg,email,typeid,dtime,ischeck) VALUES ('50','$url','$webname','$logo','$msg','$email','$typeid','$dtime','0')";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg('成功添加一个链接，但需要审核后才能显示', 'flink.php');
    exit;
} elseif ($dopost == 'add') {
    //显示模板简单PHP文件
    include_once(DEDETEMPLATE.'/apps/flink_add.htm');
    exit;
}
//显示模板简单PHP文件
include_once(DEDETEMPLATE.'/apps/flink_list.htm');
?>