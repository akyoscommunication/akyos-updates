<?php

namespace AkyosUpdates\Core;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\Seo\SeoIndexabilityCheck;
use AkyosUpdates\Core\Context\InstallationContextDetector;
use AkyosUpdates\Service\ToolAvailabilityService;
use AkyosUpdates\Service\JsonService;
use AkyosUpdates\Service\PluginService;

final class Maintenance
{
    public const SESSION_PREFIX = 'akyos_updates_session_';
    public const ANALYSIS_MODE_FULL = 'full';
    public const ANALYSIS_MODE_QUICK = 'quick';
    public const ANALYSIS_MODE_CATEGORY = 'category';

    /** @var CheckInterface[] */
    private array $checks;

    /** @param CheckInterface[] $checks */
    public function __construct(
        private InstallationContextDetector $detector,
        array $checks
    ) {
        $this->checks = array_values($checks);
    }

    public function getSiteOverview(): array
    {
        $context = $this->detector->detect();

        return array_merge(
            $context->toArray(),
            ToolAvailabilityService::probe(
                $context->getProjectRootPath(),
                $context->getWordpressRootPath()
            )
        );
    }

    /**
     * Lance une analyse complète de façon synchrone, persiste le rapport et retourne le payload CRM.
     *
     * @param list<string>|null $categories Sous-ensemble de catégories en mode full (null = toutes).
     *
     * @return array{updatedAt: string, overview: array, summary: array{total: int, ok: int, warn: int, fail: int}, categories: array, checks: array, pluginPresence: mixed}
     */
    public function runFullAnalysis(?array $categories = null): array
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(120);
        }

        $start = $this->startAnalysisByMode(self::ANALYSIS_MODE_FULL, null, $categories);
        $sessionId = (string) ($start['sessionId'] ?? '');
        if ($sessionId === '') {
            throw new \RuntimeException('Impossible de démarrer l\'analyse.');
        }

        do {
            $step = $this->runNextStep($sessionId);
        } while (($step['done'] ?? false) !== true);

        if (isset($step['error']) && is_string($step['error'])) {
            throw new \RuntimeException($step['error']);
        }

        $report = is_array($step['report'] ?? null) ? $step['report'] : [];
        $stored = get_option('akyos_updates_last_report', []);
        $updatedAt = is_array($stored) && is_string($stored['updatedAt'] ?? null)
            ? $stored['updatedAt']
            : gmdate('c');
        $overview = $this->getSiteOverview();
        $categoriesStats = is_array($report['categories'] ?? null) ? $report['categories'] : [];

        return [
            'updatedAt' => $updatedAt,
            'overview' => JsonService::mixed($overview),
            'summary' => self::computeGlobalSummary($categoriesStats),
            'categories' => JsonService::mixed($categoriesStats),
            'checks' => JsonService::mixed(is_array($report['results'] ?? null) ? $report['results'] : []),
            'pluginPresence' => JsonService::mixed($report['pluginPresence'] ?? null),
        ];
    }

    /**
     * Dernier rapport persisté, format interne (overview + results + report).
     *
     * @return array<string, mixed>
     */
    public function getPersistedReportPayload(): array
    {
        $last = get_option('akyos_updates_last_report', []);
        if (!is_array($last) || $last === []) {
            return [];
        }

        $results = is_array($last['results'] ?? null) ? $last['results'] : [];
        if ($this->isReportOutdatedForCurrentChecks($results)) {
            return [];
        }

        return $this->getReport('');
    }

    /**
     * @param array<string, array{total?: int, ok?: int, warn?: int, fail?: int}> $categories
     *
     * @return array{total: int, ok: int, warn: int, fail: int}
     */
    public static function computeGlobalSummary(array $categories): array
    {
        $summary = ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0];
        foreach ($categories as $stats) {
            if (!is_array($stats)) {
                continue;
            }
            foreach (array_keys($summary) as $key) {
                $summary[$key] += (int) ($stats[$key] ?? 0);
            }
        }

        return $summary;
    }

    public function startAnalysis(): array
    {
        return $this->startAnalysisByMode(self::ANALYSIS_MODE_FULL);
    }

    public function startAnalysisByMode(string $mode, ?string $categoryName = null, ?array $fullCategories = null): array
    {
        $mode = trim($mode);
        $categoryName = $categoryName !== null ? trim($categoryName) : '';

        if ($mode === self::ANALYSIS_MODE_CATEGORY) {
            $normalizedMode = self::ANALYSIS_MODE_CATEGORY;
            $checksToRun = ($categoryName !== '' && $this->isKnownReportCategory($categoryName))
                ? $this->resolveChecksForCategory($categoryName)
                : [];
            $selectedCategoriesForState = [];
            $partialFull = false;
        } elseif ($mode === self::ANALYSIS_MODE_QUICK) {
            $normalizedMode = self::ANALYSIS_MODE_QUICK;
            $checksToRun = $this->resolveChecksForMode(self::ANALYSIS_MODE_QUICK);
            $selectedCategoriesForState = $this->readIncludedCategoriesFromLastReport();
            $partialFull = false;
        } else {
            $normalizedMode = self::ANALYSIS_MODE_FULL;
            $fullSelection = $this->normalizeFullCategorySelection($fullCategories);
            if ($fullSelection === null) {
                $checksToRun = $this->checks;
                $selectedCategoriesForState = self::knownReportCategories();
                $partialFull = false;
            } else {
                $checksToRun = $this->resolveChecksForCategories($fullSelection);
                $selectedCategoriesForState = $fullSelection;
                $partialFull = count($checksToRun) < count($this->checks);
            }
        }

        $checkIds = array_map(
            static fn(CheckInterface $check): string => $check->getId(),
            $checksToRun
        );

        $sessionId = wp_generate_uuid4();
        $state = [
            'cursor' => 0,
            'results' => [],
            'createdAt' => gmdate('c'),
            'mode' => $normalizedMode,
            'categoryName' => $normalizedMode === self::ANALYSIS_MODE_CATEGORY ? $categoryName : '',
            'checkIds' => $checkIds,
            'selectedCategories' => $selectedCategoriesForState,
            'partialFull' => $partialFull,
        ];

        set_transient(self::SESSION_PREFIX . $sessionId, $state, HOUR_IN_SECONDS);

        return [
            'sessionId' => $sessionId,
            'totalChecks' => count($checksToRun),
            'overview' => $this->getSiteOverview(),
            'mode' => $normalizedMode,
            'checkMeta' => array_map(
                static fn(CheckInterface $check): array => self::toCheckMeta($check),
                $checksToRun
            ),
        ];
    }

    public function runNextStep(string $sessionId): array
    {
        $transientKey = self::SESSION_PREFIX . $sessionId;
        $state = get_transient($transientKey);

        if (!is_array($state)) {
            return [
                'done' => true,
                'error' => 'Session expirée ou introuvable.',
            ];
        }

        $checkIds = array_values(array_filter(
            is_array($state['checkIds'] ?? null) ? $state['checkIds'] : [],
            static fn(mixed $id): bool => is_string($id) && $id !== ''
        ));
        $checksById = $this->indexChecksById();
        $checksToRun = $this->resolveChecksFromIds($checkIds, $checksById);

        $cursor = (int) ($state['cursor'] ?? 0);
        if (!isset($checksToRun[$cursor])) {
            return $this->finalizeAnalysisSession($transientKey, $state);
        }

        $context = $this->detector->detect();
        $check = $checksToRun[$cursor];
        $lastResult = JsonService::mixed($check->run($context)->toArray());
        $state['results'][] = $lastResult;
        $cursor++;

        if (!isset($checksToRun[$cursor])) {
            $state['cursor'] = $cursor;
            set_transient($transientKey, $state, HOUR_IN_SECONDS);

            return $this->finalizeAnalysisSession($transientKey, $state);
        }

        $state['cursor'] = $cursor;
        set_transient($transientKey, $state, HOUR_IN_SECONDS);

        $nextCheck = $checksToRun[$cursor] ?? null;

        return [
            'done' => false,
            'cursor' => $cursor,
            'totalChecks' => count($checksToRun),
            'event' => [
                'checkId' => $check->getId(),
                'category' => $check->getCategory(),
                'title' => $check->getTitle(),
                'message' => $lastResult['message'] ?? '',
                'status' => $lastResult['status'] ?? '',
                'severity' => $lastResult['severity'] ?? '',
                'nextCheck' => $nextCheck instanceof CheckInterface ? self::toCheckMeta($nextCheck) : null,
            ],
            'result' => $lastResult,
        ];
    }

    private function finalizeAnalysisSession(string $transientKey, array $state): array
    {
        $finalResults = $this->buildFinalResultsForStorage($state);
        $included = $this->resolveIncludedCategoriesForReport($state);
        $report = $this->buildReport($finalResults, true, $included);
        $context = $this->detector->detect();
        update_option('akyos_updates_last_report', [
            'updatedAt' => gmdate('c'),
            'overview' => JsonService::mixed($context->toArray()),
            'results' => JsonService::mixed($finalResults),
            'report' => $report,
        ], false);
        delete_transient($transientKey);

        return [
            'done' => true,
            'report' => $report,
        ];
    }

    private static function toCheckMeta(CheckInterface $check): array
    {
        return [
            'id' => $check->getId(),
            'category' => $check->getCategory(),
            'title' => $check->getTitle(),
        ];
    }

    public function getReport(string $sessionId): array
    {
        $state = get_transient(self::SESSION_PREFIX . $sessionId);
        if (!is_array($state)) {
            $last = get_option('akyos_updates_last_report', []);
            if (!is_array($last)) {
                return [];
            }

            $results = is_array($last['results'] ?? null) ? $last['results'] : [];
            if ($this->isReportOutdatedForCurrentChecks($results)) {
                return [];
            }

            $results = $this->normalizeResultsForDisplay($results);

            return [
                'updatedAt' => is_string($last['updatedAt'] ?? null) ? $last['updatedAt'] : gmdate('c'),
                'overview' => is_array($last['overview'] ?? null) ? $last['overview'] : [],
                'results' => $results,
                'report' => $this->buildReport($results, true, $this->readIncludedCategoriesFromStoredReport($last)),
            ];
        }

        $results = is_array($state['results'] ?? null) ? $state['results'] : [];
        $results = $this->normalizeResultsForDisplay($results);
        $included = $this->resolveIncludedCategoriesForReport($state);

        return [
            'overview' => $this->getSiteOverview(),
            'results' => $results,
            'report' => $this->buildReport($results, true, $included),
        ];
    }

    private function normalizeResultsForDisplay(array $results): array
    {
        return array_map(
            static fn(array $row): array => SeoIndexabilityCheck::normalizeStoredResult($row),
            $results
        );
    }

    private function buildReport(array $results, bool $includePluginPresence = true, ?array $includedCategories = null): array
    {
        $categories = [
            'WordPress' => ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0],
            'Images' => ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0],
            'Plugins' => ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0],
            'Performance' => ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0],
            'Sécurité' => ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0],
            'SEO' => ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0],
            'Back-office' => ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0],
            'RGPD' => ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0],
        ];

        foreach ($results as $result) {
            if (($result['countsTowardCategoryStats'] ?? true) === false || ($result['id'] ?? '') === 'seo.indexability') {
                continue;
            }
            $category = $result['category'] ?? 'Back-office';
            if (!isset($categories[$category])) {
                $categories[$category] = ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0];
            }
            $status = $result['status'] ?? 'warn';
            if (!isset($categories[$category][$status])) {
                $status = 'warn';
            }
            $categories[$category]['total']++;
            $categories[$category][$status]++;
        }

        $included = $includedCategories ?? $this->deriveIncludedCategoriesFromResults($results);

        return [
            'generatedAt' => gmdate('c'),
            'categories' => $categories,
            'results' => $results,
            'pluginPresence' => $includePluginPresence ? PluginService::capture() : null,
            'includedCategories' => $included,
        ];
    }

    private function isReportOutdatedForCurrentChecks(array $results): bool
    {
        $expectedIds = array_map(
            static fn(CheckInterface $check): string => $check->getId(),
            $this->checks
        );
        $resultIds = array_values(array_filter(array_map(
            static fn(array $result): string => (string) ($result['id'] ?? ''),
            $results
        )));

        sort($expectedIds);
        sort($resultIds);

        return $expectedIds !== $resultIds;
    }

    /** @return CheckInterface[] */
    private function resolveChecksForCategory(string $categoryName): array
    {
        $out = [];
        foreach ($this->checks as $check) {
            if ($check->getCategory() === $categoryName) {
                $out[] = $check;
            }
        }

        return $out;
    }

    /** @return list<string> */
    public static function knownReportCategories(): array
    {
        return [
            'WordPress',
            'Images',
            'Plugins',
            'Performance',
            'Sécurité',
            'SEO',
            'Back-office',
            'RGPD',
        ];
    }

    private function isKnownReportCategory(string $name): bool
    {
        return in_array($name, self::knownReportCategories(), true);
    }

    /** @return CheckInterface[] */
    private function resolveChecksForMode(string $mode): array
    {
        if ($mode !== self::ANALYSIS_MODE_QUICK) {
            return $this->checks;
        }

        $last = get_option('akyos_updates_last_report', []);
        $results = is_array($last['results'] ?? null) ? $last['results'] : [];
        if ($results === []) {
            return $this->checks;
        }

        $idsToRecheck = [];
        foreach ($results as $result) {
            $status = (string) ($result['status'] ?? '');
            $id = (string) ($result['id'] ?? '');
            if ($id === '') {
                continue;
            }
            if ($status === 'skipped' || $status === 'ok' || (($result['countsTowardCategoryStats'] ?? true) === false) || $id === 'seo.indexability') {
                continue;
            }
            $idsToRecheck[] = $id;
        }

        if ($idsToRecheck === []) {
            return [];
        }

        $checksById = $this->indexChecksById();
        return $this->resolveChecksFromIds($idsToRecheck, $checksById);
    }

    /** @return array<string, CheckInterface> */
    private function indexChecksById(): array
    {
        $checksById = [];
        foreach ($this->checks as $check) {
            $checksById[$check->getId()] = $check;
        }

        return $checksById;
    }

    /** @param array<string, CheckInterface> $checksById */
    private function resolveChecksFromIds(array $checkIds, array $checksById): array
    {
        $checks = [];
        foreach ($checkIds as $checkId) {
            if (!isset($checksById[$checkId])) {
                continue;
            }
            $checks[] = $checksById[$checkId];
        }

        return $checks;
    }

    private function buildFinalResultsForStorage(array $state): array
    {
        $mode = (string) ($state['mode'] ?? self::ANALYSIS_MODE_FULL);
        $currentResults = is_array($state['results'] ?? null) ? $state['results'] : [];
        if ($mode === self::ANALYSIS_MODE_FULL && !empty($state['partialFull'])) {
            $selected = is_array($state['selectedCategories'] ?? null) ? $state['selectedCategories'] : [];

            return $this->mergeFullPartialWithSkipped($currentResults, $selected);
        }
        if ($mode !== self::ANALYSIS_MODE_QUICK && $mode !== self::ANALYSIS_MODE_CATEGORY) {
            return $currentResults;
        }

        $last = get_option('akyos_updates_last_report', []);
        $lastResults = is_array($last['results'] ?? null) ? $last['results'] : [];
        if ($lastResults === []) {
            return $currentResults;
        }

        $currentById = [];
        foreach ($currentResults as $result) {
            $id = (string) ($result['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $currentById[$id] = $result;
        }

        $merged = [];
        foreach ($lastResults as $result) {
            $id = (string) ($result['id'] ?? '');
            if ($id !== '' && isset($currentById[$id])) {
                $merged[] = $currentById[$id];
                unset($currentById[$id]);
                continue;
            }
            $merged[] = $result;
        }

        foreach ($currentById as $result) {
            $merged[] = $result;
        }

        return $merged;
    }

    private function normalizeFullCategorySelection(?array $input): ?array
    {
        if ($input === null) {
            return null;
        }

        $known = array_flip(self::knownReportCategories());
        $out = [];
        foreach ($input as $c) {
            if (!is_string($c)) {
                continue;
            }
            $t = trim($c);
            if ($t !== '' && isset($known[$t])) {
                $out[$t] = true;
            }
        }

        return array_keys($out);
    }

    /** @return CheckInterface[] */
    private function resolveChecksForCategories(array $categoryNames): array
    {
        $set = array_flip($categoryNames);
        $out = [];
        foreach ($this->checks as $check) {
            if (isset($set[$check->getCategory()])) {
                $out[] = $check;
            }
        }

        return $out;
    }

    private function mergeFullPartialWithSkipped(array $currentResults, array $selectedCategories): array
    {
        $selectedSet = array_flip($selectedCategories);
        $byId = [];
        foreach ($currentResults as $r) {
            $id = (string) ($r['id'] ?? '');
            if ($id !== '') {
                $byId[$id] = $r;
            }
        }
        $merged = [];
        foreach ($this->checks as $check) {
            if (isset($selectedSet[$check->getCategory()])) {
                $id = $check->getId();
                $merged[] = $byId[$id] ?? self::syntheticSkippedFromCheck($check);
            } else {
                $merged[] = self::syntheticSkippedFromCheck($check);
            }
        }

        return $merged;
    }

    private static function syntheticSkippedFromCheck(CheckInterface $check): array
    {
        return JsonService::mixed([
            'id' => $check->getId(),
            'category' => $check->getCategory(),
            'title' => $check->getTitle(),
            'status' => 'skipped',
            'severity' => 'neutral',
            'message' => 'Non analysé : catégorie non incluse dans cette passe.',
            'actionable' => false,
            'actionId' => null,
            'payload' => [],
            'countsTowardCategoryStats' => false,
        ]);
    }

    /** @return list<string> */
    private function readIncludedCategoriesFromLastReport(): array
    {
        $last = get_option('akyos_updates_last_report', []);
        if (!is_array($last)) {
            return self::knownReportCategories();
        }
        $r = $last['report'] ?? null;
        if (is_array($r) && is_array($r['includedCategories'] ?? null) && $r['includedCategories'] !== []) {
            return array_values($r['includedCategories']);
        }

        return self::knownReportCategories();
    }

    /** @return list<string> */
    private function readIncludedCategoriesFromStoredReport(array $last): array
    {
        $r = $last['report'] ?? null;
        if (is_array($r) && is_array($r['includedCategories'] ?? null) && $r['includedCategories'] !== []) {
            return array_values($r['includedCategories']);
        }

        $results = is_array($last['results'] ?? null) ? $last['results'] : [];

        return $this->deriveIncludedCategoriesFromResults($results);
    }

    /** @return list<string> */
    private function deriveIncludedCategoriesFromResults(array $results): array
    {
        $out = [];
        foreach ($results as $result) {
            if (($result['status'] ?? '') === 'skipped') {
                continue;
            }
            $cat = (string) ($result['category'] ?? '');
            if ($cat !== '') {
                $out[$cat] = true;
            }
        }

        return array_keys($out);
    }

    /** @return list<string> */
    private function resolveIncludedCategoriesForReport(array $state): array
    {
        $mode = (string) ($state['mode'] ?? '');
        if ($mode === self::ANALYSIS_MODE_CATEGORY) {
            return $this->readIncludedCategoriesFromLastReport();
        }
        $sc = $state['selectedCategories'] ?? null;
        if (is_array($sc) && $sc !== []) {
            return array_values($sc);
        }

        return self::knownReportCategories();
    }
}
