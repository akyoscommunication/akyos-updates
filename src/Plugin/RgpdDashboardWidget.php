<?php

namespace AkyosUpdates\Plugin;

use AkyosUpdates\Service\LinkSettingsService;

/**
 * Widget « Support Akyos » du tableau de bord WordPress, affichant la consommation
 * du forfait de maintenance via MAW (site lié par clé API).
 */
final class RgpdDashboardWidget
{
    public function __construct(private LinkSettingsService $link)
    {
    }

    public function register(): void
    {
        add_action('wp_dashboard_setup', [$this, 'addWidget']);
    }

    public function addWidget(): void
    {
        wp_add_dashboard_widget(
            'akyos_updates_support_widget',
            'Support Akyos — Nous sommes disponibles en cas de problème',
            [$this, 'render']
        );
    }

    public function render(): void
    {
        $link = $this->link->publicView();
        $data = $link['linked'] ? $this->fetchMaintenance((string) ($link['api_key'] ?? '')) : null;
        $remainingHours = is_array($data) && isset($data['remaining_hours']) ? (float) $data['remaining_hours'] : null;
        $remainingTime = is_array($data) && isset($data['remaining_time']) ? (string) $data['remaining_time'] : '';

        $status = 'danger';
        if ($remainingHours !== null) {
            if ($remainingHours >= 5) {
                $status = 'success';
            } elseif ($remainingHours >= 3) {
                $status = 'warning';
            }
        }
        ?>
        <div class="plan">
            <h3>Consommation de votre forfait de maintenance</h3>
            <?php if ($link['linked'] && $remainingHours !== null) : ?>
                <div class="progress">
                    <div class="progress-bar progress-bar-<?php echo esc_attr($status); ?>" role="progressbar"
                         aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%">
                        <?php echo esc_html($remainingTime); ?> restante(s)
                    </div>
                </div>
            <?php else : ?>
                <b><em>Liez ce site à MAW depuis Akyos Updates (Maintenance → Connexion MAW) pour afficher le suivi forfait.</em></b>
            <?php endif; ?>
        </div>
        <div class="footer">
            <p>Si vous avez un problème, besoin d'aide ou pour toute autre demande, vous pouvez utiliser notre
                <a href="https://support.akyos.com" target="_blank" rel="noopener">formulaire de support</a>,
                nous contacter par mail (<a href="mailto:info@akyos.com">info@akyos.com</a>)
                ou encore par téléphone au 03 80 10 23 57.</p>
            <p>Cordialement, L'équipe Akyos.</p>
        </div>
        <?php
    }

    /** @return array<string, mixed>|null */
    private function fetchMaintenance(string $apiKey): ?array
    {
        if ($apiKey === '') {
            return null;
        }

        $url = (string) apply_filters(
            'akyos_updates_maw_maintenance_url',
            'https://mon-agence-web.io/api/akyos-updates/maintenance'
        );

        $response = wp_remote_get($url, [
            'timeout' => 5,
            'headers' => [
                'X-Akyos-Api-Key' => $apiKey,
            ],
        ]);
        if (is_wp_error($response)) {
            return null;
        }

        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);

        return is_array($decoded) ? $decoded : null;
    }
}
