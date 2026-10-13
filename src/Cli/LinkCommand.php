<?php

namespace AkyosUpdates\Cli;

use AkyosUpdates\Service\LinkSettingsService;
use AkyosUpdates\Service\MawRegistrationService;

/**
 * Liaison à MAW en ligne de commande (même effet que « Enregistrer et lier » dans l'admin) :
 * permet à MAW de lier automatiquement un site par SSH.
 */
final class LinkCommand
{
    public function __construct(
        private LinkSettingsService $link,
        private MawRegistrationService $registration
    ) {
    }

    /**
     * Lie le site à MAW avec une clé de liaison générée dans MAW.
     *
     * ## OPTIONS
     *
     * <pairing-key>
     * : Clé de liaison à usage unique émise par MAW.
     *
     * [--maw-url=<url>]
     * : URL de base de MAW, si elle n'est pas définie par AKYOS_UPDATES_MAW_URL.
     *
     * ## EXAMPLES
     *
     *     wp akyos-updates link MAW-1234abcd --maw-url=https://maw.akyos.com
     *
     * @param list<string>          $args
     * @param array<string, string> $assoc
     */
    public function __invoke(array $args, array $assoc): void
    {
        if (isset($assoc['maw-url']) && '' !== $assoc['maw-url']) {
            $mawUrl = rtrim($assoc['maw-url'], '/');
            add_filter('akyos_updates_maw_url', static fn (): string => $mawUrl);
        }

        $this->link->save(['pairing_key' => (string) ($args[0] ?? '')]);
        $result = $this->registration->register();

        if (true !== ($result['success'] ?? false)) {
            \WP_CLI::error('Liaison à MAW refusée : ' . (string) ($result['message'] ?? 'erreur inconnue'));
        }
        \WP_CLI::success((string) ($result['message'] ?? 'Site lié à MAW.'));
    }
}
