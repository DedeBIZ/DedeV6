<?php
if (!defined('DEDEINC')) exit ('dedebiz');
require_once DEDEINC."/libraries/imageresize.class.php";
/**
 * 图像处理相关函数
 *
 * @version        $id:image.func.php 15:59 2010年7月5日 tianya $
 * @package        DedeBIZ.Helpers
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
/**
 *  缩图片自动生成函数，来源支持bmp、gif、jpg、png但生成的小图只用jpg或png格式
 *
 * @access    public
 * @param     string  $srcFile  图片路径
 * @param     string  $toW  转换到的宽度
 * @param     string  $toH  转换到的高度
 * @param     string  $toFile  输出文件到
 * @return    string
 */
if (!function_exists('ImageResize')) {
    function ImageResize($srcFile, $toW, $toH, $toFile = "")
    {
        try {
            $image = new ImageResize($srcFile);
            $image->resizeToBestFit($toW, $toH);
            $image->save($toFile);
            return true;
        } catch (ImageResizeException $e) {
            return false;
        }
    }
}
/**
 *  获得GD的版本
 *
 * @access    public
 * @return    int
 */
if (!function_exists('gdversion')) {
    function gdversion()
    {
        //没启用php.ini函数的情况下如果有GD默认视作2.0以上版本
        if (!function_exists('phpinfo')) {
            if (function_exists('imagecreate')) {
                return '2.0';
            } else {
                return 0;
            }
        } else {
            ob_start();
            phpinfo(8);
            $module_info = ob_get_contents();
            ob_end_clean();
            if (preg_match("/\bgd\s+version\b[^\d\n\r]+?([\d\.]+)/i", $module_info, $matches)) {
                $gdversion_h = $matches[1];
            } else {
                $gdversion_h = 0;
            }
            return $gdversion_h;
        }
    }
}
/**
 *  图片自动加水印函数
 *
 * @access    public
 * @param     string  $srcFile  图片源文件
 * @param     string  $fromGo  位置
 * @return    string
 */
if (!function_exists('WaterImg')) {
    function WaterImg($srcFile, $fromGo = 'up')
    {
        include(DEDEDATA.'/mark/inc_photowatermark_config.php');
        require_once(DEDEINC.'/dedeimage.class.php');
        if (isset($GLOBALS['needwatermark'])) {
            $photo_markup = $photo_markdown = empty($GLOBALS['needwatermark']) ? '0' : '1';
        }
        if ($photo_markup != '1' || ($fromGo == 'collect' && $photo_markdown != '1')) {
            return;
        }
        $info = '';
        $srcInfo = @getimagesize($srcFile, $info);
        $srcFile_w    = $srcInfo[0];
        $srcFile_h    = $srcInfo[1];
        if ($srcFile_w < $photo_wwidth || $srcFile_h < $photo_wheight) {
            return;
        }
        if ($fromGo == 'up' && $photo_markup == '0') {
            return;
        }
        if ($fromGo == 'down' && $photo_markdown == '0') {
            return;
        }
        $TRUEMarkimg = DEDEDATA.'/mark/'.$photo_markimg;
        if (!file_exists($TRUEMarkimg) || empty($photo_markimg)) {
            $TRUEMarkimg = "";
        }
        if ($photo_waterpos == 0) {
            $photo_waterpos = rand(1, 9);
        }
        $cfg_watermarktext = array();
        if ($photo_marktype == '2') {
            if (file_exists(DEDEDATA.'/mark/simhei.ttf')) {
                $cfg_watermarktext['fontpath'] =  DEDEDATA.'/mark/simhei.ttf';
            } else {
                return;
            }
        }
        $cfg_watermarktext['text'] = $photo_watertext;
        $cfg_watermarktext['size'] = $photo_fontsize;
        $cfg_watermarktext['angle'] = '0';
        $cfg_watermarktext['color'] = '255,255,255';
        $cfg_watermarktext['shadowx'] = '0';
        $cfg_watermarktext['shadowy'] = '0';
        $cfg_watermarktext['shadowcolor'] = '0,0,0';
        $photo_marktrans = 85;
        $img = new DedeImage($srcFile, 0, $cfg_watermarktext, $photo_waterpos, $photo_diaphaneity, $photo_wheight, $photo_wwidth, $photo_marktype, $photo_marktrans, $TRUEMarkimg);
        $img->watermark(0);
    }
}
/**
 *  会对空白地方填充满
 *
 * @access    public
 * @param     string  $srcFile  图片路径
 * @param     string  $toW  转换到的宽度
 * @param     string  $toH  转换到的高度
 * @param     string  $toFile  输出文件到
 * @param     string  $issave  是否保存
 * @return    bool
 */
if (!function_exists('ImageResizeNew')) {
    function ImageResizeNew($srcFile, $toW, $toH, $toFile = '', $issave = TRUE)
    {
        try {
            $image = new ImageResize($srcFile);
            $image->resizeToBestFit($toW, $toH);
            if ($issave) {
                $image->save($toFile);
            } else {
                $image->output();
            }
            return true;
        } catch (ImageResizeException $e) {
            return false;
        }
    }
}
?>