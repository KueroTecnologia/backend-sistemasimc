<?php


declare(strict_types=1);

namespace KED\Module\Cms\Middleware\Widget\Grid;

use function KED\generate_url;
use function KED\get_js_file_url;
use KED\Services\Http\Request;
use KED\Services\Http\Response;
use KED\Middleware\MiddlewareAbstract;

class AddNewButtonMiddleware extends MiddlewareAbstract
{
    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, $delegate = null)
    {
        $response->addWidget(
            'cms_widget_add_new_button',
            'cms_widget_grid_container',
            5,
            get_js_file_url("production/cms/widget/grid/add_new_button.js", true),
            [
                "url" => generate_url('widget.create')
            ]
        );

        return $delegate;
    }
}