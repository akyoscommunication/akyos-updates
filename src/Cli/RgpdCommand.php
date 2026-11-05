<?php

namespace AkyosUpdates\Cli;

use AkyosUpdates\Core\Checks\Rgpd\RgpdPluginCheck;
use AkyosUpdates\Service\RgpdSettingsService;
use AkyosUpdates\Service\RgpdThemeStyleDetector;

/**
 * Module RGPD d'akyos-updates en ligne de commande : remplace aky-gdpr sans passer par l'admin.
 */
final class RgpdCommand
{
    public function __construct(
        private RgpdSettingsService $settings,
        private RgpdThemeStyleDetector $styleDetector
    ) {
    }

    /**
     * Migre aky-gdpr vers le module RGPD d'akyos-updates, puis cale le style du bandeau sur le thème et applique les
     * correctifs RGPD. Même migration que le bouton « Migrer aky-gdpr » de l'onglet RGPD.
     *
     * Étapes : 1. reprise des réglages aky-gdpr (options en base) ; 2. style du bandeau tarteaucitron
     * (« Personnaliser le bandeau ») d'après les couleurs et la police du thème ; 3. désactivation d'aky-gdpr (double
     * bannière sinon), seulement si la migration a réussi ; 4. `analyze --categories=RGPD --fix`.
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Affiche ce qui serait fait, sans rien écrire.
     *
     * [--skip-style]
     * : Ne touche pas au style du bandeau.
     *
     * [--skip-fix]
     * : Ne lance pas les correctifs RGPD.
     *
     * [--delete]
     * : Supprime aussi les fichiers d'aky-gdpr (sur Bedrock, le retirer plutôt de Composer ou du dépôt).
     *
     * ## EXAMPLES
     *
     *     wp akyos-updates rgpd migrate
     *     wp akyos-updates rgpd migrate --dry-run
     *
     * @when after_wp_load
     */
    public function migrate(array $args, array $assocArgs): void
    {
        $dryRun = isset($assocArgs['dry-run']);
        $legacy = get_option(RgpdSettingsService::LEGACY_OPTION_KEY, []);
        $legacyPlugins = $this->legacyPlugins();
        $hasLegacy = (is_array($legacy) && $legacy !== []) || $legacyPlugins !== [];

        // 1. Réglages aky-gdpr.
        if (!$hasLegacy) {
            \WP_CLI::log('aky-gdpr : ni réglages ni extension, rien à migrer.');
        } elseif ($dryRun) {
            \WP_CLI::log(sprintf('aky-gdpr : réglages %s, extension(s) %s — migration à faire.', is_array($legacy) && $legacy !== [] ? 'présents' : 'absents', $legacyPlugins === [] ? 'aucune' : implode(', ', $legacyPlugins)));
        } else {
            $result = $this->settings->migrateFromLegacy();
            if ($result['migrated'] && !$this->settings->isActiveOnFrontend()) {
                \WP_CLI::error('Migration faite mais le bandeau akyos-updates n\'est pas actif (service de consentement manquant) : aky-gdpr laissé actif. Compléter l\'onglet RGPD puis relancer.');
            }
            \WP_CLI::log($result['migrated']
                ? sprintf('aky-gdpr : %d champ(s) repris (%s).', count($result['importedFields']), implode(', ', $result['importedFields']))
                : 'aky-gdpr : aucun réglage en base, rien de repris.');
        }

        // 2. Style du bandeau.
        if (!isset($assocArgs['skip-style'])) {
            // Simulation : le service est celui qu'apporterait la migration d'aky-gdpr.
            $serviceType = (string) ($this->settings->get()['service_type'] ?? '');
            if ($dryRun && is_array($legacy) && 'service_tarteaucitron' === ($legacy['rgpd_service_type'] ?? null)) {
                $serviceType = RgpdSettingsService::SERVICE_TARTEAUCITRON;
            }
            $this->applyStyle($dryRun, $serviceType);
        }

        // 3. Désactivation d'aky-gdpr, une fois le bandeau akyos-updates actif.
        if ($legacyPlugins !== []) {
            if ($dryRun) {
                \WP_CLI::log('aky-gdpr : serait désactivé' . (isset($assocArgs['delete']) ? ' et supprimé' : '') . '.');
            } elseif ($this->settings->isActiveOnFrontend()) {
                if (!function_exists('deactivate_plugins')) {
                    require_once ABSPATH . 'wp-admin/includes/plugin.php';
                }
                deactivate_plugins($legacyPlugins);
                \WP_CLI::log('aky-gdpr : désactivé (' . implode(', ', $legacyPlugins) . ').');
                if (isset($assocArgs['delete'])) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                    $deleted = delete_plugins($legacyPlugins);
                    \WP_CLI::log(true === $deleted ? 'aky-gdpr : supprimé.' : 'aky-gdpr : suppression impossible (Bedrock : le retirer de Composer ou du dépôt).');
                }
            } else {
                \WP_CLI::log('ATTENTION : bandeau akyos-updates inactif, aky-gdpr laissé actif.');
            }
        }

        // 4. Correctifs RGPD.
        if (!isset($assocArgs['skip-fix'])) {
            \WP_CLI::runcommand('akyos-updates analyze --categories=RGPD --fix' . ($dryRun ? ' --dry-run' : ''), ['launch' => false, 'exit_error' => false]);
        }

        \WP_CLI::success($dryRun ? 'Simulation terminée : le site n\'a pas été modifié.' : 'Module RGPD akyos-updates en place.');
    }

    private function applyStyle(bool $dryRun, string $serviceType): void
    {
        $current = $this->settings->get();
        if (RgpdSettingsService::SERVICE_TARTEAUCITRON !== $serviceType) {
            \WP_CLI::log('Style : bandeau autre que tarteaucitron, inchangé.');

            return;
        }
        $patch = $this->styleDetector->detect();
        if (!isset($patch['theme_primary'])) {
            // log et pas warning : WP Toolkit (Plesk) ne renvoie pas la sortie d'erreur de WP-CLI.
            \WP_CLI::log('Style : ATTENTION, aucune couleur trouvée dans le thème, le bandeau garde le bleu Akyos (#0052FF). À personnaliser dans l\'onglet RGPD.');

            return;
        }
        $line = implode(', ', array_map(static fn (string $key, string $value): string => $key . '=' . $value, array_keys($patch), $patch));
        if ($dryRun) {
            \WP_CLI::log('Style : serait appliqué (' . $line . ').');

            return;
        }
        $this->settings->save(array_merge($current, $patch));
        \WP_CLI::log('Style : appliqué (' . $line . ').');
    }

    /** @return list<string> fichiers des extensions aky-gdpr actives */
    private function legacyPlugins(): array
    {
        return array_values(array_map(
            static fn (array $row): string => $row['file'],
            array_filter(RgpdPluginCheck::collectMatchingPlugins(), static fn (array $row): bool => str_contains(strtolower($row['slug'] . ' ' . $row['label']), 'aky-gdpr'))
        ));
    }
}
