<?php

namespace AkyosUpdates\Service;


use Core_Upgrader;
use AkyosUpdates\Attribute\Hook;
use AkyosUpdates\Trait\ServiceTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class HostingService
{
    use ServiceTrait;

    public function __construct(
        private PathService $pathService,
    ) {
        $this->redirectRoute = 'akyos_updates_hosting';
    }

    public function getPhpVersion(): array
    {
        $phpVersion = phpversion();

        if (version_compare($phpVersion, '8.3.0', '<=')) {
            $phpVersionMessage = "<p>⭕ La version de PHP n'est pas à jour : $phpVersion</p>";
        } else {
            $phpVersionMessage = "<p>✅ La version de PHP est à jour : $phpVersion</p>";
        }

        return [
            'message' => $phpVersionMessage
        ];
    }

    public function getWordpressVersion(): array
    {
        $wordpressVersion = get_bloginfo('version');
        $updates = get_core_updates();

        if (! empty($updates) && ! is_wp_error($updates)) {
            $update = $updates[0];

            if ('upgrade' === $update->response) {
                $wordpressVersionMessage = "<p>⭕ WordPress n'est pas à jour. <br/>Version actuelle : $wordpressVersion, dernière : " . $update->current . "</p>";
            } else {
                $wordpressVersionMessage = "<p>✅ WordPress est à jour. Version : $wordpressVersion</p>";
            }
        } else {
            $wordpressVersionMessage = "<p>⭕ Impossible de déterminer l'état des mises à jour.</p>";
        }

        $versions = [];
        $wordpressVersionsApi = 'https://api.wordpress.org/core/version-check/1.7/';
        $response = wp_remote_get($wordpressVersionsApi);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        $dataVersions = $data['offers'];

        foreach ($dataVersions as $version) {
            $versions[] = $version['version'];
        }

        $versions = array_unique($versions);
        $versions = array_slice($versions, 0, 5);

        return [
            'message' => $wordpressVersionMessage,
            'action_required' => true,
            'fields' => [
                [
                    'label' => 'Sélectionner une version de Wordpress',
                    'name' => 'wordpress_version',
                    'value' => $versions,
                    'type' => 'select',
                    'required' => true
                ],
            ],
            'ajax' => [
                'hook' => 'admin_post_akyos_updates_wordpress_update',
                'success_message' => 'WordPress se met à jour en arrière plan.',
                'error_message' => 'Une erreur est survenue lors de la mise à jour de WordPress.',
            ]
        ];
    }

    #[Hook(hook: 'admin_post_akyos_updates_wordpress_update')]
    public function updateWordpress()
    {
        delete_transient('akyos_wp_update_status');
        $request = Request::createFromGlobals();

        $wordpressVersion = $request->get('wordpress_version');
        $wp_root = $this->pathService->getRootPath();
        $log_file = $this->pathService->getAppPath() . '/akyos-updates-debug.log';

        if (!function_exists('exec') && !function_exists('popen')) {
            error_log('Akyos Updates: exec and popen functions are disabled on this server.');
        }

        if (file_exists($log_file)) {
            unlink($log_file); 
        }

        $wp_cli_path = $this->pathService->getRootPath() . '/wp-cli.phar';
        if (!file_exists($wp_cli_path)) {
            $response = wp_remote_get('https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar');
            file_put_contents($wp_cli_path, $response['body']);
            chmod($wp_cli_path, 0755);
        }

        set_transient('akyos_wp_update_status', [
            'status' => 'pending',
            'target_version' => $wordpressVersion,
            'start_time' => time(),
            'log_file' => $log_file
        ], HOUR_IN_SECONDS);

        $update_command_payload = "php " . $wp_cli_path . " core update --version=$wordpressVersion --force";

        $update_command = sprintf(
            "%s >> %s 2>&1 && echo 'AKYOS_UPDATE_SUCCESS_MARKER' >> %s || echo 'AKYOS_UPDATE_FAILURE_MARKER' >> %s",
            $update_command_payload,
            escapeshellarg($log_file),
            escapeshellarg($log_file),
            escapeshellarg($log_file)
        );

        $full_command = sprintf(
            "(%s) &",
            $update_command
        );

        $handle = popen($full_command, 'r');
        if ($handle) {
            pclose($handle);
        }

        return wp_redirect(admin_url('admin.php?page=' . $this->redirectRoute));
    }

    // Function to display admin notices for WordPress update status
    #[Hook(hook: 'admin_notices')]
    function akyos_updates_show_wp_update_status_notice()
    {
        $transient_name = 'akyos_wp_update_status';
        $update_status_data = get_transient($transient_name);

        if (!$update_status_data) {
            return; // No update process tracked
        }

        $status = $update_status_data['status'] ?? 'unknown';
        $target_version = $update_status_data['target_version'] ?? 'N/A';
        $start_time = $update_status_data['start_time'] ?? 0;
        $log_file = $update_status_data['log_file'] ?? null; // Get log file from transient

        $message = '';
        $message_type = 'info'; // Default message type
        $delete_transient = false;
        $notice_displayed = false;

        if ($status === 'pending') {
            if ($log_file && file_exists($log_file)) {
                $log_content = file_get_contents($log_file);
                if ($log_content === false) {
                    $log_content = ''; // Handle error if file_get_contents fails
                }

                if (strpos($log_content, 'AKYOS_UPDATE_SUCCESS_MARKER') !== false) {
                    $current_wp_version = get_bloginfo('version');
                    // Double check with current version for extra safety, though marker should be reliable
                    if (version_compare($current_wp_version, $target_version, '>=')) {
                        $message = sprintf(
                            __('WordPress update to version %s is complete!', 'akyos-updates'),
                            esc_html($target_version)
                        );
                        $message_type = 'success';
                        unlink($this->pathService->getRootPath() . '/wp-cli.phar');
                    } else {
                        // Success marker found, but version mismatch. This is unusual.
                        $message = sprintf(
                            __('WordPress update to version %s has finished, but the version seems unchanged. Please verify. Log shows success.', 'akyos-updates'),
                            esc_html($target_version)
                        );
                        $message_type = 'warning';
                    }
                    $delete_transient = true;
                    $notice_displayed = true;
                } elseif (strpos($log_content, 'AKYOS_UPDATE_FAILURE_MARKER') !== false) {
                    $message = sprintf(
                        __('WordPress update to version %s failed. Please check the <a href="%s" target="_blank">log file</a> for details.', 'akyos-updates'),
                        esc_html($target_version),
                        esc_url(content_url(str_replace(WP_CONTENT_DIR, '', $log_file))) // Make log file link accessible if possible
                    );
                    $message_type = 'error';
                    $delete_transient = true;
                    $notice_displayed = true;
                }
            }

            // Timeout check if no markers found yet
            if (!$notice_displayed && (time() - $start_time > 600)) { // 10 minutes timeout
                $log_file_link_text = '';
                if ($log_file) {
                    $log_file_link_text = sprintf(
                        '<a href="%s" target="_blank">%s</a>',
                        esc_url(content_url(str_replace(WP_CONTENT_DIR, '', $log_file))),
                        __('log file', 'akyos-updates')
                    );
                }
                $message = sprintf(
                    __('The WordPress update to version %s has been running for a while and might have timed out or encountered an issue. Please check the %s.', 'akyos-updates'),
                    esc_html($target_version),
                    $log_file_link_text
                );
                $message_type = 'warning';
                $delete_transient = true; // Assume failure after timeout to clear transient
                $notice_displayed = true;
            }

            // If still pending and not timed out, show "in progress"
            if (!$notice_displayed) {
                $message = sprintf(
                    __('WordPress update to version %s is currently in progress...', 'akyos-updates'),
                    esc_html($target_version)
                );
                $message_type = 'info';
            }
        }

        if (!empty($message)) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr($message_type),
                wp_kses_post($message) // Use wp_kses_post for messages with potential HTML (like links)
            );
        }

        if ($delete_transient) {
            delete_transient($transient_name);
        }
    }
}
