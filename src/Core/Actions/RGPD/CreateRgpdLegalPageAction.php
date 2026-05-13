<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Core\Checks\Rgpd\RgpdLegalPagesPresenceCheck;
use AkyosUpdates\Service\SeoService;

final class CreateRgpdLegalPageAction implements ActionInterface
{
    private const KINDS = [
        'mentions_legales' => 'Mentions légales',
        'cookies' => 'Cookies',
        'data_retention' => 'Politique de conservation des données',
    ];

    public function getId(): string
    {
        return 'rgpd.create_legal_page';
    }

    public function run(array $payload = []): ActionResult
    {
        $kind = (string) ($payload['kind'] ?? '');
        if (! isset(self::KINDS[$kind])) {
            return ActionResult::failure('Type de page inconnu.');
        }

        $title = self::KINDS[$kind];
        $postId = wp_insert_post([
            'post_title' => $title,
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_content' => '',
        ], true);

        if (is_wp_error($postId)) {
            return ActionResult::failure($postId->get_error_message());
        }

        $postId = (int) $postId;
        $noindexOk = SeoService::setPostNoindex($postId, true);

        $snap = RgpdLegalPagesPresenceCheck::snapshot();

        return ActionResult::success(
            $noindexOk
                ? sprintf('Page « %s » créée et passée en noindex.', $title)
                : sprintf('Page « %s » créée. Noindex non appliqué (plugin SEO Yoast/SmartCrawl requis).', $title),
            [
            'postId' => $postId,
            'editUrl' => get_edit_post_link($postId, ''),
            'noindexApplied' => $noindexOk,
            'requirements' => $snap['requirements'],
            'missingKeys' => $snap['missingKeys'],
            'allPresent' => $snap['allPresent'],
            'actionableKinds' => $snap['actionableKinds'],
            'snapshotMessage' => $snap['message'],
            ]
        );
    }
}
