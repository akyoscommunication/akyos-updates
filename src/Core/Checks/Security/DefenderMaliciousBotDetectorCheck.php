<?php

namespace AkyosUpdates\Core\Checks\Security;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\DefenderService;

final class DefenderMaliciousBotDetectorCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'security.defender_malicious_bot_detector';
    }

    public function getCategory(): string
    {
        return 'Sécurité';
    }

    public function getTitle(): string
    {
        return 'Defender — Détection bots malveillants';
    }

    public function getSuccessMessage(): string
    {
        return 'Détection bots malveillants active.';
    }

    public function run(SiteContext $context): CheckResult
    {
        $state = DefenderService::getMaliciousBotDetectorState();
        $canUse = DefenderService::isPluginActive() && function_exists('wd_di')
            && class_exists('\WP_Defender\Model\Setting\User_Agent_Lockout');

        if (! $canUse) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Détection bots non vérifiable tant que Defender n\'est pas actif.',
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
                'Toutes les protections anti-bots sont actives.',
                false,
                null,
                $state
            );
        }

        $missing = array_values(array_filter([
            ! $state['trapRobotsTxt'] ? 'Piège robots.txt' : null,
            ! $state['catchFakeBots'] ? 'Faux bots' : null,
            ! $state['emptyHeaders'] ? 'Headers vides' : null,
        ]));

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'warn',
            'warning',
            sprintf('%d protection(s) manquante(s): %s.', count($missing), implode(', ', $missing)),
            true,
            'security.defender_enable_malicious_bot_detector',
            array_merge($state, ['missing' => $missing])
        );
    }
}
