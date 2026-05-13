<?php

namespace AkyosUpdates\Core\Checks\Seo;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Checks\Rgpd\RgpdLegalPagesPresenceCheck;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\SeoService;

final class SeoLegalPagesNoindexCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'seo.legal_pages_noindex';
    }

    public function getCategory(): string
    {
        return 'SEO';
    }

    public function getTitle(): string
    {
        return 'Pages légales noindex';
    }
    public function getSuccessMessage(): string
    {
        return 'Point validé après action corrective.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $snapshot = RgpdLegalPagesPresenceCheck::snapshot();
        $requirements = is_array($snapshot['requirements'] ?? null) ? $snapshot['requirements'] : [];
        $legalPages = [];

        foreach ($requirements as $requirement) {
            if (! is_array($requirement)) {
                continue;
            }
            $page = $requirement['page'] ?? null;
            if (! is_array($page)) {
                continue;
            }
            $id = (int) ($page['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $legalPages[] = [
                'id' => $id,
                'title' => (string) ($page['title'] ?? ''),
                'editUrl' => (string) ($page['editUrl'] ?? ''),
                'noindex' => SeoService::isPostNoindexByMeta($id),
            ];
        }

        if ($legalPages === []) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Aucune page légale détectée via le titre.',
                false,
                null,
                ['pages' => []]
            );
        }

        $indexedPages = array_values(array_filter($legalPages, static fn (array $page): bool => ! $page['noindex']));
        $allNoindex = $indexedPages === [];

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            $allNoindex ? 'ok' : 'warn',
            $allNoindex ? 'success' : 'warning',
            $allNoindex
                ? 'Toutes les pages légales détectées sont en noindex.'
                : sprintf('%d page(s) légale(s) encore indexable(s).', count($indexedPages)),
            false,
            null,
            [
                'pages' => $legalPages,
                'indexedPagesCount' => count($indexedPages),
            ]
        );
    }
}
