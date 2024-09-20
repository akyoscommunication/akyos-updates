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

    public function getComposerConfiguration(): array
    {
        $composerJson = file_exists(ABSPATH . 'composer.json');
        $authJson = file_exists(ABSPATH . 'auth.json');

        $composerMessage = '';

        if ($composerJson) {
            $composerMessage = "✅ Le fichier composer.json est présent à la racine du site <br>";
        } else {
            $composerMessage = "⭕ Le fichier composer.json n'est pas présent à la racine du site <br>";
        }

        if ($authJson) {
            $composerMessage .= "✅ Le fichier auth.json est présent à la racine du site";
        } else {
            $composerMessage .= "⭕ Le fichier auth.json n'est pas présent à la racine du site";
        }

        return [
            'message' => $composerMessage,
            'action_required' => !($composerJson && $authJson)
        ];
    }

    #[Hook(hook: 'admin_post_akyos_updates_add_composer_and_auth')]
    public function addComposerAndAuth(): bool
    {
        $composerJson = file_exists(ABSPATH . 'composer.json');
        $authJson = file_exists(ABSPATH . 'auth.json');

        if(!$composerJson) {
            $copyComposer = copy(plugin_dir_path(__DIR__) . '../defaultFiles/default_composer.txt', ABSPATH . 'composer.json');
        }

        if(!$authJson) {
            copy(plugin_dir_path(__DIR__) . '../defaultFiles/default_auth.txt', ABSPATH . 'auth.json');
        }

        return wp_redirect(admin_url('admin.php?page=akyos_updates_hosting'));
    }
}