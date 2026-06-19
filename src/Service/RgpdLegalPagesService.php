<?php

namespace AkyosUpdates\Service;

use WP_Post;

/**
 * Génère / met à jour les pages légales (politique de confidentialité, charte cookies,
 * mentions légales) à partir des réglages RGPD.
 *
 * Comportement « page cible » : si une page existante est sélectionnée dans les réglages
 * (page_privacy_id / page_cookies_id / page_mentions_id), son contenu est écrasé via
 * wp_update_post ; sinon une nouvelle page est créée. Le post_content est systématiquement
 * passé par wp_slash() avant insertion (sinon WordPress dé-slash et corrompt les quotes/HTML).
 */
final class RgpdLegalPagesService
{
    public const KIND_PRIVACY = 'privacy';
    public const KIND_COOKIES = 'cookies';
    public const KIND_MENTIONS = 'mentions';

    private const TITLES = [
        self::KIND_PRIVACY => 'Politique de conservation de données',
        self::KIND_COOKIES => 'Utilisation des cookies',
        self::KIND_MENTIONS => 'Mentions légales',
    ];

    private const SETTING_PAGE_ID = [
        self::KIND_PRIVACY => 'page_privacy_id',
        self::KIND_COOKIES => 'page_cookies_id',
        self::KIND_MENTIONS => 'page_mentions_id',
    ];

    public function __construct(private RgpdSettingsService $settings)
    {
    }

    /** @return list<string> */
    public static function kinds(): array
    {
        return [self::KIND_PRIVACY, self::KIND_COOKIES, self::KIND_MENTIONS];
    }

    /**
     * Génère/met à jour les pages demandées.
     *
     * @param list<string> $kinds Sous-ensemble de self::kinds() ; vide = toutes.
     * @return array{results: list<array<string, mixed>>, settings: array<string, mixed>}
     */
    public function generate(array $kinds = []): array
    {
        $kinds = $kinds === [] ? self::kinds() : array_values(array_intersect(self::kinds(), $kinds));
        $settings = $this->settings->get();
        $results = [];
        $touchedSettings = false;

        foreach ($kinds as $kind) {
            $title = self::TITLES[$kind];
            $content = $this->buildContent($kind, $settings);
            $settingKey = self::SETTING_PAGE_ID[$kind];
            $targetId = (int) ($settings[$settingKey] ?? 0);

            $postId = $this->upsertPage($targetId, $title, $content);
            if ($postId <= 0) {
                $results[] = [
                    'kind' => $kind,
                    'success' => false,
                    'message' => sprintf('Échec de la création/mise à jour de « %s ».', $title),
                ];
                continue;
            }

            $noindexOk = SeoService::setPostNoindex($postId, true);

            // Mémorise la page cible pour les prochaines générations.
            if ($targetId !== $postId) {
                $settings[$settingKey] = $postId;
                $touchedSettings = true;
            }

            $results[] = [
                'kind' => $kind,
                'success' => true,
                'postId' => $postId,
                'title' => $title,
                'editUrl' => get_edit_post_link($postId, ''),
                'viewUrl' => get_permalink($postId) ?: '',
                'updated' => $targetId === $postId && $targetId > 0,
                'noindexApplied' => $noindexOk,
                'message' => sprintf(
                    '%s « %s »%s.',
                    ($targetId === $postId && $targetId > 0) ? 'Page mise à jour' : 'Page créée',
                    $title,
                    $noindexOk ? ' (noindex appliqué)' : ''
                ),
            ];
        }

        if ($touchedSettings) {
            $settings = $this->settings->save($settings);
        }

        return ['results' => $results, 'settings' => $settings];
    }

    private function upsertPage(int $targetId, string $title, string $content): int
    {
        $payload = [
            'post_title' => $title,
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_content' => wp_slash($content),
        ];

        if ($targetId > 0) {
            $existing = get_post($targetId);
            if ($existing instanceof WP_Post && $existing->post_type === 'page') {
                $payload['ID'] = $targetId;
                $updated = wp_update_post($payload, true);

                return is_wp_error($updated) ? 0 : (int) $updated;
            }
        }

        $created = wp_insert_post($payload, true);

        return is_wp_error($created) ? 0 : (int) $created;
    }

