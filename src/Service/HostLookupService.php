<?php

namespace AkyosUpdates\Service;

/**
 * Détection de l'hébergeur du site et mentions légales associées.
 *
 * ponytail: heuristiques hostname / reverse DNS / en-têtes serveur + catalogue statique
 * des hébergeurs FR courants ; upgrade path = WHOIS/IP ou API dédiée.
 */
final class HostLookupService
{
    private const CACHE_KEY = 'akyos_updates_host_lookup';
    private const CACHE_TTL = HOUR_IN_SECONDS;

    /**
     * @var list<array{id: string, label: string, legal_host_name: string, legal_host_address: string, legal_host_phone: string, patterns: list<string>}>
     */
    private const KNOWN_HOSTS = [
        [
            'id' => 'ovh',
            'label' => 'OVH',
            'legal_host_name' => 'OVH SAS',
            'legal_host_address' => '2 rue Kellermann, 59100 Roubaix, France',
            'legal_host_phone' => '+33 9 72 10 10 07',
            'patterns' => ['/\bovh\b/i', '/\.ovh\./i', '/kimsufi/i', '/soyoustart/i', '/runabove/i'],
        ],
        [
            'id' => 'o2switch',
            'label' => 'o2switch',
            'legal_host_name' => 'o2switch',
            'legal_host_address' => '222-224 Boulevard Gustave Flaubert, 63000 Clermont-Ferrand, France',
            'legal_host_phone' => '+33 4 44 46 60 40',
            'patterns' => ['/\bo2switch\b/i', '/\.o2srv\.fr/i'],
        ],
        [
            'id' => 'ionos',
            'label' => 'IONOS',
            'legal_host_name' => 'IONOS SE',
            'legal_host_address' => '7 place de la Gare, 57200 Sarreguemines, France',
            'legal_host_phone' => '+33 3 87 99 24 00',
            'patterns' => ['/\bionos\b/i', '/\b1and1\b/i', '/\.kundenserver\./i'],
        ],
        [
            'id' => 'gandi',
            'label' => 'Gandi',
            'legal_host_name' => 'Gandi SAS',
            'legal_host_address' => '63-65 boulevard Masséna, 75013 Paris, France',
            'legal_host_phone' => '+33 1 70 37 76 61',
            'patterns' => ['/\bgandi\b/i', '/\.gandi\.net/i'],
        ],
        [
            'id' => 'scaleway',
            'label' => 'Scaleway',
            'legal_host_name' => 'Scaleway SAS',
            'legal_host_address' => '8 rue de la Ville l\'Évêque, 75008 Paris, France',
            'legal_host_phone' => '+33 1 84 13 00 00',
            'patterns' => ['/\bscaleway\b/i', '/\.scw\.cloud/i', '/\.online\.net/i', '/\.dedibox\./i'],
        ],
        [
            'id' => 'aws',
            'label' => 'Amazon Web Services',
            'legal_host_name' => 'Amazon Web Services EMEA SARL',
            'legal_host_address' => '38 avenue John F. Kennedy, L-1855 Luxembourg',
            'legal_host_phone' => '',
            'patterns' => ['/\bamazonaws\b/i', '/\.aws\.cloud/i', '/\.compute\.amazonaws\./i'],
        ],
        [
            'id' => 'cloudflare',
            'label' => 'Cloudflare',
            'legal_host_name' => 'Cloudflare Inc.',
            'legal_host_address' => '101 Townsend St, San Francisco, CA 94107, États-Unis',
            'legal_host_phone' => '',
            'patterns' => ['/\bcloudflare\b/i'],
        ],
        [
            'id' => 'infomaniak',
            'label' => 'Infomaniak',
            'legal_host_name' => 'Infomaniak Network SA',
            'legal_host_address' => 'Avenue de la Praille 26, 1227 Carouge, Suisse',
            'legal_host_phone' => '+41 22 820 35 44',
            'patterns' => ['/\binfomaniak\b/i'],
        ],
        [
            'id' => 'planethoster',
            'label' => 'PlanetHoster',
            'legal_host_name' => 'PlanetHoster Inc.',
            'legal_host_address' => '4416 Louis-B. Mayer, Laval, QC H7P 0G1, Canada',
            'legal_host_phone' => '+1 514 572 0861',
            'patterns' => ['/\bplanethoster\b/i', '/\.world\.secureserver\.net/i'],
        ],
        [
            'id' => 'wpengine',
            'label' => 'WP Engine',
            'legal_host_name' => 'WP Engine Inc.',
            'legal_host_address' => '504 Lavaca Street, Suite 1000, Austin, TX 78701, États-Unis',
            'legal_host_phone' => '',
            'patterns' => ['/\bwpengine\b/i', '/\.wpenginepowered\.com/i'],
        ],
    ];

