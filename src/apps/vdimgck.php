<?php
/**
 * 验证图片
 *
 * @version        $Id: vdimgck.php$
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/../system/common.inc.php');
require_once(DEDEDATA.'/config.cache.inc.php');
$config = array(
    'font_size'   => 20,
    'img_height'  => $safe_wheight,
    'word_type'  => (int)$safe_codetype,
    'img_width'   => $safe_wwidth,
    'use_boder'   => TRUE,
    'font_file'   => DEDEINC.'/data/fonts/'.mt_rand(1, 6).'.ttf',
    'wordlist_file'   => DEDEINC.'/data/words/words.txt',
    'filter_type' => 5
);
$enkey = substr(md5(substr($cfg_cookie_encode, 0, 5)), 0, 10);
$sessSavePath = DEDEDATA."/sessions_{$enkey}";
if (!is_dir($sessSavePath)) mkdir($sessSavePath);
//Session保存路径
if (is_writeable($sessSavePath) && is_readable($sessSavePath)) {
    session_save_path($sessSavePath);
}
if (!empty($cfg_domain_cookie)) session_set_cookie_params(0, '/', $cfg_domain_cookie);
if (!echo_validate_image($config)) {
    //如果不成功则初始化一个默认验证码
    @session_start();
    $_SESSION['securimage_code_value'] = strtolower('abcd');
    if (function_exists('imagecreatefromjpeg')) {
        $im = @imagecreatefromjpeg(DEDEINC.'/data/vdcode.jpg');
        header("Pragma:no-cache\r\n");
        header("Cache-Control:no-cache\r\n");
        header("Expires:0\r\n");
        imagejpeg($im);
        imagedestroy($im);
    } else {
        header("Pragma:no-cache\r\n");
        header("Cache-Control:no-cache\r\n");
        header("Expires:0\r\n");
        $c = file_get_contents(DEDEINC.'/data/vdcode.jpg', true);
        $size = filesize(DEDEINC.'/data/vdcode.jpg');
        header('Content-Type: image/x-icon');
        header("Content-length: $size");
        echo $c;
    }
}
function echo_validate_image($config = array())
{
    @session_start();
    if (!function_exists('imagettftext')) {
        return false;
    }
    //主要参数
    $font_size   = isset($config['font_size']) ? $config['font_size'] : 14;
    $img_height  = isset($config['img_height']) ? $config['img_height'] : 38;
    $img_width   = isset($config['img_width']) ? $config['img_width'] : 68;
    $font_file   = isset($config['font_file']) ? $config['font_file'] : DEDEINC.'/data/font/'.mt_rand(1, 6).'.ttf';
    $use_boder   = isset($config['use_boder']) ? $config['use_boder'] : TRUE;
    $filter_type = isset($config['filter_type']) ? $config['filter_type'] : 0;
    //创建图片，并设置背景色
    $im = @imagecreate($img_width, $img_height);
    imagecolorallocate($im, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
    //文字随机颜色
    $fontColor[]  = imagecolorallocate($im, 0x15, 0x15, 0x15);
    $fontColor[]  = imagecolorallocate($im, 0x95, 0x1e, 0x04);
    $fontColor[]  = imagecolorallocate($im, 0x93, 0x14, 0xa9);
    $fontColor[]  = imagecolorallocate($im, 0x12, 0x81, 0x0a);
    $fontColor[]  = imagecolorallocate($im, 0x06, 0x3a, 0xd5);
    //获取随机字符
    $rndstring  = '';
    if ($config['word_type'] != 3) {
        for ($i = 0; $i < 4; $i++) {
            if ($config['word_type'] == 1) {
                $c = chr(mt_rand(48, 57));
            } else if ($config['word_type'] == 2) {
                $c = chr(mt_rand(65, 90));
                if ($c == 'I') $c = 'P';
                if ($c == 'O') $c = 'N';
            }
            $rndstring .= $c;
        }
    } else {
        $chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $rndstring = '';
        $length = rand(4, 4);
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rndstring .= $chars[mt_rand(0, $max)];
        }
    }
    $_SESSION['securimage_code_value'] = strtolower($rndstring);
    $rndcodelen = strlen($rndstring);
    //增加一些噪线
    for ($i = 0; $i < 5; $i++) {
        $red = mt_rand(50, 255);
        $green = mt_rand(50, 255);
        $blue = mt_rand(50, 255);
        $tcol = imagecolorallocate($im, $red, $green, $blue);
        if (mt_rand(0, 1)) { //Horizontal
            $Xa   = mt_rand(0, $img_width / 2);
            $Ya   = mt_rand(0, $img_height);
            $Xb   = mt_rand($img_width / 2, $img_width);
            $Yb   = mt_rand(0, $img_height);
        } else { //Vertical
            $Xa   = mt_rand(0, $img_width);
            $Ya   = mt_rand(0, $img_height / 2);
            $Xb   = mt_rand(0, $img_width);
            $Yb   = mt_rand($img_height / 2, $img_height);
        }
        imagesetthickness($im, mt_rand(1, 3));
        imageline($im, $Xa, $Ya, $Xb, $Yb, $tcol);
    }
    //画边框
    if ($use_boder && $filter_type == 0) {
        $bordercolor = imagecolorallocate($im, 0x9d, 0x9e, 0x96);
        imagerectangle($im, 0, 0, $img_width - 1, $img_height - 1, $bordercolor);
    }
    //输出文字
    $lastc = '';
    for ($i = 0; $i < $rndcodelen; $i++) {
        $rndstring[$i] = strtoupper($rndstring[$i]);
        $c_fontColor = $fontColor[mt_rand(0, 4)];
        $y_pos = $i == 0 ? 4 : $i * ($font_size + 2);
        $c = mt_rand(10, 30);
        @imagettftext($im, $font_size, $c, $y_pos, 28, $c_fontColor, $font_file, $rndstring[$i]);
        $lastc = $rndstring[$i];
    }
    //图象效果
    switch ($filter_type) {
        case 1:
            imagefilter($im, IMG_FILTER_NEGATE);
            break;
        case 2:
            imagefilter($im, IMG_FILTER_EMBOSS);
            break;
        case 3:
            imagefilter($im, IMG_FILTER_EDGEDETECT);
            break;
        default:
            break;
    }
    header("Pragma:no-cache\r\n");
    header("Cache-Control:no-cache\r\n");
    header("Expires:0\r\n");
    if (function_exists("imagejpeg")) {
        header("content-type:image/jpeg\r\n");
        imagejpeg($im);
    } else {
        header("content-type:image/png\r\n");
        imagepng($im);
    }
    imagedestroy($im);
    exit();
}