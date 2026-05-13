<?php

namespace AkyosUpdates\Core\Checks\BackOffice;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\BrandaService;

final class BrandaDashboardWidgetsCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'backoffice.branda_dashboard_widgets';
    }

    public function getCategory(): string
    {
        return 'Back-office';
    }

    public function getTitle(): string
    {
        return 'Widgets tableau de bord';
    }
    public function getSuccessMessage(): string
    {
        return 'Réglages widgets tableau de bord Branda mis à jour.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $state = BrandaService::getDashboardWidgetsState();

        if (!$state['brandaActive']) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Branda n’est pas actif.',
                false,
                null,
                $state
            );
        }

        if (!$state['moduleActive']) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Module Branda « Dashboard widgets » désactivé.',
                true,
                'backoffice.branda_enable_dashboard_widgets',
                $state
            );
        }

        if ($state['availableCount'] === 0) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Liste des widgets vide. Ouvrez le tableau de bord WP une fois puis relancez l’analyse.',
                false,
                null,
                $state
            );
        }

        if (!$state['allHidden']) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                sprintf(
                    'Masquage partiel : %d / %d widgets masqués.',
                    (int) $state['hiddenCount'],
                    (int) $state['availableCount']
                ),
                true,
                'backoffice.branda_hide_dashboard_widgets',
                $state
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'ok',
            'success',
            'Tous les widgets du tableau de bord sont masqués via Branda.',
            true,
            'backoffice.branda_hide_dashboard_widgets',
            $state
        );
    }
}
