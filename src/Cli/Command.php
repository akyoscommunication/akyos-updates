<?php

namespace AkyosUpdates\Cli;

use AkyosUpdates\Core\Maintenance;
use AkyosUpdates\Service\FixRunnerService;

final class Command
{
    public function __construct(
        private Maintenance $analyzer,
        private FixRunnerService $fixRunner
    ) {
    }

    /**
     * Lance l’analyse de maintenance (équivalent de l’écran admin).
     *
     * ## OPTIONS
     *
     * [--mode=<mode>]
     * : full (défaut), quick (recheck des points non OK), category.
     *
     * [--category=<category>]
     * : Catégorie unique si --mode=category (WordPress, Images, Plugins, Performance, Sécurité, SEO, Back-office, RGPD).
     *
     * [--categories=<categories>]
     * : Sous-ensemble en mode full, séparées par des virgules.
     *
     * [--fix]
     * : Applique ensuite les correctifs automatiques du rapport.
     *
     * [--dry-run]
     * : Avec --fix : affiche le plan sans exécuter les actions.
     *
     * [--only=<ids>]
     * : Avec --fix : limite aux check IDs (virgules).
     *
     * [--format=<format>]
     * : table (défaut) ou json.
     *
     * ## EXAMPLES
     *
     *     wp akyos-updates analyze
     *     wp akyos-updates analyze --fix
     *     wp akyos-updates analyze --mode=quick --fix --dry-run
     *     wp akyos-updates analyze --categories=WordPress,Sécurité
     *
     * @when after_wp_load
     */
    public function analyze(array $args, array $assocArgs): void
    {
        $this->prepareRuntime();
        $asJson = ($assocArgs['format'] ?? 'table') === 'json';

        $mode = trim((string) ($assocArgs['mode'] ?? Maintenance::ANALYSIS_MODE_FULL));
        if ($mode === '') {
            $mode = Maintenance::ANALYSIS_MODE_FULL;
        }

        $category = isset($assocArgs['category']) ? trim((string) $assocArgs['category']) : '';
        $fullCategories = $this->parseCategories($assocArgs['categories'] ?? null);

        if ($mode === Maintenance::ANALYSIS_MODE_CATEGORY) {
            if ($category === '') {
                \WP_CLI::error('Paramètre --category requis pour --mode=category.');
            }
            if (!in_array($category, Maintenance::knownReportCategories(), true)) {
                \WP_CLI::error('Catégorie inconnue. Attendues : ' . implode(', ', Maintenance::knownReportCategories()));
            }
        }

        if ($mode === Maintenance::ANALYSIS_MODE_FULL && $fullCategories !== null && $fullCategories === []) {
            \WP_CLI::error('Sélectionne au moins une catégorie.');
        }

        $start = $this->analyzer->startAnalysisByMode(
            $mode,
            $category !== '' ? $category : null,
            $fullCategories
        );
        $sessionId = (string) ($start['sessionId'] ?? '');
        if ($sessionId === '') {
            \WP_CLI::error('Impossible de démarrer l’analyse.');
        }

        $total = (int) ($start['totalChecks'] ?? 0);
        $progress = null;
        if (!$asJson && $total > 0 && class_exists('\WP_CLI\Utils')) {
            $progress = \WP_CLI\Utils\make_progress_bar('Analyse', $total);
        } elseif (!$asJson) {
            \WP_CLI::log(sprintf('Analyse : %d check(s).', $total));
        }

        $step = ['done' => false];
        do {
            $step = $this->analyzer->runNextStep($sessionId);
            if ($progress && !empty($step['event'])) {
                $progress->tick();
            }
        } while (($step['done'] ?? false) !== true);

        if ($progress) {
            $progress->finish();
        }

        if (isset($step['error']) && is_string($step['error'])) {
            \WP_CLI::error($step['error']);
        }

        $report = is_array($step['report'] ?? null) ? $step['report'] : [];
        $this->printAnalysisOutcome($report, $asJson && empty($assocArgs['fix']));
        if (!$asJson && empty($assocArgs['fix'])) {
            \WP_CLI::success('Analyse terminée.');
        }

        if (!empty($assocArgs['fix'])) {
            $this->fix([], $assocArgs);
        }
    }

