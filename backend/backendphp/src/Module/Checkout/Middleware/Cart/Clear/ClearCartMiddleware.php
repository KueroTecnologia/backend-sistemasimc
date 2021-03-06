<?php


declare(strict_types=1);

namespace KED\Module\Checkout\Middleware\Cart\Clear;

use KED\Services\Checkout\Cart\Cart;
use KED\Services\Http\Request;
use KED\Services\Http\Response;
use KED\Middleware\Delegate;
use KED\Middleware\MiddlewareAbstract;

class ClearCartMiddleware extends MiddlewareAbstract
{
    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @param Delegate|null $delegate
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, callable $next, Delegate $delegate)
    {
        try {
            /**@var $cart Cart*/
            $cart = $request->getApp()->get(Cart::class);
            $cart->emptyCart()->commit();
            $request->getSession()->remove('cart_id');
            $response->addAlert('cart_add_success', 'success', 'Shopping cart is empty');
            $response->addData("minicart", $cart->toMiniCartJson())->notNewPage();
            $delegate->stopAndResponse();
        } catch (\Exception $e) {
            $response->addAlert('cart_add_error', 'error', $e->getMessage());
        }
        return $next($request, $response, $delegate);
    }
}