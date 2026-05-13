<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\SeoService;

final class SeoToggleIndexingAction implements ActionInterface
{
    public function getId(): string
    {
        return 'seo.toggle_indexing';
    }

    public function run(array $payload = []): ActionResult
    {
        $kind = (string) ($payload['kind'] ?? '');
        $name = (string) ($payload['name'] ?? '');
        $indexable = isset($payload['indexable']) ? (bool) $payload['indexable'] : null;
        $postId = isset($payload['postId']) ? (int) $payload['postId'] : 0;

        if ($kind === 'post') {
            if ($postId <= 0 || $indexable === null) {
                return ActionResult::failure('Payload SEO invalide.');
            }

            $updated = SeoService::setPostNoindex($postId, ! $indexable);
            if (! $updated) {
                return ActionResult::failure('Impossible de modifier ce réglage SEO.');
            }

            return ActionResult::success($indexable ? 'Page passée en indexable.' : 'Page passée en noindex.');
        }

        if (! in_array($kind, ['post_type', 'taxonomy'], true) || $name === '' || $indexable === null) {
            return ActionResult::failure('Payload SEO invalide.');
        }

        $updated = SeoService::setNoindex(
            $kind === 'taxonomy' ? 'taxonomy' : 'post_type',
            $name,
            ! $indexable
        );

        if (! $updated) {
            return ActionResult::failure('Impossible de modifier ce réglage SEO.');
        }

        return ActionResult::success(sprintf(
                '%s "%s" est maintenant %s.',
                $kind === 'taxonomy' ? 'Taxonomie' : 'Post type',
                $name,
                $indexable ? 'indexable' : 'en noindex'
            ));
    }
}
