<?php

namespace AkyosUpdates\Core\Checks\Plugins;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;

final class AuthJsonGitignoreCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'plugins.auth_json_gitignore';
    }

    public function getCategory(): string
    {
        return 'Plugins';
    }

    public function getTitle(): string
    {
        return 'auth.json dans .gitignore';
    }

    public function getSuccessMessage(): string
    {
        return 'auth.json est bien ignoré par Git.';
    }

    public function run(SiteContext $context): CheckResult
    {
        $gitignorePath = $context->getProjectRootPath() . '/.gitignore';

        if (! is_file($gitignorePath)) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'fail',
                'high',
                '.gitignore absent à la racine projet.',
                false,
                null,
                ['path' => $gitignorePath]
            );
        }

        if (self::gitignoreIgnoresAuthJson($gitignorePath)) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'ok',
                'success',
                'auth.json est présent dans .gitignore.',
                false,
                null,
                ['path' => $gitignorePath]
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'fail',
            'high',
            'auth.json absent de .gitignore : risque d’exposition des clés API dans Git.',
            false,
            null,
            ['path' => $gitignorePath, 'suggestedLine' => 'auth.json']
        );
    }

    private static function gitignoreIgnoresAuthJson(string $gitignorePath): bool
    {
        $content = file_get_contents($gitignorePath);
        if ($content === false) {
            return false;
        }

        foreach (preg_split('/\R/', $content) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_starts_with($line, '!')) {
                continue;
            }

            // ponytail: motifs courants uniquement, pas le moteur gitignore complet
            if (in_array($line, ['auth.json', '/auth.json', '**/auth.json'], true)) {
                return true;
            }
        }

        return false;
    }
}
