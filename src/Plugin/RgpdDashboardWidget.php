<?php

namespace AkyosUpdates\Plugin;

use AkyosUpdates\Service\RgpdSettingsService;

/**
 * Widget « Support Akyos » du tableau de bord WordPress, affichant la consommation
 * du forfait de maintenance. Port de aky-gdpr/admin/inc/aky-widget-maintenance.php
 * (file_get_contents remplacé par wp_remote_get, identifiant client lu dans la nouvelle option).
 */
final class RgpdDashboardWidget
{
    private const ENDPOINT = 'https://mon-agence-web.io/api/getMaintenance/5bc46cc7828a97000f885600/';

    public function __construct(private RgpdSettingsService $settings)
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
        $idClient = (string) ($this->settings->get()['id_client'] ?? '');

        $data = $this->fetchMaintenance($idClient);
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
            <?php if ($idClient !== '' && $remainingHours !== null) : ?>
                <div class="progress">
                    <div class="progress-bar progress-bar-<?php echo esc_attr($status); ?>" role="progressbar"
                         aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%">
                        <?php echo esc_html($remainingTime); ?> restante(s)
                    </div>
                </div>
            <?php else : ?>
                <b><em>Veuillez renseigner votre identifiant client (onglet RGPD) afin de recevoir vos informations de suivi.</em></b>
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
    private function fetchMaintenance(string $idClient): ?array
    {
        if ($idClient === '') {
            return null;
        }

        $response = wp_remote_get(self::ENDPOINT . rawurlencode($idClient), ['timeout' => 5]);
        if (is_wp_error($response)) {
            return null;
        }

        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);

        return is_array($decoded) ? $decoded : null;
    }
}
