<?php
namespace WeMini;
if (!defined('DEDEINC')) exit('dedebiz');
use WeChat\Contracts\BasicWeChat;
/**
 * 小程序生物认证
 * Class Soter
 * @package WeMini
 */
class Soter extends BasicWeChat
{
    /**
     * SOTER 生物认证秘钥签名验证
     * @param array $data
     * @return array
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     */
    public function verifySignature($data)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/soter/verify_signature?access_token=ACCESS_TOKEN';
        return $this->callPostApi($url, $data, true);
    }
}
?>