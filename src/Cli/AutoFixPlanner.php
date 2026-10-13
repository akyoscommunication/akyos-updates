<?php

namespace AkyosUpdates\Cli;

/**
 * Décide quelles actions du rapport peuvent partir toutes seules (CLI / --fix).
 */
final class AutoFixPlanner
{
    /** @var array<string, string> actionId => raison */
    private const MANUAL = [
        'wordpress.set_favicon' => 'Nécessite un média (attachmentId).',
        'security.defender_save_mask_login' => 'Nécessite un slug Mask Login.',
        'security.defender_save_recaptcha_keys' => 'Nécessite les clés reCAPTCHA.',
        'backoffice.create_admin_lite_user' => 'Nécessite email et nom.',
        'backoffice.branda_save_smtp' => 'Nécessite les identifiants SMTP.',
        'backoffice.branda_send_test_email' => 'Nécessite un destinataire.',
        'seo.toggle_indexing' => 'Choix d’indexation par type / page — à faire dans l’admin.',
        'plugins.generate_composer_json' => 'Génère un template, ne l’applique pas.',
        'plugins.generate_composer_guidance' => 'Génère un guide, ne l’applique pas.',
        'plugins.generate_gitignore_exceptions' => 'Propose des lignes .gitignore, ne les écrit pas.',
    ];

    /**
     * @param list<array<string, mixed>> $results
     * @param list<string> $onlyCheckIds vide = tous
     * @return array{run: list<array<string, mixed>>, skip: list<array<string, mixed>>}
     */
    public static function plan(array $results, array $onlyCheckIds = []): array
    {
        $only = [];
        foreach ($onlyCheckIds as $id) {
            $id = trim((string) $id);
            if ($id !== '') {
                $only[$id] = true;
            }
        }

        $run = [];
        $skip = [];
        foreach ($results as $result) {
            if (!is_array($result)) {
                continue;
            }
            $checkId = (string) ($result['id'] ?? '');
            if ($only !== [] && ($checkId === '' || !isset($only[$checkId]))) {
                continue;
            }
            if (($result['actionable'] ?? false) !== true) {
                continue;
            }
            $actionId = (string) ($result['actionId'] ?? '');
            if ($actionId === '') {
                continue;
            }
            $status = (string) ($result['status'] ?? '');
            if ($status === 'ok' || $status === 'skipped') {
                continue;
            }

            $title = (string) ($result['title'] ?? $checkId);
            if (isset(self::MANUAL[$actionId])) {
                $skip[] = [
                    'checkId' => $checkId,
                    'actionId' => $actionId,
                    'title' => $title,
                    'reason' => self::MANUAL[$actionId],
                ];
                continue;
            }

            $payload = self::payloadFor($actionId, $result);
            if ($payload === null) {
                $skip[] = [
                    'checkId' => $checkId,
                    'actionId' => $actionId,
                    'title' => $title,
                    'reason' => 'Déjà dans l’état attendu (aucun correctif à appliquer).',
                ];
                continue;
            }

            $run[] = [
                'checkId' => $checkId,
                'actionId' => $actionId,
                'title' => $title,
                'payload' => $payload,
            ];
        }

        return ['run' => $run, 'skip' => $skip];
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>|null null = skip
     */
    public static function payloadFor(string $actionId, array $result): ?array
    {
        $checkPayload = is_array($result['payload'] ?? null) ? $result['payload'] : [];

        if ($actionId === 'seo.toggle_site_indexing') {
            $indexed = (bool) ($checkPayload['indexed'] ?? false);
            $desired = (bool) ($checkPayload['isProduction'] ?? false);
            if ($indexed === $desired) {
                return null;
            }

            return ['indexed' => $desired];
        }

        if ($actionId === 'images.smush_apply_nextgen_config') {
            $format = (string) ($checkPayload['activeFormat'] ?? 'webp');
            if (!in_array($format, ['webp', 'avif'], true)) {
                $format = 'webp';
            }

            return [
                'nextGenEnabled' => true,
                'activeFormat' => $format,
            ];
        }

        if ($actionId === 'images.smush_apply_resize_large') {
            $width = (int) ($checkPayload['resizeWidth'] ?? 1920);
            $height = (int) ($checkPayload['resizeHeight'] ?? 1920);

            return [
                'width' => $width > 0 ? $width : 1920,
                'height' => $height > 0 ? $height : 1920,
            ];
        }

        return [];
    }
}
