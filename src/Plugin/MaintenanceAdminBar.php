<?php

namespace AkyosUpdates\Plugin;

use AkyosUpdates\Core\Maintenance;
use AkyosUpdates\Service\EnvironmentService;

final class MaintenanceAdminBar
{
    private const MAX_CRITICAL_ITEMS = 3;
    private const MAX_WARNING_ITEMS = 3;

    /** @var list<string> */
    private const PRIORITY_IDS = ['seo.site_indexing'];

    public function __construct(private Maintenance $analyzer)
    {
    }

    public function register(): void
    {
        add_action('admin_bar_menu', [$this, 'renderMenu'], 90);
        add_action('admin_enqueue_scripts', [$this, 'enqueueStyles']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueStyles']);
    }

    public function enqueueStyles(): void
    {
        if (! is_admin_bar_showing() || ! current_user_can('manage_options')) {
            return;
        }

        $cssPath = AKYOS_UPDATES_PLUGIN_DIR . 'assets/admin-bar.css';
        $version = is_readable($cssPath) ? (string) filemtime($cssPath) : AKYOS_UPDATES_VERSION;

        wp_enqueue_style(
            'akyos-updates-admin-bar',
            AKYOS_UPDATES_PLUGIN_URL . 'assets/admin-bar.css',
            [],
            $version
        );
    }

    /** @param \WP_Admin_Bar $wp_admin_bar */
    public function renderMenu($wp_admin_bar): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $summary = $this->analyzer->getStoredAlertSummary();
        $maintenanceUrl = admin_url('admin.php?page=' . AdminPage::PAGE_MAINTENANCE);
        $criticalCount = (int) ($summary['criticalCount'] ?? 0);
        $warningCount = (int) ($summary['warningCount'] ?? 0);
        $hasIssues = $criticalCount > 0 || $warningCount > 0;

        if (! $hasIssues) {
            if (! ($summary['hasReport'] ?? false)) {
                $wp_admin_bar->add_node([
                    'id' => 'akyos-updates-maintenance-status',
                    'title' => $this->renderChip('idle', 0, 0),
                    'href' => $maintenanceUrl,
                    'meta' => [
                        'class' => 'au-ab-root au-ab-root--idle',
                        'title' => 'Aucune analyse — lancer Akyos Updates Maintenance',
                    ],
                ]);
            }

            return;
        }

        $level = $criticalCount > 0 ? 'critical' : 'warning';

        $wp_admin_bar->add_node([
            'id' => 'akyos-updates-maintenance-status',
            'title' => $this->renderChip($level, $criticalCount, $warningCount),
            'href' => $maintenanceUrl,
            'meta' => [
                'class' => 'au-ab-root au-ab-root--' . $level,
                'title' => $this->buildTooltip($criticalCount, $warningCount),
            ],
        ]);

        $wp_admin_bar->add_node([
            'id' => 'akyos-updates-maintenance-head',
            'parent' => 'akyos-updates-maintenance-status',
            'title' => $this->renderPanelHeader($criticalCount, $warningCount),
            'href' => false,
            'meta' => ['class' => 'au-ab-panel-head'],
        ]);

        $criticalItems = $this->prioritize((array) ($summary['critical'] ?? []));
        $warningItems = $this->prioritize((array) ($summary['warnings'] ?? []));
        $shownCritical = array_slice($criticalItems, 0, self::MAX_CRITICAL_ITEMS);
        $shownWarnings = array_slice($warningItems, 0, self::MAX_WARNING_ITEMS);
        $hiddenCount = (count($criticalItems) - count($shownCritical)) + (count($warningItems) - count($shownWarnings));

        if ($shownCritical !== []) {
            $wp_admin_bar->add_node([
                'id' => 'akyos-updates-maintenance-section-critical',
                'parent' => 'akyos-updates-maintenance-status',
                'title' => 'Critiques',
                'href' => false,
                'meta' => ['class' => 'au-ab-panel-section au-ab-panel-section--critical'],
            ]);
            foreach ($shownCritical as $index => $result) {
                $this->addPanelItem($wp_admin_bar, $result, 'critical', $index);
            }
        }

        if ($shownWarnings !== []) {
            $wp_admin_bar->add_node([
                'id' => 'akyos-updates-maintenance-section-warning',
                'parent' => 'akyos-updates-maintenance-status',
                'title' => 'Alertes',
                'href' => false,
                'meta' => ['class' => 'au-ab-panel-section au-ab-panel-section--warning'],
            ]);
            foreach ($shownWarnings as $index => $result) {
                $this->addPanelItem($wp_admin_bar, $result, 'warning', $index + 100);
            }
        }

        if ($hiddenCount > 0) {
            $wp_admin_bar->add_node([
                'id' => 'akyos-updates-maintenance-more',
                'parent' => 'akyos-updates-maintenance-status',
                'title' => sprintf('+%d point%s masqué%s', $hiddenCount, $hiddenCount > 1 ? 's' : '', $hiddenCount > 1 ? 's' : ''),
                'href' => $maintenanceUrl,
                'meta' => ['class' => 'au-ab-panel-more'],
            ]);
        }

        $wp_admin_bar->add_node([
            'id' => 'akyos-updates-maintenance-cta',
            'parent' => 'akyos-updates-maintenance-status',
            'title' => 'Ouvrir le rapport complet →',
            'href' => $maintenanceUrl,
            'meta' => ['class' => 'au-ab-panel-cta'],
        ]);
    }

