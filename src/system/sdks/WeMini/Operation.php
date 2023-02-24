<?php
namespace WeMini;
if (!defined('DEDEINC')) exit('dedebiz');
use WeChat\Contracts\BasicWeChat;
/**
 * 小程序运维中心
 * Class Operation
 * @package WeMini
 */
class Operation extends BasicWeChat
{
    /**
     * 实时日志查询
     * @param array $data
     * @return array
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     */
    public function realtimelogSearch($data)
    {
        $url = 'https://api.weixin.qq.com/wxaapi/userlog/userlog_search?access_token=ACCESS_TOKEN';
        return $this->callPostApi($url, $data, true);
    }
}
?>