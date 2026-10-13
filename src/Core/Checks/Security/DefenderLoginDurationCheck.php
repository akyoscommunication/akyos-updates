<?php

namespace AkyosUpdates\Core\Checks\Security;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\DefenderService;

final class DefenderLoginDurationCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'security.defender_login_duration';
    }

    public function getCategory(): string
    {
        return 'Sécurité';
    }

    public function getTitle(): string
    {
        return 'Defender — Durée de login';
    }

    public function getSuccessMessage(): string
    {
        return 'Durée de login Defender configurée.';
    }

    public function run(SiteContext $context): CheckResult
    {
        $state = DefenderService::getLoginDurationState();
        $canUse = DefenderService::isPluginActive() && DefenderService::canUseHardeningApi();

        if (! $canUse) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Durée de login non vérifiable tant que Defender n\'est pas actif.',
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
                sprintf('Durée de login limitée à %d jours.', (int) $state['targetDays']),
                false,
                null,
                $state
            );
        }

        $duration = $state['duration'];
        $targetDays = (int) $state['targetDays'];
        if ($duration === 14) {
            $message = sprintf(
                'Durée de login à 14 jours (défaut WordPress). La cible est %d jours.',
                $targetDays
            );
        } elseif ($duration !== null) {
            $message = sprintf(
                'Durée de login à %d jours. La cible est %d jours.',
                (int) $duration,
                $targetDays
            );
        } else {
            $message = sprintf(
                'La durée de login n\'est pas limitée via Defender Hardening (cible : %d jours).',
                $targetDays
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'warn',
            'warning',
            $message,
            true,
            'security.defender_enable_login_duration',
            $state
        );
    }
}
