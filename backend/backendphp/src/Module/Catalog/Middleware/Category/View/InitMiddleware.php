<?php


declare(strict_types=1);

namespace KED\Module\Catalog\Middleware\Category\View;

use function KED\_mysql;
use KED\Services\Helmet;
use KED\Services\Http\Request;
use KED\Services\Http\Response;
use KED\Middleware\MiddlewareAbstract;


class InitMiddleware extends MiddlewareAbstract
{
    /**
     * @param Request $request
     * @param Response $response
     * @param null $delegate
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, $delegate = null)
    {
        if ($request->attributes->get('slug'))
            $category = _mysql()->getTable('category')
            ->leftJoin('category_description')
            ->where('category_description.seo_key', '=', $request->attributes->get('slug'))
            ->fetchOneAssoc();
        else
            $category = _mysql()->getTable('category')
                ->leftJoin('category_description')
                ->where('category.category_id', '=', $request->attributes->get('id'))
                ->fetchOneAssoc();

        if (!$category) {
            $request->attributes->set('_matched_route', 'not.found');
            $response->setStatusCode(404);
        } else
            $request->attributes->set('id', $category['category_id']);

        $this->getContainer()->get(Helmet::class)->setTitle($category['name'])
            ->addMeta([
               'name'=> 'description',
                'content' => $category['short_description']
            ]);

        $response->addState('currentPageType', 'Category')->addState('categoryId', $category['category_id']);

        return $category;
    }
}