<?php

declare(strict_types=1);

namespace KED\Module\Migration\Middleware\Module\Disable;


use function KED\_mysql;
use KED\Middleware\MiddlewareAbstract;
use KED\Services\Http\Request;
use KED\Services\Http\Response;

class ValidateMiddleware extends MiddlewareAbstract
{

    public function __invoke(Request $request, Response $response, $delegate = null)
    {
        try {
            if (!file_exists(CONFIG_PATH . DS . 'config.php') and !file_exists(CONFIG_PATH . DS . 'config.tmp.php'))
                throw new \Exception("You need to install the app first");

            $module = $request->attributes->get("module");
            $conn = _mysql();
            if (!$conn->getTable("migration")->where("module", "=", $module)->fetchOneAssoc())
                throw new \Exception(sprintf("Module %s is not installed", $module));
            if (in_array($module, CORE_MODULES))
                throw new \Exception("No no, you can not live without me");

            return $delegate;
        } catch (\Exception $e) {
            $response->addAlert("module_disable_error", "error", $e->getMessage())->notNewPage();
            return $response;
        }
    }
}