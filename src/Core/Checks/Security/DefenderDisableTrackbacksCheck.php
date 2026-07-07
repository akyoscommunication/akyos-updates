<?php

namespace AkyosUpdates\Core\Checks\Security;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\DefenderService;

final class DefenderDisableTrackbacksCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'security.defender_disable_trackbacks';
    }

    public function getCategory(): string
    {
        return 'Sécurité';
    }

    public function getTitle(): string
    {
        return 'Defender — Trackbacks / pingbacks';
    }

    public function getSuccessMessage(): string
    {
        return 'Trackbacks et pingbacks désactivés.';
    }

    public function run(SiteContext $context): CheckResult
    {
        $state = DefenderService::getDisableTrackbacksState();
        $canUse = DefenderService::isPluginActive() && DefenderService::canUseHardeningApi();

        if (! $canUse) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Trackbacks non vérifiables tant que Defender n\'est pas actif.',
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
                'Trackbacks et pingbacks sont désactivés.',
                false,
                null,
                $state
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'warn',
            'warning',
            'Trackbacks et pingbacks sont encore activés.',
            true,
            'security.defender_enable_disable_trackbacks',
            $state
        );
    }
}
