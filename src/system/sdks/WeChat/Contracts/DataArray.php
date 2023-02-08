<?php
namespace WeChat\Contracts;
if (!defined('DEDEINC')) exit('dedebiz');
use ArrayAccess;

/**
 * Class DataArray
 * @package WeChat
 */
class DataArray implements ArrayAccess
{

    /**
     * 当前配置值
     * @var array
     */
    private $config = [];

    /**
     * Config constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->config = $options;
    }

    /**
     * 设置配置项值
     * @param string $offset
     * @param string|array|null|integer $value
     */
    public function set($offset, $value)
    {
        $this->offsetSet($offset, $value);
    }

    /**
     * 获取配置项参数
     * @param string|null $offset
     * @return array|string|null
     */
    public function get($offset = null)
    {
        return $this->offsetGet($offset);
    }

    /**
     * 合并数据到对象
     * @param array $data 需要合并的数据
     * @param bool $append 是否追加数据
     * @return array
     */
    public function merge(array $data, $append = false)
    {
        if ($append) {
            return $this->config = array_merge($this->config, $data);
        }
        return array_merge($this->config, $data);
    }

    /**
     * 设置配置项值
     * @param string $offset
     * @param string|array|null|integer $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->config[] = $value;
        } else {
            $this->config[$offset] = $value;
        }
    }

    /**
     * 判断配置Key是否存在
     * @param string $offset
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    /**
     * 清理配置项
     * @param string|null $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset = null)
    {
        if (is_null($offset)) {
            $this->config = [];
        } else {
            unset($this->config[$offset]);
        }
    }

    /**
     * 获取配置项参数
     * @param string|null $offset
     * @return array|string|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset = null)
    {
        if (is_null($offset)) {
            return $this->config;
        }
        return isset($this->config[$offset]) ? $this->config[$offset] : null;
    }
}