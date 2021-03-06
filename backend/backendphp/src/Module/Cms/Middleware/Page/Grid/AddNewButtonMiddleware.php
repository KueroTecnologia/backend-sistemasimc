<?php

declare(strict_types=1);

namespace KED\Module\Cms\Middleware\Page\Grid;

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
            'cms_page_add_new_button',
            'cms_page_grid_container',
            5,
            get_js_file_url("production/cms/page/grid/add_new_button.js", true),
            [
                "url" => generate_url('page.create')
            ]
        );

        return $delegate;
    }
}