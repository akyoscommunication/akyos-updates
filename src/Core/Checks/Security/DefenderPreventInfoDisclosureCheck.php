<?php

namespace AkyosUpdates\Core\Checks\Security;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\DefenderService;

final class DefenderPreventInfoDisclosureCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'security.defender_prevent_info_disclosure';
    }

    public function getCategory(): string
    {
        return 'Sécurité';
    }

    public function getTitle(): string
    {
        return 'Defender — Divulgation d\'informations';
    }

    public function getSuccessMessage(): string
    {
        return 'Protection contre la divulgation d\'informations active.';
    }

    public function run(SiteContext $context): CheckResult
    {
        $state = DefenderService::getPreventInfoDisclosureState();
        $canUse = DefenderService::isPluginActive() && DefenderService::canUseHardeningApi();

        if (! $canUse) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Divulgation d\'informations non vérifiable tant que Defender n\'est pas actif.',
                false,
                null,
                $state
            );
        }

        if ($state['active']) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'ok',
                'success',
                'Les fichiers sensibles sont protégés.',
                false,
                null,
                $state
            );
        }

        $message = $state['manualRequired']
            ? sprintf(
                'Fichiers sensibles potentiellement exposés (serveur %s — configuration manuelle requise).',
                (string) $state['server']
            )
            : 'Fichiers sensibles potentiellement exposés.';

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'warn',
            'warning',
            $message,
            (bool) $state['autoApplicable'],
            $state['autoApplicable'] ? 'security.defender_enable_prevent_info_disclosure' : null,
            $state
        );
    }
}
