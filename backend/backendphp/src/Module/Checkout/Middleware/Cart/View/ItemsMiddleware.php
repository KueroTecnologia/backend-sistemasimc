<?php


declare(strict_types=1);

namespace KED\Module\Checkout\Middleware\Cart\View;

use function KED\generate_url;
use function KED\get_js_file_url;
use KED\Module\Graphql\Services\GraphqlExecutor;
use KED\Services\Http\Request;
use KED\Services\Http\Response;
use KED\Middleware\MiddlewareAbstract;

class ItemsMiddleware extends MiddlewareAbstract
{
    /**
     * @param Request $request
     * @param Response $response
     * @param null $delegate
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, $delegate = null)
    {
        $this->getContainer()
            ->get(GraphqlExecutor::class)
            ->waitToExecute([
                "query"=>"{
                    items: cart {
                        items {
                            cart_item_id
                            product_id
                            product_name
                            options : product_custom_options {
                                option_id
                                option_name
                                values {
                                    value_id
                                    value_text
                                    extra_price
                                }
                            }
                            variant_options {
                                attribute_id
                                attribute_name
                                attribute_code
                                option_id
                                option_name
                            }
                            thumbnail: product_thumbnail
                            productUrl
                            qty
                            product_price
                            final_price
                            total
                            removeUrl
                            error {
                                message
                            }
                        }
                    }
                }"
            ])
            ->then(function ($result) use ($response) {
                /**@var \GraphQL\Executor\ExecutionResult $result */
                if (isset($result->data['items'])) {
                    $response->addWidget(
                        'shopping_cart_items',
                        'shopping-cart-page',
                        10,
                        get_js_file_url("production/checkout/cart/items.js"),
                        [
                            "items" => $result->data['items']['items']
                        ]
                    );
                }
            }, function ($reason) { var_dump($reason);});

        return $delegate;
    }
}