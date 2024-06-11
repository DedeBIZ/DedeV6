<?php
/**
 * 修改友情链接
 *
 * @version        $id:friendlink_edit.php 10:59 2010年7月13日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('plus_友情链接');
$ENV_GOBACK_URL = empty($_COOKIE['ENV_GOBACK_URL']) ? 'friendlink_main.php' : $_COOKIE['ENV_GOBACK_URL'];
if (empty($dopost)) $dopost = '';
$id = isset($id)? intval($id) : 0;
if (isset($allid)) {
    $aids = explode(',', $allid);
    if (count($aids) == 1) {
        $id = intval($aids[0]);
        $dopost = "delete";
    }
}
if ($dopost == "delete") {
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__flink` WHERE id='$id'");
    ShowMsg("成功删除一个链接", $ENV_GOBACK_URL);
    exit();
} else if ($dopost == "delall") {
    $aids = explode(',', $aids);
    if (isset($aids) && is_array($aids)) {
        foreach ($aids as $aid) {
            $aid = intval($aid);
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__flink` WHERE id='$aid'");
        }
        ShowMsg("成功删除指定链接", $ENV_GOBACK_URL);
        exit();
    } else {
        ShowMsg("您没选定任何链接", $ENV_GOBACK_URL);
        exit();
    }
} else if ($dopost == "saveedit") {
    $logo = isset($logo)? HtmlReplace($logo, -1) : '';
    if (empty($logoimg)) {
        $logoimg = '';
    }
    if (!empty($logoimg)) {
        if (!is_uploaded_file($logoimg)) {
            ShowMsg("您没有选择上传文件".$logoimg, "-1");
            exit();
        }
        $mime = get_mime_type($logoimg);
        if (preg_match("#^unknow#", $mime)) {
            ShowMsg("系统不支持fileinfo组件，建议php.ini中开启", -1);
            exit;
        }
        if (!preg_match("#^(image)#i", $mime)) {
            ShowMsg("仅支持上传图片文件", -1);
            exit;
        }
        $logoimg_name = trim(preg_replace("#[ \r\n\t\*\%\\\/\?><\|\":]{1,}#", '', $logoimg_name));
        $fullfilename = DEDEROOT.'/static/flink/'.$logoimg_name;
        move_uploaded_file($logoimg, $fullfilename) or die("上传文件到".$fullfilename."失败");
        @unlink($logoimg);
        $logo = $cfg_cmspath.'/static/flink/'.$logoimg_name;
    }
    $sortrank = isset($sortrank)? intval($sortrank) : 1;
    $url = isset($url)? HtmlReplace($url, -1) : '';
    $webname = isset($webname)? HtmlReplace($webname, -1) : '';
    $msg = isset($msg)? HtmlReplace($msg, -1) : '';
    $email = isset($email)? HtmlReplace($email, -1) : '';
    $typeid = isset($typeid)? intval($typeid) : 0;
    $ischeck = isset($ischeck)? intval($ischeck) : 0;
    $query = "UPDATE `#@__flink` SET sortrank='$sortrank',url='$url',webname='$webname',logo='$logo',msg='$msg', email='$email',typeid='$typeid',ischeck='$ischeck' WHERE id='$id' ";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("成功修改一个链接", $ENV_GOBACK_URL);
    exit();
}
$myLink = $dsql->GetOne("SELECT `#@__flink`.*,`#@__flinktype`.typename FROM `#@__flink` LEFT JOIN `#@__flinktype` ON `#@__flink`.typeid=`#@__flinktype`.id WHERE `#@__flink`.id=$id");
include DedeInclude('templets/friendlink_edit.htm');
?>