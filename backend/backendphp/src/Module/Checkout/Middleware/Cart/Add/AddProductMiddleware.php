<?php


declare(strict_types=1);

namespace KED\Module\Checkout\Middleware\Cart\Add;

use KED\Module\Checkout\Services\Cart\Cart;
use KED\Module\Checkout\Services\Cart\Item;
use KED\Services\Http\Request;
use KED\Services\Http\Response;
use KED\Middleware\MiddlewareAbstract;

class AddProductMiddleware extends MiddlewareAbstract
{
    /**
     * @param Request $request
     * @param Response $response
     * @param null $delegate
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, $delegate = null)
    {
        $promise = $this->getContainer()->get(Cart::class)->addItem(
            $request->request->all()
        );

        $promise->then(function (Item $item) use ($response) {
            $response->addAlert('cart_add_success', 'success', "{$item->getData('product_name')} was added to shopping cart successfully")->notNewPage();
        });

        $promise->otherwise(function ($item) use ($request, $response) {
            $errors = $item->getError();
            if (count($errors) == 1 and (isset($errors['product_custom_options']) || isset($errors['variant_options']))) {
                $request->getSession()->getFlashBag()->set('error', $errors['product_custom_options'] ?? $errors['variant_options']);
                $response->redirect($item->getData('product_url'));
            } else
                foreach ($errors as $field => $message) {
                    $response->addAlert('cart_add_error', 'error', $message);
                }
            $response->notNewPage();
        });

        return $promise;
    }
}