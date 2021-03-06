<?php

declare(strict_types=1);

namespace KED\Module\Catalog\Middleware\Product\Save;

use KED\Module\Catalog\Services\ProductMutator;
use KED\Services\Http\Request;
use KED\Services\Http\Response;
use KED\Middleware\MiddlewareAbstract;
use KED\Services\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;

class UpdateMiddleware extends MiddlewareAbstract
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array|null $data
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, $delegate = null)
    {
        $data = $request->request->all();
        try {
            if ($request->attributes->get('id', null) == null)
                return $data;
            $id = $this->getContainer()
                ->get(ProductMutator::class)
                ->updateProduct(
                    (int) $request->attributes->get('id', null),
                    $data
                );

            $this->getContainer()->get(Session::class)->getFlashBag()->add('success', 'Product has been saved');
            $response->redirect($this->getContainer()->get(Router::class)->generateUrl('product.grid'));

            return $id;
        } catch(\Exception $e) {
            $response->addAlert('product_add_error', 'error', $e->getMessage())->notNewPage();

            return $response;
        }
    }
}