    /**
     * Affiche le dernier rapport persisté.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : table (défaut) ou json.
     *
     * ## EXAMPLES
     *
     *     wp akyos-updates report
     *     wp akyos-updates report --format=json
     *
     * @when after_wp_load
     */
    public function report(array $args, array $assocArgs): void
    {
        $asJson = ($assocArgs['format'] ?? 'table') === 'json';
        $stored = $this->analyzer->getReport('');
        if ($stored === []) {
            \WP_CLI::error('Aucun rapport. Lance `wp akyos-updates analyze`.');
        }

        $report = is_array($stored['report'] ?? null) ? $stored['report'] : [];
        $this->printAnalysisOutcome($report, $asJson);
    }

    /**
     * Applique les correctifs automatiques du dernier rapport.
     *
     * Ignore les actions qui demandent une saisie (favicon, SMTP, reCAPTCHA, etc.).
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Affiche le plan sans exécuter.
     *
     * [--only=<ids>]
     * : Limite aux check IDs, séparés par des virgules.
     *
     * [--action=<actionId>]
     * : Exécute une action précise (ignore le plan auto).
     *
     * [--check=<checkId>]
     * : Avec --action : rafraîchit ce check dans le rapport.
     *
     * [--payload=<json>]
     * : Avec --action : JSON du payload.
     *
     * [--format=<format>]
     * : table (défaut) ou json.
     *
     * ## EXAMPLES
     *
     *     wp akyos-updates fix
     *     wp akyos-updates fix --dry-run
     *     wp akyos-updates fix --only=wordpress.comments_disabled,wordpress.translations
     *     wp akyos-updates fix --action=wordpress.disable_comments --check=wordpress.comments_disabled
     *
     * @when after_wp_load
     */
    public function fix(array $args, array $assocArgs): void
    {
        $this->prepareRuntime();
        $asJson = ($assocArgs['format'] ?? 'table') === 'json';
        $dryRun = isset($assocArgs['dry-run']);

        if (isset($assocArgs['action'])) {
            $this->runSingleAction($assocArgs, $asJson, $dryRun);

            return;
        }

        $results = $this->reportResults();
        $only = $this->parseList($assocArgs['only'] ?? null);
        $applied = [];
        $failed = [];
        $skipped = [];
        $seenSkip = [];

        // ponytail: 3 passes max — checks chaînés (Branda enable → hide widgets).
        for ($pass = 1; $pass <= 3; $pass++) {
            $plan = AutoFixPlanner::plan($results, $only);
            foreach ($plan['skip'] as $row) {
                $key = ($row['checkId'] ?? '') . '|' . ($row['actionId'] ?? '') . '|' . ($row['reason'] ?? '');
                if (isset($seenSkip[$key])) {
                    continue;
                }
                $seenSkip[$key] = true;
                $skipped[] = $row;
            }
            if ($plan['run'] === []) {
                break;
            }

            foreach ($plan['run'] as $job) {
                if ($dryRun) {
                    $applied[] = array_merge($job, ['message' => '(dry-run)', 'success' => true]);
                    continue;
                }
                $response = $this->fixRunner->run(
                    (string) $job['actionId'],
                    is_array($job['payload'] ?? null) ? $job['payload'] : [],
                    (string) $job['checkId']
                );
                $row = array_merge($job, [
                    'success' => (bool) ($response['success'] ?? false),
                    'message' => (string) ($response['message'] ?? ''),
                ]);
                if ($row['success']) {
                    $applied[] = $row;
                } else {
                    $failed[] = $row;
                }
            }

            if ($dryRun) {
                break;
            }
            $results = $this->reportResults();
        }

        $payload = [
            'dryRun' => $dryRun,
            'applied' => $applied,
            'failed' => $failed,
            'skipped' => $skipped,
        ];

        if ($asJson) {
            \WP_CLI::print_value($payload, ['format' => 'json']);
            if ($failed !== []) {
                \WP_CLI::halt(1);
            }

            return;
        }

        $this->printFixOutcome($payload);

        if ($failed !== []) {
            \WP_CLI::error(sprintf('%d correctif(s) en échec.', count($failed)), false);
            \WP_CLI::halt(1);
        }

        if ($applied === [] && $skipped === []) {
            \WP_CLI::success('Rien à corriger automatiquement.');

            return;
        }

        \WP_CLI::success($dryRun
            ? sprintf('Plan : %d auto, %d à traiter à la main.', count($applied), count($skipped))
            : sprintf('%d correctif(s) appliqué(s), %d ignoré(s).', count($applied), count($skipped)));
    }

