<?php
namespace AliPay;
if (!defined('DEDEINC')) exit('dedebiz');
use WeChat\Contracts\BasicAliPay;

/**
 * 支付宝标准接口
 * Class Trade
 * @package AliPay
 */
class Trade extends BasicAliPay
{

    /**
     * 设置交易接口地址
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->options->set('method', $method);
        return $this;
    }

    /**
     * 获取交易接口地址
     * @return string
     */
    public function getMethod()
    {
        return $this->options->get('method');
    }

    /**
     * 设置接口公共参数
     * @param array $option
     * @return Trade
     */
    public function setOption($option = [])
    {
        foreach ($option as $key => $vo) {
            $this->options->set($key, $vo);
        }
        return $this;
    }

    /**
     * 获取接口公共参数
     * @return array|string|null
     */
    public function getOption()
    {
        return $this->options->get();
    }

    /**
     * 执行通过接口
     * @param array $options
     * @return array|boolean
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     */
    public function apply($options)
    {
        return $this->getResult($options);
    }
}