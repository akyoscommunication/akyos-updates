<?php

namespace AkyosUpdates\Core\Checks\Plugins;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;

final class AuthJsonRootCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'plugins.auth_json_root';
    }

    public function getCategory(): string
    {
        return 'Plugins';
    }

    public function getTitle(): string
    {
        return 'Présence auth.json';
    }
    public function getSuccessMessage(): string
    {
        return 'Point validé après action corrective.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $authPath = $context->getProjectRootPath() . '/auth.json';
        $exists = file_exists($authPath);

        if ($exists) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'ok',
                'success',
                'auth.json trouvé à la racine projet.',
                false,
                null,
                ['path' => $authPath]
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'fail',
            'high',
            'auth.json absent à la racine projet.',
            false,
            null,
            ['path' => $authPath]
        );
    }
}
