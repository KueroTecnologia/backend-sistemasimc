<?php


declare(strict_types=1);

namespace KED\Module\Catalog\Middleware\Attribute\Save;

use function KED\_mysql;
use KED\Services\Db\Processor;
use KED\Services\Http\Request;
use KED\Services\Http\Response;
use KED\Middleware\MiddlewareAbstract;
use KED\Services\Routing\Router;

class CreateMiddleware extends MiddlewareAbstract
{
    /**@var Processor $conn*/
    protected $conn;
    /**
     * @param Request $request
     * @param Response $response
     * @param array $data
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, $delegate = null)
    {
        if ($request->attributes->get('id', null) != null)
            return $delegate;

        $this->conn = _mysql();
        try {
            $conn = _mysql();
            $conn->getTable('attribute')
                ->insert($request->request->all());
            $id = $conn->getLastID();

            if (in_array($request->request->get('type'), ['select', 'multiselect']))
                $this->saveOptions((int) $id, $request->request->get('attribute_code'), $request->request->get('options'));

            $response->addAlert('attribute_save_success', 'success', 'Attribute saved')
                ->redirect($this->getContainer()->get(Router::class)->generateUrl('attribute.grid'));

            return $response;
        } catch(\Exception $e) {
            $response->addAlert('attribute_save_error', 'error', $e->getMessage());

            return $response;
        }
    }

    protected function saveOptions(int $attributeId, string $attributeCode, array $options)
    {
        foreach ($options as $key=>$option) {
            $optionData = [
                'attribute_id'  => $attributeId,
                'attribute_code' => $attributeCode,
                'option_text' => $option['option_text']
            ];
            $this->conn->getTable('attribute_option')->insert($optionData);
        }
    }
}