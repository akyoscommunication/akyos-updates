<?php

namespace AkyosUpdates\Core\Checks\Plugins;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;

final class ComposerRootCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'plugins.composer_root';
    }

    public function getCategory(): string
    {
        return 'Plugins';
    }

    public function getTitle(): string
    {
        return 'Présence composer.json';
    }
    public function getSuccessMessage(): string
    {
        return 'Point validé après action corrective.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $composerPath = $context->getProjectRootPath() . '/composer.json';
        $exists = is_file($composerPath);

        if ($exists) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'ok',
                'success',
                'composer.json trouvé à la racine projet.',
                false,
                null,
                ['path' => $composerPath]
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'fail',
            'high',
            'composer.json absent à la racine projet.',
            true,
            'plugins.generate_composer_json',
            ['path' => $composerPath, 'installationType' => $context->getInstallationType()]
        );
    }
}
