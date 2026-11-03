<?php

namespace AkyosUpdates\Service;

use AkyosUpdates\Core\Actions\ChangeWordPressVersionAction;
use AkyosUpdates\Core\Actions\UpdateTranslationsAction;
use Automatic_Upgrader_Skin;
use Plugin_Upgrader;
use Theme_Upgrader;
use WP_Error;

/**
 * Mise à jour du site pilotée par MAW (POST /crm/maintenance), sans shell : liste FERMÉE de commandes, un élément à la
 * fois. Chaque mise à jour d'extension ou de thème copie d'abord son dossier, puis appelle l'accueil du site en
 * interne : erreur fatale ou 500 après coup → l'ancienne version est remise avant de répondre. Un site cassé par une
 * mise à jour n'a donc jamais besoin d'un accès serveur pour être réparé.
 */
final class MaintenanceCommandService
{
    public const COMMANDS = ['updates', 'backup', 'update-core', 'update-plugin', 'update-theme', 'install-theme-package', 'update-translations', 'activate', 'deactivate', 'delete-redundant', 'cache-clear', 'login-pages', 'health'];

    /** Doublons de la stack WPMU (skill wp-maj, étape 0) : seules extensions supprimables. */
    private const REDUNDANT_PLUGINS = ['autoptimize', 'imsanity', 'webp-converter-for-media', 'wordfence', 'really-simple-ssl', 'wp-super-cache', 'w3-total-cache', 'litespeed-cache', 'imagify', 'shortpixel-image-optimiser', 'ewww-image-optimizer'];
    private const BACKUPS_KEPT = 3;

