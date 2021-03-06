<?php


declare(strict_types=1);

/** @var \KED\Services\Event\EventDispatcher $eventDispatcher */
/** @var Container $container */

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use function KED\_mysql;
use KED\Module\Checkout\Services\Cart\Cart;
use KED\Module\Checkout\Services\Cart\Item;
use KED\Module\Discount\Services\CouponCollection;
use KED\Module\Discount\Services\Type\CouponCollectionFilterType;
use KED\Module\Discount\Services\Type\CouponCollectionType;
use KED\Module\Discount\Services\Type\CouponType;
use KED\Services\Di\Container;
use KED\Services\Http\Request;
use KED\Services\Routing\Router;

$eventDispatcher->addListener(
        "admin_menu",
        function (array $items) {
            return array_merge($items, [
                [
                    "id" => "coupon_add_new",
                    "sort_order" => 10,
                    "url" => \KED\generate_url("coupon.create"),
                    "title" => "New Coupon",
                    "icon" => "hand-holding-usd",
                    "parent_id" => "quick_links"
                ],
                [
                    "id" => "coupon",
                    "sort_order" => 30,
                    "url" => null,
                    "title" => "Coupon",
                    "parent_id" => null
                ],
                [
                    "id" => "coupon_grid",
                    "sort_order" => 10,
                    "url" => \KED\generate_url("coupon.grid"),
                    "title" => "Coupons",
                    "icon" => "tags",
                    "parent_id" => "coupon"
                ]
            ]);
        },
        0
);

$eventDispatcher->addListener(
    'register.checkout.cart.middleware',
    function (\KED\Services\MiddlewareManager $middlewareManager) {
        $middlewareManager->registerMiddleware(\KED\Module\Discount\Middleware\Cart\CouponMiddleware::class, 21);
    },
    0
);

$eventDispatcher->addListener(
    'filter.query.type',
    function (&$fields, Container $container) {
        $fields['coupon'] = [
            'type' => $container->get(CouponType::class),
            'description' => 'Return a coupon',
            'args' => [
                'id' => Type::nonNull(Type::id())
            ],
            'resolve' => function ($value, $args, Container $container, ResolveInfo $info) {
                if ($container->get(Request::class)->isAdmin() == false)
                    return false;

                return _mysql()->getTable('coupon')->load($args['id']);
            }
        ];

        $fields['couponCollection'] = [
            'type' => $container->get(CouponCollectionType::class),
            'description' => "Return list of coupon and total count",
            'args' => [
                'filter' =>  [
                    'type' => $container->get(CouponCollectionFilterType::class)
                ]
            ],
            'resolve' => function ($rootValue, $args, Container $container, ResolveInfo $info) {
                if ($container->get(Request::class)->isAdmin() == false)
                    return [];
                else
                    return $container->get(CouponCollection::class)->getData($rootValue, $args, $container, $info);
            }
        ];
    },
    5
);

$eventDispatcher->addListener("register_cart_field", function (&$fields) {
    // Register discount to cart
    $fields["coupon"] = [
        "resolver" => function (Cart $cart) {
            $coupon = $cart->getDataSource()['coupon'] ?? $cart->getData("coupon") ?? null;
            return \KED\the_container()->get(\KED\Module\Discount\Services\CouponHelper::class)->applyCoupon($coupon, $cart);
        },
        "dependencies" => ['customer_id', 'customer_group_id', 'items']
    ];

    $fields["discount_amount"] = [
        "resolver" => function (Cart $cart) {
            $items = $cart->getItems();
            $discount = 0;
            foreach ($items as $item)
                $discount += $item->getData('discount_amount');

            return $discount;
        },
        "dependencies" => ["coupon"]
    ];

    $fields["grand_total"] = [
        "resolver" => function (Cart $cart) use ($fields){
            return $fields["grand_total"]["resolver"]($cart) - $cart->getData('discount_amount');
        },
        "dependencies" => array_merge($fields["grand_total"]["dependencies"], ["discount_amount"])
    ];
});

$eventDispatcher->addListener("register_cart_item_field", function (array &$fields) {
    $fields["discount_amount"] = [
        "resolver" => function (Item $item) {
            return $item->getDataSource()['discount_amount'] ?? 0;
        }
    ];

    $fields["total"] = [
        "resolver" => function (Item $item) use ($fields){
            return $fields["total"]["resolver"]($item) - $item->getData('discount_amount');
        },
        "dependencies" => array_merge($fields["total"]["dependencies"], ["discount_amount"])
    ];
});