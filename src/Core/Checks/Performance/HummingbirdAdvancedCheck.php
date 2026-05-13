<?php

namespace AkyosUpdates\Core\Checks\Performance;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\HummingbirdService;

final class HummingbirdAdvancedCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'performance.hummingbird_advanced';
    }

    public function getCategory(): string
    {
        return 'Performance';
    }

    public function getTitle(): string
    {
        return 'Hummingbird Advanced (assets, emoji, prefetch, preconnect)';
    }
    public function getSuccessMessage(): string
    {
        return 'Réglages Advanced Hummingbird mis à jour.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $state = HummingbirdService::getAdvancedState();

        if (!($state['available'] ?? false)) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Hummingbird non disponible.',
                true,
                'performance.hummingbird_apply_advanced_config',
                []
            );
        }

        if (!($state['moduleAvailable'] ?? false)) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Module Advanced Hummingbird indisponible.',
                true,
                'performance.hummingbird_apply_advanced_config',
                []
            );
        }

        $queryStringEnabled = (bool) ($state['queryStringEnabled'] ?? false);
        $emojiEnabled = (bool) ($state['emojiEnabled'] ?? false);
        $prefetchMissing = is_array($state['prefetchMissing'] ?? null) ? $state['prefetchMissing'] : [];
        $preconnectMissing = is_array($state['preconnectMissing'] ?? null) ? $state['preconnectMissing'] : [];

        $payload = [
            'queryStringEnabled' => $queryStringEnabled,
            'emojiEnabled' => $emojiEnabled,
            'prefetchMissing' => $prefetchMissing,
            'prefetchUrls' => is_array($state['prefetchUrls'] ?? null) ? $state['prefetchUrls'] : [],
            'preconnectMissing' => $preconnectMissing,
            'preconnectUrls' => is_array($state['preconnectUrls'] ?? null) ? $state['preconnectUrls'] : [],
        ];

        if ($queryStringEnabled && $emojiEnabled && $prefetchMissing === [] && $preconnectMissing === []) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'ok',
                'success',
                'Réglages Advanced Hummingbird conformes (query strings, emoji, prefetch, preconnect).',
                false,
                null,
                $payload
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'warn',
            'warning',
            'Réglages Advanced Hummingbird à aligner.',
            true,
            'performance.hummingbird_apply_advanced_config',
            $payload
        );
    }
}
