<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Service\CrmApiAuthService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Diagnostic en lecture seule pour MAW (fiche projet, MCP) : fin des logs, lecture d'un fichier, requête SELECT.
 *
 * Même authentification que l'API CRM (clé du site). Jamais d'écriture ; les fichiers de secrets ne sont
 * jamais lus et les colonnes sensibles des requêtes sont masquées.
 */
final class DiagnosticController
{
    private const MAX_LINES = 500;
    private const MAX_BYTES = 65536;
    private const MAX_ROWS = 200;
    private const MASK = '•••';

    /** Fichiers porteurs de secrets : jamais lus. */
    private const DENIED_FILES = ['wp-config.php', '.env', 'auth.json', '.htpasswd', 'id_rsa', 'id_ed25519', 'id_ecdsa'];
    private const DENIED_PATTERN = '/(^\.env|\.(pem|key|p12|pfx|sql|sql\.gz)$)/i';

    /** Colonnes ou options dont la valeur est masquée dans les résultats de requête. */
    private const SENSITIVE_NAME = '/pass|secret|token|salt|api[_-]?key|private|license|activation_key/i';

    public function register(): void
    {
        register_rest_route('akyos-updates/v1', '/crm/logs', [
            'methods' => 'GET',
            'callback' => [$this, 'logs'],
            'permission_callback' => [CrmApiAuthService::class, 'permissionCallback'],
            'args' => ['lines' => ['required' => false, 'type' => 'integer']],
        ]);

        register_rest_route('akyos-updates/v1', '/crm/file', [
            'methods' => 'GET',
            'callback' => [$this, 'file'],
            'permission_callback' => [CrmApiAuthService::class, 'permissionCallback'],
            'args' => ['path' => ['required' => true, 'type' => 'string']],
        ]);

        register_rest_route('akyos-updates/v1', '/crm/query', [
            'methods' => 'POST',
            'callback' => [$this, 'query'],
            'permission_callback' => [CrmApiAuthService::class, 'permissionCallback'],
            'args' => ['sql' => ['required' => true, 'type' => 'string']],
        ]);
    }

    public function logs(WP_REST_Request $request): WP_REST_Response
    {
        $lines = max(1, min(self::MAX_LINES, (int) ($request->get_param('lines') ?: 100)));
        $candidates = array_filter([
            is_string(defined('WP_DEBUG_LOG') ? WP_DEBUG_LOG : null) ? WP_DEBUG_LOG : null,
            WP_CONTENT_DIR . '/debug.log',
            ini_get('error_log') ?: null,
        ]);

        $logs = [];
        foreach (array_unique($candidates) as $file) {
            if (is_file($file) && is_readable($file)) {
                $logs[] = ['file' => $this->relative($file), 'lines' => $this->tail($file, $lines)];
            }
        }

        return new WP_REST_Response(['logs' => $logs]);
    }

    public function file(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $root = $this->root();
        $path = realpath($root . '/' . ltrim((string) $request->get_param('path'), '/'));
        if (false === $path || !str_starts_with($path, $root . '/') || !is_file($path)) {
            return new WP_Error('akyos_updates_not_found', 'Fichier introuvable dans le site.', ['status' => 404]);
        }
        $name = basename($path);
        if (in_array($name, self::DENIED_FILES, true) || preg_match(self::DENIED_PATTERN, $name)) {
            return new WP_Error('akyos_updates_forbidden', 'Fichier de secrets : lecture refusée.', ['status' => 403]);
        }

        $size = (int) filesize($path);

        return new WP_REST_Response([
            'path' => $this->relative($path),
            'size' => $size,
            'truncated' => $size > self::MAX_BYTES,
            'content' => (string) file_get_contents($path, false, null, 0, self::MAX_BYTES),
        ]);
    }

    public function query(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $sql = rtrim(trim((string) $request->get_param('sql')), ';');
        if (!preg_match('/^(SELECT|SHOW|DESCRIBE|DESC|EXPLAIN)\s/i', $sql)
            || str_contains($sql, ';')
            || preg_match('/\b(INTO\s+(OUT|DUMP)FILE|LOAD_FILE|SLEEP|BENCHMARK|GET_LOCK)\b/i', $sql)) {
            return new WP_Error('akyos_updates_forbidden', 'Requête refusée : une seule instruction SELECT, SHOW, DESCRIBE ou EXPLAIN.', ['status' => 400]);
        }
        if (preg_match('/^SELECT\s/i', $sql) && !preg_match('/\bLIMIT\s+\d+/i', $sql)) {
            $sql .= ' LIMIT ' . self::MAX_ROWS;
        }

        global $wpdb;
        $rows = $wpdb->get_results($sql, ARRAY_A);
        if ('' !== $wpdb->last_error) {
            return new WP_Error('akyos_updates_query_error', $wpdb->last_error, ['status' => 400]);
        }

        $rows = array_map([$this, 'maskRow'], array_slice((array) $rows, 0, self::MAX_ROWS));

        return new WP_REST_Response(['prefix' => $wpdb->prefix, 'rows' => $rows, 'count' => count($rows)]);
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function maskRow(array $row): array
    {
        // Ligne de wp_options : la sensibilité dépend du nom de l'option, pas de la colonne.
        $sensitiveOption = isset($row['option_name']) && preg_match(self::SENSITIVE_NAME, (string) $row['option_name']);
        foreach ($row as $column => $value) {
            if (preg_match(self::SENSITIVE_NAME, (string) $column) || ($sensitiveOption && 'option_value' === $column)) {
                $row[$column] = self::MASK;
            }
        }

        return $row;
    }

    /** Racine du site : dossier du projet Bedrock (au-dessus de web/wp) ou de WordPress. */
    private function root(): string
    {
        $wordpress = rtrim(ABSPATH, '/');
        $bedrock = dirname($wordpress, 2);

        return (string) realpath(is_file($bedrock . '/composer.json') && is_dir($bedrock . '/web') ? $bedrock : $wordpress);
    }

    private function relative(string $file): string
    {
        return ltrim(str_replace($this->root(), '', $file), '/');
    }

    /**
     * @return list<string>
     */
    private function tail(string $file, int $lines): array
    {
        $handle = new \SplFileObject($file, 'r');
        $handle->seek(PHP_INT_MAX);
        $last = $handle->key();
        $output = [];
        for ($line = max(0, $last - $lines); $line <= $last; ++$line) {
            $handle->seek($line);
            $output[] = rtrim((string) $handle->current(), "\r\n");
        }

        return array_values(array_filter($output, static fn (string $line): bool => '' !== $line));
    }
}
