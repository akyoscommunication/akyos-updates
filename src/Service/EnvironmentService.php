<?php

namespace AkyosUpdates\Service;

final class EnvironmentService
{
    public static function getWpEnv(?string $projectRootPath = null): string
    {
        if (defined('WP_ENV') && is_string(WP_ENV) && WP_ENV !== '') {
            return WP_ENV;
        }

        $root = $projectRootPath ?? self::detectProjectRootPath();
        if ($root !== '') {
            $fromFile = self::readDotEnvVar($root, 'WP_ENV');
            if ($fromFile !== null && $fromFile !== '') {
                return $fromFile;
            }
        }

        return 'unknown';
    }

    public static function isProduction(?string $projectRootPath = null): bool
    {
        return self::getWpEnv($projectRootPath) === 'production';
    }

    public static function isNonProduction(?string $projectRootPath = null): bool
    {
        return ! self::isProduction($projectRootPath);
    }

    public static function getEnvKind(?string $projectRootPath = null): string
    {
        $env = self::getWpEnv($projectRootPath);

        return match ($env) {
            'production' => 'production',
            'development', 'local' => 'development',
            'staging' => 'staging',
            default => 'other',
        };
    }

    public static function isIndexingForcedOff(): bool
    {
        return defined('DISALLOW_INDEXING') && DISALLOW_INDEXING;
    }

    /**
     * @return array{
     *     disabled: bool,
     *     reason: ?string,
     *     hint: string
     * }
     */
    public static function resolveIndexingSwitch(?string $projectRootPath = null): array
    {
        if (self::isIndexingForcedOff()) {
            return [
                'disabled' => true,
                'reason' => 'bedrock',
                'hint' => 'Indexation verrouillée par DISALLOW_INDEXING (Bedrock).',
            ];
        }

        return [
            'disabled' => false,
            'reason' => null,
            'hint' => '',
        ];
    }

    public static function isSitePubliclyIndexed(): bool
    {
        if (self::isIndexingForcedOff()) {
            return false;
        }

        return (string) get_option('blog_public', '1') === '1';
    }

    public static function setSitePubliclyIndexed(bool $indexed): bool
    {
        if (self::isIndexingForcedOff()) {
            return false;
        }

        return (bool) update_option('blog_public', $indexed ? '1' : '0', true);
    }

    public static function detectProjectRootPath(): string
    {
        $abspath = untrailingslashit(ABSPATH);
        $bedrockWpRoot = str_ends_with($abspath, '/web/wp') || str_ends_with($abspath, '\\web\\wp');
        $bedrockConfigPresent = file_exists(dirname($abspath, 2) . '/config/application.php');

        if ($bedrockWpRoot || $bedrockConfigPresent) {
            return dirname($abspath, 2);
        }

        return $abspath;
    }

    private static function readDotEnvVar(string $projectRoot, string $key): ?string
    {
        $path = rtrim($projectRoot, '/\\') . '/.env';
        if (! is_readable($path)) {
            return null;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return null;
        }

        $pattern = '/^\s*' . preg_quote($key, '/') . '\s*=\s*(?:[\'"]([^\'"]*)[\'"]|([^#\s]+))/m';
        if (! preg_match($pattern, $content, $matches)) {
            return null;
        }

        $value = trim($matches[1] !== '' ? $matches[1] : $matches[2]);

        return $value !== '' ? $value : null;
    }
}
