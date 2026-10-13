<?php

namespace AkyosUpdates\Core\Checks\WordPress;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\WordpressService;

final class TranslationUpdatesCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'wordpress.translations';
    }

    public function getCategory(): string
    {
        return 'WordPress';
    }

    public function getTitle(): string
    {
        return 'Traductions';
    }

    public function getSuccessMessage(): string
    {
        return 'Traductions à jour.';
    }

    public function run(SiteContext $context): CheckResult
    {
        $locale = (string) get_locale();
        $updates = WordpressService::getPendingTranslationUpdates();
        $payload = [
            'locale' => $locale,
            'pendingCount' => count($updates),
            'updates' => self::formatUpdates($updates),
        ];

        if ($updates === []) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'ok',
                'success',
                sprintf('Traductions à jour pour la locale %s.', $locale),
                false,
                null,
                $payload
            );
        }

        $summary = WordpressService::summarizeTranslationUpdates($updates);
        $count = count($updates);

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'warn',
            'warning',
            sprintf(
                '%d traduction(s) disponible(s) pour %s%s.',
                $count,
                $locale,
                $summary !== '' ? ' (' . $summary . ')' : ''
            ),
            true,
            'wordpress.update_translations',
            $payload
        );
    }

    /** @param list<object> $updates
     * @return list<array{type: string, slug: string, language: string, version: string}>
     */
    private static function formatUpdates(array $updates): array
    {
        $formatted = [];
        foreach ($updates as $update) {
            $formatted[] = [
                'type' => (string) ($update->type ?? ''),
                'slug' => (string) ($update->slug ?? 'default'),
                'language' => (string) ($update->language ?? ''),
                'version' => (string) ($update->version ?? ''),
            ];
        }

        return $formatted;
    }
}
