<?php

namespace AkyosUpdates\Core\Checks\Security;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\DefenderService;

final class DefenderSecurityHeadersCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'security.defender_security_headers';
    }

    public function getCategory(): string
    {
        return 'Sécurité';
    }

    public function getTitle(): string
    {
        return 'Defender Security Headers';
    }
    public function getSuccessMessage(): string
    {
        return 'Configuration Defender Security Headers mise à jour.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $headers = DefenderService::getSecurityHeadersState();
        $canUse = DefenderService::isPluginActive() && DefenderService::canUseApi();

        if (!$canUse) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Security Headers non vérifiables tant que Defender n\'est pas actif.',
                false,
                null,
                $headers
            );
        }

        $missing = array_values(array_filter([
            !$headers['xFrameOptions'] ? 'X-Frame-Options' : null,
            !$headers['xXssProtection'] ? 'X-XSS-Protection' : null,
            !$headers['xContentTypeOptions'] ? 'X-Content-Type-Options' : null,
            !$headers['strictTransport'] ? 'Strict Transport' : null,
        ]));

        if ($missing === []) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'ok',
                'success',
                'Tous les headers sécurité demandés sont actifs.',
                false,
                null,
                $headers
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'warn',
            'warning',
            sprintf('%d header(s) manquant(s): %s.', count($missing), implode(', ', $missing)),
            true,
            'security.defender_enable_security_headers',
            array_merge($headers, ['missing' => $missing])
        );
    }
}
