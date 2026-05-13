<?php

namespace AkyosUpdates\Core\Checks\Plugins;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\ComposerService;
use AkyosUpdates\Service\WordpressService;

final class ComposerPluginsCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'plugins.composer_audit';
    }

    public function getCategory(): string
    {
        return 'Plugins';
    }

    public function getTitle(): string
    {
        return 'Audit composer plugins';
    }
    public function getSuccessMessage(): string
    {
        return 'Tous les plugins détectés sont couverts par composer.json (ou exclus de l’audit).';
    }


    public function run(SiteContext $context): CheckResult
    {
        $composerPath = $context->getProjectRootPath() . '/composer.json';
        if (! file_exists($composerPath)) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'fail',
                'high',
                'composer.json absent à la racine.',
                false,
                null,
                ['missingComposerJson' => true]
            );
        }

        $raw = (string) file_get_contents($composerPath);
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'fail',
                'high',
                'composer.json invalide (JSON cassé).',
                true,
                'plugins.generate_composer_guidance',
                ['invalidJson' => true]
            );
        }

        if (! function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $require = is_array($decoded['require'] ?? null) ? $decoded['require'] : [];
        $managedByComposer = ComposerService::fromRequire($require);

        $plugins = get_plugins();
        $missing = [];
        foreach (array_keys($plugins) as $pluginFile) {
            $slug = strtok($pluginFile, '/');
            if (! in_array($slug, $managedByComposer, true) && ! str_starts_with($slug, 'akyos-')) {
                $missing[] = $slug;
            }
        }

        $missing = array_values(array_unique($missing));
        if ($missing === []) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'ok',
                'success',
                'Tous les plugins actifs semblent couverts par Composer ou liste autorisée.',
                false,
                null,
                [
                    'composerPath' => $composerPath,
                    'missingPlugins' => [],
                    'missingWpackagistSlugs' => [],
                    'missingGitignoreSlugs' => [],
                    'installationType' => $context->getInstallationType(),
                ]
            );
        }

        $missingWpackagistSlugs = [];
        $missingGitignoreSlugs = [];
        foreach ($missing as $slug) {
            if (WordpressService::isListedOnWordPressOrg($slug)) {
                $missingWpackagistSlugs[] = $slug;
            } else {
                $missingGitignoreSlugs[] = $slug;
            }
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'warn',
            'medium',
            sprintf('%d plugins non trouvés dans Composer.', count($missing)),
            true,
            'plugins.generate_composer_guidance',
            [
                'composerPath' => $composerPath,
                'missingPlugins' => $missing,
                'require' => $require,
                'missingWpackagistSlugs' => $missingWpackagistSlugs,
                'missingGitignoreSlugs' => $missingGitignoreSlugs,
                'installationType' => $context->getInstallationType(),
            ]
        );
    }
}
