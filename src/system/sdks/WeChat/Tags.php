<?php
namespace WeChat;
if (!defined('DEDEINC')) exit('dedebiz');
use WeChat\Contracts\BasicWeChat;
/**
 * 会员标签管理
 * Class Tags
 * @package WeChat
 */
class Tags extends BasicWeChat
{
    /**
     * 获取粉丝标签列表
     * @throws Exceptions\InvalidResponseException
     * @throws Exceptions\LocalCacheException
     */
    public function getTags()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/tags/get?access_token=ACCESS_TOKEN";
        $this->registerApi($url, __FUNCTION__, func_get_args());
        return $this->httpGetForJson($url);
    }
    /**
     * 创建粉丝标签
     * @param string $name
     * @return array
     * @throws Exceptions\InvalidResponseException
     * @throws Exceptions\LocalCacheException
     */
    public function createTags($name)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/tags/create?access_token=ACCESS_TOKEN";
        $this->registerApi($url, __FUNCTION__, func_get_args());
        return $this->httpPostForJson($url, ['tag' => ['name' => $name]]);
    }
    /**
     * 更新粉丝标签
     * @param integer $id 标签ID
     * @param string $name 标签名称
     * @return array
     * @throws Exceptions\InvalidResponseException
     * @throws Exceptions\LocalCacheException
     */
    public function updateTags($id, $name)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/tags/update?access_token=ACCESS_TOKEN";
        $this->registerApi($url, __FUNCTION__, func_get_args());
        return $this->httpPostForJson($url, ['tag' => ['name' => $name, 'id' => $id]]);
    }
    /**
     * 删除粉丝标签
     * @param int $tagId
     * @return array
     * @throws Exceptions\InvalidResponseException
     * @throws Exceptions\LocalCacheException
     */
    public function deleteTags($tagId)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/tags/delete?access_token=ACCESS_TOKEN';
        $this->registerApi($url, __FUNCTION__, func_get_args());
        return $this->httpPostForJson($url, ['tag' => ['id' => $tagId]]);
    }
    /**
     * 批量为会员打标签
     * @param array $openids
     * @param integer $tagId
     * @return array
     * @throws Exceptions\InvalidResponseException
     * @throws Exceptions\LocalCacheException
     */
    public function batchTagging(array $openids, $tagId)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging?access_token=ACCESS_TOKEN';
        $this->registerApi($url, __FUNCTION__, func_get_args());
        return $this->httpPostForJson($url, ['openid_list' => $openids, 'tagid' => $tagId]);
    }
    /**
     * 批量为会员取消标签
     * @param array $openids
     * @param integer $tagId
     * @return array
     * @throws Exceptions\InvalidResponseException
     * @throws Exceptions\LocalCacheException
     */
    public function batchUntagging(array $openids, $tagId)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/tags/members/batchuntagging?access_token=ACCESS_TOKEN';
        $this->registerApi($url, __FUNCTION__, func_get_args());
        return $this->httpPostForJson($url, ['openid_list' => $openids, 'tagid' => $tagId]);
    }
    /**
     * 获取会员身上的标签列表
     * @param string $openid
     * @return array
     * @throws Exceptions\InvalidResponseException
     * @throws Exceptions\LocalCacheException
     */
    public function getUserTagId($openid)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/tags/getidlist?access_token=ACCESS_TOKEN';
        $this->registerApi($url, __FUNCTION__, func_get_args());
        return $this->httpPostForJson($url, ['openid' => $openid]);
    }
}
?>