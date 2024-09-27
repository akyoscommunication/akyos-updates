<?php

namespace AkyosUpdates\Service;

use AkyosUpdates\Attribute\Hook;

class HostingService
{
    public function getPhpVersion(): string
    {
        $phpVersion = phpversion();

        if (version_compare($phpVersion, '8.3.0', '<=')) {
            $phpVersionMessage = "⭕ La version de PHP n'est pas à jour : $phpVersion";
        } else {
            $phpVersionMessage = "✅ La version de PHP est à jour : $phpVersion";
        }

        return $phpVersionMessage;
    }
}