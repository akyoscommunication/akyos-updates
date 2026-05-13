<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\DefenderService;
use WP_Defender\Model\Setting\Password_Protection;

final class DefenderEnablePwnedPasswordsAction implements ActionInterface
{
    public function getId(): string
    {
        return 'security.defender_enable_pwned_passwords';
    }

    public function run(array $payload = []): ActionResult
    {
        if (! DefenderService::isPluginActive() || ! DefenderService::canUseApi()) {
            return ActionResult::failure('Defender doit être actif pour configurer Pwned Passwords.');
        }

        /** @var Password_Protection $model */
        $model = wd_di()->get(Password_Protection::class);
        $model->enabled = true;

        if (! is_array($model->user_roles) || $model->user_roles === []) {
            if (function_exists('get_editable_roles')) {
                $model->user_roles = array_keys(get_editable_roles());
            } else {
                $model->user_roles = ['administrator'];
            }
        }

        if (! $model->validate()) {
            $message = method_exists($model, 'get_formatted_errors')
                ? (string) $model->get_formatted_errors()
                : 'Configuration Pwned Passwords invalide.';

            return ActionResult::failure($message);
        }

        $model->save();

        return ActionResult::success('Pwned Passwords Defender activé.', DefenderService::getPwnedPasswordsState());
    }
}