    /** @param array<string, mixed> $assocArgs */
    private function runSingleAction(array $assocArgs, bool $asJson, bool $dryRun): void
    {
        $actionId = trim((string) $assocArgs['action']);
        if ($actionId === '') {
            \WP_CLI::error('--action vide.');
        }

        $payload = [];
        if (isset($assocArgs['payload'])) {
            $decoded = json_decode((string) $assocArgs['payload'], true);
            if (!is_array($decoded)) {
                \WP_CLI::error('--payload n’est pas un JSON objet.');
            }
            $payload = $decoded;
        }
        $checkId = trim((string) ($assocArgs['check'] ?? ''));

        if ($dryRun) {
            $row = [
                'actionId' => $actionId,
                'checkId' => $checkId,
                'payload' => $payload,
                'message' => '(dry-run)',
            ];
            if ($asJson) {
                \WP_CLI::print_value($row, ['format' => 'json']);
            } else {
                \WP_CLI::log(sprintf('[dry-run] %s%s', $actionId, $checkId !== '' ? ' ← ' . $checkId : ''));
            }
            \WP_CLI::success('Simulation OK.');

            return;
        }

        $response = $this->fixRunner->run($actionId, $payload, $checkId);
        if ($asJson) {
            \WP_CLI::print_value($response, ['format' => 'json']);
        } else {
            $message = (string) ($response['message'] ?? '');
            if (!empty($response['success'])) {
                \WP_CLI::success($message !== '' ? $message : $actionId);
            } else {
                \WP_CLI::error($message !== '' ? $message : 'Action en échec.');
            }
        }

        if (empty($response['success'])) {
            \WP_CLI::halt(1);
        }
    }

