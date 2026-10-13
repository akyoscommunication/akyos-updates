<?php

namespace AkyosUpdates\Core\Checks\Seo;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\InstallationContextDetector;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\EnvironmentService;

final class SeoSiteIndexingCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'seo.site_indexing';
    }

    public function getCategory(): string
    {
        return 'SEO';
    }

    public function getTitle(): string
    {
        return 'Indexation du site';
    }

    public function getSuccessMessage(): string
    {
        return 'Le site en ligne est indexable par les moteurs de recherche.';
    }

    public static function normalizeStoredResult(array $result): array
    {
        if (($result['id'] ?? '') !== 'seo.site_indexing') {
            return $result;
        }

        return (new self())->run((new InstallationContextDetector())->detect())->toArray();
    }

    public function run(SiteContext $context): CheckResult
    {
        $projectRoot = $context->getProjectRootPath();
        $indexed = EnvironmentService::isSitePubliclyIndexed();
        $wpEnv = EnvironmentService::getWpEnv($projectRoot);
        $envKind = EnvironmentService::getEnvKind($projectRoot);
        $isProduction = $envKind === 'production';
        $indexingForcedOff = EnvironmentService::isIndexingForcedOff();
        $switch = EnvironmentService::resolveIndexingSwitch($projectRoot);

        $payload = [
            'indexed' => $indexed,
            'wpEnv' => $wpEnv,
            'envKind' => $envKind,
            'isProduction' => $isProduction,
            'indexingForcedOff' => $indexingForcedOff,
            'switchDisabled' => $switch['disabled'],
            'switchDisabledReason' => $switch['reason'],
            'switchHint' => $switch['hint'],
        ];

        if (! $isProduction) {
            $payload['envRisk'] = $indexed ? 'dev_indexed' : 'dev_environment';

            if ($indexed) {
                return new CheckResult(
                    $this->getId(),
                    $this->getCategory(),
                    $this->getTitle(),
                    'fail',
                    'high',
                    sprintf(
                        'Critique : le site est indexable en « %s ». Coupez l’indexation — ce n’est pas l’environnement de production.',
                        $wpEnv
                    ),
                    ! $switch['disabled'],
                    'seo.toggle_site_indexing',
                    $payload,
                );
            }

            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'fail',
                'high',
                sprintf(
                    'Environnement « %s » : vous n’êtes pas sur le site en ligne. L’indexation doit rester désactivée.',
                    $wpEnv
                ),
                ! $switch['disabled'],
                'seo.toggle_site_indexing',
                $payload,
            );
        }

        $payload['envRisk'] = $indexed ? 'live_ok' : 'live_not_indexed';

        if ($indexed) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'ok',
                'success',
                'Site en ligne (production) : indexation active pour les moteurs de recherche.',
                ! $switch['disabled'],
                'seo.toggle_site_indexing',
                $payload,
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'fail',
            'high',
            'Critique : site en ligne (production) mais non indexé — les moteurs de recherche sont dissuadés d’indexer le site.',
            ! $switch['disabled'],
            'seo.toggle_site_indexing',
            $payload,
        );
    }
}
