<?php

namespace AkyosUpdates\Core\Checks\Performance;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\HummingbirdService;

final class HummingbirdPageCacheCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'performance.hummingbird_page_cache';
    }

    public function getCategory(): string
    {
        return 'Performance';
    }

    public function getTitle(): string
    {
        return 'Hummingbird Page Caching';
    }
    public function getSuccessMessage(): string
    {
        return 'Configuration Hummingbird Page Caching mise à jour.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $state = HummingbirdService::getPageCacheState();

        if (!($state['available'] ?? false)) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Hummingbird non disponible.',
                true,
                'performance.hummingbird_apply_page_cache_config',
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
                'Module Page Caching Hummingbird indisponible.',
                true,
                'performance.hummingbird_apply_page_cache_config',
                []
            );
        }

        $missing = is_array($state['missing'] ?? null) ? $state['missing'] : [];

        if ($missing === []) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'ok',
                'success',
                'Configuration Page Caching Hummingbird conforme.',
                false,
                null,
                [
                    'pageCachingEnabled' => (bool) ($state['pageCachingEnabled'] ?? false),
                    'preloadEnabled' => (bool) ($state['preloadEnabled'] ?? false),
                    'varnishEnabled' => (bool) ($state['varnishEnabled'] ?? false),
                    'clearIntervalEnabled' => (bool) ($state['clearIntervalEnabled'] ?? false),
                    'clearOnUpdateEnabled' => (bool) ($state['clearOnUpdateEnabled'] ?? false),
                    'missing' => [],
                ]
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'warn',
            'warning',
            sprintf('%d réglage(s) Hummingbird à activer: %s.', count($missing), implode(', ', $missing)),
            true,
            'performance.hummingbird_apply_page_cache_config',
            [
                'pageCachingEnabled' => (bool) ($state['pageCachingEnabled'] ?? false),
                'preloadEnabled' => (bool) ($state['preloadEnabled'] ?? false),
                'varnishEnabled' => (bool) ($state['varnishEnabled'] ?? false),
                'clearIntervalEnabled' => (bool) ($state['clearIntervalEnabled'] ?? false),
                'clearOnUpdateEnabled' => (bool) ($state['clearOnUpdateEnabled'] ?? false),
                'missing' => $missing,
            ]
        );
    }
}
