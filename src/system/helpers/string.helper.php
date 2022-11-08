<?php
if (!defined('DEDEINC')) exit('dedebiz');
/**
 * 字符串小助手
 *
 * @version        $id:string.helper.php 5 14:24 2010年7月5日 tianya $
 * @package        DedeBIZ.Helpers
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
//拼音的缓冲数组
$pinyins = array();
/**
 *  中文截取2，单字节截取模式
 *  如果是request的文档，必须使用这个函数
 *
 * @access    public
 * @param     string  $str  需要截取的字符串
 * @param     int  $slen  截取的长度
 * @param     int  $startdd  开始标记处
 * @return    string
 */
if (!function_exists('cn_substrR')) {
    function cn_substrR($str, $slen, $startdd = 0)
    {
        $str = cn_substr(stripslashes($str), $slen, $startdd);
        return addslashes($str);
    }
}
/**
 *  中文截取2，单字节截取模式
 *
 * @access    public
 * @param     string  $str  需要截取的字符串
 * @param     int  $slen  截取的长度
 * @param     int  $startdd  开始标记处
 * @return    string
 */
if (!function_exists('cn_substr')) {
    function cn_substr($str, $slen, $startdd = 0)
    {
        global $cfg_soft_lang;
        if ($cfg_soft_lang == 'utf-8') {
            return cn_substr_utf8($str, $slen, $startdd);
        }
        $restr = '';
        $c = '';
        $str_len = strlen($str);
        if ($str_len < $startdd + 1) {
            return '';
        }
        if ($str_len < $startdd + $slen || $slen == 0) {
            $slen = $str_len - $startdd;
        }
        $enddd = $startdd + $slen - 1;
        for ($i = 0; $i < $str_len; $i++) {
            if ($startdd == 0) {
                $restr .= $c;
            } else if ($i > $startdd) {
                $restr .= $c;
            }
            if (ord($str[$i]) > 0x80) {
                if ($str_len > $i + 1) {
                    $c = $str[$i].$str[$i + 1];
                }
                $i++;
            } else {
                $c = $str[$i];
            }
            if ($i >= $enddd) {
                if (strlen($restr) + strlen($c) > $slen) {
                    break;
                } else {
                    $restr .= $c;
                    break;
                }
            }
        }
        return $restr;
    }
}
/**
 *  utf-8中文截取，单字节截取模式
 *
 * @access    public
 * @param     string  $str  需要截取的字符串
 * @param     int  $slen  截取的长度
 * @param     int  $startdd  开始标记处
 * @return    string
 */
if (!function_exists('cn_substr_utf8')) {
    function cn_substr_utf8($str, $length, $start = 0)
    {
        if (strlen($str) < $start + 1) {
            return '';
        }
        preg_match_all("/./su", $str, $ar);
        $str = '';
        $tstr = '';
        //为了兼容mysql4.1以下版本,与数据库varchar一致,这里使用按字节截取
        for ($i = 0; isset($ar[0][$i]); $i++) {
            if (strlen($tstr) < $start) {
                $tstr .= $ar[0][$i];
            } else {
                if (strlen($str) < $length + strlen($ar[0][$i])) {
                    $str .= $ar[0][$i];
                } else {
                    break;
                }
            }
        }
        return $str;
    }
}
/**
 *  HTML转换为文本
 *
 * @param    string  $str 需要转换的字符串
 * @param    string  $r   如果$r=0直接返回文档,否则需要使用反斜线引用字符串
 * @return   string
 */
if (!function_exists('Html2Text')) {
    function Html2Text($str, $r = 0)
    {
        if (!function_exists('SpHtml2Text')) {
            require_once(DEDEINC."/inc/inc_fun_funString.php");
        }
        if ($r == 0) {
            return SpHtml2Text($str);
        } else {
            $str = SpHtml2Text(stripslashes($str));
            return addslashes($str);
        }
    }
}
/**
 *  文本转HTML
 *
 * @param    string  $txt 需要转换的文本文档
 * @return   string
 */
if (!function_exists('Text2Html')) {
    function Text2Html($txt)
    {
        $txt = str_replace("  ", "　", $txt);
        $txt = str_replace("<", "&lt;", $txt);
        $txt = str_replace(">", "&gt;", $txt);
        $txt = preg_replace("/[\r\n]{1,}/isU", "<br>\r\n", $txt);
        return $txt;
    }
}
/**
 *  获取半角字符
 *
 * @param     string  $fnum  数字字符串
 * @return    string
 */
if (!function_exists('GetAlabNum')) {
    function GetAlabNum($fnum)
    {
        $nums = array("０", "１", "２", "３", "４", "５", "６", "７", "８", "９");
        //$fnums = "0123456789";
        $fnums = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
        $fnum = str_replace($nums, $fnums, $fnum);
        $fnum = preg_replace("/[^0-9\.-]/", '', $fnum);
        if ($fnum == '') {
            $fnum = 0;
        }
        return $fnum;
    }
}
/**
 *  获取拼音以gbk编码为准
 *
 * @access    public
 * @param     string  $str     字符串信息
 * @param     int     $ishead  是否取头字母
 * @param     int     $isclose 是否关闭字符串资源
 * @return    string
 */
if (!function_exists('GetPinyin')) {
    function GetPinyin($str, $ishead = 0, $isclose = 1)
    {
        global $cfg_soft_lang;
        if (!function_exists('SpGetPinyin')) {
            //全局函数仅是inc_fun_funAdmin.php文件中函数的一个映射
            require_once(DEDEINC."/inc/inc_fun_funAdmin.php");
        }
        if ($cfg_soft_lang == 'utf-8') {
            return SpGetPinyin(utf82gb($str), $ishead, $isclose);
        } else {
            return SpGetPinyin($str, $ishead, $isclose);
        }
    }
}
/**
 *  将实体网页代码转换成标准网页代码（兼容php4）
 *
 * @access    public
 * @param     string  $str     字符串信息
 * @param     long    $options  替换的字符集
 * @return    string
 */
