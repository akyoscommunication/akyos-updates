<?php

namespace AkyosUpdates\Cli;

use AkyosUpdates\Core\Actions\GenerateGitignorePluginExceptionsAction;

/**
 * Ce que le rapport demande au dépôt Git du site (le plugin ne voit que le serveur : c'est la maintenance qui commite).
 * Exposé par `wp akyos-updates repo-plan` et dans `repository` de /crm/site-status (lu par MAW).
 */
final class RepoPlan
{
    /**
     * @param list<array<string, mixed>> $results checks du rapport
     * @param array<string, string> $versions slug => version installée
     * @return array{gitignore: list<string>, untrack: list<string>, composerRequire: list<array{slug: string, version: string}>, serverAuthJson: bool}
     */
    public static function fromResults(array $results, array $versions = []): array
    {
        $byId = [];
        foreach ($results as $result) {
            if (is_array($result) && isset($result['id'])) {
                $byId[(string) $result['id']] = $result;
            }
        }
        $failing = static fn (string $id): bool => isset($byId[$id]) && !in_array($byId[$id]['status'] ?? 'ok', ['ok', 'skipped'], true);

        $payload = is_array($byId['plugins.composer_audit']['payload'] ?? null) ? $byId['plugins.composer_audit']['payload'] : [];
        $type = (string) ($payload['installationType'] ?? '');
        $gitignore = $failing('plugins.auth_json_gitignore') ? ['auth.json'] : [];
        $composerRequire = [];
        if ($failing('plugins.composer_audit')) {
            foreach ((array) ($payload['missingGitignoreSlugs'] ?? []) as $slug) {
                $line = GenerateGitignorePluginExceptionsAction::lineForSlug((string) $slug, $type);
                if ($line !== '') {
                    $gitignore[] = $line;
                }
            }
            foreach ((array) ($payload['missingWpackagistSlugs'] ?? []) as $slug) {
                $composerRequire[] = ['slug' => (string) $slug, 'version' => (string) ($versions[(string) $slug] ?? '*')];
            }
        }

        return [
            'gitignore' => $gitignore,
            // Le serveur ne voit pas l'index Git : toujours vérifier dans le dépôt (git rm --cached --ignore-unmatch).
            'untrack' => ['auth.json'],
            'composerRequire' => $composerRequire,
            // Bedrock seulement : ailleurs la racine projet est la racine web, un auth.json y serait téléchargeable.
            'serverAuthJson' => $type === 'bedrock' && $failing('plugins.auth_json_root'),
        ];
    }

    /** @return array<string, string> slug => version installée */
    public static function installedVersions(): array
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $versions = [];
        foreach (get_plugins() as $file => $data) {
            $versions[(string) strtok((string) $file, '/')] = (string) ($data['Version'] ?? '');
        }

        return $versions;
    }
}
