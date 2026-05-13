<?php

namespace AkyosUpdates\Core\Context;

final class SiteContext
{
    public function __construct(
        private string $installationType,
        private string $projectRootPath,
        private string $wordpressRootPath,
        private string $pluginsPath,
        private string $wordpressVersion,
        private string $phpVersion,
        private string $activeTheme
    ) {
    }

    public function getInstallationType(): string
    {
        return $this->installationType;
    }

    public function getProjectRootPath(): string
    {
        return $this->projectRootPath;
    }

    public function getWordpressRootPath(): string
    {
        return $this->wordpressRootPath;
    }

    public function getPluginsPath(): string
    {
        return $this->pluginsPath;
    }

    public function getWordpressVersion(): string
    {
        return $this->wordpressVersion;
    }

    public function getPhpVersion(): string
    {
        return $this->phpVersion;
    }

    public function getActiveTheme(): string
    {
        return $this->activeTheme;
    }

    public function toArray(): array
    {
        return [
            'installationType' => $this->installationType,
            'projectRootPath' => $this->projectRootPath,
            'wordpressRootPath' => $this->wordpressRootPath,
            'pluginsPath' => $this->pluginsPath,
            'wordpressVersion' => $this->wordpressVersion,
            'phpVersion' => $this->phpVersion,
            'activeTheme' => $this->activeTheme,
        ];
    }
}
