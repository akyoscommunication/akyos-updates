<?php

namespace AkyosUpdates\Service;

/**
 * Liaison site WordPress ↔ outil MAW (clé de pairing + clé API locale).
 */
final class LinkSettingsService
{
    public const OPTION_KEY = 'akyos_updates_link';

    /** @var array<string, mixed>|null */
    private ?array $cache = null;

    /** @return array{pairing_key: string, api_key: string, linked_at: string, link_error: string} */
    public function get(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $stored = get_option(self::OPTION_KEY, []);
        $this->cache = $this->normalize(is_array($stored) ? $stored : []);

        return $this->cache;
    }

    public function getApiKey(): string
    {
        return trim((string) ($this->get()['api_key'] ?? ''));
    }

    public function ensureApiKey(): string
    {
        $settings = $this->get();
        if ($settings['api_key'] !== '') {
            return $settings['api_key'];
        }

        $settings['api_key'] = wp_generate_password(48, false, false);
        $this->persist($settings);

        return $settings['api_key'];
    }

    public function isLinked(): bool
    {
        $settings = $this->get();

        return $settings['linked_at'] !== '';
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function save(array $input): array
    {
        $current = $this->get();
        $pairing = array_key_exists('pairing_key', $input)
            ? sanitize_text_field((string) $input['pairing_key'])
            : $current['pairing_key'];

        $settings = $this->normalize([
            'pairing_key' => $pairing,
            'api_key' => $current['api_key'] !== '' ? $current['api_key'] : '',
            'linked_at' => $current['linked_at'],
            'link_error' => $current['link_error'],
        ]);

        if ($settings['api_key'] === '') {
            $settings['api_key'] = wp_generate_password(48, false, false);
        }

        return $this->persist($settings);
    }

    public function markLinked(): array
    {
        $settings = $this->get();
        $settings['linked_at'] = gmdate('c');
        $settings['link_error'] = '';

        return $this->persist($settings);
    }

    public function markLinkError(string $message): array
    {
        $settings = $this->get();
        $settings['link_error'] = sanitize_text_field($message);

        return $this->persist($settings);
    }

    public function regenerateApiKey(): array
    {
        $settings = $this->get();
        $settings['api_key'] = wp_generate_password(48, false, false);
        $settings['linked_at'] = '';
        $settings['link_error'] = '';

        return $this->persist($settings);
    }

    /** @return array<string, mixed> */
    public function publicView(): array
    {
        $settings = $this->get();
        $apiKey = (string) ($settings['api_key'] ?? '');

        return [
            'pairing_key' => (string) ($settings['pairing_key'] ?? ''),
            'api_key' => $apiKey,
            'api_key_preview' => $this->maskKey($apiKey),
            'linked' => $this->isLinked(),
            'linked_at' => (string) ($settings['linked_at'] ?? ''),
            'link_error' => (string) ($settings['link_error'] ?? ''),
            'configured' => $apiKey !== '' || CrmApiAuthService::isConfigured(),
        ];
    }

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'pairing_key' => '',
            'api_key' => '',
            'linked_at' => '',
            'link_error' => '',
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function normalize(array $input): array
    {
        $defaults = self::defaults();

        return [
            'pairing_key' => isset($input['pairing_key']) ? sanitize_text_field((string) $input['pairing_key']) : $defaults['pairing_key'],
            'api_key' => isset($input['api_key']) ? sanitize_text_field((string) $input['api_key']) : $defaults['api_key'],
            'linked_at' => isset($input['linked_at']) ? sanitize_text_field((string) $input['linked_at']) : $defaults['linked_at'],
            'link_error' => isset($input['link_error']) ? sanitize_text_field((string) $input['link_error']) : $defaults['link_error'],
        ];
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    private function persist(array $settings): array
    {
        $normalized = $this->normalize($settings);
        update_option(self::OPTION_KEY, $normalized, false);
        $this->cache = $normalized;

        return $normalized;
    }

    private function maskKey(string $key): string
    {
        $key = trim($key);
        if ($key === '') {
            return '';
        }
        if (strlen($key) <= 8) {
            return str_repeat('•', strlen($key));
        }

        return substr($key, 0, 4) . str_repeat('•', max(8, strlen($key) - 8)) . substr($key, -4);
    }
}
