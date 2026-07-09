<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\EnvironmentService;

final class SeoToggleSiteIndexingAction implements ActionInterface
{
    public function getId(): string
    {
        return 'seo.toggle_site_indexing';
    }

    public function run(array $payload = []): ActionResult
    {
        if (! isset($payload['indexed'])) {
            return ActionResult::failure('Payload invalide.');
        }

        $indexed = (bool) $payload['indexed'];

        if (EnvironmentService::isIndexingForcedOff()) {
            return ActionResult::failure(
                'Indexation verrouillée par DISALLOW_INDEXING (Bedrock). Modifie config/environments/ ou WP_ENV dans .env.'
            );
        }

        $projectRoot = EnvironmentService::detectProjectRootPath();
        $wpEnv = EnvironmentService::getWpEnv($projectRoot);

        if ($indexed && ! EnvironmentService::isProduction($projectRoot)) {
            return ActionResult::failure(sprintf(
                'Indexation interdite : environnement « %s » (production requise). Vérifie WP_ENV dans .env.',
                $wpEnv
            ));
        }

        if (EnvironmentService::isSitePubliclyIndexed() === $indexed) {
            return ActionResult::success(
                $indexed ? 'Le site est déjà indexable.' : 'Le site est déjà non indexé.'
            );
        }

        if (! EnvironmentService::setSitePubliclyIndexed($indexed)) {
            return ActionResult::failure('Impossible de modifier le réglage d’indexation du site.');
        }

        return ActionResult::success(
            $indexed
                ? 'Le site est maintenant indexable par les moteurs de recherche.'
                : 'Le site n’est plus indexé : les moteurs de recherche sont dissuadés.'
        );
    }
}
