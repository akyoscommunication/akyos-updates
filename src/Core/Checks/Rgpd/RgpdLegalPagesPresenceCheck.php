<?php

namespace AkyosUpdates\Core\Checks\Rgpd;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use WP_Post;

final class RgpdLegalPagesPresenceCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'rgpd.legal_pages_presence';
    }

    public function getCategory(): string
    {
        return 'RGPD';
    }

    public function getTitle(): string
    {
        return 'Pages légales';
    }
    public function getSuccessMessage(): string
    {
        return 'Pages légales : état mis à jour.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $payload = self::snapshot();

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            $payload['allPresent'] ? 'ok' : 'warn',
            $payload['allPresent'] ? 'success' : 'warning',
            $payload['message'],
            false,
            null,
            $payload
        );
    }

    /** @return array{allPresent: bool, message: string, requirements: array<int, array<string, mixed>>, missingKeys: array<int, string>, actionableKinds: array<int, string>} */
    public static function snapshot(): array
    {
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => ['publish', 'private', 'draft'],
            'numberposts' => -1,
            'orderby' => 'ID',
            'order' => 'DESC',
            'suppress_filters' => true,
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        $found = [
            'mentions_legales' => null,
            'cookies' => null,
            'data_retention' => null,
        ];

        foreach ($pages as $page) {
            if (! $page instanceof WP_Post) {
                continue;
            }

            $haystack = mb_strtolower(trim(($page->post_title ?? '') . ' ' . ($page->post_name ?? '')));

            if ($found['mentions_legales'] === null && self::matchesMentionsLegales($haystack)) {
                $found['mentions_legales'] = self::pageRow($page);
            }
            if ($found['cookies'] === null && self::matchesCookies($haystack)) {
                $found['cookies'] = self::pageRow($page);
            }
            if ($found['data_retention'] === null && self::matchesDataRetention($haystack)) {
                $found['data_retention'] = self::pageRow($page);
            }
            if (
                $found['mentions_legales'] !== null
                && $found['cookies'] !== null
                && $found['data_retention'] !== null
            ) {
                break;
            }
        }

        $definitions = [
            'mentions_legales' => [
                'label' => 'Mentions légales',
                'matchHint' => '« légales » dans le titre ou le slug',
            ],
            'cookies' => [
                'label' => 'Cookies',
                'matchHint' => '« cookie » dans le titre ou le slug',
            ],
            'data_retention' => [
                'label' => 'Politique de conservation des données',
                'matchHint' => '« politique » et (« conservation » ou « données »), ou « conservation » + « données »',
            ],
        ];

        $requirements = [];
        $missingKeys = [];

        foreach ($definitions as $key => $meta) {
            $row = $found[$key];
            $present = $row !== null;
            if (! $present) {
                $missingKeys[] = $key;
            }
            $requirements[] = [
                'key' => $key,
                'label' => $meta['label'],
                'matchHint' => $meta['matchHint'],
                'found' => $present,
                'page' => $row,
            ];
        }

        $allPresent = $missingKeys === [];
        $message = $allPresent
            ? 'Les trois types de pages légales attendues sont trouvés (critères titre / slug).'
            : sprintf(
                'Manque %d page(s) : %s.',
                count($missingKeys),
                implode(', ', array_map(
                    static fn(string $k): string => $definitions[$k]['label'] ?? $k,
                    $missingKeys
                ))
            );

        return [
            'allPresent' => $allPresent,
            'message' => $message,
            'requirements' => $requirements,
            'missingKeys' => $missingKeys,
            'actionableKinds' => $missingKeys,
        ];
    }

    private static function matchesMentionsLegales(string $haystack): bool
    {
        return str_contains($haystack, 'légales')
            || str_contains($haystack, 'legales');
    }

    private static function matchesCookies(string $haystack): bool
    {
        return str_contains($haystack, 'cookie');
    }

    private static function matchesDataRetention(string $haystack): bool
    {
        $hasPolitique = str_contains($haystack, 'politique');
        $hasConservation = str_contains($haystack, 'conservation');
        $hasDonnees = str_contains($haystack, 'données') || str_contains($haystack, 'donnees');

        if ($hasPolitique && ($hasConservation || $hasDonnees)) {
            return true;
        }

        return $hasConservation && $hasDonnees;
    }

    /** @return array{id: int, title: string, editUrl: string, viewUrl: string} */
    private static function pageRow(WP_Post $page): array
    {
        $id = (int) $page->ID;

        return [
            'id' => $id,
            'title' => (string) $page->post_title,
            'editUrl' => get_edit_post_link($id, ''),
            'viewUrl' => get_permalink($id) ?: '',
        ];
    }
}
