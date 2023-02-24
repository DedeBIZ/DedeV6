<?php
namespace WeChat\Exceptions;
if (!defined('DEDEINC')) exit('dedebiz');
/**
 * 加载类异常
 * Class InvalidInstanceException
 * @package WeChat\Exceptions
 */
class InvalidInstanceException extends \Exception
{
    /**
     * @var array
     */
    public $raw = [];
    /**
     * InvalidResponseException constructor.
     * @param string $message
     * @param integer $code
     * @param array $raw
     */
    public function __construct($message, $code = 0, $raw = [])
    {
        parent::__construct($message, intval($code));
        $this->raw = $raw;
    }
}
?>