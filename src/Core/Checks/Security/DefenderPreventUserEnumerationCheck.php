<?php

namespace AkyosUpdates\Core\Checks\Security;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\DefenderService;

final class DefenderPreventUserEnumerationCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'security.defender_prevent_user_enumeration';
    }

    public function getCategory(): string
    {
        return 'Sécurité';
    }

    public function getTitle(): string
    {
        return 'Defender — Énumération utilisateurs';
    }

    public function getSuccessMessage(): string
    {
        return 'Protection contre l\'énumération utilisateurs active.';
    }

    public function run(SiteContext $context): CheckResult
    {
        $state = DefenderService::getPreventUserEnumerationState();
        $canUse = DefenderService::isPluginActive() && DefenderService::canUseHardeningApi();

        if (! $canUse) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Énumération utilisateurs non vérifiable tant que Defender n\'est pas actif.',
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
                'Toutes les protections anti-énumération sont actives.',
                false,
                null,
                $state
            );
        }

        $missing = array_values(array_filter([
            ! $state['restApi'] ? 'REST API utilisateurs' : null,
            ! $state['oembed'] ? 'oEmbed auteur' : null,
            ! $state['authorSitemap'] ? 'Sitemap auteurs' : null,
        ]));

        $message = sprintf(
            '%d protection(s) manquante(s): %s.',
            count($missing),
            implode(', ', $missing)
        );

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'warn',
            'warning',
            $message,
            true,
            'security.defender_enable_prevent_user_enumeration',
            array_merge($state, ['missing' => $missing])
        );
    }
}
