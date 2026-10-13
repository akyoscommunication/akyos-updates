<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\WordpressService;
use Automatic_Upgrader_Skin;
use Language_Pack_Upgrader;
use WP_Error;

final class UpdateTranslationsAction implements ActionInterface
{
    public function getId(): string
    {
        return 'wordpress.update_translations';
    }

    public function run(array $payload = []): ActionResult
    {
        if (! current_user_can('update_languages')) {
            return ActionResult::failure(
                'Droits insuffisants pour mettre à jour les traductions.',
                [],
                'akyos_updates_forbidden'
            );
        }

        $updates = WordpressService::getPendingTranslationUpdates();
        if ($updates === []) {
            return ActionResult::success('Aucune traduction en attente.', [
                'updatedCount' => 0,
                'locale' => (string) get_locale(),
            ]);
        }

        if (! class_exists(Language_Pack_Upgrader::class)) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once ABSPATH . 'wp-admin/includes/class-language-pack-upgrader.php';
        }
        if (! class_exists(Automatic_Upgrader_Skin::class)) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';
            require_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';
        }

        $pendingCount = count($updates);
        $upgrader = new Language_Pack_Upgrader(new Automatic_Upgrader_Skin());
        $result = $upgrader->bulk_upgrade($updates);

        if ($result instanceof WP_Error) {
            return ActionResult::failure($result->get_error_message(), [
                'locale' => (string) get_locale(),
            ], $result->get_error_code());
        }

        if ($result === false) {
            return ActionResult::failure(
                'Échec de la mise à jour des traductions (accès fichiers ou connexion WordPress.org).',
                ['locale' => (string) get_locale()]
            );
        }

        WordpressService::refreshTranslationUpdateOffers();
        $remaining = WordpressService::getPendingTranslationUpdates();
        $updatedCount = max(0, $pendingCount - count($remaining));

        if ($remaining !== []) {
            return ActionResult::failure(
                sprintf(
                    '%d traduction(s) mise(s) à jour, %d encore en attente.',
                    $updatedCount,
                    count($remaining)
                ),
                [
                    'updatedCount' => $updatedCount,
                    'pendingCount' => count($remaining),
                    'locale' => (string) get_locale(),
                ]
            );
        }

        return ActionResult::success(
            sprintf(
                '%d traduction(s) mise(s) à jour pour %s.',
                $updatedCount > 0 ? $updatedCount : $pendingCount,
                get_locale()
            ),
            [
                'updatedCount' => $updatedCount > 0 ? $updatedCount : $pendingCount,
                'locale' => (string) get_locale(),
            ]
        );
    }
}
