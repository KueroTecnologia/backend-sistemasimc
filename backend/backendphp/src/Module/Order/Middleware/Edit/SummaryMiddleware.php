<?php


declare(strict_types=1);

namespace KED\Module\Order\Middleware\Edit;

use function KED\create_mutable_var;
use function KED\generate_url;
use function KED\get_js_file_url;
use KED\Module\Graphql\Services\GraphqlExecutor;
use KED\Services\Http\Request;
use KED\Services\Http\Response;
use KED\Middleware\MiddlewareAbstract;

class SummaryMiddleware extends MiddlewareAbstract
{
    /**
     * @param Request $request
     * @param Response $response
     * @param null $delegate
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, $delegate = null)
    {
        // Loading data by using GraphQL
        $this->getContainer()
            ->get(GraphqlExecutor::class)
            ->waitToExecute([
                "query"=> create_mutable_var("order_edit_summary_query", "{
                    summary : order (id: {$request->attributes->get('id')}) {
                        order_id
                        currency
                        coupon
                        discount_amount
                        tax_amount
                        sub_total
                        grand_total
                    }
                }")
            ])
            ->then(function ($result) use ($response) {
                /**@var \GraphQL\Executor\ExecutionResult $result */
                if (isset($result->data['summary'])) {
                    $response->addWidget(
                        'order_summary',
                        'order_edit_right',
                        30,
                        get_js_file_url("production/order/edit/summary.js", true),
                        $result->data['summary']
                    );
                }
            });

        return $delegate;
    }
}
