<?php

namespace AkyosUpdates\Service;

final class LoginTokenService
{
    public const QUERY_ARG = 'akyos_maw_login';
    public const TTL = 300;

    public function register(): void
    {
        add_action('init', [$this, 'maybeAuthenticate'], 1);
    }

    /**
     * @return array{url: string, expiresIn: int, expiresAt: string}
     */
    public function createLoginLink(?string $redirect = null): array
    {
        $token = bin2hex(random_bytes(32));
        $redirectTarget = $this->normalizeRedirect($redirect);

        set_transient($this->transientKey($token), [
            'redirect' => $redirectTarget,
        ], self::TTL);

        $identity = SiteIdentityService::collect();
        $loginBase = (string) ($identity['loginUrl'] ?? wp_login_url());

        return [
            'url' => add_query_arg(self::QUERY_ARG, $token, $loginBase),
            'expiresIn' => self::TTL,
            'expiresAt' => gmdate('c', time() + self::TTL),
        ];
    }

    public function maybeAuthenticate(): void
    {
        if (is_user_logged_in() || ! isset($_GET[self::QUERY_ARG])) {
            return;
        }

        $token = sanitize_text_field(wp_unslash((string) $_GET[self::QUERY_ARG]));
        if ($token === '' || ! preg_match('/^[a-f0-9]{64}$/', $token)) {
            return;
        }

        $payload = get_transient($this->transientKey($token));
        if (! is_array($payload)) {
            return;
        }

        delete_transient($this->transientKey($token));

        $userId = $this->resolveLoginUserId();
        if ($userId <= 0) {
            return;
        }

        wp_clear_auth_cookie();
        wp_set_auth_cookie($userId, false, is_ssl());
        wp_set_current_user($userId);

        $redirect = $this->normalizeRedirect($payload['redirect'] ?? null);
        wp_safe_redirect($redirect);
        exit;
    }

    private function resolveLoginUserId(): int
    {
        $users = get_users([
            'role' => 'administrator',
            'number' => 1,
            'orderby' => 'ID',
            'order' => 'ASC',
            'fields' => 'ID',
        ]);

        if ($users === []) {
            return 0;
        }

        return (int) $users[0];
    }

    private function normalizeRedirect(?string $redirect): string
    {
        $fallback = admin_url();
        if (! is_string($redirect) || trim($redirect) === '') {
            return $fallback;
        }

        $redirect = esc_url_raw($redirect);
        if ($redirect === '' || ! wp_validate_redirect($redirect, false)) {
            return $fallback;
        }

        return $redirect;
    }

    private function transientKey(string $token): string
    {
        return 'akyos_maw_login_' . $token;
    }
}
