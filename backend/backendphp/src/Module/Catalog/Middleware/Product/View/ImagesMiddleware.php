<?php


declare(strict_types=1);

namespace KED\Module\Catalog\Middleware\Product\View;

use function KED\get_js_file_url;
use KED\Module\Graphql\Services\GraphqlExecutor;
use KED\Services\Http\Request;
use KED\Services\Http\Response;
use KED\Middleware\MiddlewareAbstract;


class ImagesMiddleware extends MiddlewareAbstract
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

        $this->getContainer()
            ->get(GraphqlExecutor::class)
            ->waitToExecute([
                "query"=>"{
                    productImages(productId: {$request->attributes->get('id')})
                    {
                        images {
                            image
                            main
                            thumb
                            isMain
                        }
                        productName 
                    }
                }"
            ])
            ->then(function ($result) use ($response) {
                /**@var \GraphQL\Executor\ExecutionResult $result */
                //var_dump($result);
                if (isset($result->data['productImages']) and $result->data['productImages']) {
//                    $mainImage = null;
//                    foreach ($result->data['productImages']['images'] as $key => $val)
//                        if ($val['isMain'] == true) {
//                            $mainImage = $val;
//                            unset($result->data['productImages']['images'][$key]);
//                        }
                    $response->addWidget(
                        'product_view_images',
                        'product_page_middle_left',
                        10,
                        get_js_file_url("production/catalog/product/view/images.js", false),
                        $result->data['productImages']
                    );
                }
            });

        return $delegate;
    }
}