    /** @param \WP_Admin_Bar $wp_admin_bar @param array<string, mixed> $result */
    private function addPanelItem($wp_admin_bar, array $result, string $level, int $index): void
    {
        $id = (string) ($result['id'] ?? ('item-' . $index));
        $featured = $id === 'seo.site_indexing';
        $classes = 'au-ab-panel-item au-ab-panel-item--' . $level;
        if ($featured) {
            $classes .= ' au-ab-panel-item--featured';
        }

        $wp_admin_bar->add_node([
            'id' => 'akyos-updates-maintenance-item-' . $id,
            'parent' => 'akyos-updates-maintenance-status',
            'title' => $this->renderPanelItem($result, $level, $featured),
            'href' => admin_url('admin.php?page=' . AdminPage::PAGE_MAINTENANCE),
            'meta' => [
                'class' => $classes,
                'title' => (string) ($result['message'] ?? ''),
            ],
        ]);
    }

    /**
     * @param list<array<string, mixed>> $items
     * @return list<array<string, mixed>>
     */
    private function prioritize(array $items): array
    {
        $priority = [];
        $rest = [];
        foreach ($items as $row) {
            if (! is_array($row)) {
                continue;
            }
            if (in_array((string) ($row['id'] ?? ''), self::PRIORITY_IDS, true)) {
                $priority[] = $row;
            } else {
                $rest[] = $row;
            }
        }

        return array_merge($priority, $rest);
    }

    private function buildTooltip(int $criticalCount, int $warningCount): string
    {
        $parts = [];
        if ($criticalCount > 0) {
            $parts[] = sprintf('%d critique%s', $criticalCount, $criticalCount > 1 ? 's' : '');
        }
        if ($warningCount > 0) {
            $parts[] = sprintf('%d alerte%s', $warningCount, $warningCount > 1 ? 's' : '');
        }

        return 'Maintenance Akyos — ' . implode(', ', $parts);
    }

    private function renderChip(string $level, int $criticalCount, int $warningCount): string
    {
        if ($level === 'idle') {
            return '<span class="au-ab-chip">'
                . '<span class="au-ab-chip__dot au-ab-chip__dot--idle" aria-hidden="true"></span>'
                . '<span class="au-ab-chip__label">Maintenance</span>'
                . '<span class="au-ab-chip__hint">Analyser</span>'
                . '</span>';
        }

        $summary = [];
        if ($criticalCount > 0) {
            $summary[] = '<span class="au-ab-chip__pill au-ab-chip__pill--critical" title="Critiques">'
                . esc_html((string) $criticalCount) . ' crit.'
                . '</span>';
        }
        if ($warningCount > 0) {
            $summary[] = '<span class="au-ab-chip__pill au-ab-chip__pill--warning" title="Alertes">'
                . esc_html((string) $warningCount) . ' alert.'
                . '</span>';
        }

        return '<span class="au-ab-chip">'
            . '<span class="au-ab-chip__dot au-ab-chip__dot--' . esc_attr($level) . '" aria-hidden="true"></span>'
            . '<span class="au-ab-chip__label">Maintenance</span>'
            . '<span class="au-ab-chip__counts">' . implode('', $summary) . '</span>'
            . '</span>';
    }

    private function renderPanelHeader(int $criticalCount, int $warningCount): string
    {
        $wpEnv = strtoupper(EnvironmentService::getWpEnv(EnvironmentService::detectProjectRootPath()));
        $envKind = EnvironmentService::getEnvKind();
        $envLabel = match ($envKind) {
            'production' => 'Site en ligne',
            'development' => 'Développement',
            'staging' => 'Staging',
            default => 'Environnement',
        };

        return '<span class="au-ab-panel-head__inner">'
            . '<span class="au-ab-panel-head__row">'
            . '<span class="au-ab-panel-head__title">Maintenance</span>'
            . '<span class="au-ab-panel-head__env au-ab-panel-head__env--' . esc_attr($envKind) . '">' . esc_html($wpEnv) . '</span>'
            . '</span>'
            . '<span class="au-ab-panel-head__subtitle">' . esc_html($envLabel) . '</span>'
            . '<span class="au-ab-panel-head__stats">'
            . ($criticalCount > 0
                ? '<span class="au-ab-panel-head__stat au-ab-panel-head__stat--critical">' . esc_html((string) $criticalCount) . ' critique' . ($criticalCount > 1 ? 's' : '') . '</span>'
                : '')
            . ($warningCount > 0
                ? '<span class="au-ab-panel-head__stat au-ab-panel-head__stat--warning">' . esc_html((string) $warningCount) . ' alerte' . ($warningCount > 1 ? 's' : '') . '</span>'
                : '')
            . '</span>'
            . '</span>';
    }

    /** @param array<string, mixed> $result */
    private function renderPanelItem(array $result, string $level, bool $featured): string
    {
        $title = (string) ($result['title'] ?? 'Contrôle');
        $category = (string) ($result['category'] ?? '');
        $message = $this->excerpt((string) ($result['message'] ?? ''), $featured ? 96 : 68);

        return '<span class="au-ab-panel-item__inner">'
            . ($featured ? '<span class="au-ab-panel-item__flag">Environnement / indexation</span>' : '')
            . '<span class="au-ab-panel-item__title">' . esc_html($title) . '</span>'
            . ($category !== '' ? '<span class="au-ab-panel-item__cat">' . esc_html($category) . '</span>' : '')
            . ($message !== '' ? '<span class="au-ab-panel-item__msg">' . esc_html($message) . '</span>' : '')
            . '</span>';
    }

    private function excerpt(string $text, int $max): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
        if ($text === '') {
            return '';
        }
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($text) <= $max) {
                return $text;
            }

            return rtrim(mb_substr($text, 0, $max - 1)) . '…';
        }

        if (strlen($text) <= $max) {
            return $text;
        }

        return rtrim(substr($text, 0, $max - 1)) . '…';
    }
}
