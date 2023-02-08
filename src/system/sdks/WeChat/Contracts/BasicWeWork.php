<?php
namespace WeChat\Contracts;
if (!defined('DEDEINC')) exit('dedebiz');
/**
 * 企业微信基础类
 * Class BasicWeWork
 * @package WeChat\Contracts
 */
class BasicWeWork extends BasicWeChat
{
    /**
     * 获取访问 AccessToken
     * @return string
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     */
    public function getAccessToken()
    {
        if ($this->access_token) return $this->access_token;
        $ckey = $this->config->get('appid') . '_access_token';
        if ($this->access_token = Tools::getCache($ckey)) return $this->access_token;
        list($appid, $secret) = [$this->config->get('appid'), $this->config->get('appsecret')];
        $result = Tools::json2arr(Tools::get("https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$appid}&corpsecret={$secret}"));
        if (isset($result['access_token']) && $result['access_token']) Tools::setCache($ckey, $result['access_token'], 7000);
        return $this->access_token = $result['access_token'];
    }

}