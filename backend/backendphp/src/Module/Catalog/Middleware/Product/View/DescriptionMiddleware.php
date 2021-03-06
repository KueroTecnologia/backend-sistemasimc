<?php


declare(strict_types=1);

namespace KED\Module\Catalog\Middleware\Product\View;

use function KED\get_js_file_url;
use KED\Module\Graphql\Services\GraphqlExecutor;
use KED\Services\Http\Request;
use KED\Services\Http\Response;
use KED\Middleware\MiddlewareAbstract;


class DescriptionMiddleware extends MiddlewareAbstract
{
    /**
     * @param Request $request
     * @param Response $response
     * @param null $delegate
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, $delegate = null)
    {
        if ($response->getStatusCode() == 404)
            return $delegate;

        if ($response->hasWidget('product_view_general_info'))
            return $delegate;

        $this->getContainer()
            ->get(GraphqlExecutor::class)
            ->waitToExecute([
                "query"=>"{
                    description: product(id: {$request->attributes->get('id')})
                    {
                        description
                    }
                }"
            ])
            ->then(function ($result) use ($response) {
                /**@var \GraphQL\Executor\ExecutionResult $result */
                if (isset($result->data['description']) and $result->data['description']) {
                    $response->addWidget(
                        'product_description',
                        'product_single_tabs',
                        10,
                        get_js_file_url("production/catalog/product/view/description.js", false),
                        ['description' => $result->data['description']['description']]
                    );
                }
            });

        return $delegate;
    }
}