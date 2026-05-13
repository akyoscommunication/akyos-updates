<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Core\Context\InstallationContextDetector;
use AkyosUpdates\Service\WordpressService;

final class GenerateComposerGuidanceAction implements ActionInterface
{
    public function getId(): string
    {
        return 'plugins.generate_composer_guidance';
    }

    public function run(array $payload = []): ActionResult
    {
        $detector = new InstallationContextDetector();
        $installationType = $detector->detect()->getInstallationType();

        $missingPlugins = is_array($payload['missingPlugins'] ?? null) ? $payload['missingPlugins'] : [];
        $missingList = array_values(array_unique(array_filter(array_map('strval', $missingPlugins))));

        $availableSlugs = [];
        $unavailableSlugs = [];
        $lines = [];

        foreach ($missingList as $slug) {
            if ($slug === '') {
                continue;
            }
            if (WordpressService::isListedOnWordPressOrg($slug)) {
                $availableSlugs[] = $slug;
                $lines[] = sprintf('    "wpackagist-plugin/%s": "*",', $slug);
            } else {
                $unavailableSlugs[] = $slug;
            }
        }

        $composerProposalsText = $lines === [] ? '' : implode("\n", $lines);
        $countAvailable = count($availableSlugs);
        $countUnavailable = count($unavailableSlugs);

        if ($countAvailable > 0 && $countUnavailable === 0) {
            $message = sprintf(
                '%d plugin(s) repéré(s) sur WordPress.org : lignes prêtes pour le bloc require de composer.json.',
                $countAvailable
            );
        } elseif ($countAvailable > 0) {
            $message = sprintf(
                '%d sur %d absent(s) de composer sont sur WordPress.org (lignes dans la fenêtre). %d autre(s) : dépôt public non détecté via l’annuaire WordPress.org.',
                $countAvailable,
                count($missingList),
                $countUnavailable
            );
        } elseif ($missingList === []) {
            $message = 'Aucun plugin manquant dans le payload.';
        } else {
            $message = 'Aucun de ces plugins n’a été trouvé sur WordPress.org : ajout automatique via wpackagist impossible ici.';
        }

        return ActionResult::success($message, [
            'composerProposalsText' => $composerProposalsText,
            'availableSlugs' => $availableSlugs,
            'unavailableSlugs' => $unavailableSlugs,
            'missingPlugins' => $missingList,
            'missingWpackagistSlugs' => $availableSlugs,
            'missingGitignoreSlugs' => $unavailableSlugs,
            'installationType' => $installationType,
            'remainingMissing' => count($missingList),
        ]);
    }
}
