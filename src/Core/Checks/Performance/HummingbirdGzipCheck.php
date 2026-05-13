<?php

namespace AkyosUpdates\Core\Checks\Performance;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\HummingbirdService;

final class HummingbirdGzipCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'performance.hummingbird_gzip';
    }

    public function getCategory(): string
    {
        return 'Performance';
    }

    public function getTitle(): string
    {
        return 'Hummingbird Gzip';
    }
    public function getSuccessMessage(): string
    {
        return 'Configuration Hummingbird Gzip mise à jour.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $state = HummingbirdService::getGzipState();

        if (!($state['available'] ?? false)) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Hummingbird non disponible.',
                true,
                'performance.hummingbird_enable_gzip',
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
                'Module Gzip Hummingbird indisponible.',
                true,
                'performance.hummingbird_enable_gzip',
                []
            );
        }

        $gzipActive = (bool) ($state['gzipActive'] ?? false);
        $compressionType = (string) ($state['compressionType'] ?? '');

        if (! $gzipActive) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Compression Gzip Hummingbird inactive.',
                true,
                'performance.hummingbird_enable_gzip',
                [
                    'gzipActive' => false,
                    'compressionType' => $compressionType,
                ]
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'ok',
            'success',
            'Compression Gzip Hummingbird active.',
            false,
            null,
            [
                'gzipActive' => true,
                'compressionType' => $compressionType,
            ]
        );
    }
}
