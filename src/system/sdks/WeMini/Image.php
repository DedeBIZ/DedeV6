<?php
namespace WeMini;
if (!defined('DEDEINC')) exit ('dedebiz');
use WeChat\Contracts\BasicWeChat;
/**
 * 小程序图像处理
 * Class Image
 * @package WeMini
 */
class Image extends BasicWeChat
{
    /**
     * 本接口提供基于小程序的图片智能裁剪能力
     * @param string $img_url 要检测的图片 url，传这个则不用传 img 参数。
     * @param string $img form-data 中媒体文件标识，有filename、filelength、content-type等信息，传这个则不用穿 img_url
     * @return array
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     */
    public function aiCrop($img_url, $img)
    {
        $url = "https://api.weixin.qq.com/cv/img/aicrop?access_token=ACCESS_TOCKEN";
        return $this->callPostApi($url, ['img_url' => $img_url, 'img' => $img], true);
    }
    /**
     * 本接口提供基于小程序的条码/二维码识别的API
     * @param string $img_url 要检测的图片 url，传这个则不用传 img 参数。
     * @param string $img form-data 中媒体文件标识，有filename、filelength、content-type等信息，传这个则不用穿 img_url
     * @return array
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     */
    public function scanQRCode($img_url, $img)
    {
        $url = "https://api.weixin.qq.com/cv/img/qrcode?img_url=ENCODE_URL&access_token=ACCESS_TOCKEN";
        return $this->callPostApi($url, ['img_url' => $img_url, 'img' => $img], true);
    }
    /**
     * 本接口提供基于小程序的图片高清化能力
     * @param string $img_url 要检测的图片 url，传这个则不用传 img 参数
     * @param string $img form-data 中媒体文件标识，有filename、filelength、content-type等信息，传这个则不用穿 img_url
     * @return array
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     */
    public function superresolution($img_url, $img)
    {
        $url = "https://api.weixin.qq.com/cv/img/qrcode?img_url=ENCODE_URL&access_token=ACCESS_TOCKEN";
        return $this->callPostApi($url, ['img_url' => $img_url, 'img' => $img], true);
    }
}
?>