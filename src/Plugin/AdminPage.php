<?php

namespace AkyosUpdates\Plugin;

use AkyosUpdates\Core\Maintenance;
use AkyosUpdates\Service\RgpdSettingsService;
use AkyosUpdates\Service\LinkSettingsService;

final class AdminPage
{
    private string $hookSuffix = '';
    /** @var array<string, bool> */
    private array $moduleHandles = [];

    private ?string $reactRefreshPreambleBase = null;

    public function __construct(
        private Maintenance $analyzer,
        private ?RgpdSettingsService $rgpdSettings = null,
        private ?LinkSettingsService $linkSettings = null
    ) {
    }

    public function register(): void
    {
        $this->hookSuffix = add_menu_page(
            'Akyos Updates',
            'Akyos Updates',
            'manage_options',
            'akyos-updates',
            [$this, 'render'],
            'dashicons-superhero-alt',
            58
        );

        add_action('admin_print_footer_scripts', [$this, 'printReactRefreshPreamble'], 1);
    }

    public function render(): void
    {
        echo '<div id="akyos-updates-admin-app" data-logo-url="' . esc_attr(AKYOS_UPDATES_PLUGIN_URL . 'assets/images/logo-akyos.png') . '"></div>';
    }

    public function enqueue(string $hook): void
    {
        if ($hook !== $this->hookSuffix) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script('wp-api-fetch');

        $bootstrap = [
            'restUrl' => esc_url_raw(rest_url('akyos-updates/v1/')),
            'nonce' => wp_create_nonce('wp_rest'),
            'overview' => $this->analyzer->getSiteOverview(),
            'reportCategories' => Maintenance::knownReportCategories(),
            'logoUrl' => AKYOS_UPDATES_PLUGIN_URL . 'assets/images/logo-akyos.png',
            'restDebug' => (defined('WP_DEBUG') && WP_DEBUG)
                || (defined('AKYOS_UPDATES_REST_DEBUG') && constant('AKYOS_UPDATES_REST_DEBUG')),
            'i18n' => [
                'startAnalysis' => 'Lancer l’analyse',
                'analyzing' => 'Analyse en cours',
            ],
            'rgpd' => [
                'settings' => $this->rgpdSettings ? $this->rgpdSettings->get() : RgpdSettingsService::defaults(),
                'serviceTypes' => RgpdSettingsService::serviceTypes(),
            ],
            'link' => $this->linkSettings
                ? $this->linkSettings->publicView()
                : (new LinkSettingsService())->publicView(),
        ];

        $devServer = 'http://127.0.0.1:5173';
        $isDev = $this->isViteServerRunning($devServer);

        if ($isDev) {
            $this->reactRefreshPreambleBase = $devServer;

            wp_register_script('akyos-updates-admin-vite-client', $devServer . '/@vite/client', [], null, true);
            $this->moduleHandles['akyos-updates-admin-vite-client'] = true;
            wp_enqueue_script('akyos-updates-admin-vite-client');

            wp_register_script('akyos-updates-admin', $devServer . '/src/main.jsx', ['akyos-updates-admin-vite-client'], null, true);
            $this->moduleHandles['akyos-updates-admin'] = true;
            wp_add_inline_script('akyos-updates-admin', 'window.AKYOS_UPDATES_BOOTSTRAP = ' . wp_json_encode($bootstrap) . ';', 'before');
            wp_enqueue_script('akyos-updates-admin');
            add_filter('script_loader_tag', [$this, 'forceModuleScriptTag'], 10, 3);
            return;
        }

        $this->reactRefreshPreambleBase = null;

        wp_enqueue_style('akyos-updates-admin', AKYOS_UPDATES_PLUGIN_URL . 'assets/admin/build/index.css', [], AKYOS_UPDATES_VERSION);
        wp_register_script('akyos-updates-admin', AKYOS_UPDATES_PLUGIN_URL . 'assets/admin/build/index.js', [], AKYOS_UPDATES_VERSION, true);
        $this->moduleHandles['akyos-updates-admin'] = true;
        wp_add_inline_script('akyos-updates-admin', 'window.AKYOS_UPDATES_BOOTSTRAP = ' . wp_json_encode($bootstrap) . ';', 'before');
        wp_enqueue_script('akyos-updates-admin');
        add_filter('script_loader_tag', [$this, 'forceModuleScriptTag'], 10, 3);
    }

    private function isViteServerRunning(string $devServer): bool
    {
        $response = wp_remote_get($devServer . '/@vite/client', [
            'timeout' => 0.3,
            'redirection' => 0,
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $status = wp_remote_retrieve_response_code($response);
        return $status >= 200 && $status < 300;
    }

    public function forceModuleScriptTag(string $tag, string $handle, string $src): string
    {
        if (!isset($this->moduleHandles[$handle])) {
            return $tag;
        }

        return sprintf('<script type="module" src="%s"></script>', esc_url($src));
    }

    public function printReactRefreshPreamble(): void
    {
        if ($this->reactRefreshPreambleBase === null) {
            return;
        }

        $base = esc_url($this->reactRefreshPreambleBase);

        printf(
            '<script type="module">import { injectIntoGlobalHook } from "%s/@react-refresh";injectIntoGlobalHook(window);window.$RefreshReg$=()=>{};window.$RefreshSig$=()=>(type)=>type;</script>',
            $base
        );
    }
}
