<?php
/**
 * 添加友情链接
 *
 * @version        $id:friendlink_add.php 10:59 2010年7月13日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__)."/config.php");
CheckPurview('plus_友情链接');
if (empty($dopost)) $dopost = '';
if ($dopost == "add") {
    $dtime = time();
    if (is_uploaded_file($logoimg)) {
        $names = split("\.", $logoimg_name);
        $shortname = ".".$names[count($names) - 1];
        if (!preg_match("#(jpg|gif|png)$#", $shortname)) {
            $shortname = '.gif';
        }
        $filename = MyDate("ymdHis", time()).mt_rand(1000, 9999).$shortname;
        $imgurl = $cfg_medias_dir."/flink";
        if (!is_dir($cfg_basedir.$imgurl)) {
            MkdirAll($cfg_basedir.$imgurl, $cfg_dir_purview);
        }
        $imgurl = $imgurl."/".$filename;
        $mime = get_mime_type($logoimg);
        if (preg_match("#^unknow#", $mime)) {
            ShowMsg("系统不支持fileinfo组件，建议php.ini中开启", -1);
            exit;
        }
        if (!preg_match("#^image#i", $mime)) {
            ShowMsg("非图片格式文件，无法正常上传", -1);
            exit;
        }
        move_uploaded_file($logoimg, $cfg_basedir.$imgurl) or die("复制文件到".$cfg_basedir.$imgurl."失败");
        @unlink($logoimg);
    } else {
        $imgurl = $logo;
    }
    //强制检测会员友情链接分类是否数据结构不符
    if (empty($typeid) || preg_match("#[^0-9]#", $typeid)) {
        $typeid = 0;
        $dsql->ExecuteNoneQuery("ALTER TABLE `#@__flinktype` CHANGE `ID` `id` MEDIUMINT( 8 ) UNSIGNED DEFAULT NULL AUTO_INCREMENT;");
    }
    $sortrank = isset($sortrank)? intval($sortrank) : 1;
    $url = isset($url)? HtmlReplace($url, -1) : '';
    $imgurl = isset($imgurl)? HtmlReplace($imgurl, -1) : '';
    $webname = isset($webname)? HtmlReplace($webname, -1) : '';
    $msg = isset($msg)? HtmlReplace($msg, -1) : '';
    $email = isset($email)? HtmlReplace($email, -1) : '';
    $typeid = isset($typeid)? intval($typeid) : 0;
    $ischeck = isset($ischeck)? intval($ischeck) : 0;
    $query = "INSERT INTO `#@__flink` (sortrank,url,webname,logo,msg,email,typeid,dtime,ischeck) VALUES ('$sortrank','$url','$webname','$imgurl','$msg','$email','$typeid','$dtime','$ischeck'); ";
    $rs = $dsql->ExecuteNoneQuery($query);
    $burl = empty($_COOKIE['ENV_GOBACK_URL']) ? "friendlink_main.php" : $_COOKIE['ENV_GOBACK_URL'];
    if ($rs) {
        ShowMsg("成功添加一个链接", $burl);
        exit();
    } else {
        ShowMsg("添加链接时出错，原因：".$dsql->GetError(), "javascript:;");
        exit();
    }
}
include DedeInclude('templets/friendlink_add.htm');
?>