    /** @param array<string, mixed> $settings */
    private function buildContent(string $kind, array $settings): string
    {
        $siteName = esc_html((string) ($settings['site_name'] ?? ''));
        $address = esc_html((string) ($settings['address'] ?? ''));
        $mail = (string) ($settings['mail'] ?? '');
        $contact = esc_url((string) ($settings['contact_url'] ?? ''));
        $cookiesLink = esc_url($this->resolveCookiesLink($settings));

        if ($kind === self::KIND_MENTIONS) {
            return $this->buildMentions($settings, $siteName, $address, $mail, $contact);
        }
        if ($kind === self::KIND_COOKIES) {
            return $this->buildCookies($siteName, $contact);
        }

        return $this->buildPrivacy($siteName, $address, $mail, $contact, $cookiesLink);
    }

    /** @param array<string, mixed> $settings */
    private function resolveCookiesLink(array $settings): string
    {
        $cookiesId = (int) ($settings['page_cookies_id'] ?? 0);
        if ($cookiesId > 0) {
            $permalink = get_permalink($cookiesId);
            if (is_string($permalink) && $permalink !== '') {
                return $permalink;
            }
        }

        return home_url('/utilisation-des-cookies');
    }

    private function buildPrivacy(string $siteName, string $address, string $mail, string $contact, string $cookiesLink): string
    {
        $mailEsc = esc_attr($mail);
        $mailHtml = esc_html($mail);

        return <<<HTML
<p style="text-align: center;">&nbsp;</p>
<h2 class="title-primary">1. Généralités</h2>
{$siteName} ({$address}), en sa qualité de responsable du traitement, attache une grande importance à la protection et au respect de votre vie privée. La présente politique vise à vous informer de nos pratiques concernant la collecte, l'utilisation et le partage des informations que vous êtes amenés à nous fournir par le biais de notre site internet (le « Site »). {$siteName} sera désignée par la suite en tant que « {$siteName} », « nous » ou « notre ».

Cette politique (ainsi que nos Conditions générales d'utilisation et tout document auquel il y est fait référence ainsi que notre Charte sur les Cookies) présente la manière dont nous traitons les données personnelles que nous recueillons et que vous nous fournissez. Nous vous invitons à lire attentivement le présent document pour connaître et comprendre nos pratiques quant aux traitements de vos données personnelles que nous mettons en œuvre.
<p style="text-align: center;">&nbsp;</p>
<h2 class="title-primary">2. Les informations que nous recueillons</h2>
Nous sommes susceptibles de recueillir et de traiter les données suivantes :
<p style="text-align: center;">&nbsp;</p>
<h3 class="subtitle-primary">2.1. Les informations que vous nous transmettez directement</h3>
En utilisant notre Site, vous êtes amenés à nous transmettre des informations, dont certaines sont de nature à vous identifier (« Données Personnelles »). C'est notamment le cas lorsque vous remplissez des formulaires, lorsque vous nous contactez – que ce soit par téléphone, email ou tout autre moyen de communication – ou lorsque vous nous faites part d'un problème.

Ces informations contiennent notamment les données suivantes :

<b>2.1.1.</b> Les données nécessaires à l'utilisation des services que nous fournissons sur notre Site ou à l'accès à tout autre service fournis par nous. Ces données sont notamment vos nom et prénom, adresse e-mail et numéro de téléphone ;

<b>2.1.2.</b> Une adresse postale ;

<b>2.1.3.</b> Une copie de tous les échanges intervenus entre vous et {$siteName} ;

<b>2.1.4.</b> Les données que nous pouvons vous demander de fournir lorsque vous nous signalez un problème relatif à nos services, comme par exemple l'objet de votre demande d'assistance ;
<p style="text-align: center;">&nbsp;</p>
<h3 class="subtitle-primary">2.2. Les données que nous recueillons automatiquement</h3>
<b>2.2.1.</b> Dans le cas où vous vous connectez à nos services en utilisant les fonctionnalités de réseaux sociaux mises à votre disposition, {$siteName} sera susceptibles de recueillir certaines de vos Données Personnelles lorsque vous interagissez avec des fonctionnalités de ces réseaux sociaux, tel que les fonctionnalités « J'aime ».

<b>2.2.2.</b> Lors de chacune de vos visites, nous sommes susceptibles de recueillir, conformément à la législation applicable et avec votre accord, le cas échéant, des informations relatives aux appareils sur lesquels vous utilisez nos services ou aux réseaux depuis lesquels vous accédez à nos services. Parmi les technologies utilisées pour recueillir ces informations, nous avons notamment recours aux cookies (pour en savoir plus à ce sujet, veuillez vous référer à notre <a href="{$cookiesLink}">Charte sur les Cookies</a>).
<p style="text-align: center;">&nbsp;</p>
<h3 class="subtitle-primary">2.3. Durée de conservation de vos données</h3>
<b>2.3.1.</b> Vos Données sont archivées à l'issue d'une durée de 2 ans après votre dernière utilisation de notre Site.
<p style="text-align: center;">&nbsp;</p>
<h2 class="title-primary">3. Comment utilisons-nous les données que nous recueillons ?</h2>
Nous utilisons les données que nous recueillons afin de :

<b>3.1.</b> exécuter les contrats conclus entre vous et nous, et vous fournir les informations et services demandés ;

<b>3.2.</b> vous envoyer des renseignements sur nos services par e-mail ou tout autre moyen de communication ;

<b>3.3.</b> vous envoyer, conformément aux dispositions légales applicables et avec votre accord lorsque la législation l'exige, des messages marketing, publicitaires et promotionnels ;

<b>3.4.</b> afin de vous informer des modifications apportées à nos services ;

<b>3.5.</b> améliorer et optimiser notre Site ;

<b>3.6.</b> vous permettre d'utiliser les fonctionnalités interactives de nos services si vous le souhaitez ;
<p style="text-align: center;">&nbsp;</p>
<h2 class="title-primary">4. Cookies et technologies semblables</h2>
Pour en savoir plus, consultez notre <a href="{$cookiesLink}">Charte sur les Cookies</a>.
<p style="text-align: center;">&nbsp;</p>
<h2 class="title-primary">5. Modification de notre politique de confidentialité</h2>
Nous pouvons être amené à modifier occasionnellement la présente politique de confidentialité. Lorsque cela est nécessaire, nous vous en informerons et/ou solliciterons votre accord.
<p style="text-align: center;">&nbsp;</p>
<h2 class="title-primary">6. Contact</h2>
Pour toute question relative à la présente politique de confidentialité ou pour toute demande relative à vos données personnelles, vous pouvez nous contacter en :
<ul>
 	<li>adressant un email à notre délégué à la protection des données à l'adresse <a href="mailto:{$mailEsc}">{$mailHtml}</a></li>
 	<li>en remplissant ce <a href="{$contact}">formulaire en ligne</a></li>
 	<li>ou en nous adressant un courrier à l'adresse suivante : {$siteName} – A l'attention du Délégué à la Protection des Données – {$address}</li>
</ul>
HTML;
    }

    private function buildCookies(string $siteName, string $contact): string
    {
        return <<<HTML
{$siteName} (« nous », « nos » ou « notre ») utilise les cookies afin de vous proposer un service web amélioré et davantage personnalisé. À travers cette Charte d'utilisation des Cookies de {$siteName}, nous vous présentons comment et pourquoi nous utilisons des cookies sur ce site, en toute transparence.
<p style="text-align: center;">&nbsp;</p>
<h2 class="title-primary">Qu'est-ce qu'un cookie et à quoi sert-il ?</h2>
Un cookie est un petit fichier texte enregistré, et/ou lu par votre navigateur, sur le disque dur de votre terminal (PC, ordinateur portable ou smartphone, par exemple) et déposé par les sites internet que vous visitez. Selon leur fonction et le but de leur utilisation, les cookies sont classés parmi les catégories décrites ci-dessous et utilisées par {$siteName} sur ce site :

<span class="italic bold">Les cookies absolument nécessaires</span> vous permettent de vous déplacer sur le site et d'utiliser ses fonctionnalités de base. Ces cookies sont indispensables pour l'utilisation de ce site.

<span class="italic bold">Les cookies de fonctionnalité</span> sont utilisés pour vous reconnaître lorsque vous revenez sur notre site et nous permettent de vous proposer des fonctionnalités améliorées et davantage personnalisées.

<span class="italic bold">Les cookies d'analyse et de performance</span> nous permettent de reconnaître et de compter le nombre de visiteurs sur notre site et de collecter des informations sur la manière dont notre site est utilisé.

<span class="italic bold">Les cookies publicitaires ou de ciblage</span> enregistrent votre visite sur notre site, ainsi que les pages que vous avez consultées et les liens que vous avez suivis.
<p style="text-align: center;">&nbsp;</p>
<h2 class="title-primary">Que faire si vous ne souhaitez pas activer les cookies ?</h2>
Vous pouvez révoquer votre consentement à l'utilisation des cookies à tout moment en paramétrant votre navigateur internet. Pour en savoir plus vous pouvez suivre les liens référencés ci-dessous :
<ul>
 	<li><a href="https://support.microsoft.com/fr-fr/help/17442/windows-internet-explorer-delete-manage-cookies" target="_blank" rel="noopener">Microsoft Internet Explorer</a></li>
 	<li><a href="https://support.google.com/accounts/answer/61416?hl=fr" target="_blank" rel="noopener">Google Chrome</a></li>
 	<li><a href="https://support.apple.com/kb/PH19214?locale=fr_FR" target="_blank" rel="noopener">Safari</a></li>
 	<li><a href="https://support.mozilla.org/fr/kb/activer-desactiver-cookies-preferences" target="_blank" rel="noopener">Firefox</a></li>
 	<li><a href="https://help.opera.com/en/latest/web-preferences/#cookies" target="_blank" rel="noopener">Opera</a></li>
</ul>
<p style="text-align: center;">&nbsp;</p>
<h2 class="title-primary">Des questions concernant la Charte sur les Cookies de {$siteName} ?</h2>
Si vous avez des questions concernant la Charte sur les Cookies de {$siteName}, n'hésitez pas à nous contacter en utilisant le <a href="{$contact}">formulaire en ligne</a>.
HTML;
    }

    /** @param array<string, mixed> $settings */
    private function buildMentions(array $settings, string $siteName, string $address, string $mail, string $contact): string
    {
        $companyName = esc_html((string) ($settings['legal_company_name'] ?? ''));
        $legalForm = esc_html((string) ($settings['legal_form'] ?? ''));
        $capital = esc_html((string) ($settings['legal_capital'] ?? ''));
        $rcs = esc_html((string) ($settings['legal_rcs'] ?? ''));
        $siret = esc_html((string) ($settings['legal_siret'] ?? ''));
        $tva = esc_html((string) ($settings['legal_tva'] ?? ''));
        $director = esc_html((string) ($settings['legal_publication_director'] ?? ''));
        $hostName = esc_html((string) ($settings['legal_host_name'] ?? ''));
        $hostAddress = esc_html((string) ($settings['legal_host_address'] ?? ''));
        $hostPhone = esc_html((string) ($settings['legal_host_phone'] ?? ''));
        $mailEsc = esc_attr($mail);
        $mailHtml = esc_html($mail);

        return <<<HTML
<h2 class="title-primary">Éditeur du site</h2>
<ul>
    <li><strong>Nom du site :</strong> {$siteName}</li>
    <li><strong>Raison sociale :</strong> {$companyName}</li>
    <li><strong>Forme juridique :</strong> {$legalForm}</li>
    <li><strong>Capital social :</strong> {$capital}</li>
    <li><strong>Adresse :</strong> {$address}</li>
    <li><strong>E-mail :</strong> <a href="mailto:{$mailEsc}">{$mailHtml}</a></li>
    <li><strong>Contact :</strong> <a href="{$contact}">{$contact}</a></li>
    <li><strong>RCS :</strong> {$rcs}</li>
    <li><strong>SIRET :</strong> {$siret}</li>
    <li><strong>TVA intracommunautaire :</strong> {$tva}</li>
    <li><strong>Directeur de la publication :</strong> {$director}</li>
</ul>
<h2 class="title-primary">Hébergement</h2>
<ul>
    <li><strong>Hébergeur :</strong> {$hostName}</li>
    <li><strong>Adresse :</strong> {$hostAddress}</li>
    <li><strong>Téléphone :</strong> {$hostPhone}</li>
</ul>
HTML;
    }
}
