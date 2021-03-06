<?php


declare(strict_types=1);

namespace KED\Module\Discount\Services\Type;


use GraphQL\Type\Definition\InputObjectType;
use function KED\dispatch_event;
use KED\Module\Graphql\Services\FilterFieldType;
use KED\Services\Di\Container;

class CouponCollectionFilterType extends InputObjectType
{
    public function __construct(Container $container)
    {
        $config = [
            'name'=> 'CouponCollectionFilter',
            'fields' => function () use ($container) {
                $fields = [
                    'id' => $container->get(FilterFieldType::class),
                    'coupon' => $container->get(FilterFieldType::class),
                    'description' => $container->get(FilterFieldType::class),
                    'free_shipping' => $container->get(FilterFieldType::class),
                    'status' => $container->get(FilterFieldType::class),
                    'limit' => $container->get(FilterFieldType::class),
                    'page' => $container->get(FilterFieldType::class),
                    'sortBy' => $container->get(FilterFieldType::class),
                    'sortOrder' => $container->get(FilterFieldType::class)
                ];

                dispatch_event('filter.couponCollectionFilter.input', [&$fields]);

                return $fields;
            }
        ];
        parent::__construct($config);
    }
}