<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\DefenderService;
use WP_Defender\Model\Setting\Security_Headers;

final class DefenderEnableSecurityHeadersAction implements ActionInterface
{
    public function getId(): string
    {
        return 'security.defender_enable_security_headers';
    }

    public function run(array $payload = []): ActionResult
    {
        if (! DefenderService::isPluginActive() || ! DefenderService::canUseApi()) {
            return ActionResult::failure('Defender doit être actif pour configurer les Security Headers.');
        }

        /** @var Security_Headers $model */
        $model = wd_di()->get(Security_Headers::class);
        $model->sh_xframe = true;
        $model->sh_xss_protection = true;
        $model->sh_content_type_options = true;
        $model->sh_strict_transport = true;

        if (! $model->validate()) {
            $message = method_exists($model, 'get_formatted_errors')
                ? (string) $model->get_formatted_errors()
                : 'Configuration Security Headers invalide.';

            return ActionResult::failure($message);
        }

        $model->save();

        return ActionResult::success('Security Headers Defender activés.', DefenderService::getSecurityHeadersState());
    }
}
