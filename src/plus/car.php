<?php

/**
 *
 * 显示购物车的商品
 *
 * @version        $Id: car.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../include/common.inc.php");
define('_PLUS_TPL_', DEDEROOT.'/templets/plus');
require_once(DEDEINC.'/dedetemplate.class.php');
require_once DEDEINC.'/shopcar.class.php';
require_once DEDEINC.'/memberlogin.class.php';
$cart = new MemberShops();

if (isset($dopost) && $dopost == 'makeid') {
    AjaxHead();
    $cart->MakeOrders();
    echo $cart->OrdersId;
    exit;
}
$cfg_ml = new MemberLogin();
//获得购物车内商品,返回数组
$Items = $cart->getItems();
if ($cart->cartCount() < 1) {
    ShowMsg("购物车中不存在任何商品！", "javascript:window.close();", false, 5000);
    exit;
}
@sort($Items);

$carts = array(
    'orders_id' => $cart->OrdersId,
    'cart_count' => $cart->cartCount(),
    'price_count' => $cart->priceCount()
);

$dtp = new DedeTemplate();
$dtp->Assign('carts', $carts);
$dtp->LoadTemplate(_PLUS_TPL_.'/car.htm');
$dtp->Display();
exit;
