<?php

namespace AkyosUpdates\Core\Checks\Security;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\DefenderService;

final class DefenderRecaptchaCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'security.defender_recaptcha_keys';
    }

    public function getCategory(): string
    {
        return 'Sécurité';
    }

    public function getTitle(): string
    {
        return 'Defender reCAPTCHA';
    }
    public function getSuccessMessage(): string
    {
        return 'Configuration Defender reCAPTCHA mise à jour.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $canUse = DefenderService::isPluginActive() && DefenderService::canUseApi();
        $state = DefenderService::getRecaptchaState();

        if (!$canUse) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'reCAPTCHA non vérifiable tant que Defender n\'est pas actif.',
                false,
                null,
                $state
            );
        }

        $hasKeys = (bool) ($state['hasKeys'] ?? false);
        $keysValid = (bool) ($state['keysValid'] ?? false);
        $isReady = $hasKeys && $keysValid;

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            $isReady ? 'ok' : 'warn',
            $isReady ? 'success' : 'warning',
            $isReady
            ? 'Clés reCAPTCHA configurées. Vérifie le widget de preview pour valider la clé site.'
            : 'Clés reCAPTCHA absentes ou invalides.',
            !$isReady,
            $isReady ? null : 'security.defender_save_recaptcha_keys',
            $state
        );
    }
}
