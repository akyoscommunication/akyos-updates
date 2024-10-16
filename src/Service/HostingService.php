<?php

namespace AkyosUpdates\Service;


class HostingService
{
    public function getPhpVersion(): string
    {
        $phpVersion = phpversion();

        if (version_compare($phpVersion, '8.3.0', '<=')) {
            $phpVersionMessage = "<p>⭕ La version de PHP n'est pas à jour : $phpVersion</p>";
        } else {
            $phpVersionMessage = "<p>✅ La version de PHP est à jour : $phpVersion</p>";
        }

        return $phpVersionMessage;
    }
}
