<?php

namespace AkyosUpdates\Service;

/**
 * Récupération des informations légales d'une entreprise française.
 *
 * Source : API officielle gratuite « Recherche d'entreprises » de la DINUM
 * (https://recherche-entreprises.api.gouv.fr), sans clé. societe.com n'expose pas
 * d'API publique gratuite : cette API gouvernementale fournit les mêmes données légales.
 * Le capital social et le RCS n'y figurent pas et restent à compléter manuellement.
 */
final class CompanyLookupService
{
    private const ENDPOINT = 'https://recherche-entreprises.api.gouv.fr/search';
    private const CACHE_PREFIX = 'akyos_updates_company_lookup_';
    private const CACHE_TTL = HOUR_IN_SECONDS;

    /** Catégories juridiques INSEE les plus courantes (niveau III). */
    private const LEGAL_FORMS = [
        '1000' => 'Entrepreneur individuel',
        '5202' => 'Société en nom collectif',
        '5410' => 'SARL d\'économie mixte',
        '5499' => 'Société à responsabilité limitée (SARL)',
        '5498' => 'SARL unipersonnelle (EURL)',
        '5505' => 'Société anonyme (SA)',
        '5510' => 'Société anonyme (SA)',
        '5599' => 'Société anonyme (SA)',
        '5710' => 'Société par actions simplifiée (SAS)',
        '5720' => 'Société par actions simplifiée unipersonnelle (SASU)',
        '5800' => 'Société européenne',
        '6540' => 'Société civile immobilière (SCI)',
        '6599' => 'Société civile',
        '9220' => 'Association déclarée',
    ];

    /**
     * @return array{found: bool, company: array<string, string>, message: string}
     */
    public function lookup(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return ['found' => false, 'company' => [], 'message' => 'Renseigne un SIRET, SIREN ou nom d\'entreprise.'];
        }

        $cacheKey = self::CACHE_PREFIX . md5($query);
        $cached = get_transient($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $url = add_query_arg(
            ['q' => $query, 'page' => 1, 'per_page' => 1],
            self::ENDPOINT
        );

        $response = wp_remote_get($url, [
            'timeout' => 8,
            'headers' => ['Accept' => 'application/json'],
        ]);

        if (is_wp_error($response)) {
            return ['found' => false, 'company' => [], 'message' => 'Annuaire entreprises injoignable : ' . $response->get_error_message()];
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 300) {
            return ['found' => false, 'company' => [], 'message' => 'Annuaire entreprises a répondu HTTP ' . $code . '.'];
        }

        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        $results = is_array($body['results'] ?? null) ? $body['results'] : [];
        if ($results === []) {
            $result = ['found' => false, 'company' => [], 'message' => 'Aucune entreprise trouvée pour « ' . $query .' ».'];
            set_transient($cacheKey, $result, self::CACHE_TTL);
            return $result;
        }

        $company = $this->mapResult($results[0]);
        $result = [
            'found' => true,
            'company' => $company,
            'message' => 'Entreprise trouvée : ' . ($company['legal_company_name'] ?: $query) . '.',
        ];
        set_transient($cacheKey, $result, self::CACHE_TTL);

        return $result;
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, string>
     */
    private function mapResult(array $result): array
    {
        $siren = isset($result['siren']) ? (string) $result['siren'] : '';
        $siege = is_array($result['siege'] ?? null) ? $result['siege'] : [];

        $companyName = (string) ($result['nom_raison_sociale'] ?? $result['nom_complet'] ?? '');
        $natureJuridique = (string) ($result['nature_juridique'] ?? '');
        $siret = (string) ($siege['siret'] ?? '');

        $director = '';
        $dirigeants = is_array($result['dirigeants'] ?? null) ? $result['dirigeants'] : [];
        if (isset($dirigeants[0]) && is_array($dirigeants[0])) {
            $prenom = trim((string) ($dirigeants[0]['prenoms'] ?? ''));
            $nom = trim((string) ($dirigeants[0]['nom'] ?? ''));
            $director = trim($prenom . ' ' . $nom);
        }

        return [
            'legal_company_name' => $companyName,
            'legal_form' => self::LEGAL_FORMS[$natureJuridique] ?? ($natureJuridique !== '' ? 'Code ' . $natureJuridique : ''),
            'legal_siret' => $siret,
            'legal_tva' => $this->computeFrenchVat($siren),
            'legal_publication_director' => $director,
            'address' => $this->formatAddress($siege),
        ];
    }

    /** @param array<string, mixed> $siege */
    private function formatAddress(array $siege): string
    {
        $full = trim((string) ($siege['adresse'] ?? ''));
        if ($full !== '') {
            return $full;
        }

        $line = trim(implode(' ', array_filter([
            (string) ($siege['numero_voie'] ?? ''),
            (string) ($siege['type_voie'] ?? ''),
            (string) ($siege['libelle_voie'] ?? ''),
        ])));
        $cityLine = trim((string) ($siege['code_postal'] ?? '') . ' ' . (string) ($siege['libelle_commune'] ?? ''));

        return trim($line . ($line !== '' && $cityLine !== '' ? ', ' : '') . $cityLine);
    }

    /** Calcule le n° de TVA intracommunautaire français à partir du SIREN. */
    private function computeFrenchVat(string $siren): string
    {
        $siren = preg_replace('/\D/', '', $siren) ?? '';
        if (strlen($siren) !== 9) {
            return '';
        }

        $key = (12 + 3 * ((int) $siren % 97)) % 97;

        return sprintf('FR%02d%s', $key, $siren);
    }
}