    /**
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     */
    public function run(string $command, array $args): array
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }
        if (! self::loadAdmin()) {
            return ['success' => false, 'message' => 'Accès direct aux fichiers impossible (WP_Filesystem demande des identifiants FTP) : mise à jour à faire à la main.'];
        }
        $slug = sanitize_key((string) ($args['slug'] ?? ''));

        return match ($command) {
            'updates' => $this->updates(),
            'backup' => $this->backup(),
            'update-core' => $this->updateCore(),
            'update-plugin' => $this->updatePlugin($slug),
            'update-theme' => $this->updateTheme($slug),
            'install-theme-package' => $this->installThemePackage((string) ($args['url'] ?? ''), $slug),
            'update-translations' => (new UpdateTranslationsAction())->run()->toArray(),
            'activate', 'deactivate' => $this->toggle($slug, 'activate' === $command),
            'delete-redundant' => $this->deleteRedundant($slug),
            'cache-clear' => $this->cacheClear(),
            'login-pages' => $this->loginPages(),
            'health' => ['success' => true] + self::health(),
            default => ['success' => false, 'message' => sprintf('Commande « %s » inconnue.', $command)],
        };
    }

    /** @return array<string, mixed> */
    private function updates(): array
    {
        wp_version_check();
        wp_update_plugins();
        wp_update_themes();
        $pluginUpdates = get_site_transient('update_plugins')->response ?? [];
        $themeUpdates = get_site_transient('update_themes')->response ?? [];
        $core = get_core_updates();

        $plugins = [];
        foreach (get_plugins() as $file => $data) {
            $plugins[] = [
                'slug' => self::pluginSlug($file),
                'name' => (string) $data['Name'],
                'version' => (string) $data['Version'],
                'newVersion' => isset($pluginUpdates[$file]) ? (string) ($pluginUpdates[$file]->new_version ?? '') : null,
                'active' => is_plugin_active($file),
            ];
        }
        $themes = [];
        foreach (wp_get_themes() as $stylesheet => $theme) {
            $themes[] = [
                'slug' => $stylesheet,
                'name' => (string) $theme->get('Name'),
                'version' => (string) $theme->get('Version'),
                'newVersion' => isset($themeUpdates[$stylesheet]['new_version']) ? (string) $themeUpdates[$stylesheet]['new_version'] : null,
                'active' => $stylesheet === get_stylesheet() || $stylesheet === get_template(),
            ];
        }

        return [
            'success' => true,
            'core' => ['version' => ChangeWordPressVersionAction::installedVersion(), 'latest' => is_array($core) && isset($core[0]->current) ? (string) $core[0]->current : null],
            'plugins' => $plugins,
            'themes' => $themes,
            'translations' => count(WordpressService::getPendingTranslationUpdates()),
        ];
    }

    /**
     * Export SQL des tables du site (gzip) dans un dossier fermé au web ; les trois derniers sont gardés.
     *
     * @return array<string, mixed>
     */
    private function backup(): array
    {
        global $wpdb;
        $dir = self::backupDir();
        $file = $dir . '/db-' . gmdate('Ymd-His') . '-' . wp_generate_password(12, false) . '.sql.gz';
        $gz = gzopen($file, 'wb6');
        if (false === $gz) {
            return ['success' => false, 'message' => 'Dossier de sauvegarde non inscriptible : ' . $dir];
        }
        $tables = $wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($wpdb->prefix) . '%'));
        foreach ($tables as $table) {
            $create = $wpdb->get_row("SHOW CREATE TABLE `{$table}`", ARRAY_N);
            if (! is_array($create)) {
                continue;
            }
            gzwrite($gz, "DROP TABLE IF EXISTS `{$table}`;\n{$create[1]};\n");
            for ($offset = 0; ; $offset += 500) {
                $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$table}` LIMIT %d OFFSET %d", 500, $offset), ARRAY_N);
                foreach ($rows as $row) {
                    $values = array_map(static fn ($value): string => null === $value ? 'NULL' : "'" . $wpdb->_real_escape((string) $value) . "'", $row);
                    gzwrite($gz, "INSERT INTO `{$table}` VALUES (" . implode(',', $values) . ");\n");
                }
                if (count($rows) < 500) {
                    break;
                }
            }
        }
        gzclose($gz);

        $all = glob($dir . '/db-*.sql.gz') ?: [];
        rsort($all);
        foreach (array_slice($all, self::BACKUPS_KEPT) as $old) {
            wp_delete_file($old);
        }

        return ['success' => true, 'message' => sprintf('Base sauvegardée : %d tables, %s.', count($tables), size_format((int) filesize($file))), 'file' => basename($file)];
    }

    /** @return array<string, mixed> */
    private function updateCore(): array
    {
        $core = get_core_updates();
        $latest = is_array($core) && isset($core[0]->current) ? (string) $core[0]->current : '';
        $installed = ChangeWordPressVersionAction::installedVersion();
        if ('' === $latest || version_compare($latest, $installed, '<=')) {
            return ['success' => true, 'message' => 'WordPress est déjà à jour (' . $installed . ').'];
        }
        $before = self::health();
        $result = (new ChangeWordPressVersionAction())->run(['targetVersion' => $latest])->toArray();
        // Base mise à jour par une requête neuve : celle-ci a encore l'ancienne version du cœur en mémoire.
        $database = wp_remote_get(admin_url('upgrade.php?step=1'), ['timeout' => 120, 'sslverify' => false]);
        // Le cœur n'a pas de retour arrière automatique : l'état du site est rendu pour que l'appelant le constate.
        return $result + [
            'databaseUpgrade' => $database instanceof WP_Error ? $database->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code($database),
            'healthBefore' => $before,
            'healthAfter' => self::health(),
        ];
    }

    /** @return array<string, mixed> */
    private function updatePlugin(string $slug): array
    {
        $file = self::pluginFile($slug);
        if (null === $file) {
            return ['success' => false, 'message' => sprintf('Extension « %s » introuvable.', $slug)];
        }
        if ('akyos-updates' === $slug) {
            return ['success' => false, 'message' => 'akyos-updates se met à jour lui-même (GitHub) : pas par cette commande.'];
        }
        $dir = WP_PLUGIN_DIR . '/' . dirname($file);
        $version = (string) (get_plugin_data(WP_PLUGIN_DIR . '/' . $file, false, false)['Version'] ?? '');

        return $this->guarded('.' === dirname($file) ? null : $dir, $slug, $version, static function () use ($file): mixed {
            wp_update_plugins();
            $result = (new Plugin_Upgrader(new Automatic_Upgrader_Skin()))->bulk_upgrade([$file]);

            return is_array($result) ? ($result[$file] ?? false) : $result;
        }, static fn (): string => (string) (get_plugin_data(WP_PLUGIN_DIR . '/' . $file, false, false)['Version'] ?? ''));
    }

    /** @return array<string, mixed> */
    private function updateTheme(string $slug): array
    {
        $theme = wp_get_theme($slug);
        if (! $theme->exists()) {
            return ['success' => false, 'message' => sprintf('Thème « %s » introuvable.', $slug)];
        }

        return $this->guarded($theme->get_stylesheet_directory(), $slug, (string) $theme->get('Version'), static function () use ($slug): mixed {
            wp_update_themes();
            $result = (new Theme_Upgrader(new Automatic_Upgrader_Skin()))->bulk_upgrade([$slug]);

            return is_array($result) ? ($result[$slug] ?? false) : $result;
        }, static fn (): string => (string) wp_get_theme($slug)->get('Version'));
    }

    /**
     * Thème premium (zip Envato transmis par MAW) installé par-dessus l'ancien, avec le même garde-fou.
     *
     * @return array<string, mixed>
     */
    private function installThemePackage(string $url, string $slug): array
    {
        if (! str_starts_with($url, 'https://') || '' === $slug) {
            return ['success' => false, 'message' => 'Lien https du zip et slug du thème requis.'];
        }
        $theme = wp_get_theme($slug);
        if (! $theme->exists()) {
            return ['success' => false, 'message' => sprintf('Thème « %s » introuvable : le zip ne remplace qu\'un thème installé.', $slug)];
        }

        return $this->guarded($theme->get_stylesheet_directory(), $slug, (string) $theme->get('Version'), static fn (): mixed => (new Theme_Upgrader(new Automatic_Upgrader_Skin()))->install($url, ['overwrite_package' => true]), static function () use ($slug): string {
            wp_clean_themes_cache();

            return (string) wp_get_theme($slug)->get('Version');
        });
    }

    /**
     * Mise à jour avec retour arrière : copie du dossier, mise à jour, appel de l'accueil ; si le site répondait avant
     * et casse après, le dossier d'origine est remis.
     *
     * @param callable(): mixed  $upgrade
     * @param callable(): string $currentVersion
     * @return array<string, mixed>
     */
    private function guarded(?string $dir, string $slug, string $before, callable $upgrade, callable $currentVersion): array
    {
        $healthBefore = self::health();
        $backup = null;
        if (null !== $dir && is_dir($dir)) {
            $backup = self::backupDir() . '/rollback-' . $slug . '-' . wp_generate_password(8, false);
            if (true !== copy_dir($dir, $backup)) {
                return ['success' => false, 'message' => sprintf('Copie de sécurité de « %s » impossible : mise à jour annulée.', $slug)];
            }
        }

        $result = $upgrade();
        $after = $currentVersion();
        if ($result instanceof WP_Error || false === $result || null === $result) {
            self::discard($backup);

            return ['success' => false, 'message' => $result instanceof WP_Error ? $result->get_error_message() : 'Mise à jour refusée (aucune version disponible, accès fichiers ou licence).', 'from' => $before, 'to' => $after];
        }

        $healthAfter = self::health();
        if (true === $healthBefore['ok'] && false === $healthAfter['ok'] && null !== $backup && null !== $dir) {
            self::removeDir($dir);
            rename($backup, $dir);
            // Sans ça, un opcache sans revalidation servirait encore la version cassée.
            wp_opcache_invalidate_directory($dir);
            wp_clean_plugins_cache();
            wp_clean_themes_cache();

            return ['success' => false, 'rolledBack' => true, 'message' => sprintf('« %s » %s → %s a cassé le site (%s) : version %s remise.', $slug, $before, $after, $healthAfter['detail'], $before), 'from' => $before, 'to' => $before, 'healthAfter' => self::health()];
        }
        self::discard($backup);

        return ['success' => true, 'message' => sprintf('« %s » %s → %s.', $slug, $before, $after), 'from' => $before, 'to' => $after, 'healthAfter' => $healthAfter];
    }

    /** @return array<string, mixed> */
    private function toggle(string $slug, bool $activate): array
    {
        $file = self::pluginFile($slug);
        if (null === $file || 'akyos-updates' === $slug) {
            return ['success' => false, 'message' => sprintf('Extension « %s » introuvable ou non modifiable.', $slug)];
        }
        if ($activate) {
            $result = activate_plugin($file);
            if ($result instanceof WP_Error) {
                return ['success' => false, 'message' => $result->get_error_message()];
            }
        } else {
            deactivate_plugins([$file]);
        }

        return ['success' => true, 'message' => sprintf('« %s » %s.', $slug, $activate ? 'activée' : 'désactivée'), 'health' => self::health()];
    }

    /** @return array<string, mixed> */
    private function deleteRedundant(string $slug): array
    {
        $file = self::pluginFile($slug);
        if (! in_array($slug, self::REDUNDANT_PLUGINS, true) || null === $file) {
            return ['success' => false, 'message' => 'Seuls les doublons de la stack WPMU installés peuvent être supprimés : ' . implode(', ', self::REDUNDANT_PLUGINS) . '.'];
        }
        deactivate_plugins([$file]);
        $result = delete_plugins([$file]);

        return $result instanceof WP_Error || true !== $result
            ? ['success' => false, 'message' => $result instanceof WP_Error ? $result->get_error_message() : 'Suppression impossible.']
            : ['success' => true, 'message' => sprintf('« %s » supprimée.', $slug)];
    }

    /** @return array<string, mixed> */
    private function cacheClear(): array
    {
        wp_cache_flush();
        $hummingbird = 'absent';
        if (class_exists('\Hummingbird\Core\Utils')) {
            $module = \Hummingbird\Core\Utils::get_module('page_cache');
            if ($module && method_exists($module, 'clear_cache')) {
                $module->clear_cache();
                $hummingbird = 'vidé';
            }
        }

        return ['success' => true, 'message' => 'Cache objet vidé, Page Cache Hummingbird : ' . $hummingbird . '.'];
    }

    /**
     * Pages de connexion / compte (skill wp-maj) : elles ne doivent jamais être servies par le Page Cache.
     *
     * @return array<string, mixed>
     */
    private function loginPages(): array
    {
        $pages = [];
        foreach (get_pages() as $page) {
            $template = (string) get_page_template_slug($page->ID);
            if (preg_match('/login|connexion|reset|account|checkout|mon-compte|mot-de-passe/i', $template . ' ' . $page->post_name)) {
                $pages[] = ['url' => (string) get_permalink($page->ID), 'template' => $template];
            }
        }

        return ['success' => true, 'pages' => $pages];
    }

    /**
     * Accueil du site appelé en interne, hors cache : null = impossible à vérifier (loopback bloqué).
     *
     * @return array{ok: ?bool, status: ?int, detail: string}
     */
    public static function health(): array
    {
        $response = wp_remote_get(add_query_arg('akyos_health', wp_generate_password(8, false), home_url('/')), [
            'timeout' => 30,
            'redirection' => 3,
            'sslverify' => false,
            'headers' => ['Cache-Control' => 'no-cache'],
        ]);
        if ($response instanceof WP_Error) {
            return ['ok' => null, 'status' => null, 'detail' => 'accueil injoignable en interne : ' . $response->get_error_message()];
        }
        $status = (int) wp_remote_retrieve_response_code($response);
        $fatal = 1 === preg_match('/Fatal error|Parse error|Uncaught |There has been a critical error|Il y a eu une erreur critique/i', (string) wp_remote_retrieve_body($response));

        return ['ok' => $status < 500 && ! $fatal, 'status' => $status, 'detail' => $fatal ? 'erreur PHP sur l\'accueil' : 'HTTP ' . $status];
    }

    private static function pluginFile(string $slug): ?string
    {
        foreach (array_keys(get_plugins()) as $file) {
            if (self::pluginSlug($file) === $slug) {
                return $file;
            }
        }

        return null;
    }

    private static function pluginSlug(string $file): string
    {
        return '.' === dirname($file) ? basename($file, '.php') : dirname($file);
    }

    /** Dossier des sauvegardes, fermé au web (Apache : .htaccess ; nginx : nom imprévisible des fichiers). */
    private static function backupDir(): string
    {
        $dir = WP_CONTENT_DIR . '/akyos-backups';
        if (! is_dir($dir)) {
            wp_mkdir_p($dir);
            file_put_contents($dir . '/.htaccess', "Require all denied\nDeny from all\n");
            file_put_contents($dir . '/index.php', "<?php // Silence.\n");
        }

        return $dir;
    }

    private static function discard(?string $backup): void
    {
        if (null !== $backup) {
            self::removeDir($backup);
        }
    }

    private static function removeDir(string $dir): void
    {
        global $wp_filesystem;
        $wp_filesystem->delete($dir, true);
    }

    private static function loadAdmin(): bool
    {
        foreach (['file.php', 'misc.php', 'plugin.php', 'theme.php', 'update.php', 'upgrade.php', 'class-wp-upgrader.php'] as $include) {
            require_once ABSPATH . 'wp-admin/includes/' . $include;
        }

        return true === WP_Filesystem() && 'direct' === get_filesystem_method();
    }
}
