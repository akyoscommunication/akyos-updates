<?php

namespace AkyosUpdates\Service;

/**
 * Checks dont la conformité dépend du projet : le statut du rapport vient d’une validation humaine
 * (orange tant que non validé, vert une fois validé). La validation porte sur une empreinte de la
 * configuration : si la configuration change, la validation devient caduque (orange).
 */
final class ManualValidationService
{
    public const ACTION_ID = 'report.validate_check';
    private const OPTION = 'akyos_updates_manual_validations';

    /** Clés du payload qui définissent « la configuration » validée, par check. */
    private const FINGERPRINT_KEYS = [
        'seo.indexability' => ['rows'],
        'backoffice.branda_smtp' => ['brandaActive', 'smtpModuleActive', 'fromEmail', 'fromName', 'smtpHost', 'smtpPort', 'smtpEncryption', 'smtpAuth', 'smtpUsername', 'passwordSet'],
        'plugins.composer_audit' => ['missingComposerJson', 'invalidJson', 'missingPlugins'],
        'rgpd.analytics_tracking' => ['signals'],
    ];

    public static function requiresValidation(string $checkId): bool
    {
        return isset(self::FINGERPRINT_KEYS[$checkId]);
    }

    /**
     * Applique l’état de validation à un résultat de check (idempotent).
     *
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    public static function apply(array $result): array
    {
        $id = (string) ($result['id'] ?? '');
        if (! self::requiresValidation($id) || ($result['status'] ?? '') === 'skipped') {
            return $result;
        }

        $previous = is_array($result['validation'] ?? null) ? $result['validation'] : [];
        $autoStatus = (string) ($previous['autoStatus'] ?? $result['status'] ?? 'info');
        $autoSeverity = (string) ($previous['autoSeverity'] ?? $result['severity'] ?? 'neutral');
        $autoMessage = (string) ($previous['autoMessage'] ?? $result['message'] ?? '');

        $stored = self::all()[$id] ?? null;
        $validated = is_array($stored) && hash_equals((string) ($stored['fingerprint'] ?? ''), self::fingerprint($result));
        $stale = is_array($stored) && ! $validated;
        $validatedAt = $validated ? (string) ($stored['validatedAt'] ?? '') : null;
        $validatedBy = $validated ? (string) ($stored['validatedBy'] ?? '') : null;

        if ($validated) {
            $suffix = sprintf('Configuration validée%s%s.', $validatedBy !== '' ? ' par ' . $validatedBy : '', $validatedAt !== '' ? ' le ' . wp_date('d/m/Y', strtotime($validatedAt)) : '');
        } elseif ($stale) {
            $suffix = 'La configuration a changé depuis la dernière validation : à revalider.';
        } else {
            $suffix = 'Configuration à valider (dépend du projet).';
        }

        return array_merge($result, [
            'status' => $validated ? 'ok' : 'warn',
            'severity' => $validated ? 'success' : 'warning',
            'message' => trim($autoMessage . ' ' . $suffix),
            'countsTowardCategoryStats' => true,
            'validation' => [
                'required' => true,
                'validated' => $validated,
                'stale' => $stale,
                'validatedAt' => $validatedAt,
                'validatedBy' => $validatedBy,
                'actionId' => self::ACTION_ID,
                'autoStatus' => $autoStatus,
                'autoSeverity' => $autoSeverity,
                'autoMessage' => $autoMessage,
            ],
        ]);
    }

    /** @param array<string, mixed> $freshResult Résultat brut du check au moment de la validation. */
    public static function set(string $checkId, array $freshResult, bool $validated): void
    {
        $all = self::all();
        if ($validated) {
            $user = wp_get_current_user();
            $all[$checkId] = [
                'fingerprint' => self::fingerprint($freshResult),
                'validatedAt' => gmdate('c'),
                'validatedBy' => $user->exists() ? (string) $user->user_login : 'MAW',
            ];
        } else {
            unset($all[$checkId]);
        }
        update_option(self::OPTION, $all, false);
    }

    /**
     * État des validations pour l’API CRM.
     *
     * @param list<array<string, mixed>> $results
     * @return list<array<string, mixed>>
     */
    public static function summarize(array $results): array
    {
        $out = [];
        foreach ($results as $result) {
            if (! is_array($result) || ! is_array($result['validation'] ?? null)) {
                continue;
            }
            $v = $result['validation'];
            $out[] = [
                'checkId' => (string) ($result['id'] ?? ''),
                'title' => (string) ($result['title'] ?? ''),
                'status' => (string) ($result['status'] ?? ''),
                'validated' => (bool) ($v['validated'] ?? false),
                'stale' => (bool) ($v['stale'] ?? false),
                'validatedAt' => $v['validatedAt'] ?? null,
                'validatedBy' => $v['validatedBy'] ?? null,
                'autoStatus' => (string) ($v['autoStatus'] ?? ''),
                'autoMessage' => (string) ($v['autoMessage'] ?? ''),
            ];
        }

        return $out;
    }

    /** @return array<string, array{fingerprint?: string, validatedAt?: string, validatedBy?: string}> */
    private static function all(): array
    {
        $all = get_option(self::OPTION, []);

        return is_array($all) ? $all : [];
    }

    /** @param array<string, mixed> $result */
    private static function fingerprint(array $result): string
    {
        $payload = is_array($result['payload'] ?? null) ? $result['payload'] : [];
        $subset = [];
        foreach (self::FINGERPRINT_KEYS[(string) ($result['id'] ?? '')] ?? [] as $key) {
            $subset[$key] = $payload[$key] ?? null;
        }

        return md5((string) wp_json_encode($subset));
    }
}
