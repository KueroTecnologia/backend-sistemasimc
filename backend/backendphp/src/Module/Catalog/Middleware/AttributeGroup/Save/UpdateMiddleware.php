<?php


declare(strict_types=1);

namespace KED\Module\Catalog\Middleware\AttributeGroup\Save;

use function KED\_mysql;
use KED\Services\Db\Processor;
use KED\Services\Http\Request;
use KED\Services\Http\Response;
use KED\Middleware\MiddlewareAbstract;
use KED\Services\Routing\Router;

class UpdateMiddleware extends MiddlewareAbstract
{
    /**@var Processor $conn*/
    protected $conn;

    /**
     * @param Request $request
     * @param Response $response
     * @param null $delegate
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, $delegate = null)
    {
        if ($request->attributes->get('id', null) == null)
            return $delegate;

        $this->conn = _mysql();
        try {
            $conn = _mysql();
            $conn->startTransaction();

            $conn->getTable('attribute_group')
                ->where('attribute_group_id', '=', $request->attributes->get('id'))
                ->update($request->request->all());

            $oldAttributes = $conn->getTable('attribute_group_link')->where('group_id', '=', $request->attributes->get('id'))->fetchAllAssoc();

            if ($attributes = $request->request->get('attributes'))
                foreach ($attributes as $attribute) {
                    if ($conn->getTable('attribute')->load($attribute))
                        $conn->getTable('attribute_group_link')->insertOnUpdate(['attribute_id'=>$attribute, 'group_id'=>$request->attributes->get('id')]);
                }

            foreach ($oldAttributes as $oldAttr)
                if (!in_array($oldAttr['attribute_id'], $attributes))
                    $conn->getTable('attribute_group_link')
                        ->where('attribute_id', '=', $oldAttr['attribute_id'])
                        ->andWhere('group_id', '=', $request->attributes->get('id'))
                        ->delete();

            $conn->commit();
            $response->addAlert('attribute_group_save_success', 'success', 'Attribute group saved')
                ->redirect($this->getContainer()->get(Router::class)->generateUrl('attribute.group.grid'));

            return $response;
        } catch(\Exception $e) {
            $conn->rollback();
            $response->addAlert('attribute_group_save_error', 'error', $e->getMessage());
            return $response;
        }
    }
}