    /**
     * @return array{found: bool, host: array<string, string>, message: string, detected_as: string}
     */
    public function detect(): array
    {
        $cached = get_transient(self::CACHE_KEY);
        if (is_array($cached)) {
            return $cached;
        }

        $signals = $this->collectSignals();
        if ($signals === []) {
            $result = [
                'found' => false,
                'host' => [],
                'message' => 'Impossible de collecter des indices sur l\'hébergeur (environnement local ?).',
                'detected_as' => '',
            ];
            set_transient(self::CACHE_KEY, $result, self::CACHE_TTL);

            return $result;
        }

        foreach (self::KNOWN_HOSTS as $known) {
            foreach ($known['patterns'] as $pattern) {
                foreach ($signals as $signal) {
                    if (@preg_match($pattern, $signal) === 1) {
                        $result = [
                            'found' => true,
                            'host' => [
                                'legal_host_name' => $known['legal_host_name'],
                                'legal_host_address' => $known['legal_host_address'],
                                'legal_host_phone' => $known['legal_host_phone'],
                            ],
                            'message' => 'Hébergeur détecté : ' . $known['label'] . '.',
                            'detected_as' => $known['id'],
                        ];
                        set_transient(self::CACHE_KEY, $result, self::CACHE_TTL);

                        return $result;
                    }
                }
            }
        }

        $hint = $this->shortSignalHint($signals);
        $result = [
            'found' => false,
            'host' => [],
            'message' => 'Hébergeur non reconnu'
                . ($hint !== '' ? ' (indices : ' . $hint . ')' : '')
                . '. Complète les champs manuellement.',
            'detected_as' => '',
        ];
        set_transient(self::CACHE_KEY, $result, self::CACHE_TTL);

        return $result;
    }

    /** @return list<string> */
    private function collectSignals(): array
    {
        $signals = [];

        $siteHost = (string) wp_parse_url(home_url(), PHP_URL_HOST);
        if ($siteHost !== '') {
            $signals[] = $siteHost;
            $ip = gethostbyname($siteHost);
            if ($ip !== '' && $ip !== $siteHost) {
                $signals[] = $ip;
                $reverse = gethostbyaddr($ip);
                if (is_string($reverse) && $reverse !== '' && $reverse !== $ip) {
                    $signals[] = $reverse;
                }
            }
        }

        foreach (['SERVER_NAME', 'HTTP_HOST', 'SERVER_SOFTWARE', 'DOCUMENT_ROOT'] as $key) {
            if (! empty($_SERVER[$key]) && is_string($_SERVER[$key])) {
                $signals[] = $_SERVER[$key];
            }
        }

        $response = wp_remote_head(home_url(), ['timeout' => 5, 'redirection' => 3]);
        if (! is_wp_error($response)) {
            $server = wp_remote_retrieve_header($response, 'server');
            if (is_string($server) && $server !== '') {
                $signals[] = $server;
            }
            $via = wp_remote_retrieve_header($response, 'via');
            if (is_string($via) && $via !== '') {
                $signals[] = $via;
            }
        }

        $unique = [];
        foreach ($signals as $signal) {
            $signal = trim($signal);
            if ($signal !== '' && ! in_array($signal, $unique, true)) {
                $unique[] = $signal;
            }
        }

        return $unique;
    }

    /** @param list<string> $signals */
    private function shortSignalHint(array $signals): string
    {
        $parts = [];
        foreach (array_slice($signals, 0, 2) as $signal) {
            if (strlen($signal) > 48) {
                $parts[] = substr($signal, 0, 45) . '…';
            } else {
                $parts[] = $signal;
            }
        }

        return implode(', ', $parts);
    }
}
