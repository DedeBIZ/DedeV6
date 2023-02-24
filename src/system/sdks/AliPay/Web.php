<?php
namespace AliPay;
if (!defined('DEDEINC')) exit('dedebiz');
use WeChat\Contracts\BasicAliPay;
/**
 * 支付宝网站支付
 * Class Web
 * @package AliPay
 */
class Web extends BasicAliPay
{
    /**
     * Web constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->options->set('method', 'alipay.trade.page.pay');
        $this->params->set('product_code', 'FAST_INSTANT_TRADE_PAY');
    }
    /**
     * 创建数据操作
     * @param array $options
     * @return string
     */
    public function apply($options)
    {
        parent::applyData($options);
        return $this->buildPayHtml();
    }
}
?>