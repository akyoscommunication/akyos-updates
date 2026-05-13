<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Core\Context\InstallationContextDetector;
use AkyosUpdates\Service\WordpressService;

final class GenerateGitignorePluginExceptionsAction implements ActionInterface
{
    public function getId(): string
    {
        return 'plugins.generate_gitignore_exceptions';
    }

    public function run(array $payload = []): ActionResult
    {
        $detector = new InstallationContextDetector();
        $context = $detector->detect();
        $installationType = $context->getInstallationType();

        $missingPlugins = is_array($payload['missingPlugins'] ?? null) ? $payload['missingPlugins'] : [];
        $missingList = array_values(array_unique(array_filter(array_map('strval', $missingPlugins))));

        $missingWpackagist = null;
        $missingGitignore = null;
        if (array_key_exists('missingWpackagistSlugs', $payload) && is_array($payload['missingWpackagistSlugs'])) {
            $missingWpackagist = array_values(array_unique(array_filter(array_map('strval', $payload['missingWpackagistSlugs']))));
        }
        if (array_key_exists('missingGitignoreSlugs', $payload) && is_array($payload['missingGitignoreSlugs'])) {
            $missingGitignore = array_values(array_unique(array_filter(array_map('strval', $payload['missingGitignoreSlugs']))));
        }

        if ($missingWpackagist === null || $missingGitignore === null) {
            $missingWpackagist = [];
            $missingGitignore = [];
            foreach ($missingList as $slug) {
                if ($slug === '') {
                    continue;
                }
                if (WordpressService::isListedOnWordPressOrg($slug)) {
                    $missingWpackagist[] = $slug;
                } else {
                    $missingGitignore[] = $slug;
                }
            }
        }

        $slugs = $missingGitignore;
        $lines = [];
        foreach ($slugs as $slug) {
            $line = self::lineForSlug($slug, $installationType);
            if ($line !== '') {
                $lines[] = $line;
            }
        }

        $gitignoreProposalsText = $lines === [] ? '' : implode("\n", $lines);
        $n = count($slugs);

        if ($n === 0) {
            $message = 'Aucun plugin hors annuaire WordPress.org à traiter par exception .gitignore.';
        } else {
            $message = sprintf(
                '%d exception(s) .gitignore proposée(s) (%s).',
                $n,
                $installationType === 'bedrock' ? 'chemins type Bedrock' : 'chemins type installation classique'
            );
        }

        return ActionResult::success($message, [
            'gitignoreProposalsText' => $gitignoreProposalsText,
            'gitignoreSlugs' => $slugs,
            'installationType' => $installationType,
            'missingPlugins' => $missingList,
            'missingWpackagistSlugs' => $missingWpackagist,
            'missingGitignoreSlugs' => $missingGitignore,
            'remainingMissing' => count($missingList),
        ]);
    }

    private static function lineForSlug(string $slug, string $installationType): string
    {
        $slug = trim($slug);
        if ($slug === '') {
            return '';
        }

        if ($installationType === 'bedrock') {
            return sprintf('!web/app/plugins/%s/', $slug);
        }

        return sprintf('!wp-content/plugins/%s/', $slug);
    }
}
