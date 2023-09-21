<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 模型列表函数
 * 
 * @version        $id:inc_list_functions.php 13:52 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
/**
 *  获得是否推荐的表述
 *
 * @param     string  $iscommend  推荐
 * @return    string
 */
function IsCommendArchives($iscommend)
{
    $s = '';
    if (preg_match('/c/', $iscommend)) {
        $s .= '推荐';
    } else if (preg_match('/h/', $iscommend)) {
        $s .= ' 头条';
    } else if (preg_match('/p/', $iscommend)) {
        $s .= ' 图片';
    } else if (preg_match('/j/', $iscommend)) {
        $s .= ' 跳转';
    }
    return $s;
}
/**
 *  获得推荐的标题
 *
 * @param     string  $title  标题
 * @param     string  $iscommend  推荐
 * @return    string
 */
function GetCommendTitle($title, $iscommend)
{
    if (preg_match('/c/', $iscommend)) {
        $title = '$title <span class="btn btn-success btn-sm">推荐</span>';
    }
    return '$title';
}
$GLOBALS['RndTrunID'] = 1;
/**
 *  更换颜色
 *
 * @param     string  $color1  颜色1
 * @param     string  $color2  颜色2
 * @return    string
 */
function GetColor($color1, $color2)
{
    $GLOBALS['RndTrunID']++;
    if ($GLOBALS['RndTrunID'] % 2 == 0) {
        return $color1;
    } else {
        return $color2;
    }
}
/**
 *  检查图片是否存在
 *
 * @param     string  $picname  图片地址
 * @return    string
 */
function CheckPic($picname)
{
    if ($picname != "") {
        return $picname;
    } else {
        return '/static/web/img/thumbnail.jpg';
    }
}
/**
 *  判断文档是否生成网页
 *
 * @param     int  $ismake  是否生成
 * @return    string
 */
function IsHtmlArchives($ismake)
{
    if ($ismake == 1) {
        return '已生成';
    } else if ($ismake == -1) {
        return '仅动态';
    } else {
        return '未生成';
    }
}
/**
 *  获得文档的限定级别名称
 *
 * @param     string  $arcrank  级别名称
 * @return    string
 */
function GetRankName($arcrank)
{
    global $arcArray;
    if (!is_array($arcArray)) {
        $dsql->SetQuery("SELECT * FROM `#@__arcrank`");
        $dsql->Execute();
        while ($row = $dsql->GetObject()) {
            $arcArray[$row->rank] = $row->membername;
        }
    }
    if (isset($arcArray[$arcrank])) {
        return $arcArray[$arcrank];
    } else {
        return "不限";
    }
}
/**
 *  判断文档是否为图片文档
 *
 * @param     string  $picname  图片名称
 * @return    string
 */
function IsPicArchives($flag)
{
    if (strtolower($flag) == "p") {
        return ' <span class="btn btn-light btn-sm">图片</span>';
    } else {
        return '';
    }
}
?>