<?php


declare(strict_types=1);

namespace KED\Module\SendGrid\Middleware\Setting;

use function KED\_mysql;
use function KED\generate_url;
use function KED\get_js_file_url;
use KED\Services\Helmet;
use KED\Services\Http\Request;
use KED\Middleware\MiddlewareAbstract;
use KED\Services\Http\Response;
use KED\Services\Routing\Router;

class FormMiddleware extends MiddlewareAbstract
{
    public function __invoke(Request $request, Response $response, $delegate = null)
    {
        if ($request->getMethod() == 'POST')
            return $delegate;

        $this->getContainer()->get(Helmet::class)->setTitle("SendGrid email setting");
        $stm = _mysql()
            ->executeQuery("SELECT * FROM `setting`
WHERE `name` LIKE 'sendgrid_%'
UNION
SELECT * FROM `setting`
WHERE `name` NOT IN (SELECT `name` FROM `setting` WHERE language_id = :language AND `name` LIKE 'sendgrid_%')
AND language_id = 0
AND `name` LIKE 'sendgrid_%'");

        $setting = [];
        while ($row = $stm->fetch()) {
            if ($row['json'] == 1)
                $setting[$row['name']] = json_decode($row['value'], true);
            else
                $setting[$row['name']] = $row['value'];
        }

        $response->addWidget(
            'sendgrid_setting_form',
            'content',
            10,
            get_js_file_url("production/sendgrid/setting_form.js", true),
            array_merge($setting, [
                "formAction"=>$this->getContainer()->get(Router::class)->generateUrl('setting.sendgrid'),
                "dashboardUrl" => generate_url("dashboard"),
                "cancelUrl" => generate_url("setting.sendgrid")
            ])
        );

        return $delegate;
    }
}