if (!function_exists('htmlspecialchars_decode')) {
    function htmlspecialchars_decode($str, $options = ENT_COMPAT)
    {
        $trans = get_html_translation_table(HTML_SPECIALCHARS, $options);
        $decode = array();
        foreach ($trans as $char => $entity) {
            $decode[$entity] = $char;
        }
        $str = strtr($str, $decode);
        return $str;
    }
}
if (!function_exists('ubb')) {
    function ubb($Text)
    {
        $Text = trim($Text);
        //$Text=htmlspecialchars($Text);
        //$Text=ereg_replace("\n","<br>",$Text);
        $Text = preg_replace("/\\t/is", "  ", $Text);
        $Text = preg_replace("/\[hr\]/is", "<hr>", $Text);
        $Text = preg_replace("/\[separator\]/is", "<br>", $Text);
        $Text = preg_replace("/\[h1\](.+?)\[\/h1\]/is", "<h1>\\1</h1>", $Text);
        $Text = preg_replace("/\[h2\](.+?)\[\/h2\]/is", "<h2>\\1</h2>", $Text);
        $Text = preg_replace("/\[h3\](.+?)\[\/h3\]/is", "<h3>\\1</h3>", $Text);
        $Text = preg_replace("/\[h4\](.+?)\[\/h4\]/is", "<h4>\\1</h4>", $Text);
        $Text = preg_replace("/\[h5\](.+?)\[\/h5\]/is", "<h5>\\1</h5>", $Text);
        $Text = preg_replace("/\[h6\](.+?)\[\/h6\]/is", "<h6>\\1</h6>", $Text);
        $Text = preg_replace("/\[center\](.+?)\[\/center\]/is", "<center>\\1</center>", $Text);
        //$Text=preg_replace("/\[url=([^\[]*)\](.+?)\[\/url\]/is","<a href=\\1 target='_blank'>\\2</a>",$Text);
        $Text = preg_replace("/\[url\](.+?)\[\/url\]/is", "<a href=\"\\1\" target='_blank'>\\1</a>", $Text);
        $Text = preg_replace("/\[url=(http:\/\/.+?)\](.+?)\[\/url\]/is", "<a href='\\1' target='_blank'>\\2</a>", $Text);
        $Text = preg_replace("/\[url=(.+?)\](.+?)\[\/url\]/is", "<a href=\\1>\\2</a>", $Text);
        $Text = preg_replace("/\[img\](.+?)\[\/img\]/is", "<img src=\\1>", $Text);
        $Text = preg_replace("/\[img\s(.+?)\](.+?)\[\/img\]/is", "<img \\1 src=\\2>", $Text);
        $Text = preg_replace("/\[color=(.+?)\](.+?)\[\/color\]/is", "<font color=\\1>\\2</font>", $Text);
        $Text = preg_replace("/\[style=(.+?)\](.+?)\[\/style\]/is", "<div class='\\1'>\\2</div>", $Text);
        $Text = preg_replace("/\[size=(.+?)\](.+?)\[\/size\]/is", "<font size=\\1>\\2</font>", $Text);
        $Text = preg_replace("/\[sup\](.+?)\[\/sup\]/is", "<sup>\\1</sup>", $Text);
        $Text = preg_replace("/\[sub\](.+?)\[\/sub\]/is", "<sub>\\1</sub>", $Text);
        $Text = preg_replace("/\[pre\](.+?)\[\/pre\]/is", "<pre>\\1</pre>", $Text);
        if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            $Text = preg_replace_callback("/\[colorTxt\](.+?)\[\/colorTxt\]/is", "color_txt", $Text);
        } else {
            $Text = preg_replace("/\[colorTxt\](.+?)\[\/colorTxt\]/eis", "color_txt('\\1')", $Text);
        }
        $Text = preg_replace("/\[email\](.+?)\[\/email\]/is", "<a href='mailto:\\1'>\\1</a>", $Text);
        $Text = preg_replace("/\[i\](.+?)\[\/i\]/is", "<i>\\1</i> ", $Text);
        $Text = preg_replace("/\[u\](.+?)\[\/u\]/is", "\\1", $Text);
        $Text = preg_replace("/\[b\](.+?)\[\/b\]/is", "\\1", $Text);
        $Text = preg_replace("/\[quote\](.+?)\[\/quote\]/is", "<blockquote>引用:<div style='border:1px solid silver;background:#EFFFDF;color:#393939;padding:6px'>\\1</div></blockquote>", $Text);
        $Text = preg_replace("/\[sig\](.+?)\[\/sig\]/is", "<div style='text-align: left; color: darkgreen; margin-left: 5%'><br><br>--------------------------<br>\\1<br>--------------------------</div>", $Text);
        return $Text;
    }
}
if (!function_exists('color_txt')) {
    function color_txt($str)
    {
        if (is_array($str)) {
            $str = $str[1];
        }
        $len        = mb_strlen($str);
        $colorTxt   = '';
        for ($i = 0; $i < $len; $i++) {
            $colorTxt .=  '<span style="color:'.rand_color().'">'.mb_substr($str, $i, 1, 'utf-8').'</span>';
        }
        return $colorTxt;
    }
}
if (!function_exists('rand_color')) {
    function rand_color()
    {
        return '#'.sprintf("%02X", mt_rand(0, 255)).sprintf("%02X", mt_rand(0, 255)).sprintf("%02X", mt_rand(0, 255));
    }
}
?>