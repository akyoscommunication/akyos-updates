<?php

namespace AkyosUpdates\Core\Checks\BackOffice;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\BrandaService;

final class BrandaSmtpCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'backoffice.branda_smtp';
    }

    public function getCategory(): string
    {
        return 'Back-office';
    }

    public function getTitle(): string
    {
        return 'SMTP';
    }
    public function getSuccessMessage(): string
    {
        return 'SMTP Branda mis à jour.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $state = BrandaService::getSmtpState();

        if (!$state['brandaActive']) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Branda n’est pas actif : SMTP non vérifiable.',
                false,
                null,
                $state
            );
        }

        if (!$state['smtpModuleActive']) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Module SMTP Branda désactivé. Enregistrez la configuration pour l’activer.',
                true,
                'backoffice.branda_save_smtp',
                $state
            );
        }

        if (!$state['configured']) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'SMTP incomplet (expéditeur, hôte, port, identifiants si auth).',
                true,
                'backoffice.branda_save_smtp',
                $state
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'ok',
            'success',
            'SMTP Branda configuré.',
            true,
            'backoffice.branda_save_smtp',
            $state
        );
    }
}
