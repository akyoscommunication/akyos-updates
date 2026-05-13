<?php

namespace AkyosUpdates;

final class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register([self::class, 'autoload']);
    }

    public static function autoload(string $class): void
    {
        $prefix = __NAMESPACE__ . '\\';
        if (strpos($class, $prefix) !== 0) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
        $file = AKYOS_UPDATES_PLUGIN_DIR . 'src/' . $relativePath;

        if (file_exists($file)) {
            require_once $file;
            return;
        }

        if (str_starts_with($relative, 'Core\\Actions\\')) {
            $basename = basename($relativePath);
            $candidates = glob(AKYOS_UPDATES_PLUGIN_DIR . 'src/Core/Actions/*/' . $basename);
            if (is_array($candidates) && $candidates !== [] && is_readable($candidates[0])) {
                require_once $candidates[0];
            }
        }
    }
}
