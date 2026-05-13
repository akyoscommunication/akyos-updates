<?php

namespace AkyosUpdates\Core\Checks\BackOffice;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\BrandaService;

final class AdminLiteUserCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'backoffice.admin_lite_user';
    }

    public function getCategory(): string
    {
        return 'Back-office';
    }

    public function getTitle(): string
    {
        return 'Utilisateur « Admin lite »';
    }
    public function getSuccessMessage(): string
    {
        return 'Utilisateur admin lite créé.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $users = BrandaService::getUsersWithAdminLiteRole();
        $hasRole = get_role(BrandaService::ROLE_ADMIN_LITE) !== null;
        $count = count($users);

        $payload = [
            'hasRoleDefinition' => $hasRole,
            'userCount' => $count,
            'usersPreview' => array_slice(array_map(static function ($u): array {
                return [
                    'id' => (int) $u->ID,
                    'email' => (string) $u->user_email,
                    'name' => (string) $u->display_name,
                ];
            }, $users), 0, 5),
        ];

        if ($count > 0) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'ok',
                'success',
                sprintf('%d compte(s) avec le rôle admin lite.', $count),
                false,
                null,
                $payload
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'warn',
            'warning',
            $hasRole
                ? 'Aucun utilisateur avec le rôle admin lite.'
                : 'Le rôle admin lite sera créé à la première création d’utilisateur (équivalent éditeur).',
            true,
            'backoffice.create_admin_lite_user',
            $payload
        );
    }
}