    private function prepareRuntime(): void
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        if (function_exists('wp_raise_memory_limit')) {
            wp_raise_memory_limit('admin');
        }
        $this->ensureAdminUser();
    }

    private function ensureAdminUser(): void
    {
        if (function_exists('current_user_can') && current_user_can('manage_options')) {
            return;
        }

        $users = get_users([
            'role' => 'administrator',
            'number' => 1,
            'orderby' => 'ID',
            'order' => 'ASC',
        ]);
        if ($users === []) {
            \WP_CLI::warning('Aucun administrateur trouvé. Passe --user=<id> si une action échoue sur les droits.');

            return;
        }

        wp_set_current_user((int) $users[0]->ID);
    }

    /** @return list<array<string, mixed>> */
    private function reportResults(): array
    {
        $stored = $this->analyzer->getReport('');
        if ($stored === []) {
            \WP_CLI::error('Aucun rapport. Lance `wp akyos-updates analyze`.');
        }
        $results = $stored['results'] ?? null;
        if (!is_array($results)) {
            $report = is_array($stored['report'] ?? null) ? $stored['report'] : [];
            $results = $report['results'] ?? [];
        }

        return is_array($results) ? array_values($results) : [];
    }

    /** @param mixed $raw */
    private function parseCategories($raw): ?array
    {
        $list = $this->parseList($raw);
        if ($list === []) {
            return $raw === null || $raw === '' ? null : [];
        }

        foreach ($list as $item) {
            if (!in_array($item, Maintenance::knownReportCategories(), true)) {
                \WP_CLI::error('Catégorie inconnue : ' . $item);
            }
        }

        return $list;
    }

    /**
     * @param mixed $raw
     * @return list<string>
     */
    private function parseList($raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }
        $parts = is_array($raw) ? $raw : explode(',', (string) $raw);
        $out = [];
        foreach ($parts as $part) {
            if (!is_string($part) && !is_numeric($part)) {
                continue;
            }
            $t = trim((string) $part);
            if ($t !== '') {
                $out[] = $t;
            }
        }

        return array_values(array_unique($out));
    }

    /** @param array<string, mixed> $report */
    private function printAnalysisOutcome(array $report, bool $asJson): void
    {
        $categories = is_array($report['categories'] ?? null) ? $report['categories'] : [];
        $summary = Maintenance::computeGlobalSummary($categories);
        $results = is_array($report['results'] ?? null) ? $report['results'] : [];

        if ($asJson) {
            \WP_CLI::print_value([
                'summary' => $summary,
                'categories' => $categories,
                'issues' => $this->issueRows($results),
            ], ['format' => 'json']);

            return;
        }

        $rows = [];
        foreach ($categories as $name => $stats) {
            if (!is_array($stats)) {
                continue;
            }
            $rows[] = [
                'categorie' => (string) $name,
                'ok' => (int) ($stats['ok'] ?? 0),
                'warn' => (int) ($stats['warn'] ?? 0),
                'fail' => (int) ($stats['fail'] ?? 0),
                'total' => (int) ($stats['total'] ?? 0),
            ];
        }
        if ($rows !== [] && class_exists('\WP_CLI\Utils')) {
            \WP_CLI\Utils\format_items('table', $rows, ['categorie', 'ok', 'warn', 'fail', 'total']);
        }

        $issues = $this->issueRows($results);
        if ($issues !== []) {
            \WP_CLI::log('');
            \WP_CLI::log('Points à traiter :');
            if (class_exists('\WP_CLI\Utils')) {
                \WP_CLI\Utils\format_items('table', $issues, ['status', 'categorie', 'id', 'titre', 'action']);
            }
        }

        \WP_CLI::log(sprintf(
            'Total %d — ok %d / warn %d / fail %d',
            $summary['total'],
            $summary['ok'],
            $summary['warn'],
            $summary['fail']
        ));
    }

    /**
     * @param list<array<string, mixed>> $results
     * @return list<array<string, string>>
     */
    private function issueRows(array $results): array
    {
        $rows = [];
        foreach ($results as $result) {
            if (!is_array($result)) {
                continue;
            }
            $status = (string) ($result['status'] ?? '');
            if ($status !== 'fail' && $status !== 'warn') {
                continue;
            }
            if (($result['countsTowardCategoryStats'] ?? true) === false) {
                continue;
            }
            $rows[] = [
                'status' => $status,
                'categorie' => (string) ($result['category'] ?? ''),
                'id' => (string) ($result['id'] ?? ''),
                'titre' => (string) ($result['title'] ?? ''),
                'action' => (string) ($result['actionId'] ?? ''),
            ];
        }

        return $rows;
    }

    /** @param array{dryRun: bool, applied: list<array>, failed: list<array>, skipped: list<array>} $payload */
    private function printFixOutcome(array $payload): void
    {
        foreach ($payload['applied'] as $row) {
            \WP_CLI::log(sprintf(
                '%s %s (%s) — %s',
                $payload['dryRun'] ? '[dry-run]' : 'OK',
                $row['title'] ?? $row['checkId'] ?? '',
                $row['actionId'] ?? '',
                $row['message'] ?? ''
            ));
        }
        foreach ($payload['failed'] as $row) {
            \WP_CLI::warning(sprintf(
                'ÉCHEC %s (%s) — %s',
                $row['title'] ?? $row['checkId'] ?? '',
                $row['actionId'] ?? '',
                $row['message'] ?? ''
            ));
        }
        foreach ($payload['skipped'] as $row) {
            \WP_CLI::log(sprintf(
                'SKIP %s (%s) — %s',
                $row['title'] ?? $row['checkId'] ?? '',
                $row['actionId'] ?? '',
                $row['reason'] ?? ''
            ));
        }
    }
}
