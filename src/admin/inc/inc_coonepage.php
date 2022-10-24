<?php
/**
 * 采集指定页面作为文档发布源
 *
 * @version        $Id: inc_coonepage.php 2022-07-01 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeHttpDown;
helper("charset");
/**
 *  获取一个页面
 *
 * @access    public
 * @param     string  $gurl  操作地址
 * @return    string
 */
function CoOnePage($gurl)
{
    global $dsql, $cfg_auot_description;
    $redatas = array('title' => '', 'body' => '', 'source' => '', 'writer' => '', 'description' => '', 'keywords' => '');
    $redatas['source'] = preg_replace("/(http|https):\/\//i", "", $gurl);
    $redatas['source'] = preg_replace("/\/(.*)$/i", "", $redatas['source']);
    $row = $dsql->GetOne("SELECT * FROM `#@__co_onepage` WHERE url LIKE '".$redatas['source']."'");
    $s = $e = '';
    if (is_array($row)) {
        list($s, $e) = explode('{@body}', $row['rule']);
        $s = trim($s);
        $e = trim($e);
        if ($row['issource'] == 1) {
            $redatas['source'] = $row['title'];
        }
    }
    $htd = new DedeHttpDown();
    $htd->OpenUrl($gurl);
    $body = $htd->GetHtml();
    if ($body != '') {
        //编码自动转换
        if ($row['lang'] == 'gb2312') {
            $body = gb2utf8($body);
        }
        //获取标题
        $inarr = array();
        preg_match("/<title>(.*)<\/title>/isU", $body, $inarr);
        if (isset($inarr[1])) {
            $redatas['title'] = $inarr[1];
        }
        //获取关键词
        $inarr = array();
        preg_match("/<meta[\s]+name=['\"]keywords['\"] content=['\"](.*)['\"]/isU", $body, $inarr);
        if (isset($inarr[1])) {
            $redatas['keywords'] = cn_substr(html2text($inarr[1]), 30);
        }
        //获取摘要
        $inarr = array();
        preg_match("/<meta[\s]+name=['\"]description['\"] content=['\"](.*)['\"]/isU", $body, $inarr);
        if (isset($inarr[1])) {
            $redatas['description'] = cn_substr(html2text($inarr[1]), $cfg_auot_description);
        }
        //获取内容
        if ($s != '' && $e != '') {
            $redatas['body'] = GetHtmlAreaA($s, $e, $body);
            if ($redatas['body'] != '' && $redatas['description'] == '') {
                $redatas['description'] = cn_substr(html2text($redatas['body']), $GLOBALS['cfg_auot_description']);
            }
        }
    }
    return $redatas;
}
/**
 *  获取特定区域的HTML
 *
 * @access    public
 * @param     string  $s  开始标识符
 * @param     string  $e  末尾标识符
 * @param     string  $html  文档信息
 * @return    string
 */
function GetHtmlAreaA($s, $e, &$html)
{
    if ($html == "" || $s == "") {
        return "";
    }
    $posstart = @strpos($html, $s);
    if ($posstart === FALSE) {
        return "";
    }
    $posend = strpos($html, $e, $posstart);
    if ($posend > $posstart && $posend !== FALSE) {
        return substr($html, $posstart + strlen($s), $posend - $posstart - strlen($s));
    } else {
        return '';
    }
}
?>