<?php

namespace AkyosUpdates\Core\Checks\Images;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\PluginService;
use AkyosUpdates\Service\SmushService;

final class SmushVersionUpgradeCheck implements CheckInterface
{
    private const SMUSH_FILES = [
        'wp-smushit/wp-smush.php',
        'wp-smush-pro/wp-smush.php',
    ];

    public function getId(): string
    {
        return 'images.smush_version_requirement';
    }

    public function getCategory(): string
    {
        return 'Images';
    }

    public function getTitle(): string
    {
        return 'Smush 4 requis (audit Images)';
    }

    public function getSuccessMessage(): string
    {
        return 'Point validé après action corrective.';
    }

    public function run(SiteContext $context): CheckResult
    {
        if (SmushService::isSmushV4AnalysisSupported()) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'ok',
                'success',
                'Smush 4 ou supérieur est actif ; l’audit détaillé Images est disponible.',
                false,
                null,
                [
                    'installed' => true,
                    'active' => true,
                    'meetsV4' => true,
                    'detectedVersion' => SmushService::getInstalledSmushVersion(),
                ],
                true
            );
        }

        $installed = PluginService::resolveInstalledPluginFile(self::SMUSH_FILES) !== null;
        $active = SmushService::isPluginActive();
        $v = trim(SmushService::getInstalledSmushVersion());
        $label = $v !== '' ? $v : 'inconnue';

        if (!$installed) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'fail',
                'high',
                'Smush n’est pas installé. Installer Smush (free ou Pro) en version 4 ou supérieur et l’activer pour l’audit Images.',
                false,
                null,
                [
                    'installed' => false,
                    'active' => false,
                    'meetsV4' => false,
                    'detectedVersion' => '',
                ],
                true
            );
        }

        if (!$active) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'fail',
                'high',
                'Smush est installé mais inactif. Activer le plugin (version 4 ou supérieur requise pour l’audit détaillé Images).',
                false,
                null,
                [
                    'installed' => true,
                    'active' => false,
                    'meetsV4' => false,
                    'detectedVersion' => $v,
                ],
                true
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'fail',
            'high',
            'Smush ' . $label . ' : l’audit détaillé Images (stats, réglages, bulk) nécessite Smush 4 ou supérieur. Mettre à jour le plugin depuis le tableau de bord des extensions.',
            false,
            null,
            [
                'installed' => true,
                'active' => true,
                'meetsV4' => false,
                'detectedVersion' => $v,
                'requiredMajor' => 4,
            ],
            true
        );
    }
}
