<?php


declare(strict_types=1);

/** @var \KED\Services\Event\EventDispatcher $eventDispatcher */

use function KED\get_config;
use KED\Module\Checkout\Services\Cart\Cart;

$eventDispatcher->addListener(
    'register.checkout.index.middleware',
    function (\KED\Services\MiddlewareManager $middlewareManager) {
        $middlewareManager->registerMiddleware(\KED\Module\FlatRate\Middleware\Checkout\FlatRateMiddleware::class, 31);
    },
    10
);

$eventDispatcher->addListener(
    'shipping_method',
    function ($method, array $context = []) {

        /**@var Cart $cart*/
        $cart = $context[0];
        $requestingMethod = $cart->getDataSource()['shipping_method'] ?? null;
        if ($requestingMethod !== "flat_rate")
            return $method;

        $shippingAddress = \KED\_mysql()->getTable('cart_address')->load($cart->getData('shipping_address_id'));
        if (
            $requestingMethod == 'flat_rate' and
            get_config('shipment_flat_rate_status') == 1 and
            (
                in_array($shippingAddress['country'] ?? null, get_config('shipment_flat_rate_countries', [])) ||
                get_config('shipment_flat_rate_countries', []) == []
            )
        )
            return 'flat_rate';
        else
            return null;
    },
    0
);

$eventDispatcher->addListener(
    'shipping_fee_excl_tax',
    function ($value, array $context = []) {
        /**@var Cart $cart*/
        $cart = $context[0];
        if ($cart->getData('shipping_method') != 'flat_rate')
            return $value;
        $coupon = \KED\the_container()->get(\KED\Module\Discount\Services\CouponHelper::class)->getCoupon();
        if ($coupon and $coupon['free_shipping'] == 1)
            return 0;

        return get_config('shipment_flat_rate_fee', 0);
    },
    0
);