<?php

namespace AkyosUpdates\Core\Checks\Security;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\DefenderService;

final class DefenderFirewallTranslationsCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'security.defender_firewall_translations';
    }

    public function getCategory(): string
    {
        return 'Sécurité';
    }

    public function getTitle(): string
    {
        return 'Defender Firewall — messages (FR)';
    }
    public function getSuccessMessage(): string
    {
        return 'Messages firewall Defender mis à jour.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $state = DefenderService::getFirewallMessagesState();

        if (!($state['readable'] ?? false)) {
            $reason = (string) ($state['unavailableReason'] ?? '');
            $unreadableMessage =
                $reason === 'exception'
                ? 'Impossible de lire les messages firewall Defender (erreur technique, conteneur DI ou version du module). Vérifier la compatibilité Defender et les logs PHP.'
                : 'Messages firewall Defender non vérifiables tant que Defender n\'est pas actif.';

            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                $unreadableMessage,
                false,
                null,
                self::summarizeFirewallPayload($state)
            );
        }

        $needs = (bool) ($state['needsTranslation'] ?? false);
        $parts = [];
        if (($state['ipBan']['needsTranslation'] ?? false)) {
            $parts[] = 'blocage IP / pays';
        }
        if (($state['loginLockout']['needsTranslation'] ?? false)) {
            $parts[] = 'protection connexion';
        }
        if (($state['notfoundLockout']['needsTranslation'] ?? false)) {
            $parts[] = 'détection 404';
        }
        if (($state['userAgent']['needsTranslation'] ?? false)) {
            $parts[] = 'user-agent';
        }

        $detail = $needs && $parts !== []
            ? ' Textes par défaut (anglais) ou vides : ' . implode(', ', $parts) . '.'
            : '';

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            $needs ? 'warn' : 'ok',
            $needs ? 'warning' : 'success',
            $needs
            ? 'Au moins un message affiché aux visiteurs bloqués est encore le libellé anglais d\'origine ou vide.' . $detail
            : 'Les messages firewall affichés aux visiteurs correspondent aux libellés français attendus.',
            $needs,
            $needs ? 'security.defender_apply_firewall_translations' : null,
            self::summarizeFirewallPayload($state)
        );
    }

    public static function summarizeFirewallPayload(array $state): array
    {
        $out = [
            'readable' => (bool) ($state['readable'] ?? false),
            'fullyTranslated' => (bool) ($state['fullyTranslated'] ?? false),
            'needsTranslation' => (bool) ($state['needsTranslation'] ?? false),
            'ipBan' => [
                'needsTranslation' => (bool) (($state['ipBan']['needsTranslation'] ?? false)),
            ],
            'loginLockout' => [
                'needsTranslation' => (bool) (($state['loginLockout']['needsTranslation'] ?? false)),
            ],
            'notfoundLockout' => [
                'needsTranslation' => (bool) (($state['notfoundLockout']['needsTranslation'] ?? false)),
            ],
            'userAgent' => [
                'needsTranslation' => (bool) (($state['userAgent']['needsTranslation'] ?? false)),
            ],
        ];
        $reason = $state['unavailableReason'] ?? null;
        if (is_string($reason) && $reason !== '') {
            $out['unavailableReason'] = $reason;
        }

        return $out;
    }
}
