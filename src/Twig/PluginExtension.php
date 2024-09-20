<?php

namespace AkyosUpdates\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class PluginExtension extends AbstractExtension implements GlobalsInterface
{
    public function getGlobals(): array
    {
        return [
            'admin_url' => admin_url('admin-post.php'),
        ];
    }
}