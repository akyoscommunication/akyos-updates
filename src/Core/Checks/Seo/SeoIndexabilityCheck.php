<?php

namespace AkyosUpdates\Core\Checks\Seo;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\SeoService;

final class SeoIndexabilityCheck implements CheckInterface
{
    private static function titleLabel(): string
    {
        return 'Paramétrage indexation — post types et taxonomies';
    }

    private static function formatMessage(int $indexableCount, int $totalRows): string
    {
        $detail = sprintf('%d indexable(s), %d noindex.', $indexableCount, $totalRows - $indexableCount);

        return sprintf(
            'Aperçu des réglages d’indexation des archives. Les choix dépendent du projet.',
            $detail
        );
    }

    public static function normalizeStoredResult(array $result): array
    {
        if (($result['id'] ?? '') !== 'seo.indexability') {
            return $result;
        }

        $payload = is_array($result['payload'] ?? null) ? $result['payload'] : [];
        $rows = is_array($payload['rows'] ?? null) ? $payload['rows'] : [];
        $indexableCount = count(array_filter($rows, static fn(array $row): bool => (bool) ($row['indexable'] ?? false)));

        return array_merge($result, [
            'title' => self::titleLabel(),
            'status' => 'info',
            'severity' => 'neutral',
            'countsTowardCategoryStats' => false,
            'message' => self::formatMessage($indexableCount, count($rows)),
            'payload' => array_merge($payload, [
                'indexableCount' => $indexableCount,
                'rows' => $rows,
            ]),
        ]);
    }

    public function getId(): string
    {
        return 'seo.indexability';
    }

    public function getCategory(): string
    {
        return 'SEO';
    }

    public function getTitle(): string
    {
        return self::titleLabel();
    }
    public function getSuccessMessage(): string
    {
        return 'Point validé après action corrective.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $rows = [];

        $postTypes = get_post_types(['public' => true], 'objects');
        foreach ($postTypes as $postType) {
            if (!isset($postType->name) || in_array($postType->name, ['attachment'], true)) {
                continue;
            }

            $noindex = SeoService::isNoindexEnabled('post_type', (string) $postType->name);
            $rows[] = [
                'kind' => 'post_type',
                'name' => (string) $postType->name,
                'label' => (string) ($postType->labels->singular_name ?? $postType->label ?? $postType->name),
                'indexable' => !$noindex,
            ];
        }

        $taxonomies = get_taxonomies(['public' => true], 'objects');
        foreach ($taxonomies as $taxonomy) {
            if (!isset($taxonomy->name)) {
                continue;
            }

            $noindex = SeoService::isNoindexEnabled('taxonomy', (string) $taxonomy->name);
            $rows[] = [
                'kind' => 'taxonomy',
                'name' => (string) $taxonomy->name,
                'label' => (string) ($taxonomy->labels->singular_name ?? $taxonomy->label ?? $taxonomy->name),
                'indexable' => !$noindex,
            ];
        }

        $indexableCount = count(array_filter($rows, static fn(array $row): bool => (bool) $row['indexable']));

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'info',
            'neutral',
            self::formatMessage($indexableCount, count($rows)),
            true,
            'seo.toggle_indexing',
            [
                'rows' => $rows,
                'indexableCount' => $indexableCount,
            ],
            false,
        );
    }
}
